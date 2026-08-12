# W3 Total Cache — Configuration Guide
**Version:** 1.0 | **Date:** 2026-07-15 | **Plugin:** W3 Total Cache (free)

W3 Total Cache replaced WP Super Cache as the caching plugin per the Ascendance spec (Section 2.2). This guide covers setup and recommended settings.

---

## Initial Setup

1. Go to **Performance → General Settings** in WP admin
2. Work through each section as documented below

---

## Recommended Settings (Local Dev vs Production)

### Page Cache
| Setting | Local Dev | Production |
|---------|-----------|-----------|
| Enable | ❌ Off | ✅ On |
| Page cache method | — | Disk: Enhanced |
| Cache mobile | — | ✅ On |
| Cache SSL (HTTPS) | — | ✅ On |

> On local dev, leave page cache **off** — it makes debugging harder.

### Minify
| Setting | Local Dev | Production |
|---------|-----------|-----------|
| Enable | ❌ Off | ✅ On |
| HTML minify | — | ✅ On |
| JS minify | — | ✅ On |
| CSS minify | — | ✅ On |
| JS delivery method | — | Non-blocking (defer) |

> Test minification on staging before production — some plugins are incompatible with JS minification.

### Browser Cache
| Setting | Value |
|---------|-------|
| Enable | ✅ On (all environments) |
| Set Last-Modified header | ✅ On |
| Set Expires header | ✅ On |
| Expires max-age for CSS/JS | 1 year (31536000 seconds) |
| Expires max-age for images | 1 month (2592000 seconds) |

### Database Cache
| Setting | Value |
|---------|-------|
| Enable | ❌ Off (Phase 1 — enable only if DB queries are slow) |

### Object Cache
| Setting | Value |
|---------|-------|
| Enable | ❌ Off (Phase 1 — enable only if a Redis/Memcached server is available) |

### CDN (if using)
| Setting | Value |
|---------|-------|
| Enable | ✅ On (if using Cloudflare or similar) |
| CDN type | Cloudflare / Generic Mirror |

---

## After Configuration — Test Steps

1. **Flush all caches:** Performance → Dashboard → Empty All Caches
2. **Visit your site in incognito mode** — check pages load correctly
3. **Check headers with Chrome DevTools:**
   - Open DevTools → Network tab
   - Reload the page
   - Click on a CSS/JS file — check Response Headers for `Cache-Control: max-age=...`
4. **Test minification:** View Page Source — confirm HTML is minified (whitespace removed)
5. **Test with PageSpeed Insights:** [https://pagespeed.web.dev/](https://pagespeed.web.dev/) — score should improve vs. uncached

---

## Cache Flush Triggers

W3 Total Cache automatically flushes the page cache when:
- A post is published or updated
- A comment is approved

Manual flush needed when:
- You update a plugin or theme
- You change `.htaccess` rules
- You change W3TC settings

**Flush command:** Performance → Dashboard → Empty All Caches  
Or via WP-CLI: `wp w3-total-cache flush all`

---

## Compatibility Notes

| Plugin | Compatibility | Notes |
|--------|--------------|-------|
| Yoast SEO | ✅ Compatible | No known conflicts |
| Wordfence | ✅ Compatible | Wordfence WAF runs before cache — no conflict |
| EWWW Image Optimizer | ✅ Compatible | WebP delivery works alongside W3TC |
| Polylang | ✅ Compatible | Cache respects language URL variants |
| WP Mail SMTP | ✅ Compatible | Email is not cached |

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| Site looks broken after enabling | Flush all caches; check JS minification — disable if conflict |
| Admin slow or broken | W3TC should exclude `/wp-admin/` by default; verify in Minify exceptions |
| CSS not updated after theme change | Flush all caches; check browser cache with hard refresh (`Ctrl+Shift+R`) |
| Polylang language switch broken | Ensure "Cache different versions for logged-in users" is ON |
