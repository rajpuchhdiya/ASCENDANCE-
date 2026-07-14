# Editor Manual — Ascendance CMS

Audience: Editors and content managers. Purpose: day-to-day content operations.

Access & Accounts
- Login: `https://yourdomain.com/wp-login.php` (use the hidden login slug if configured). Admins and Editors must use Two-Factor Authentication.
- If you lose access, contact Site Admin to unlock or reset; do NOT share recovery codes.

Creating a Post
1. Go to Dashboard → Posts → Add New.
2. Enter title, add content in the block editor. Use headings (H2) for sections.
3. Assign category and featured image in the right-hand panel.
4. Select visibility: Public / Private / Password Protected.
5. Save draft, then Preview to confirm layout and images.
6. Publish when ready. Use schedule feature to publish at a future time.

Media Uploads
- Upload images via Media → Add New or directly in the editor.
- Image guidelines: Web-optimized JPG/PNG, max dimensions 2048px, use descriptive filenames.
- For large uploads, use SFTP and run `wp media import` on server if necessary.

SEO & Metadata
- The site uses Complianz for cookie consent and tag blocking — ensure analytics tags are not embedded directly in post HTML.
- Edit meta descriptions and social preview images in the SEO plugin (if installed).

Payments & Subscriptions (Editors)
- Editors should NOT manage payment gateway credentials.
- To check subscription access, create a test subscriber account and verify gated content shows/hides correctly.

Content Review Workflow
- Use revisions to track changes. To revert, open post → Revisions → Browse changes → Restore.
- For major edits, create a staging draft and notify reviewers.

GDPR / SAR Requests
- Follow `docs/editor-runbook-gdpr.md` (search and export user data). Use the Complianz plugin tooling for consent records.

Troubleshooting (common)
- If images fail to load: check `wp-content/uploads` permissions and run `wp media regenerate`.
- If editor throws errors: disable browser extensions, then check console logs; if WAF blocks requests, whitelist admin IP in Wordfence.

Contacts
- Site Admin: [add contact email]
- Security Lead: [add contact email]

