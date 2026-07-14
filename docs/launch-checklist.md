# Production Launch Checklist

Objective: Switch live keys, submit sitemap, run live smoke tests, and enable monitoring. This checklist is prescriptive—follow items in order and stop for any critical failure.

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

Step 3 — Application Switches
- Put site into maintenance mode (optional) via WP-CLI or plugin.
- Flush caches and rewrites:
  - `wp rewrite flush --path=/path/to/site`
  - Clear object/cache layers (Redis, memcached, Varnish)
- Set `WP_HOME`/`WP_SITEURL` if domain changed.
- Ensure Wordfence WAF is `protecting` and not in `learning` mode.
- Enforce Two-Factor for admin/editor roles (confirm at least two admins have completed setup).

Step 4 — Submit Sitemap
- Regenerate sitemap if necessary (Yoast/WordPress built-in).
- Ping major search engines (example):
  - `https://www.google.com/ping?sitemap=https://yourdomain.com/sitemap.xml`
  - `https://www.bing.com/webmaster/ping.aspx?siteMap=https://yourdomain.com/sitemap.xml`
- Optionally add to Search Console / Bing Webmaster through their UIs/API.

Step 5 — Live Smoke Tests (run immediately)
- Anonymous browse: homepage, articles, images, search — no errors.
- New subscriber signup and onboarding (use a test card in gateway if available).
- Returning subscriber login and access to paid content.
- Admin/editor login (2FA enforced) — publish a small test post.
- Payment success, failure, refund, and webhook handling.
- File upload (image) and media library behavior.
- Contact form / transactional email delivery.

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

