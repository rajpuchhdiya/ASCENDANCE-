# Ascendance — User Guide
Date: 2026-07-13

Purpose: quick reference for editors, subscribers, and non-technical users on how to operate the Ascendance platform.

1. Overview
- Platform: WordPress-based site with an editorial AI Studio, member paywall, newsletter integration, and analytics.

2. Access & Roles
- Editors / Authors: create content, use AI Studio, publish drafts.
- Subscribers: view gated content according to membership tier; manage account via PMPro account links.
- Administrators: full access to settings and system dashboards.

3. Logging In
- The standard WordPress login address (`/wp-login.php`) is **disabled for security**. Direct access returns a 404 page.
- Use the **custom login portal** instead:
  - Default address: `https://yoursite.com/portal/`
  - Your administrator will provide the exact URL if the default slug has been changed.
- You must pass **Google reCAPTCHA v3** verification before your credentials are accepted.
  reCAPTCHA v3 is **invisible** — no checkbox or puzzle to solve. It runs silently in the background.
  If login repeatedly fails with a security error, try refreshing the page and submitting again.
- If you are an editor, request an Editor role from site admin.

4. Creating Content (Editors)
- Create a new Brief / Dossier / Update via the WordPress admin → Posts → Add New (or from theme "Create" links).
- Use the block editor to author content. You can use the AI features:
  - AI Studio (Admin menu): compile full drafts from source notes. (Access: Users with `edit_posts` capability.)
  - AI SEO sidebar (Block editor right sidebar): generate suggested `title` and `meta description` and apply to draft.

5. Using AI Studio (Admin) — Quick Steps
- Open **AI Studio** from the WP admin sidebar (Editors and Admins).
- Paste source notes, choose Article Type and Provider, then click **Compile Intelligence Draft**.
- Review the generated draft in the right pane. Use `Shorten`, `Expand`, or `Cautious Tone` to refine blocks.
- Copy or push the draft into a WP Draft (copy to clipboard or use editor buttons where available).

6. Draft SEO (Block Editor)
- Open a draft in the block editor, open the **AI SEO** sidebar, click **Generate SEO**.
- Preview the suggested `Title` and `Meta description`. Click **Apply to Draft** to set post title/excerpt and write common SEO plugin meta keys.

7. Membership & Paywall (Subscribers)
- To subscribe or manage membership, visit the site Account pages (PMPro pages). Use the site's checkout to purchase a plan.
- To access gated content, ensure you are logged into a subscribing account with the correct tier.

8. Billing Portal
- Editors & subscribers can be redirected to Stripe's billing portal when the site is configured. Check account pages for a "Manage Billing" link.

9. Newsletter
- Subscribe via site forms (footer or newsletter shortcodes). Confirm subscription through the Brevo confirmation email.

10. Analytics & Privacy
- The site uses GTM / GA4 and Complianz consent gating. If you decline analytics, tracking is disabled.

11. Troubleshooting (common issues)
- **Login page returns 404:**
  - You are visiting `/wp-login.php` directly — this is intentional. Use the custom portal URL (e.g. `/portal/`).
  - Contact your administrator for the current login slug if the default has been changed.
- **Login / registration fails with a security error:**
  - Google reCAPTCHA v3 is running silently in the background. This error means the request was flagged as suspicious.
  - Ensure JavaScript is enabled and that `google.com` is not blocked by an ad blocker or browser extension.
  - Try refreshing the page and submitting again — a fresh reCAPTCHA token will be generated.
  - reCAPTCHA is intentionally inactive on local development installs; only active on staging/production.
- AI Studio admin menu not visible:
  - Ensure you are logged in with an Editor/Admin account (capability `edit_posts`).
  - Append `?ai_debug=1` to the admin URL while logged in as an Administrator to see debug notice that confirms the plugin loaded.
  - Check `wp-content/debug.log` for errors if the notice does not appear.
- AI SEO sidebar missing in block editor:
  - Confirm `wp-content/plugins/ascendance-core/assets/js/ai-seo-editor.js` exists and no JS errors in the browser console.
  - Ensure the plugin is active.
- Generated drafts are blank or error: check that environment API keys are configured for the chosen provider.

12. Local Editor Workflow (quick)
- If you are asked to test locally, follow these quick commands (developer support may be needed):

```bash
# From repository root
composer install
cd wp-content/themes/ascendance && npm ci && npm run build
```

13. Support & Contacts
- For technical issues, contact the site administrator or the development team (see `docs/developer-guide.md` for more details).
