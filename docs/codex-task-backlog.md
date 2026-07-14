# Codex Task Backlog — Ascendance
Date: 2026-06-17

This backlog captures the next actionable engineering tasks discovered in the audit and recommended owners/notes.

- Task: Verify Stripe library & webhooks
  - Priority: High
  - Files/Areas: `wp-content/plugins/paid-memberships-pro/services/stripe-webhook.php`, `wp-content/plugins/ascendance-core/includes/class-stripe-billing.php`, `.env`
  - Notes: Confirm `stripe/stripe-php` availability (bundled by PMPro or via composer). Test webhooks via Stripe CLI and ensure idempotency.

- Task: Configure PMPro in staging
  - Priority: High
  - Files/Areas: WP Admin → Memberships settings, `.env`
  - Notes: Set Stripe keys, webhook secret, account pages. Run end-to-end checkout test.

- Task: Install and validate SEO plugin [COMPLETED]
  - Priority: Medium
  - Files/Areas: `wp-content/plugins/wordpress-seo/`, `class-search-seo.php`
  - Notes: Yoast SEO downloaded, extracted via PowerShell, and activated successfully on WordPress. Injected custom JSON-LD schema works in tandem.

- Task: Set GTM & Brevo keys in staging
  - Priority: Medium
  - Files/Areas: `.env`, `wp-content/mu-plugins/consent-gate-analytics.php`, `wp-content/plugins/ascendance-core/includes/class-newsletter.php`, `class-analytics.php`
  - Notes: Populate `ASCENDANCE_GTM_ID`, `ASCENDANCE_NEWSLETTER_API_KEY`, `ASCENDANCE_NEWSLETTER_LIST_ID` and validate consents.

- Task: Add CI pipeline [COMPLETED]
  - Priority: Medium
  - Files/Areas: `.github/workflows/ci.yml`, `DEPLOY.md`
  - Notes: GitHub Actions workflow created to lint PHP code, verify dependencies, and build production assets. Promotion rules documented in DEPLOY.md.

- Task: Content migration scripts [COMPLETED]
  - Priority: Low -> Medium
  - Files/Areas: `scripts/migrate-legacy.php`
  - Notes: Migration script completed, tested, and ready to be run on target environments. Handles cleanups and redirects.

- Task: Performance & caching validation [COMPLETED]
  - Priority: Low
  - Files/Areas: theme assets (`wp-content/themes/ascendance/assets/dist/`), `wp-content/mu-plugins/performance-tuning.php`
  - Notes: Production theme assets compiled and minified via Vite. Custom performance tuning MU plugin implemented to strip emojis, clean head metadata, limit heartbeat, preconnect Stripe/Brevo APIs, and defer scripts.

For each task above, when work is started or completed, update both `docs/project-status-audit.md` and this backlog entry with date, files modified and brief notes.
