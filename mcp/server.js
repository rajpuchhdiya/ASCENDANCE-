#!/usr/bin/env node
/* Ascendance Strategies design system — MCP server.
   Exposes tokens, page map, invariants, voice rules and the deployment guide
   so an AI coding tool building the WordPress theme reads the real system
   instead of guessing. Read-only: no tool here writes to your repo. */

import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import { ListResourcesRequestSchema, ReadResourceRequestSchema, ListToolsRequestSchema, CallToolRequestSchema } from "@modelcontextprotocol/sdk/types.js";
import { readFileSync, existsSync } from "node:fs";
import { dirname, join } from "node:path";
import { fileURLToPath } from "node:url";
import { TOKENS, REGISTERS, PAGE_MAP, INVARIANTS, VOICE, DEPENDENCIES } from "./design-system.js";

const HERE = dirname(fileURLToPath(import.meta.url));
const ROOT = process.env.ASCENDANCE_ROOT || join(HERE, "..");
const doc = (name) => { const p = join(ROOT, name); return existsSync(p) ? readFileSync(p, "utf8") : `Not found: ${name}. Set ASCENDANCE_ROOT to the repository root.`; };
const json = (o) => JSON.stringify(o, null, 2);

const RESOURCES = [
  { uri: "ascendance://tokens", name: "Design tokens", description: "Colour, type and geometry tokens with their light and dark values, and the rule each one exists to enforce.", mimeType: "application/json", body: () => json(TOKENS) },
  { uri: "ascendance://registers", name: "Visual registers", description: "Editorial vs Data Terminal: which pages belong to which, and why they must not converge.", mimeType: "application/json", body: () => json(REGISTERS) },
  { uri: "ascendance://pages", name: "Page map", description: "Destination URL to source file, with per-page migration notes.", mimeType: "application/json", body: () => json(PAGE_MAP) },
  { uri: "ascendance://invariants", name: "Invariants", description: "Rules that have been broken and caught in review. Check these after any sweep.", mimeType: "application/json", body: () => json(INVARIANTS) },
  { uri: "ascendance://voice", name: "Voice and copy rules", description: "Register, confidence language, and the banned-phrase list.", mimeType: "application/json", body: () => json(VOICE) },
  { uri: "ascendance://dependencies", name: "Third-party dependencies", description: "External origins, what each is used for, and the risk attached.", mimeType: "application/json", body: () => json(DEPENDENCIES) },
  { uri: "ascendance://doc/deployment", name: "DEPLOYMENT.md", description: "Headers, CSP options, robots policy, launch checklist.", mimeType: "text/markdown", body: () => doc("DEPLOYMENT.md") },
  { uri: "ascendance://doc/handoff", name: "HANDOFF.md", description: "Build map: shared systems, content insertion points, known non-issues.", mimeType: "text/markdown", body: () => doc("HANDOFF.md") },
  { uri: "ascendance://doc/brand", name: "README.md", description: "Brand and content fundamentals.", mimeType: "text/markdown", body: () => doc("README.md") },
  { uri: "ascendance://css/tokens", name: "colors_and_type.css", description: "The authoritative token stylesheet. Import before any page CSS.", mimeType: "text/css", body: () => doc("colors_and_type.css") }
];

const TOOLS = [
  {
    name: "get_token",
    description: "Resolve a design token to its light and dark values and the rule governing its use. Use before hard-coding any colour.",
    inputSchema: { type: "object", properties: { name: { type: "string", description: "Token name, with or without the leading --. e.g. red-fill, --ink-3, accent-txt" } }, required: ["name"] }
  },
  {
    name: "check_copy",
    description: "Check a string against the house copy rules: em-dashes, 'intelligence' as a noun, 'verification' as a service, banned phrases. Run before shipping any UI or editorial string.",
    inputSchema: { type: "object", properties: { text: { type: "string" } }, required: ["text"] }
  },
  {
    name: "check_contrast",
    description: "Compute the WCAG contrast ratio between two colours and report pass or fail at AA for the given text size.",
    inputSchema: { type: "object", properties: { foreground: { type: "string", description: "hex, e.g. #BC1B1D" }, background: { type: "string" }, fontSizePx: { type: "number", default: 16 }, bold: { type: "boolean", default: false } }, required: ["foreground", "background"] }
  },
  {
    name: "lookup_page",
    description: "Given a destination URL or a source file path, return the mapping and any migration notes.",
    inputSchema: { type: "object", properties: { query: { type: "string", description: "e.g. /registers/sar-registry or ui_kits/marketing/faq.html" } }, required: ["query"] }
  },
  {
    name: "review_plan",
    description: "Given a description of a change you are about to make, return the invariants that change is most likely to break. Call this BEFORE editing.",
    inputSchema: { type: "object", properties: { description: { type: "string" } }, required: ["description"] }
  }
];

/* ---- tool implementations ---- */

const flatTokens = () => {
  const out = {};
  for (const [group, entries] of Object.entries(TOKENS))
    for (const [k, v] of Object.entries(entries))
      if (k.startsWith("--")) out[k] = { group, ...(typeof v === "string" ? { value: v } : v) };
  return out;
};

function getToken(name) {
  const key = name.startsWith("--") ? name : "--" + name;
  const all = flatTokens();
  if (all[key]) return { token: key, ...all[key] };
  const near = Object.keys(all).filter(k => k.includes(key.replace(/^--/, "")));
  return { token: key, found: false, didYouMean: near, hint: "Read ascendance://tokens for the full set." };
}

function checkCopy(text) {
  const issues = [];
  const SANCTIONED = "Championing The US-DRC Strategic Partnership\u2014Everywhere";
  const stripped = text.split(SANCTIONED).join("");
  const dashes = (stripped.match(/\u2014/g) || []).length;
  if (dashes) issues.push({ rule: "em-dash", count: dashes, fix: "Rewrite the sentence. The only sanctioned em-dash in the build is the footer tagline." });
  for (const m of text.matchAll(/\b(intelligence)\b/gi)) {
    const ctx = text.slice(Math.max(0, m.index - 30), m.index + 40);
    if (/DRC Intelligence Map|Intelligence Map/i.test(ctx)) continue;
    issues.push({ rule: "intelligence-noun", context: ctx.trim(), fix: "Use 'analysis', 'the weekly brief', or name the asset class." });
  }
  if (/\bverification\b/i.test(text) && !/verified, not estimated/i.test(text))
    issues.push({ rule: "verification-service", fix: "Do not sell verification as a service. 'Verified, not estimated' as an evidentiary standard is allowed." });
  if (/request a briefing/i.test(text)) issues.push({ rule: "banned-phrase", phrase: "Request a briefing", fix: "Briefings are invitation-only. Use 'Request invitation' or route to a diagnostic call." });
  if (/[\u{1F300}-\u{1FAFF}\u{2600}-\u{27BF}]/u.test(text)) issues.push({ rule: "emoji", fix: "The brand uses no emoji." });
  return { pass: issues.length === 0, issues };
}

const lum = (hex) => {
  const h = hex.replace("#", "").trim();
  const f = h.length === 3 ? h.split("").map(c => c + c).join("") : h;
  const [r, g, b] = [0, 2, 4].map(i => parseInt(f.slice(i, i + 2), 16) / 255)
    .map(v => v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4));
  return 0.2126 * r + 0.7152 * g + 0.0722 * b;
};

function checkContrast(fg, bg, size = 16, bold = false) {
  const a = lum(fg), b = lum(bg);
  const ratio = (Math.max(a, b) + 0.05) / (Math.min(a, b) + 0.05);
  const large = size >= 24 || (size >= 18.66 && bold);
  const need = large ? 3 : 4.5;
  return {
    ratio: Number(ratio.toFixed(2)), required: need, large,
    passAA: ratio >= need, passAAA: ratio >= (large ? 4.5 : 7),
    advice: ratio >= need ? "Passes AA." :
      "Fails AA. If this is red text on a dark ground use --accent-txt (#ec6a6b, or #f59595 on mid-navy). Never lift a colour used as a FILL under white text."
  };
}

function lookupPage(q) {
  const s = q.toLowerCase().trim();
  const hits = PAGE_MAP.filter(p => p.url.toLowerCase().includes(s) || p.source.toLowerCase().includes(s));
  return hits.length ? hits : { found: false, hint: "Read ascendance://pages for the full map." };
}

function reviewPlan(desc) {
  const d = desc.toLowerCase();
  const K = {
    "pinned-fill": ["red", "colour", "color", "accent", "button", "cta", "fill", "dark mode", "dark theme"],
    "wordmark-tokens": ["logo", "wordmark", "lockup", "masthead"],
    "em-dash": ["copy", "text", "wording", "headline", "rewrite"],
    "faq-schema": ["faq", "schema", "json-ld", "structured data", "find and replace", "find-and-replace"],
    "intelligence-noun": ["copy", "title", "label", "nav", "wording"],
    "skip-target": ["react", "root", "mount", "skip", "accessibility", "a11y", "id="],
    "focus-ring": ["focus", "outline", "accessibility", "a11y", "keyboard"],
    "media-wrapper": ["css", "extract", "port", "stylesheet", "regex", "sweep", "media query", "responsive"],
    "shared-chrome": ["footer", "header", "nav", "masthead", "toggle", "theme", "every page", "all pages", "sweep"],
    "theme-key": ["theme", "dark", "localstorage", "toggle"],
    "server-gate": ["paywall", "gate", "tier", "subscription", "teaser", "access"]
  };
  const ids = Object.entries(K).filter(([, words]) => words.some(w => d.includes(w))).map(([id]) => id);
  const rules = INVARIANTS.filter(i => ids.includes(i.id));
  return {
    matched: rules.length ? rules : INVARIANTS.filter(i => i.caughtInProduction),
    note: rules.length ? "These have been broken before by changes like yours. Verify each after editing."
      : "No specific match. Showing every invariant already broken once in this build.",
    alwaysCheck: "Re-measure contrast in BOTH themes from a clean load after any token change."
  };
}

/* ---- wiring ---- */

const server = new Server({ name: "ascendance-design-system", version: "1.0.0" }, { capabilities: { resources: {}, tools: {} } });

server.setRequestHandler(ListResourcesRequestSchema, async () => ({
  resources: RESOURCES.map(({ uri, name, description, mimeType }) => ({ uri, name, description, mimeType }))
}));

server.setRequestHandler(ReadResourceRequestSchema, async (req) => {
  const r = RESOURCES.find(x => x.uri === req.params.uri);
  if (!r) throw new Error(`Unknown resource: ${req.params.uri}`);
  return { contents: [{ uri: r.uri, mimeType: r.mimeType, text: r.body() }] };
});

server.setRequestHandler(ListToolsRequestSchema, async () => ({ tools: TOOLS }));

server.setRequestHandler(CallToolRequestSchema, async (req) => {
  const a = req.params.arguments || {};
  let result;
  switch (req.params.name) {
    case "get_token": result = getToken(a.name); break;
    case "check_copy": result = checkCopy(a.text); break;
    case "check_contrast": result = checkContrast(a.foreground, a.background, a.fontSizePx ?? 16, a.bold ?? false); break;
    case "lookup_page": result = lookupPage(a.query); break;
    case "review_plan": result = reviewPlan(a.description); break;
    default: throw new Error(`Unknown tool: ${req.params.name}`);
  }
  return { content: [{ type: "text", text: json(result) }] };
});

await server.connect(new StdioServerTransport());
