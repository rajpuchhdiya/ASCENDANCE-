# Plugin Inventory — Ascendance Intelligence Platform
**Version:** 2.0 | **Date:** 2026-07-15 | **Total plugins:** 14

> Do NOT store API keys or credentials in this document. All secrets live in `.env` or 1Password.

---

## Plugin Stack (Production)

| # | Plugin | Folder | Purpose | Key Config |
|---|--------|--------|---------|-----------|
| 1 | **Ascendance Core** | `ascendance-core/` | Custom plugin: CPTs, paywall, AI Studio, Mission Control, Stripe, newsletter, schema, search | All settings via `.env` |
| 2 | **Advanced Custom Fields** | `advanced-custom-fields/` | Field UI builder — used for Brief/Update/Dossier meta fields | Field groups in `acf-json/` |
| 3 | **Yoast SEO** | `wordpress-seo/` | SEO foundation: sitemaps, breadcrumbs, robots meta, basic Article schema | Configure via SEO → Settings |
| 4 | **Polylang** | `polylang/` | EN + FR bilingual content and URL routing | Languages → Settings |
| 5 | **Wordfence Security** | `wordfence/` | WAF, malware scanner, brute-force protection, 2FA enforcement | Firewall must be in Protect mode |
| 6 | **UpdraftPlus** | `updraftplus/` | Automated DB + files backups to remote storage (S3/Backblaze/Google Drive) | Settings → UpdraftPlus → Remote storage |
| 7 | **Complianz GDPR** | `complianz-gdpr/` | Cookie consent banner, GDPR scanner, privacy/cookie policy generator | Settings → Complianz |
| 8 | **Redirection** | `redirection/` | 301 redirect manager — critical for content migration | Tools → Redirection |
| 9 | **WP Mail SMTP** | `wp-mail-smtp/` | Ensures WordPress transactional emails are sent reliably via SMTP | Settings → WP Mail SMTP |
| 10 | **W3 Total Cache** | `w3-total-cache/` | Page caching, browser caching, minification, CDN integration | Performance → General Settings |
| 11 | **EWWW Image Optimizer** | `ewww-image-optimizer/` | Local image compression and WebP conversion — no external API calls | Media → EWWW Image Optimizer |
| 12 | **WPS Hide Login** | `wps-hide-login/` | Moves `wp-login.php` to a custom URL (default: `/portal/`) | Settings → WPS Hide Login |
| 13 | **Code Snippets** | `code-snippets/` | Safe `functions.php` additions without editing theme files | Snippets → All Snippets |
| 14 | **Paid Memberships Pro** | `paid-memberships-pro/` | Gating configuration, membership level management, and built-in Stripe gateway | Configured via `.env` overrides |

---

## Why These Plugins (Spec Alignment)

These plugins match the approved list from `Ascendance_Master_Document.txt` Section 2.2. No Pro/paid plugins. All free. Each chosen because the feature cannot reasonably be rebuilt in-house.

---

## Removed Plugins (History)

| Plugin | Removed | Reason |
|--------|---------|--------|
| WP Super Cache | 2026-07-15 | Replaced by W3 Total Cache (spec's specified caching plugin) |
| two-factor-temp | 2026-07-15 | Wordfence Free includes 2FA — no separate plugin needed |
| WP File Manager | 2026-07-15 | Not in spec; known security risk (remote file execution vulnerabilities) |
| Akismet | 2026-07-15 | Not in spec; comment spam not a primary concern for gated platform |

---

## Native Security Modules (Built into `ascendance-core`)

These features are implemented natively — **no separate plugin required:**

| Feature | Class file | Env variable(s) |
|---------|-----------|----------------|
| Hidden login URL | `class-login-security.php` | `WP_LOGIN_SLUG` (default: `portal`) |
| Google reCAPTCHA v3 | `class-recaptcha.php` | `RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY`, `RECAPTCHA_THRESHOLD` |

> Note: WPS Hide Login (plugin #12) provides the UI-accessible configuration. The native `class-login-security.php` handles the server-side redirect logic. Both work together — the plugin is not redundant.

---

## Required Env Variables by Plugin

| Plugin | Variable | Where to set |
|--------|---------|-------------|
| WP Mail SMTP | SMTP host/port/user/pass via plugin UI | Settings → WP Mail SMTP |
| Ascendance Core (AI Studio) | `ANTHROPIC_API_KEY`, `OPENAI_API_KEY`, `GEMINI_API_KEY` | `.env` |
| Ascendance Core (Stripe) | `STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SECRET`, `STRIPE_PUBLISHABLE_KEY` | `.env` |
| Ascendance Core (Newsletter) | `ASCENDANCE_NEWSLETTER_API_KEY`, `ASCENDANCE_NEWSLETTER_LIST_ID` | `.env` |
| Ascendance Core (Analytics) | `ASCENDANCE_GTM_ID` | `.env` |
| Ascendance Core (reCAPTCHA) | `RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY` | `.env` |
| UpdraftPlus | Remote storage credentials | Plugin UI → Settings → Remote Storage |

---

## Update Policy

1. Update plugins in **staging first**, run Wordfence scan + functional smoke tests before pushing to production.
2. For Wordfence and Complianz — read changelogs before major version upgrades.
3. Plugin file modifications are managed through **Git deployment** (`DISALLOW_FILE_MODS=true` on production).
4. Never update plugins directly in the production WP dashboard — always use Git.

---

## How to Add a New Plugin

1. Download from WordPress.org to `wp-content/plugins/` on local dev.
2. Test activation on local — check for errors in `wp-content/debug.log`.
3. Test on staging — run full smoke test.
4. Commit to Git and deploy via normal Git workflow.
5. Update this inventory document.
6. Verify `DISALLOW_FILE_MODS=true` is set in production `.env` so the plugin cannot auto-update.
