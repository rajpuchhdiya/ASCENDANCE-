# WebP & Image Optimization — Verification Guide
**Version:** 1.0 | **Date:** 2026-07-15 | **Plugin:** EWWW Image Optimizer (free)

EWWW Image Optimizer handles local image compression and WebP conversion with no external API calls and no upload limits.

---

## Initial Setup

1. Go to **Media → EWWW Image Optimizer** (or **Settings → EWWW Image Optimizer**)
2. Run the **Setup Wizard** if prompted
3. Configure as below:

---

## Recommended Settings

### Basic Settings
| Setting | Value |
|---------|-------|
| Speed vs. Savings | Slider → push toward Savings (lossless for PNG, lossy for JPG) |
| Convert to WebP | ✅ Enable |
| WebP delivery method | **JS WebP Rewriting** (safest) or **Apache WebP Rewriting** (faster — requires `.htaccess` support) |
| Remove original after conversion | ❌ Off — keep originals as fallback |
| Lazy Loading | ✅ Enable |
| Resize new images | Max-width: `2048px`, Max-height: `2048px` |

### WebP Delivery Method (choose one)

**Option A — JS WebP Rewriting (Recommended for XAMPP / shared hosting)**
- EWWW serves WebP images via JavaScript
- Works on any host, no server config needed
- Tiny JS overhead (~1KB)

**Option B — Apache WebP Rewriting (Better performance)**
- EWWW adds rules to `.htaccess` to serve `.webp` files automatically
- Requires Apache with `mod_rewrite` and `mod_headers` (XAMPP has both)
- Enable under EWWW settings → WebP → Enable Apache WebP Rewriting → Save → EWWW will add the rewrite rules

---

## Verification Steps

### Step 1 — Upload a test image
1. Go to **Media → Add New**
2. Upload a JPG or PNG image (at least 200KB for visible optimization)
3. EWWW should process it immediately — you'll see "Image optimized" in the upload summary

### Step 2 — Confirm WebP was generated
1. Go to **Media → Library**
2. Click the uploaded image → **Edit**
3. In the image URL (e.g. `https://yoursite.com/wp-content/uploads/2026/07/test.jpg`)
4. Manually change `.jpg` to `.webp` in your browser address bar
5. If a WebP version exists, the image loads — ✅ WebP working
6. If it 404s, go to EWWW settings and check WebP conversion is enabled

### Step 3 — Check WebP is being served to Chrome
1. Open your site in **Chrome**
2. Open DevTools → Network → Filter by `Img`
3. Click any image → Response Headers
4. Look for `Content-Type: image/webp`
5. If `Content-Type: image/jpeg` — WebP rewriting is not active (check `.htaccess` or switch to JS method)

### Step 4 — Bulk optimize existing media
1. Go to **Media → Bulk Optimize**
2. Click **Scan for Unoptimized Images**
3. Click **Start Optimizing**
4. Wait for completion (may take several minutes for large media libraries)

---

## Checking the .htaccess WebP Rules (Apache method)

If using Apache WebP Rewriting, EWWW adds these rules to `.htaccess`. Verify they are present:

```apache
# BEGIN EWWW Image Optimizer
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteCond %{HTTP_ACCEPT} image/webp
  RewriteCond %{REQUEST_FILENAME} (.*)\.(jpe?g|png)$
  RewriteCond %{REQUEST_FILENAME}.webp -f
  RewriteRule ^ %{REQUEST_FILENAME}.webp [L,T=image/webp]
</IfModule>
<IfModule mod_headers.c>
  <FilesMatch "\.(jpe?g|png)$">
    Header append Vary Accept
  </FilesMatch>
</IfModule>
# END EWWW Image Optimizer
```

---

## PageSpeed / Lighthouse Verification

After enabling EWWW:
1. Open [https://pagespeed.web.dev/](https://pagespeed.web.dev/)
2. Test a Brief page URL
3. In **Opportunities** section, check:
   - "Serve images in next-gen formats" — should be resolved or much smaller savings
   - "Properly size images" — should show passing if resize limits are set

---

## Troubleshooting

| Problem | Fix |
|---------|-----|
| WebP files not generated | Check EWWW settings → WebP → ensure enabled; run Bulk Optimize |
| Browser still receives JPEG | Switch to JS WebP Rewriting; clear all caches (W3TC + browser) |
| `.htaccess` rules not added | Manually trigger from EWWW → WebP → Test WebP Rewriting → Save |
| Images not optimizing on upload | Check `wp-content/uploads` folder is writable |
| Lazy loading causing layout shift | Adjust lazy loading threshold in EWWW Advanced Settings |
