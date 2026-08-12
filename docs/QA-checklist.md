# QA Checklist — Ascendance Intelligence Platform
**Version:** 2.0 | **Date:** 2026-07-15

Follow each section manually on staging. Mark Pass/Fail. Create a ticket for every failure with reproduction steps. All P1 (launch blocker) items must pass before go-live.

---

## 1. Anonymous Visitor Flow

| # | Test | Priority | Result |
|---|------|----------|--------|
| 1.1 | Visit homepage — loads without errors, CSS/JS load correctly | P1 | Pass / Fail |
| 1.2 | Visit 3+ public Brief pages — content renders, no PHP errors | P1 | Pass / Fail |
| 1.3 | Visit a gated Brief — paywall block shows (not full content) | P1 | Pass / Fail |
| 1.4 | Cookie consent banner appears on first visit | P1 | Pass / Fail |
| 1.5 | Declining cookies blocks GA4 / GTM from loading | P1 | Pass / Fail |
| 1.6 | No admin endpoints exposed (`/wp-admin/` returns 302 or 404 for guests) | P1 | Pass / Fail |
| 1.7 | `/wp-login.php` returns 404 (WPS Hide Login active) | P1 | Pass / Fail |
| 1.8 | Custom login URL (`/portal/` or configured slug) loads the login form | P1 | Pass / Fail |
| 1.9 | Site search returns relevant results for test query "DRC cobalt" | P2 | Pass / Fail |
| 1.10 | Language switcher (EN/FR) changes language and URL | P2 | Pass / Fail |

---

## 2. New Subscriber Sign-Up

| # | Test | Priority | Result |
|---|------|----------|--------|
| 2.1 | Subscribe page (`/subscribe/`) loads with three tier cards | P1 | Pass / Fail |
| 2.2 | Click "Choose Essential" → redirects to Stripe Checkout | P1 | Pass / Fail |
| 2.3 | Complete Stripe test checkout → redirects to `/membership-confirmation/` | P1 | Pass / Fail |
| 2.4 | User account created with `ascendance_essential` role | P1 | Pass / Fail |
| 2.5 | Welcome email received (check WP Mail SMTP logs) | P1 | Pass / Fail |
| 2.6 | New subscriber can log in and access Essential content | P1 | Pass / Fail |
| 2.7 | New subscriber cannot access Professional content | P1 | Pass / Fail |
| 2.8 | `/membership-confirmation/` shows magic-link password setup CTA | P2 | Pass / Fail |

---

## 3. Returning Subscriber

| # | Test | Priority | Result |
|---|------|----------|--------|
| 3.1 | Login with email + password → 2FA prompt (if 2FA enabled on account) | P1 | Pass / Fail |
| 3.2 | Successful login → redirects to `/dashboard/` | P1 | Pass / Fail |
| 3.3 | Dashboard shows tier-appropriate recent content | P1 | Pass / Fail |
| 3.4 | Account page (`/account/`) shows current tier and renewal date | P2 | Pass / Fail |
| 3.5 | "Manage Billing" button redirects to Stripe Customer Portal | P2 | Pass / Fail |
| 3.6 | Preferences page (`/account/preferences/`) loads and saves topic notifications | P2 | Pass / Fail |

---

## 4. Editor / Admin Flows

| # | Test | Priority | Result |
|---|------|----------|--------|
| 4.1 | Admin login enforces 2FA (Wordfence) | P1 | Pass / Fail |
| 4.2 | Mission Control loads in under 2 seconds | P1 | Pass / Fail |
| 4.3 | Mission Control shows correct subscriber count by tier | P2 | Pass / Fail |
| 4.4 | Editor can create, save, preview, and publish a Brief | P1 | Pass / Fail |
| 4.5 | All ACF fields (Analytical Claim, Public Excerpt, Key Takeaways, Sources) save correctly | P1 | Pass / Fail |
| 4.6 | AI Studio opens and generates a draft from sample source notes | P1 | Pass / Fail |
| 4.7 | Tier assignment on a Brief gates content correctly (verify as subscriber) | P1 | Pass / Fail |
| 4.8 | Media upload (JPG/PNG) succeeds; EWWW optimizes and generates WebP | P2 | Pass / Fail |
| 4.9 | Revisions are saved on post edits | P2 | Pass / Fail |
| 4.10 | Polylang translation link creates French language version | P2 | Pass / Fail |

---

## 5. Payment Edge Cases

| # | Test | Priority | Result |
|---|------|----------|--------|
| 5.1 | Stripe test card (4242...) completes checkout | P1 | Pass / Fail |
| 5.2 | Stripe declined card (4000...0002) shows error, no account created | P1 | Pass / Fail |
| 5.3 | Subscription cancellation via Stripe portal — webhook demotes user role | P1 | Pass / Fail |
| 5.4 | `invoice.payment_failed` webhook sends "card declined" email | P1 | Pass / Fail |
| 5.5 | Duplicate webhook replay does not create duplicate records (idempotency) | P1 | Pass / Fail |
| 5.6 | Comp subscription granted manually; user gets tier access | P2 | Pass / Fail |

---

## 6. Transactional Email (WP Mail SMTP)

| # | Test | Priority | Result |
|---|------|----------|--------|
| 6.1 | WP Mail SMTP → Send Test Email → email received in inbox (not spam) | P1 | Pass / Fail |
| 6.2 | New subscriber welcome email arrives within 2 minutes | P1 | Pass / Fail |
| 6.3 | Password reset email arrives and link works | P1 | Pass / Fail |
| 6.4 | Email headers show correct From name ("Ascendance Strategies") and address | P2 | Pass / Fail |

---

## 7. Redirects (Redirection Plugin)

| # | Test | Priority | Result |
|---|------|----------|--------|
| 7.1 | Redirection plugin active — Tools → Redirection loads | P1 | Pass / Fail |
| 7.2 | Monitor permalink changes enabled | P1 | Pass / Fail |
| 7.3 | 404 error logging enabled | P2 | Pass / Fail |
| 7.4 | Test a manual redirect rule (e.g. `/old-page/` → `/intelligence/`) | P1 | Pass / Fail |
| 7.5 | All URLs from migration redirect map (`docs/migration-redirects-map.md`) return 301 | P1 | Pass / Fail |

---

## 8. Caching (W3 Total Cache)

| # | Test | Priority | Result |
|---|------|----------|--------|
| 8.1 | W3TC enabled with page cache on | P1 | Pass / Fail |
| 8.2 | First page load creates a cache file (check W3TC → Page Cache → Stats) | P2 | Pass / Fail |
| 8.3 | Second load of same page served from cache (faster, `X-Powered-By: W3 Total Cache` or similar header) | P2 | Pass / Fail |
| 8.4 | Browser cache headers present on CSS/JS/images (check DevTools → Network) | P2 | Pass / Fail |
| 8.5 | Cache flushes after publishing a new Brief | P1 | Pass / Fail |
| 8.6 | Admin pages (`/wp-admin/`) are NOT cached | P1 | Pass / Fail |

---

## 9. Schema / JSON-LD Verification

| # | Test | Priority | Result |
|---|------|----------|--------|
| 9.1 | Public Brief passes Google Rich Results Test (`NewsArticle` eligible) | P1 | Pass / Fail |
| 9.2 | FAQ-style Brief shows `FAQPage` schema in Rich Results Test | P1 | Pass / Fail |
| 9.3 | Dossier shows `NewsArticle` or `Report` schema | P2 | Pass / Fail |
| 9.4 | Schema includes `publisher.logo` (set in Yoast → Organization) | P2 | Pass / Fail |
| 9.5 | Schema includes `author.name` on all Briefs | P1 | Pass / Fail |

---

## 10. WebP Image Verification

| # | Test | Priority | Result |
|---|------|----------|--------|
| 10.1 | Upload a JPG — EWWW generates `.webp` version | P1 | Pass / Fail |
| 10.2 | Chrome receives `Content-Type: image/webp` for images (check DevTools → Network) | P1 | Pass / Fail |
| 10.3 | PageSpeed Insights: "Serve images in next-gen formats" is resolved or minimal | P2 | Pass / Fail |
| 10.4 | Images are lazy-loaded (check `loading="lazy"` attribute in page source) | P2 | Pass / Fail |

---

## 11. Search QA

| # | Test | Query | Expected result |
|---|------|-------|----------------|
| 11.1 | Basic search | "DRC cobalt" | Briefs tagged with DRC and Cobalt appear in top 5 |
| 11.2 | Exact title search | "US-DRC Strategic Partnership" | The public Brief appears first |
| 11.3 | Topic search | "Sakania-Lobito Corridor" | Related Briefs and Updates appear |
| 11.4 | No results | "xyznotarealterm" | "No results found" message, not an error |
| 11.5 | Gated content | "professional dossier cobalt" | Non-subscribers see paywall on result click |
| 11.6 | Multilingual | Search in French | French content appears for FR locale |

---

## 12. Security & Backup Checks

| # | Test | Priority | Result |
|---|------|----------|--------|
| 12.1 | Wordfence WAF in Protect mode (not Learning mode) | P1 | Pass / Fail |
| 12.2 | Wordfence scan: no critical findings | P1 | Pass / Fail |
| 12.3 | UpdraftPlus scheduled backup exists and ran successfully | P1 | Pass / Fail |
| 12.4 | Latest backup uploaded to remote storage (S3/Dropbox/Google Drive) | P1 | Pass / Fail |
| 12.5 | 2FA enforced — admin accounts cannot log in without second factor | P1 | Pass / Fail |
| 12.6 | `/wp-login.php` returns 404 | P1 | Pass / Fail |

---

## Launch Blocker Criteria (All Must Pass Before Go-Live)

- [ ] All P1 items above: Pass
- [ ] No critical Wordfence findings unresolved
- [ ] Payment flow works end-to-end (Stripe test mode)
- [ ] Transactional email delivered to inbox (not spam)
- [ ] GDPR cookie consent blocking analytics until consent
- [ ] Admin 2FA verified for at least 2 admin accounts
- [ ] Backups scheduled with remote storage confirmed
- [ ] All migration redirects return 301

---

## Notes / Tickets

_Create a ticket per failing item. Paste reproduction steps and any error logs._
