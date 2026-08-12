# Editor Manual — Ascendance Intelligence Platform
**Version:** 1.0 | **Date:** 2026-07-15 | **Audience:** Editors, Content Managers, Administrators

---

## 1. Logging In

The standard WordPress login page (`/wp-login.php`) is **hidden for security** — direct access returns a 404.

**Login URL:** `https://yoursite.com/portal/`  
_(On local dev: `http://localhost/Ascendance/portal/`)_

- All admin and editor accounts **must** use **Two-Factor Authentication (2FA)** via Wordfence.
- On first login, Wordfence will prompt you to set up TOTP (Google Authenticator / Authy). Scan the QR code, enter the 6-digit code to confirm.
- Store your recovery codes in a password manager (1Password, Bitwarden). Never share them.
- If you are locked out, contact the Site Admin — do **not** share recovery codes.

---

## 2. Mission Control — Your Daily Dashboard

After logging in, you land on **Mission Control** (the custom Ascendance admin dashboard). It shows everything you need at a glance:

| Widget | What it shows |
|--------|--------------|
| **Subscribers** | Active count by tier (Essential / Professional / Enterprise), new signups this week, cancellations |
| **This Week** | Published, in draft, scheduled posts |
| **AI Usage** | Drafts generated this month, running cost, % of monthly cap used |
| **Alerts** | Security notices, backup status, any system issues |
| **Drafts Pending Review** | Posts awaiting editorial review — with one-click Review / Publish / Schedule |
| **Recent Activity** | Last 10 events: publishes, signups, webhooks, digest sends |
| **Top Content** | This month's most-read Briefs with read counts and subscriber conversion |
| **Site Health** | WordPress version, plugin status, PHP, SSL, backup, 2FA, Stripe/Brevo/AI API status |

**Quick Actions** (right column): + New Brief · + New Update · + New Dossier · Open AI Studio · Send digest · Run schema check

---

## 3. Content Types

Ascendance has **three custom post types** (not standard WordPress Posts):

| Type | Purpose | Typical length | Access |
|------|---------|----------------|--------|
| **Brief** | Deep analysis, FAQ-style, AEO-optimised | 800–2,500 words | Public, Essential, or Professional |
| **Update** | Short-form, time-sensitive news items | 150–400 words | Essential or Professional |
| **Dossier** | Long-form research document | 2,500–8,000 words | Professional |

Access these from the left admin sidebar: **Briefs · Updates · Dossiers**

---

## 4. Creating a Brief

### 4.1 Manual creation

1. Go to **Briefs → Add New Brief**
2. Fill in the required fields:

| Field | Where | Notes |
|-------|-------|-------|
| **Title** | Top of editor | The H1. Keep under 70 characters for SEO |
| **Subhead / Dek** | ACF panel (right sidebar) | 1–2 sentences. Describes the angle |
| **Analytical Claim** | ACF panel | The core argument in one sentence. Visible to non-subscribers |
| **Public Excerpt** | ACF panel | 50–80 words. Visible to non-subscribers. Your citation surface for AI engines |
| **Body** | Block editor | Use H2 for main sections (they become FAQ schema). Lead paragraph must be standalone-citable |
| **Key Takeaways** | ACF repeater block | 3–5 bullet points. Displayed prominently — also lifted by AI engines |
| **Sources** | ACF repeater block | Publication name + URL |
| **Related Briefs** | ACF post-object block | Link 2–3 related Briefs for internal linking |

3. **Right sidebar — Taxonomies:**
   - **Tier**: `public` / `essential` / `professional` — controls who sees the full content
   - **Topic**: select from existing topics (e.g. Critical Minerals, Geopolitics). Add new sparingly
   - **Region**: select all relevant regions (DRC, United States, Sahel, etc.)
   - **Tags**: entity-level tags (company names, individuals, projects). Add freely

4. **Right sidebar — Featured Image:** Always set one. Use 1200×630px minimum, descriptive filename.

5. **Yoast SEO panel** (below editor): Set the focus keyphrase, review the readability score, edit meta description if needed. Aim for green on both.

6. Click **Save Draft**, then **Preview** to check layout.

7. When ready: **Publish** or **Schedule** (use Schedule for a specific date/time).

### 4.2 AEO content structure (follow every time)

```
Block 1 — Lead paragraph (40–80 words, definitional, standalone-citable)
Block 2 — Key Takeaways (ACF block, 3–5 bullets)
Block 3 — H2: First question (2–3 paragraphs)
Block 4 — H2: Second question (comparison or context)
Block 5 — H2: What has happened so far? (timeline)
Block 6 — Related Briefs (ACF post-object)
Block 7 — Sources (ACF repeater)
```

> Every H2 becomes a potential FAQ schema entry. Write H2s as questions when the Brief is FAQ-style.

---

## 5. Creating an Update

Updates are short, time-sensitive items linked to a parent Brief.

1. Go to **Updates → Add New Update**
2. Fields: Title · Body (short) · Linked Brief (ACF post-object) · Tier · Region · Tags
3. Setting the **Linked Brief** triggers a notification email to subscribers who follow that Brief's topic.
4. Updates always use the **essential** or **professional** tier — never `public`.
5. Publish immediately — Updates are time-sensitive.

---

## 6. Creating a Dossier

Dossiers are long-form research documents, Professional-tier only.

1. Go to **Dossiers → Add New Dossier**
2. Follow the same field structure as Briefs, but:
   - No AEO requirement — narrative structure is fine
   - Always tier: `professional`
   - Include an **Executive Summary** block at the top (ACF field)
   - Sources section is mandatory
3. Dossiers typically go through a longer editorial review — save as Draft and use **Send to editor for review** in the AI Studio if AI-assisted.

---

## 7. Using AI Studio to Draft Content

AI Studio is your primary drafting tool. It lets you generate a full Brief draft using Claude (default), GPT-4, or Gemini, then review and humanise before publishing.

**Access:** Admin sidebar → **AI Studio**

### Workflow (5 steps):

**Step 1 — Input**
- Select: Article type · Tier · Topic · Region(s)
- Paste your raw notes, source URLs, press releases, or a structured outline into the Source Notes box
- Optionally paste 2–3 existing Briefs as voice reference (copy their full text)
- Select AI provider (Claude is recommended for long-form analysis)
- Select system prompt: `Editorial voice (default)` for most Briefs

**Step 2 — Generate**
- Click **Generate Draft**
- The draft streams into the output panel in real time. Token count and cost update live.
- Do not navigate away while generating.

**Step 3 — Review and Humanise** _(the most important step)_
- Read every paragraph. Your job is to make it indistinguishable from a hand-written Ascendance Brief.
- Use the inline tools: `🔄 Regenerate` · `✂ Shorten` · `➕ Expand` · `🎯 More cautious`
- Fact-check every claim. Remove anything that sounds AI-generated (hedging, filler, generic transitions).
- Add your own editorial judgment, nuance, and named-entity precision.

**Step 4 — Images**
- AI Studio suggests image prompts (e.g. "Wide shot, Port of Lobito, container cranes, dawn light")
- Source images manually — do **not** use AI-generated images in Ascendance content (Phase 1 policy)
- Upload sourced images via Media Library and set as Featured Image

**Step 5 — Save & Publish**
- Click **Save as Draft** to push the draft into WordPress as a Draft Brief/Update/Dossier
- Or **Send to Editor for Review** to flag for another editor
- Complete the ACF fields (analytical claim, public excerpt, key takeaways, sources) in the post editor
- Run the Yoast SEO check before publishing

### Monthly cap
AI Studio enforces a monthly cost cap (default $25). When the cap is reached, Generate is disabled. Contact Site Admin to raise the cap.

---

## 8. Setting Tier Access

Every Brief, Update, and Dossier has a **Tier** taxonomy term. This controls who sees the full content.

| Tier | Who can read | Use for |
|------|-------------|---------|
| `public` | Everyone (including search engines and AI crawlers) | Explainer Briefs, FAQ content, pillar pages |
| `essential` | Essential + Professional + Enterprise subscribers | Most Briefs and all Updates |
| `professional` | Professional + Enterprise subscribers | Dossiers and sensitive analysis |

> **Paywall logic:** Non-subscribers see: Headline · Subhead · Analytical Claim · Public Excerpt · First paragraph · Paywall block ("Subscribe to read"). This is server-side — it cannot be bypassed by disabling JavaScript.

---

## 9. Managing Topics, Regions, and Tags

**Topics** (`topic` taxonomy — hierarchical):
- Add topics sparingly. Before creating a new topic, check if an existing one fits.
- Use broad topics (e.g. Critical Minerals) as parents; specific sub-topics as children.
- Go to **Topics** in the admin sidebar to manage the hierarchy.

**Regions** (`region` taxonomy — flat):
- Always assign at least one region to every piece of content.
- Use the most specific region that applies (e.g. "DRC" rather than "Africa" if the content is DRC-specific).

**Tags** (`intelligence_tag` taxonomy):
- Tags are entity-level: company names, person names, project names, instruments (e.g. `Lobito Atlantic Railway`, `KoBold Metals`, `Gécamines`).
- Add tags freely — they become the foundation for Phase 2 entity profile pages.
- Use consistent naming: `Lobito Atlantic Railway` not `Lobito railway` or `LAR`.

---

## 10. Multilingual Content (Polylang — EN + FR)

Ascendance publishes in **English and French**.

1. Open any Brief/Update/Dossier in the editor.
2. In the right sidebar, find the **Languages** panel (Polylang).
3. The current language is shown. To create the French translation:
   - Click the **+** icon next to `French`
   - A new draft opens — write/paste the French translation
   - The two posts are linked — changing tier/taxonomies on one side propagates to both
4. For taxonomy terms (topics, regions), translate them under **Languages → Translations** in the admin sidebar.
5. The language switcher in the frontend header is automatic — Polylang handles routing.

> Always publish both language versions at the same time. A French subscriber seeing an English-only post is a bad experience.

---

## 11. SEO — Yoast SEO Panel

Every post has a Yoast SEO panel below the block editor.

| Field | Guidance |
|-------|---------|
| **Focus keyphrase** | The primary search term (e.g. "US-DRC Strategic Partnership") |
| **SEO title** | Defaults to post title. Keep under 60 characters |
| **Meta description** | 120–156 characters. Include the focus keyphrase. This is what appears in Google results |
| **Social preview** | Set a custom Open Graph image if the featured image is not ideal for social sharing |

- **Green readability**: aim for green. Orange is acceptable. Red = rewrite.
- **Green SEO**: aim for green. The most important checks: keyphrase in title, keyphrase in first paragraph, outbound links.

> Our custom plugin layers AnalysisNewsArticle, FAQPage, and Report JSON-LD schema on top of what Yoast provides. You do not need to manage schema manually — it is automatic based on content type and structure.

---

## 12. Media Uploads

- **Accepted formats:** JPG, PNG, WebP, PDF (for dossier attachments)
- **Image guidelines:** Minimum 1200×630px for Featured Images. Use descriptive filenames (`lobito-port-2026.jpg` not `IMG_4892.jpg`).
- **WebP:** EWWW Image Optimizer automatically converts uploads to WebP for faster page loads.
- **Large files:** Upload directly via Media → Add New. For bulk uploads, use SFTP + `wp media import`.
- **Alt text:** Always fill in the alt text field. Describe the image for accessibility.

---

## 13. Subscriber Management (Admins)

### Viewing subscribers
Go to **Mission Control → Subscribers widget** or **Users → All Users** and filter by role (Essential / Professional / Enterprise).

### Granting a comp subscription
1. Go to **Users → Edit User** for the target user
2. Scroll to the **Ascendance** meta box
3. Set: Tier (dropdown) · Expiry date (optional) · Notes
4. Save. The role is assigned immediately without going through Stripe.
5. Comp subscriptions are tagged `source=comp` in the subscriber table — they do not affect revenue stats.

### Cancellations and refunds
- Cancellations are managed through Stripe. When a subscription is cancelled, the webhook automatically demotes the user's role.
- For refunds, issue the refund in the Stripe dashboard — the webhook handles the WordPress side.
- If a webhook fails, you can manually demote the user role from their User profile.

---

## 14. Sending Newsletter Campaigns (Brevo)

Ascendance uses **Brevo** (formerly Sendinblue) for newsletter campaigns and the weekly digest.

1. Log into Brevo at `https://app.brevo.com` (credentials in 1Password)
2. Go to **Campaigns → Create a campaign**
3. Audience: Select the Ascendance subscriber list (filtered by tier if needed)
4. Design: Use the Ascendance template (saved in Brevo)
5. Subject line: Keep under 50 characters. Include the most newsworthy item.
6. Schedule: Wednesday 8:00 AM GMT for the weekly digest (established send time)
7. After sending: check Brevo analytics (open rate, click rate, unsubscribes) in 24 hours

The **weekly digest** is triggered automatically by the plugin (every Wednesday). You only need to log into Brevo for ad-hoc campaigns.

---

## 15. GDPR — Handling Subject Access Requests (SARs)

If a subscriber emails requesting their data or erasure:

1. Follow `docs/editor-runbook-gdpr.md` for the step-by-step process
2. Use **Tools → Export Personal Data** in WP admin to export their data
3. Use **Tools → Erase Personal Data** to erase on confirmed erasure requests
4. Response deadline: **30 days** from the request date (GDPR requirement)
5. Log the request and response in the SAR register (see GDPR doc)

---

## 16. Troubleshooting (Common Issues)

| Problem | Cause | Fix |
|---------|-------|-----|
| Images not loading | Upload permissions | Check `wp-content/uploads` permissions — must be writable |
| Editor throws errors | Browser extension conflict | Disable extensions, try incognito mode |
| Admin menu not loading | WAF blocking | Whitelist your IP in Wordfence (Firewall → Whitelist IPs) |
| AI Studio returns blank | Missing API key | Check `.env` for `ANTHROPIC_API_KEY` / `OPENAI_API_KEY` |
| Paywall not gating | Tier not set | Check the Tier taxonomy is assigned to the post |
| Schema check failing | Missing ACF fields | Ensure Analytical Claim and Public Excerpt are filled |
| Translations not showing | Polylang not linked | Open both language versions and link them in the Languages panel |

---

## 17. Contacts

| Role | Contact |
|------|---------|
| **Site Admin** | [add contact email] |
| **Security Lead** | [add contact email] |
| **Development** | [add contact email / GitHub] |
| **Brevo account** | Credentials in 1Password → Ascendance → Brevo |
| **Stripe dashboard** | Credentials in 1Password → Ascendance → Stripe |

---

## 18. Key URLs (Bookmark These)

| Resource | URL |
|----------|-----|
| Admin login | `https://yoursite.com/portal/` |
| Mission Control | `https://yoursite.com/wp-admin/` |
| AI Studio | `https://yoursite.com/wp-admin/admin.php?page=ascendance-ai-studio` |
| Brevo campaigns | `https://app.brevo.com` |
| Stripe dashboard | `https://dashboard.stripe.com` |
| Google Search Console | `https://search.google.com/search-console` |
| Google Rich Results Test | `https://search.google.com/test/rich-results` |
| Yoast knowledge base | `https://yoast.com/help/` |
