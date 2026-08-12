/* Ascendance Strategies design system, as MCP data.
   Kept as one plain module so it can be edited without touching server code. */

export const TOKENS = {
  accent: {
    "--red-fill": { light: "#BC1B1D", dark: "#BC1B1D", use: "White-on-red FILLS only. PINNED in both themes so lifting red text can never drag a CTA below AA." },
    "--red": { light: "#BC1B1D", dark: "#ec6a6b", use: "Red TEXT. Lifts on dark grounds." },
    "--red-deep": { light: "#8f1416", dark: "#f59595", use: "Hover / deeper red text." },
    "--accent-txt": { light: "#BC1B1D", dark: "#ec6a6b", use: "Red text inside the standalone registers. #f59595 on mid-navy grounds." },
    "--red-wash": { light: "#fbe6e6", dark: "#3a201e", use: "Tinted ground behind accent chips." }
  },
  neutrals: {
    "--paper": { light: "#f7f4ef", dark: "#1c1813" },
    "--paper-2": { light: "#f1ede5", dark: "#232019" },
    "--card": { light: "#ffffff", dark: "#26221b" },
    "--ink": { light: "#211d18", dark: "#eae4d8" },
    "--ink-2": { light: "#4a443b", dark: "#cbc4b5" },
    "--ink-3": { light: "#6f695c", dark: "#9d9684", use: "Muted meta. Raised from #837c70 to clear AA." },
    "--ink-4": { light: "#8f8879", dark: "#726b5b", use: "Decorative only. Never body or numerals." },
    "--hairline": { light: "#e5dfd4", dark: "#383127" },
    "--hairline-2": { light: "#d5cdbe", dark: "#494134" }
  },
  inkSurface: {
    "--ink-surface": { light: "#272119", dark: "#26211a", use: "Dark bands. On a dark page an inset block reads as RAISED, so it sits ABOVE --paper, never below." },
    "--on-ink": { light: "#efe9dd", dark: "#eae4d8", use: "Held below pure white to limit halation." },
    "--on-ink-soft": { light: "#d9d2c3", dark: "#d3ccbd", use: "Body copy on dark." },
    "--on-ink-muted": { light: "#b3ab99", dark: "#a9a190" }
  },
  registerNavy: {
    "--terminal-bg": "#132038", "--navy": "#1A2C4A", "--deep-navy": "#0F1B30", "--mid-navy": "#22385C",
    "--text-on-dark": "#EAEFF7", "--divider-dark": "#334561",
    note: "Data Terminal register only. Lifted from #0D1626 for reading comfort. Pure white on navy is banned; use #EAEFF7."
  },
  status: {
    ok: { light: "#19773f", dark: "#4fc985" },
    warn: { light: "#a85d0c", dark: "#f0954e" },
    info: { light: "#1f6fa3", dark: "#5aa9e0" },
    gap: { light: "#BC1B1D", dark: "#ec6a6b" },
    note: "Every status colour needs BOTH values. One value cannot serve a white card and a navy ground."
  },
  type: {
    display: "Zilla Slab — headlines, slab kickers",
    body: "Spectral — prose",
    ui: "Archivo — nav, labels, buttons",
    mono: "JetBrains Mono — data, registers, codes",
    wordmark: "Cooper Hewitt 600/700, jsdelivr @fontsource ONLY. The cdnfonts.com source silently fails and corrupts the wordmark.",
    registerStack: "Noto Serif / Cooper Hewitt / JetBrains Mono inside ui_kits/reference/*"
  },
  geometry: { radius: "2px maximum", gradients: "none", secondAccent: "none", emoji: "none" }
};

export const REGISTERS = {
  editorial: { ground: "cream", font: "Noto Serif", feel: "Foreign Affairs: drop cap, generous space", pages: ["hub-us-drc-partnership.html", "lobito-file-dossier.html"] },
  dataTerminal: { ground: "navy #132038", font: "monospace-forward", feel: "database query: dense rows, live result counter", pages: ["spa-glossary.html", "sar-registry.html", "cami-registry.html", "regulatory-reform-tracker.html", "drc-sovereign-rating.html"] },
  rule: "Each file is committed to ONE register and stays there. Side by side they should feel like two different tools under one brand. Do not converge them."
};

export const PAGE_MAP = [
  { url: "/", source: "ui_kits/portal/index.html", kind: "React SPA", note: "Hash routes #s/<Section> #x/<key> #a/<key> #t/<tag>. Replace with permalinks in WP." },
  { url: "/advisory/", source: "ui_kits/marketing/index.html" },
  { url: "/advisory/methodology", source: "ui_kits/marketing/methodology.html" },
  { url: "/advisory/industries-we-cover", source: "ui_kits/marketing/industries.html" },
  { url: "/faq", source: "ui_kits/marketing/faq.html", note: "FAQPage JSON-LD is GENERATED from the visible markup. Never edit both with one find-and-replace." },
  { url: "/subscribe", source: "ui_kits/marketing/pricing.html" },
  { url: "/login", source: "ui_kits/marketing/login.html", note: "No CSRF, rate limiting or enumeration protection. See DEPLOYMENT.md section 5." },
  { url: "/legal", source: "ui_kits/marketing/legal.html", note: "Counsel review required before publish." },
  { url: "/account/", source: "ui_kits/dashboard/index.html", note: "Gate behind auth." },
  { url: "/us-drc-partnership/", source: "ui_kits/reference/hub-us-drc-partnership.html" },
  { url: "/registers/spa-glossary", source: "ui_kits/reference/spa-glossary.html" },
  { url: "/registers/sar-registry", source: "ui_kits/reference/sar-registry.html" },
  { url: "/registers/cami-registry", source: "ui_kits/reference/cami-registry.html" },
  { url: "/registers/regulatory-reform-tracker", source: "ui_kits/reference/regulatory-reform-tracker.html" },
  { url: "/registers/drc-sovereign-rating", source: "ui_kits/reference/drc-sovereign-rating.html" },
  { url: "/dossiers/lobito-corridor", source: "ui_kits/reference/lobito-file-dossier.html" }
];

export const INVARIANTS = [
  { id: "pinned-fill", rule: "Brand red #BC1B1D is PINNED for fills (--red-fill). Red TEXT uses --red / --accent-txt, which lift on dark. Never let a lifted red land under white text.", caughtInProduction: true },
  { id: "wordmark-tokens", rule: "The wordmark's S box pairs a TEXT token with a SURFACE token, never two text tokens. Two text tokens converge in dark theme and the letter disappears.", caughtInProduction: true },
  { id: "em-dash", rule: "Exactly one em-dash exists in the build: the footer tagline 'Championing The US-DRC Strategic Partnership—Everywhere'. There are no others, including in data strings." },
  { id: "faq-schema", rule: "FAQPage JSON-LD is generated FROM the visible markup. The schema contains the same sentences, so a blind find-and-replace corrupts the JSON. Edit markup, then regenerate.", caughtInProduction: true },
  { id: "intelligence-noun", rule: "'Intelligence' is never used as a noun in UI copy. Proper nouns (DRC Intelligence Map) are exempt." },
  { id: "verification-service", rule: "'Verification' is never sold as a service. 'Verified, not estimated' is the evidentiary standard and is allowed." },
  { id: "skip-target", rule: "In the portal, id='main' lives on the <main> React renders, NOT on #root. React empties #root on mount; a duplicate id there breaks the mount entirely.", caughtInProduction: true },
  { id: "focus-ring", rule: ":focus-visible ring is written as a plain selector list, NOT :where(), which has zero specificity and loses to page rules. Never reintroduce outline:none.", caughtInProduction: true },
  { id: "media-wrapper", rule: "Never extract CSS rules with a flat regex. It strips @media wrappers and the mobile overrides then apply at every width. Use a brace-balanced parser.", caughtInProduction: true },
  { id: "shared-chrome", rule: "Masthead, nav, footer and theme toggle must be ONE template part. They are currently duplicated across 16 standalone files, which is how the dashboard silently missed the theme system and the legal footer.", caughtInProduction: true },
  { id: "theme-key", rule: "One localStorage key, 'as-theme' (light|dark|auto), applied pre-paint by an inline <script> in every <head>. Keep it shared so preference persists site-wide." },
  { id: "server-gate", rule: "The paywall must be enforced server-side. Teaser truncation in markup is presentation only and must never be the gate." }
];

export const VOICE = {
  register: "Institutional, plain, evidence-first. No metadiscourse, no hype, no 'this, not that' constructions.",
  claims: "Every substantive claim carries a confidence indication: HIGH / MED-HIGH / MEDIUM / LOW.",
  vintage: "Registers state the date of the sources they reflect, not the date of retrieval.",
  banned: ["em-dashes (one sanctioned exception)", "'intelligence' as a noun in UI", "'verification' as a service", "'Request a briefing'", "emoji"],
  signoff: "Washington. Paris. Kinshasa."
};

export const DEPENDENCIES = [
  { origin: "unpkg.com", use: "React 18.3.1 + Babel standalone", risk: "low", note: "Pinned with SRI. Do not remove integrity attributes. Precompile JSX for production." },
  { origin: "fonts.googleapis.com / fonts.gstatic.com", use: "Noto Serif, Barlow, JetBrains Mono", risk: "GDPR", note: "Google logs visitor IPs. Self-host, given the SASU privacy policy." },
  { origin: "cdn.jsdelivr.net", use: "Cooper Hewitt 600/700 (wordmark)", risk: "medium", note: "Version-pinned. Add SRI or self-host." }
];
