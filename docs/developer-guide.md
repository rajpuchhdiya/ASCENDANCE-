# Ascendance — Developer Guide
Date: 2026-07-13

Purpose: technical onboarding for developers working on Ascendance. Covers code structure, setup, key components, and common tasks.

1. Requirements
- PHP 8.2+
- Composer
- Node.js (16+ recommended) and npm
- MySQL
- Local web server (XAMPP / MAMP / Valet)

2. Repository layout (high-level)
- `wp-content/plugins/ascendance-core/` — core plugin (CPTs, ACF, paywall, AI Studio, security modules, integrations)
- `wp-content/themes/ascendance/` — theme, frontend templates, Vite build tooling
- `docs/` — runbooks, audit, guides
- `composer.json` — PHP autoload and dependencies

3. Initial setup (developer machine)
```bash
# Clone repository
git clone <repo> Ascendance
cd Ascendance

# Install PHP deps
composer install

# Build theme assets
cd wp-content/themes/ascendance
npm ci
npm run build

# Ensure WordPress has a database and copy .env from template
cp .env.example .env
# Edit .env with your local credentials
```

4. Environment variables
- The project reads environment variables from the system (and `.env` locally). Keys used:
  - `OPENAI_API_KEY` or `ANTHROPIC_API_KEY` or `GEMINI_API_KEY` — AI Studio providers
  - `ASCENDANCE_GTM_ID` — Google Tag Manager container ID
  - `ASCENDANCE_NEWSLETTER_API_KEY`, `ASCENDANCE_NEWSLETTER_LIST_ID` — Brevo newsletter
  - `WP_LOGIN_SLUG` — Custom login page slug (default: `portal`). See §13.
  - `RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY`, `RECAPTCHA_THRESHOLD` — reCAPTCHA v3 keys. See §14.

5. Key plugin classes
- `class-cpt-taxonomy.php` — CPT and taxonomy registration.
- `class-acf-fields.php` — programmatic ACF registration (mirrors `acf-json`).
- `class-paywall.php` — server-side paywall gating and REST gate helpers.
- `class-stripe-billing.php` — PMPro & Stripe overrides and billing portal redirect.
- `class-newsletter.php` — Brevo integration and PMPro sync.
- `class-search-seo.php` — weighted search and JSON-LD schema generation.
- `class-ai-studio.php` — AI Studio: admin UI, REST endpoints, usage logging, Gutenberg SEO sidebar.
- `class-login-security.php` — Hidden login URL (WPS Hide Login equivalent). See §13.
- `class-recaptcha.php` — Google reCAPTCHA v3 invisible bot protection. See §14.

6. AI Studio details
- REST endpoints:
  - `POST /wp-json/ascendance/v1/ai-studio/generate` — compile draft
  - `POST /wp-json/ascendance/v1/ai-studio/regenerate-section` — refine a block
  - `POST /wp-json/ascendance/v1/ai/seo` — generate SEO suggestions
  - `POST /wp-json/ascendance/v1/ai/seo-apply` — apply SEO payload to post

- The plugin logs per-call usage to a DB table `wp_ascendance_ai_usage` (created on plugin initialization).

7. Adding/changing AI providers
- `class-ai-studio.php` has provider-specific methods: `call_gpt()`, `call_claude()`, `call_gemini()`.
- To add a new provider, add a method for the HTTP request, map env keys, and update switch statements.

8. Gutenberg integration
- Editor JS is at `wp-content/plugins/ascendance-core/assets/js/ai-seo-editor.js` and is registered via `enqueue_block_editor_assets` in `class-ai-studio.php`.

9. Testing and debugging
- Enable WP debugging in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```
- Check `wp-content/debug.log` for traces.

10. Stripe webhooks and PMPro
- PMPro bundles Stripe webhook handling; verify webhook endpoints in PMPro settings and test with the Stripe CLI.
- Ensure idempotency handling for custom webhook logic if you implement separate controllers.

11. CI / Deployment
- Add GitHub Actions to run `composer install --no-dev`, build theme assets, and run static checks.
- Refer to `DEPLOY.md` for manual server steps; implement pipeline to copy `.env` from secrets store and run `composer install --no-dev` + `npm run build`.

12. Recommended development workflow
- Create feature branches off `staging`, open PRs, run CI, request reviews, and merge to `staging`.
- QA on staging, then merge to `main` for production after passing smoke tests.

13. Login Security — Hidden URL (`class-login-security.php`)

**What it does**
- Serves the WordPress login form at a secret custom slug instead of `/wp-login.php`.
- Any direct request to `/wp-login.php` or `/wp-admin/` from an unauthenticated visitor returns **404**.
- All internal WordPress URL helpers (`wp_login_url()`, `wp_logout_url()`, `site_url('wp-login.php')`, `wp_redirect()`) are filtered to point at the custom slug automatically.
- Logout redirects to the custom slug (not the default login page).

**Configuration**
```env
# .env
WP_LOGIN_SLUG="portal"    # change to any slug you prefer
```

**Slug change procedure**
1. Update `WP_LOGIN_SLUG` in `.env` on every environment.
2. Visit **WP Admin → Settings → Permalinks → Save Changes** (or run `wp rewrite flush`).
3. The class auto-detects the slug change (stored in `ascendance_login_slug` option) and flushes rules.

**Local development**
- The class is active on `local` too — use `http://localhost/Ascendance/portal/` to log in.
- Direct `/wp-login.php` requests will 404 locally as well (by design).

**Password-reset links**
- Email reset links contain `action=rp&key=...`; the class allows these through automatically (key presence is validated).

14. Google reCAPTCHA v3 — Invisible CAPTCHA (`class-recaptcha.php`)

**What it does**
- Loads the reCAPTCHA v3 JS library (`google.com/recaptcha/api.js?render=SITE_KEY`) on the login page.
- On form submit, calls `grecaptcha.execute(siteKey, { action })` to silently obtain a token.
- Sends `POST` to `https://www.google.com/recaptcha/api/siteverify` with the token + secret key.
- Checks two conditions:
  1. `success === true` — token is valid.
  2. `score >= threshold` — the request looks human enough.
- Protected forms: login (`action: login`), registration (`action: register`), lost-password (`action: lostpassword`).
- Action names are validated server-side to prevent token reuse across different forms.
- **Fail-open**: if Google's API is unreachable, the request is allowed through.
- **Silently skipped**: only when `RECAPTCHA_SITE_KEY` or `RECAPTCHA_SECRET_KEY` is blank.
- **Works on `localhost`**: Google whitelists `localhost` by default — no need to add it in the reCAPTCHA Admin Console.

**Configuration**
```env
# .env (staging / production)
RECAPTCHA_SITE_KEY="6Lc..."
RECAPTCHA_SECRET_KEY="6Lc..."
RECAPTCHA_THRESHOLD="0.5"   # adjust between 0.0 (permissive) and 1.0 (strict)
```

**Getting keys**
1. Go to https://www.google.com/recaptcha/admin/create
2. Choose **reCAPTCHA v3** (not v2).
3. Add your domain(s) under "Domains".
4. Copy the **Site Key** and **Secret Key** into your server's `.env`. Never commit to Git.

**Score tuning**
| Score | Meaning | Recommended action |
|---|---|---|
| 0.9–1.0 | Very likely human | Allow |
| 0.5–0.8 | Uncertain | Allow (default threshold) |
| 0.1–0.4 | Likely bot | Block (lower threshold to catch more) |
| 0.0 | Almost certainly bot | Block |

Start with `RECAPTCHA_THRESHOLD=0.5` and adjust after reviewing `debug.log` score entries.

**Google ToS requirement**
reCAPTCHA v3 requires the badge or an inline disclosure on any page that uses it.
The class renders a standard reCAPTCHA badge (bottom-right) automatically.

**Smoke-testing without real keys**
- Use the reCAPTCHA test key pair in CI:
  - Site key: `6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI`
  - Secret key: `6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe`
  - These always return `score: 0.9` — safe for automated tests.

15. Extending the platform (examples)
- Semantic search POC: create a background job to generate embeddings for CPTs, store vectors in pgvector or an external vector DB, and add a REST search endpoint.
- Add moderation: create a `class-ai-moderation.php` that runs inputs through a moderation API before sending to LLM.

16. Contacts and knowledge base
- Use `docs/` for runbooks. Add troubleshooting notes to `docs/editor-manual.md` and `docs/security.md` when you discover environment-specific steps.

17. Local quick commands
```bash
# Run theme build watch during front-end development
cd wp-content/themes/ascendance && npm run dev

# Use the Stripe CLI to forward webhooks to local site
stripe listen --forward-to localhost:8080/Ascendance/wp-json/pmpro/v1/stripe-webhook

# Flush rewrite rules after changing WP_LOGIN_SLUG (alternative to Permalinks screen)
wp rewrite flush --hard
```

Questions or missing items? Open an issue describing the change and I'll draft the needed runbook or code change.
