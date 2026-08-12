# Schema Verification Guide — Ascendance Intelligence Platform
**Version:** 1.0 | **Date:** 2026-07-15

Ascendance generates JSON-LD schema automatically for all content types via `class-search-seo.php`. This guide explains how to verify schema is correct after publishing.

---

## Schema Types Generated

| Content Type | Schema Types |
|-------------|-------------|
| Brief (public, FAQ-style) | `NewsArticle` + `FAQPage` |
| Brief (gated) | `AnalysisNewsArticle` |
| Update | `NewsArticle` |
| Dossier | `Report` |
| Author pages | `Person` |
| FAQ page | `FAQPage` |

---

## 1. Verify with Google Rich Results Test

This is the primary verification method. Do this after every major publish.

**Steps:**
1. Open [https://search.google.com/test/rich-results](https://search.google.com/test/rich-results)
2. Paste the full URL of the published Brief/Update/Dossier
3. Click **Test URL**
4. Wait for the crawl to complete (30–60 seconds)
5. Review results:
   - ✅ **"Page is eligible for rich results"** — schema is valid
   - ⚠️ **Warnings** — schema is valid but some optional fields are missing (acceptable)
   - ❌ **Errors** — schema is invalid (must fix before launch)

**Expected results per content type:**
- Public Brief (FAQ-style): should show `NewsArticle` + `FAQPage` eligible
- Gated Brief: should show `NewsArticle` eligible (FAQPage only if H2 questions present)
- Dossier: should show `NewsArticle` or `Report` eligible

---

## 2. Inspect Raw JSON-LD in Browser

**Steps:**
1. Open the Brief URL in Chrome/Firefox
2. Right-click → **View Page Source**
3. Search (`Ctrl+F`) for `application/ld+json`
4. You should see one or more `<script type="application/ld+json">` blocks

**What to look for:**
```json
{
  "@context": "https://schema.org",
  "@type": "NewsArticle",
  "headline": "What is the US-DRC Strategic Partnership?",
  "datePublished": "2026-07-15T10:00:00+00:00",
  "dateModified": "2026-07-15T10:00:00+00:00",
  "author": { "@type": "Person", "name": "Editor Name" },
  "publisher": {
    "@type": "Organization",
    "name": "Ascendance Strategies",
    "logo": { "@type": "ImageObject", "url": "https://yoursite.com/logo.png" }
  },
  "description": "A primer on the framework...",
  "image": "https://yoursite.com/wp-content/uploads/featured.jpg"
}
```

**FAQPage block (for FAQ-style Briefs):**
```json
{
  "@type": "FAQPage",
  "mainEntity": [
    {
      "@type": "Question",
      "name": "What does the partnership cover?",
      "acceptedAnswer": { "@type": "Answer", "text": "..." }
    }
  ]
}
```

---

## 3. Common Schema Errors and Fixes

| Error | Cause | Fix |
|-------|-------|-----|
| `Missing field: image` | No featured image set | Set a featured image on the post |
| `Missing field: author` | Post has no author assigned | Assign an author in the post sidebar |
| `Missing field: datePublished` | Post saved as draft, not published | Publish the post |
| `FAQPage not appearing` | No H2 headings in content | Add H2-formatted questions in the Brief body |
| `description too short` | Public excerpt < 50 chars | Expand the Public Excerpt ACF field |
| `publisher logo missing` | Logo not configured in Yoast | SEO → Search Appearance → Organization → Logo |

---

## 4. Validate with Schema.org Validator

For deeper validation beyond Google's Rich Results Test:

1. Open [https://validator.schema.org/](https://validator.schema.org/)
2. Paste the page URL or paste the raw JSON-LD
3. Review any warnings about optional or recommended fields

---

## 5. Monthly Schema Audit

Run this check monthly across key content:
1. Homepage
2. 3 most recent public Briefs
3. 1 Essential Brief
4. 1 Dossier
5. About page (Person schema for authors)

Log results in `docs/QA-checklist.md` under "Schema verification".

---

## 6. Schema Quick-Action in Mission Control

Mission Control → Quick Actions → **Run schema check** triggers a batch validation of the last 10 published posts and logs any schema errors to the activity log.
