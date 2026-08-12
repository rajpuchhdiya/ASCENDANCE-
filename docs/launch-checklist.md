# Production Launch Checklist
**Version:** 2.0 | **Date:** 2026-07-15

Objective: Switch live keys, activate all required plugins, submit sitemap, run live smoke tests, and enable monitoring. This checklist is prescriptive — follow items in order and stop for any critical failure.

Pre-launch (Day-of)
- Ensure full backups exist and are available offsite. Confirm latest DB + files backup < 1 hour old.
- Verify `scripts/rotate-salts.php` has run and `.env` backup exists.
- Confirm staging smoke tests passed and all critical Wordfence findings resolved.
- Notify stakeholders and schedule maintenance window.

Step 1 — Switch Live Keys & Secrets
- Update production `.env` (or secrets store) with live values:
  - `ENV=production`
  - Payment gateway sandbox -> live keys
  - hCaptcha site & secret
  - AWS S3 credentials (for backups)
  - Any API keys (mail provider, search console, analytics)
- Ensure `.env` permissions are secure (`chmod 600`) and not in webroot if possible.

Step 2 — Server & DNS
- Point DNS A record to production IP (or update load balancer target). Allow TTL propagation.
- Install/renew SSL certificate (Let’s Encrypt or provided cert). Verify HTTPS.
- Ensure web server user owns files and `wp-content/uploads` writable.

Step 3 — Plugin Activation & Configuration
- Activate all 13 required plugins (see `docs/plugin-inventory.md`).
- **WP Mail SMTP:** Settings → WP Mail SMTP → configure SMTP provider → send test email. Confirm delivery.
- **W3 Total Cache:** Performance → General Settings → Enable Page Cache (Disk: Enhanced). Enable Minify + Browser Cache. See `docs/w3-total-cache-config.md`.
- **EWWW Image Optimizer:** Media → EWWW → enable WebP conversion. Run Bulk Optimize on existing media. Verify WebP delivery. See `docs/webp-verification.md`.
- **WPS Hide Login:** Settings → WPS Hide Login → set custom login slug → Save. Test the new URL.
- **Redirection:** Tools → Redirection → Setup → enable Permalink Monitoring + 404 Logging. Import redirect rules from `docs/migration-redirects-map.md`.
- **UpdraftPlus:** Settings → UpdraftPlus → configure remote storage (S3/Backblaze/Google Drive) → run first manual backup → verify remote upload.
- **Polylang:** Languages → Settings → confirm EN as default, FR as second language → verify language switcher on frontend.
- **Wordfence:** Confirm WAF is in `Protect` mode (not Learning). Enable 2FA requirement for admin/editor roles.
- **Yoast SEO:** SEO → Search Appearance → set Organization name, logo, social profiles → save.
- **Complianz:** Settings → Complianz → complete setup wizard → verify consent banner appears on fresh visit.

Step 4 — Application Switches
- Put site into maintenance mode (optional) via WP-CLI or plugin.
- Flush all caches:
  - W3 Total Cache: Performance → Dashboard → Empty All Caches
  - `wp rewrite flush --path=/path/to/site`
- Set `WP_HOME`/`WP_SITEURL` if domain changed.
- Verify custom login URL works (WPS Hide Login). Confirm `/wp-login.php` returns 404.
- Enforce Two-Factor for admin/editor roles (confirm at least two admins have completed setup).

Step 5 — Submit Sitemap
- Regenerate sitemap via Yoast SEO (SEO → General → Features → XML Sitemaps).
- Ping major search engines:
  - `https://www.google.com/ping?sitemap=https://yourdomain.com/sitemap_index.xml`
  - `https://www.bing.com/webmaster/ping.aspx?siteMap=https://yourdomain.com/sitemap_index.xml`
- Add sitemap to Google Search Console and Bing Webmaster Tools.

Step 6 — Live Smoke Tests (run immediately — use `docs/QA-checklist.md`)
- Anonymous browse: homepage, 3+ public Briefs, search — no errors.
- Verify cookie consent banner and analytics blocking.
- `/wp-login.php` returns 404. Custom login URL works.
- New subscriber signup → Stripe Checkout → welcome email → access to content.
- Returning subscriber login (with 2FA) → dashboard loads.
- Admin/editor login (2FA enforced) — publish a [SAMPLE] test Brief.
- Test WP Mail SMTP: Settings → Send Test Email → confirm received.
- Payment success + failure paths (Stripe test cards).
- Image upload → EWWW optimizes → WebP version generated.
- Run schema check: Mission Control → Quick Actions → Run Schema Check.
- Run Wordfence scan → resolve any critical findings.

Step 6 — Monitoring & Alerts
- Configure uptime monitoring (Pingdom/UptimeRobot) to check homepage and checkout endpoints every 1–5 minutes.
- Configure application error logging and alerting (Sentry or log push to central store).
- Configure backup monitoring — alert on failed backups and offsite upload failures.
- Ensure Wordfence alerts configured for firewall/critical findings and alert emails or Slack webhook.

Rollback Plan
- If critical failures occur, revert DNS to previous IP, restore the last known-good backup, and re-open the maintenance window.
- Keep detailed incident notes and timeline.

Post-launch
- Monitor logs and error rates for 24–72 hours.
- Run full Wordfence scan and resolve any new findings.
- Confirm analytics, search indexing, payments and backups functioning as expected.

Commands and helper script
- See `scripts/launch_production.php` for an automated assist script to flush caches, ping sitemaps, run smoke checks, and record results via WP-CLI.

