# Training Videos Plan — Ascendance Intelligence Platform
**Version:** 1.0 | **Date:** 2026-07-15 | **Format:** Loom (or equivalent screen-recorder), 5–12 min each

---

## Overview

Ten training videos covering everything editors and administrators need to operate the Ascendance platform from day one. Record with Loom at 1080p. Show mouse clicks, speak clearly, and keep credentials off-screen. Include a timestamped index in each Loom description.

Store Loom links in the private handover folder (1Password Secure Notes → Ascendance → Training Videos).

---

## Video 1 — Mission Control Tour
**Duration:** 6–8 min | **Audience:** Editors, Admins

**What to cover:**
- How to log in (custom portal URL, 2FA prompt)
- Landing on Mission Control — overview of all widgets
- Subscriber widget: reading tier counts, what "↑ 8 new" means
- This Week widget: difference between published / draft / scheduled
- AI Usage widget: understanding the monthly cap and cost tracker
- Alerts widget: what triggers an alert, how to act on it
- Drafts Pending Review: one-click Review → Publish workflow
- Recent Activity log: types of events shown
- Quick Actions: launching AI Studio, creating new content, sending digest
- Site Health strip: what each indicator means and who to call if red

**Script outline:**
1. Open Mission Control and pause on the full layout — narrate each section
2. Click into the Subscriber detail view, show tier breakdown
3. Open a pending draft from the Drafts widget and walk through the review options
4. Point out Site Health — click each indicator to show what it links to
5. Wrap up with Quick Actions — demonstrate opening AI Studio

---

## Video 2 — Creating a Brief Manually
**Duration:** 8–10 min | **Audience:** Editors

**What to cover:**
- Where to find the Brief editor (Briefs → Add New)
- The AEO content structure (lead paragraph → key takeaways → H2 questions → sources)
- Every required ACF field: Subhead, Analytical Claim, Public Excerpt, Key Takeaways, Sources, Related Briefs
- Setting Tier (public / essential / professional) — what each does to visibility
- Topic, Region, and Tag taxonomy assignment
- Setting the Featured Image
- Yoast SEO panel: focus keyphrase, meta description, readability score
- Saving as draft, previewing, and publishing

**Script outline:**
1. Start from Briefs → Add New — show empty post
2. Write a short sample title and walk through the block editor
3. Open the ACF sidebar panel — fill each field explaining why it matters
4. Set tier to `essential` — explain paywall consequences
5. Assign Topic (Critical Minerals), Region (DRC), and 2 tags
6. Open Yoast panel — enter a keyphrase, watch the analysis update
7. Preview the post — show how paywall renders for non-subscribers
8. Publish

---

## Video 3 — Creating a Brief via AI Studio
**Duration:** 10–12 min | **Audience:** Editors

**What to cover:**
- Opening AI Studio from the admin sidebar
- Understanding the input panel: Article type, Tier, Topic, Regions
- Pasting source notes (show an example with a press release snippet)
- Selecting Claude vs GPT-4 vs Gemini — when to use each
- Choosing the system prompt variant
- Clicking Generate Draft — watching the stream in real time
- Reviewing the draft block by block — the humanise step
- Using inline tools: Regenerate, Shorten, Expand, More Cautious
- Handling image prompt suggestions
- Reviewing the auto-generated Public Excerpt and Key Takeaways
- Save as Draft → completing ACF fields → publishing

**Script outline:**
1. Open AI Studio — walk through the UI layout
2. Paste a short sample press release into Source Notes
3. Select: Brief / Essential / Geopolitics / DRC — hit Generate
4. Walk through the streamed output — narrate what to look for
5. Demonstrate Regenerate on one paragraph that "sounds AI"
6. Show the image prompts — explain the sourcing policy
7. Click Save as Draft — open the resulting Brief in the editor
8. Complete the remaining ACF fields
9. Run Yoast check before publishing

---

## Video 4 — Setting Tier Access & Understanding the Paywall
**Duration:** 5–7 min | **Audience:** Editors, Admins

**What to cover:**
- The three tiers explained: public, essential, professional
- Where to set tier on a post (Tier taxonomy in right sidebar)
- What non-subscribers see: headline, subhead, analytical claim, public excerpt, first paragraph, paywall block
- What Essential subscribers see vs Professional subscribers see
- The consequence of accidentally publishing at `public` tier — and how to fix it
- Testing the paywall: log out or use a different browser with a test subscriber account

**Script outline:**
1. Open an existing Essential Brief — show the Tier panel
2. Log out in a second browser — visit the same Brief URL
3. Scroll through what a non-subscriber sees — identify each visible element
4. Log in as a test Essential subscriber — show full access
5. Log in as a test Professional subscriber — show full access to a Dossier
6. Go back to editor — show how to change tier from essential to public (and warn against it)

---

## Video 5 — Publishing an Update
**Duration:** 5–6 min | **Audience:** Editors

**What to cover:**
- What an Update is vs a Brief
- Navigating to Updates → Add New
- Required fields: Title, Body, Linked Brief (ACF post-object), Tier, Region, Tags
- How linking a parent Brief triggers topic notifications
- Length guidance: 150–400 words, time-sensitive
- Publishing immediately (no scheduling for Updates)
- Confirming the notification email was triggered (check Brevo logs)

**Script outline:**
1. Start from Updates → Add New
2. Write a short 200-word update — use a real headline format
3. Open the ACF panel — set Linked Brief (show the post-object search)
4. Set Tier to Essential, Region to DRC, add 2 tags
5. Click Publish
6. Go to Brevo → Campaigns → Transactional to show the notification sent

---

## Video 6 — Managing Topics, Regions, and Tags
**Duration:** 5–7 min | **Audience:** Editors, Admins

**What to cover:**
- The difference between Topics (hierarchical), Regions (flat), Tags (entity-level)
- Why tag discipline matters — Phase 2 entity profiles depend on it
- How to add a new Topic: Topics → Add New, parent assignment
- How to add a new Region
- Tag naming convention: full proper names, consistent casing
- When NOT to create a new topic (check first — reuse existing)
- Merging duplicate tags (admin task)
- Viewing all taxonomy terms from the admin sidebar

**Script outline:**
1. Go to Topics admin — walk through the hierarchy
2. Try adding a duplicate topic — demonstrate the check
3. Show a well-tagged Brief in the editor with all three taxonomies
4. Go to Tags admin — show how inconsistent tags appear
5. Demonstrate renaming a tag to fix a naming error
6. Go to Regions — add a new country, show it appear in a Brief

---

## Video 7 — Subscriber Management
**Duration:** 7–9 min | **Audience:** Admins

**What to cover:**
- Mission Control subscriber widget vs Users admin
- Filtering Users by role (Essential / Professional / Enterprise)
- Viewing a subscriber's full profile: tier, subscription dates, Stripe customer ID
- Granting a comp subscription: Ascendance meta box, tier, expiry, notes
- Difference between comp and paid subscribers in the stats
- Processing a manual cancellation (role demotion)
- What happens automatically when Stripe fires a cancellation webhook
- Checking webhook logs in Mission Control → Activity

**Script outline:**
1. Open Users → All Users, filter by `ascendance_essential`
2. Open a subscriber profile — show the Ascendance meta box
3. Grant a comp subscription to a test user — set 30-day expiry
4. Verify the user can now access Essential content (second browser)
5. Expire the comp by changing the date — show access revoked
6. Go to Mission Control → Recent Activity — show webhook events

---

## Video 8 — Sending Campaigns via Brevo
**Duration:** 6–8 min | **Audience:** Editors, Admins

**What to cover:**
- Brevo login and dashboard overview
- Navigating to Campaigns → Create Campaign
- Selecting the Ascendance subscriber list (segmentation by tier if needed)
- Using the saved Ascendance email template
- Writing a good subject line (under 50 characters, newsworthy)
- Preview on desktop and mobile before sending
- Scheduling vs sending immediately
- Viewing post-send analytics: open rate, click rate, unsubscribes
- How the automatic weekly digest works (plugin triggers it — no manual action needed)

**Script outline:**
1. Log into Brevo — show the main dashboard
2. Campaigns → Create → select list
3. Choose the saved template — show customisation
4. Write a subject line — preview on mobile
5. Schedule for Wednesday 8AM GMT
6. Show a previous campaign's analytics as an example

---

## Video 9 — Refining the AI System Prompt
**Duration:** 6–8 min | **Audience:** Editors, Admins

**What to cover:**
- Where the system prompt lives: AI Studio → System Prompt dropdown → Edit Prompt
- What the default editorial voice prompt says (tone, structure, style)
- Why the prompt might need adjustment (new editorial direction, voice drift)
- How to test a prompt change: generate a sample Brief, compare with previous output
- Version control: save old prompt before changing (copy to Notes)
- Warning: prompt changes affect all future AI Studio generations
- When to call the developer vs. editing the prompt yourself (scope guidance)

**Script outline:**
1. Open AI Studio — click System Prompt dropdown → Edit Prompt
2. Read through the current default prompt on screen
3. Make a small sample change (e.g. "Add one sentence reminding the AI to cite specific dates")
4. Generate a short sample output — show the effect
5. Revert the change — explain version control best practice
6. Show the prompt history log (if available in the admin UI)

---

## Video 10 — Handling a SAR / Erasure Request (GDPR)
**Duration:** 6–8 min | **Audience:** Admins

**What to cover:**
- What a Subject Access Request (SAR) is and the 30-day deadline
- Where to log the request (SAR register in `docs/editor-runbook-gdpr.md`)
- WP Admin → Tools → Export Personal Data: searching by email, exporting
- What data is included in the export (posts, comments, user meta, Stripe info note)
- WP Admin → Tools → Erase Personal Data: the erasure flow, confirmation step
- What is NOT erased automatically (Stripe billing records — must be done in Stripe dashboard)
- How Complianz handles consent records
- Sending the data export or erasure confirmation to the requester

**Script outline:**
1. Open a test user — navigate to Tools → Export Personal Data
2. Search by email — show the data categories
3. Send the download link (show the process)
4. Navigate to Tools → Erase Personal Data — walk through the confirmation
5. Open the SAR register doc — show how to log the event
6. Open Stripe dashboard — show where to delete customer data there
7. Close with the response template from the GDPR runbook

---

## Recording Notes

- Record at **1080p minimum**
- Show mouse pointer clearly — use a cursor highlighter tool
- Keep credentials **off-screen at all times**
- Use the Loom description field for a timestamped index (e.g. `0:00 Intro · 1:30 Login · 3:00 Mission Control`)
- Keep videos between 5–12 minutes — split if a topic runs longer
- Use a screen reader / closed captions where possible for accessibility

## Delivery

- Loom links stored in: **1Password → Ascendance → Training Videos** (private vault)
- Editor confirmation: each editor completes the short checklist below after watching

## Editor Confirmation Checklist

- [ ] Video 1 — I understand Mission Control and know where to check alerts
- [ ] Video 2 — I can create a Brief manually with all required fields
- [ ] Video 3 — I can use AI Studio to draft a Brief from source notes
- [ ] Video 4 — I understand tier access and can test the paywall
- [ ] Video 5 — I can publish an Update linked to a parent Brief
- [ ] Video 6 — I follow the tag naming convention and know when to add new topics
- [ ] Video 7 — I know how to grant a comp subscription and process a cancellation
- [ ] Video 8 — I can compose and schedule a Brevo campaign
- [ ] Video 9 — I understand the AI system prompt and know the change process
- [ ] Video 10 — I know the SAR process and the 30-day deadline
