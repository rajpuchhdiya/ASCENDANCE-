# Legacy URL Migration Map & 301 Redirection Guide

This document maps legacy pages and post routes from the previous Elementor-based site to the new Ascendance custom post types and page templates. It provides instructions on performing migrations and managing 301 redirects to preserve SEO and link equity.

---

## 1. Automated CLI Migration Utility

To migrate historical Elementor-based posts and pages into clean Gutenberg blocks and target custom post types (`brief`, `update`, or `dossier`), use the CLI migration script [migrate-legacy.php](file:///c:/XAMPP/htdocs/Ascendance/scripts/migrate-legacy.php).

### Execution Instructions
Run the script using the PHP command-line interface from the project root:

```bash
# Run a dry-run to simulate the migration and print the redirect map
php scripts/migrate-legacy.php --dry-run --source-type=post --target-type=brief

# Run a live migration for standard posts to Intelligence Brief CPT
php scripts/migrate-legacy.php --source-type=post --target-type=brief --tier=professional --topic=critical-minerals

# Migrate a single post ID for testing
php scripts/migrate-legacy.php --post-id=105 --target-type=update --tier=essential
```

---

## 2. Server-Level Redirect Rules (`.htaccess`)

For performance and immediate edge handling, critical global and wildcard redirect rules are hardcoded in [.htaccess](file:///c:/XAMPP/htdocs/Ascendance/.htaccess):

```apache
# BEGIN Legacy 301 Redirects
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /Ascendance/

  # Specific Page Redirects
  RewriteRule ^contact-us/?$ contact/ [R=301,L]
  RewriteRule ^services-and-industries/?$ services/ [R=301,L]

  # Blog to Intelligence Wildcard redirects
  RewriteRule ^blog/(.*)$ intelligence/$1 [R=301,L]
  RewriteRule ^blog/?$ intelligence/ [R=301,L]
</IfModule>
# END Legacy 301 Redirects
```

---

## 3. Redirect Mapping Directory

| Legacy Source URL | Target URL | Redirect Code | Notes |
| :--- | :--- | :--- | :--- |
| `/contact-us/` | `/contact/` | `301 (Permanent)` | Form page replacement |
| `/services-and-industries/` | `/services/` | `301 (Permanent)` | Marketing services page |
| `/blog/` | `/intelligence/` | `301 (Permanent)` | Main archive home |
| `/blog/(.*)` | `/intelligence/$1` | `301 (Permanent)` | Wildcard redirect for historical articles |

---

## 4. Rank Math / Yoast Redirection CSV Export

To import these redirects directly into SEO managers (Rank Math Redirection or Yoast SEO Premium), save the list below as a `.csv` file and upload it in the plugin settings dashboard:

```csv
source,target,code,regex
/contact-us/,/contact/,301,0
/services-and-industries/,/services/,301,0
/blog/,/intelligence/,301,0
/blog/(.*),/intelligence/$1,301,1
```

*Note: For the wildcard rule, ensure the "Regex" option is enabled in the SEO redirect manager UI.*
