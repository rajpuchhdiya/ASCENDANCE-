# Plugin Inventory — Ascendance
Date: 2026-07-13

This document lists primary plugins, purpose, and location. Do NOT store credentials here.

Core plugins installed (verify versions in the WP admin Plugins screen):

- **Wordfence Security** — Firewall, malware scanner. Path: `wp-content/plugins/wordfence`.
- **Two-Factor** — Enforces 2FA for accounts. Path: `wp-content/plugins/two-factor`.
- **Complianz (GDPR)** — Consent management platform. Path: `wp-content/plugins/complianz-gdpr`.
- **Paid Memberships Pro** — Subscription management and paywall. Path: `wp-content/plugins/paid-memberships-pro`.
- **Advanced Custom Fields** — Programmatic field registration. Path: `wp-content/plugins/advanced-custom-fields`.
- **Akismet** — Spam filtering for comments. Path: `wp-content/plugins/akismet`.
- **Yoast SEO** — On-page SEO metadata. Path: `wp-content/plugins/wordpress-seo`.
- **WP Super Cache** — Page caching. Path: `wp-content/plugins/wp-super-cache`.

Native security modules (built into `ascendance-core` — no separate plugin needed):

| Feature | Class file | Env variable(s) |
|---|---|---|
| Hidden Login URL | `class-login-security.php` | `WP_LOGIN_SLUG` (default: `portal`) |
| Google reCAPTCHA v3 | `class-recaptcha.php` | `RECAPTCHA_SITE_KEY`, `RECAPTCHA_SECRET_KEY`, `RECAPTCHA_THRESHOLD` |

> Note: WPS Hide Login and the reCAPTCHA plugin are **not required** — both features are implemented
> natively in `ascendance-core` to keep the plugin count low and avoid conflicts with `DISALLOW_FILE_MODS`.

How to update safely
- Update plugins in staging first and run a full Wordfence scan and functional smoke tests.
- For critical plugins (Wordfence, Two-Factor, Complianz), check changelogs and test major version upgrades on staging.
- Security modules (Login Security, hCaptcha) are updated through the normal Git deployment workflow.

Notes
- For any plugin that requires API keys, store keys in a secure secrets manager or in `.env` outside the repo.
- If a plugin is removed, follow uninstall instructions to clean up options & user meta.
