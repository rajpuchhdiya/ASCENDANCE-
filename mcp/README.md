# Ascendance Design System — MCP Server

Exposes the design system, page map, copy rules and deployment guide over the Model Context Protocol, so an AI coding tool building the WordPress theme reads the real system instead of inferring it from screenshots.

Read-only. Nothing here writes to your repository.

## Install

```bash
cd mcp
npm install
```

Node 18 or later.

## Connect

Add to your MCP client config. `ASCENDANCE_ROOT` points at the repository root so the server can serve `DEPLOYMENT.md`, `HANDOFF.md`, `README.md` and `colors_and_type.css`; it defaults to the parent directory.

**Claude Desktop** — `claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "ascendance": {
      "command": "node",
      "args": ["/absolute/path/to/repo/mcp/server.js"],
      "env": { "ASCENDANCE_ROOT": "/absolute/path/to/repo" }
    }
  }
}
```

**Claude Code** — from the repository root:

```bash
claude mcp add ascendance -- node ./mcp/server.js
```

Cursor, Windsurf, Zed and other MCP clients take the same `command` / `args` / `env` shape.

## Resources

| URI | Contents |
|---|---|
| `ascendance://tokens` | Every token, its light and dark values, and the rule it enforces |
| `ascendance://registers` | Editorial vs Data Terminal, and which pages belong to each |
| `ascendance://pages` | Destination URL to source file, with migration notes |
| `ascendance://invariants` | Rules already broken and caught in review |
| `ascendance://voice` | Register, confidence language, banned phrases |
| `ascendance://dependencies` | Third-party origins and the risk attached to each |
| `ascendance://doc/deployment` | `DEPLOYMENT.md` |
| `ascendance://doc/handoff` | `HANDOFF.md` |
| `ascendance://doc/brand` | `README.md` |
| `ascendance://css/tokens` | `colors_and_type.css` |

## Tools

**`review_plan`** — describe a change before making it; returns the invariants that change is most likely to break. Every rule it knows was broken at least once during this build, so the list is empirical rather than theoretical.

**`get_token`** — resolve a token to its light and dark values plus its governing rule. Call before hard-coding any colour.

**`check_copy`** — run a string against the house rules: em-dashes, "intelligence" as a noun, "verification" as a service, banned phrases, emoji. The sanctioned footer tagline and the proper noun "DRC Intelligence Map" are exempt.

**`check_contrast`** — WCAG ratio for a foreground and background at a given size, with advice specific to this palette when it fails.

**`lookup_page`** — map a URL or source path to its counterpart.

## The one thing to understand

Red serves two roles and needs two tokens.

- `--red-fill` is **pinned** to `#BC1B1D` in both themes. It is for white-on-red fills — buttons, badges, the wordmark's A box. It must never lift, or the white label on it drops below AA.
- `--red` and `--accent-txt` are for red **text**, and they lift to `#ec6a6b` on dark grounds (`#f59595` on mid-navy).

Collapsing these back into one token is the single most likely way to break this system.

## Editing

`design-system.js` is plain data with no server logic in it. Change a token or add an invariant there; `server.js` needs no edit.

Keep this in sync with `colors_and_type.css` — the stylesheet is authoritative for rendering, this module is authoritative for intent.
