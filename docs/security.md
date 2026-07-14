# Security Hardening Guide
Date: 2026-07-13

Purpose: Steps to reach a launch-safe security posture: Wordfence, 2FA, hidden login, hCaptcha, headers, backups, and key management.

Prerequisites
- SSH/terminal access to servers.
- `wp` (WP-CLI) installed on server(s).
- `.env` present on server, permissions `600`.

1) Plugins to install (recommended)

- Wordfence (slug: `wordfence`) — host-level firewall, malware scanning.
- Two-Factor (slug: `two-factor`) — account 2FA.
- Google reCAPTCHA v3: **built into `ascendance-core`** (`class-recaptcha.php`) — no separate plugin needed.

WP-CLI install/activate example:

```bash
wp plugin install wordfence --activate
wp plugin install two-factor --activate
# reCAPTCHA is handled natively; configure RECAPTCHA_SITE_KEY / RECAPTCHA_SECRET_KEY in .env
```

2) Wordfence setup
- After activation, open Wordfence > Firewall and put firewall into "Learning Mode" while testing.
- Once UAT completes, set to "Enabled and Protecting".
- Configure scan schedule and alert email in Wordfence options.

3) Two-Factor setup
- Enforce 2FA for all administrator accounts: encourage TOTP apps (Authy/Google Authenticator) or U2F security keys.
- Configure recovery codes and require 2FA for login via plugin settings.

4) Hidden login ✅ Implemented
- **Built into `ascendance-core`** (`class-login-security.php`) — no WPS Hide Login plugin needed.
- Set `WP_LOGIN_SLUG` in the server `.env` to a secret slug (no leading slash), e.g. `admin-login-9f3j2`.
- Direct requests to `/wp-login.php` and unauthenticated `/wp-admin/` return **404**.
- After changing the slug, flush permalinks: WP Admin → Settings → Permalinks → Save, or run `wp rewrite flush`.
- All internal WordPress login URLs (login, logout, registration, lost-password) are automatically rewritten.

5) Google reCAPTCHA v3 ✅ Implemented
- **Built into `ascendance-core`** (`class-recaptcha.php`) — no separate reCAPTCHA plugin needed.
- Provide `RECAPTCHA_SITE_KEY` and `RECAPTCHA_SECRET_KEY` in `.env` to enable invisible, score-based protection.
- `RECAPTCHA_THRESHOLD` (default `0.5`) controls the minimum human-likeness score required (0.0 = bot, 1.0 = human).
- CAPTCHA is applied on: login form, registration form, lost-password form.
- Automatically skipped only when keys are blank (not configured).
- Works on `localhost` — Google whitelists it by default.
- Get free keys at https://www.google.com/recaptcha/admin/create → choose **reCAPTCHA v3**.

6) HTTP Headers
- `.htaccess` has been updated to include HSTS, CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy. Confirm mod_headers is active on Apache.

7) Backups
- The project includes `bin/db-backup.php` which writes gzipped dumps to `wp-content/backups` and rotates (keeps last 14).
- Recommended schedule: daily backups via cron (Linux) or Task Scheduler (Windows). Example cron entry:

```cron
0 2 * * * /usr/bin/php /var/www/Ascendance/bin/db-backup.php >> /var/log/ascendance/db-backup.log 2>&1
```

- Optional: upload backups offsite with the included `scripts/backup-to-s3.sh` (requires AWS CLI configured with least-privilege credentials and `AWS_S3_BUCKET` set on the server).

8) Key Management & Rotation
- Never commit secrets to Git. Use server `.env` with strict permissions (`chmod 600 .env`).
- For production, prefer a secrets manager (AWS Secrets Manager, Vault, Azure Key Vault). Store only access to the vault on the server.
- A `scripts/rotate-salts.php` script is provided to fetch fresh WordPress salts and update `.env` safely; run this after scheduling maintenance and notify users to re-login if needed.

Usage: rotate salts

```bash
php scripts/rotate-salts.php /var/www/Ascendance/.env
```

9) Emergency response
- If a key is suspected compromised:
  1. Rotate the key immediately in the provider control panel.
  2. Update `.env` on the server and restart PHP-FPM.
  3. Rotate WordPress salts (run `scripts/rotate-salts.php`).
  4. Rotate any API credentials used by integrations.

10) Post-deployment checklist
- Verify Wordfence firewall status and scheduled scans.
- Confirm 2FA enforced for admin users.
- Confirm hCaptcha triggers on login and server-side verification passes.
- Confirm backups are created and offsite upload succeeds.
