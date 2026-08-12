=== GT - AI SEO/GEO/AEO Optimizer ===
Contributors: gatetouch, gatetouchteam
Tags: seo, sitemap, schema, meta-tags, open-graph
Requires at least: 5.7
Tested up to: 7.0
Stable tag: 1.4.1
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Complete SEO suite: search appearance templates, schema, sitemaps, redirects, AI crawler control, and one-click import from six SEO plugins.

== Description ==

**GT - AI SEO/GEO/AEO Optimizer** is a complete SEO suite for WordPress covering traditional search, AEO (Answer Engine Optimization) and GEO (Generative Engine Optimization).

Everything below works with **no account, no activation key, and no API key**: search appearance templates for every page type, structured data, XML sitemaps, breadcrumbs, redirects, robots.txt, llms.txt, security.txt, hardening, and the full SEO/AEO/GEO scoring engine. Connecting an AI provider is optional and only unlocks content generation — you bring your own key and pay the provider directly.

= Search Appearance — every page type, not just posts =

Most plugins only template posts and pages. This one resolves the title, description, robots directives, canonical and social tags for **nine page contexts** through a single engine:

* Front page and blog home
* Posts, pages and every custom post type
* Categories, tags and every custom taxonomy
* Author archives
* Date archives
* Post type archives
* Search results
* 404 pages

Each level follows the same order: **per-object override → site template → built-in default**. So a category with no settings of its own still gets a unique, keyword-bearing title automatically, and you can override any individual category, tag or author from its own edit screen.

Templates use **65 variables** — `#title#`, `#sep#`, `#site_title#`, `#excerpt#`, `#term#`, `#term_posts#`, `#author_name#`, `#primary_category#`, `#currentyear#`, custom fields via `#cf_your_field#`, custom taxonomies via `#tax_your_taxonomy#`, plus WooCommerce tags like `#wc_price#`, `#wc_sku#`, `#wc_stock#` and `#wc_brand#`.

= Import from six SEO plugins =

**Yoast SEO, Rank Math, All in One SEO, SEOPress, The SEO Framework and Slim SEO.**

The importer brings across titles, meta descriptions, focus keywords, robots rules, canonicals, social metadata, schema types, redirects and site-wide templates. Template variables are converted automatically, so a Yoast title like `%%title%% %%sep%% %%sitename%%` keeps rendering correctly as `#title# #sep# #site_title#`.

* **Preview before writing.** See exactly what will be imported, with conflict counts and converted samples.
* **Nothing is deleted.** The import only reads from your old plugin.
* **Rollback snapshot.** Site-wide settings are captured before the import begins.
* **Verify pass.** Re-reads both plugins afterwards and reports any field that did not land.
* **Never overwrites your work.** By default only empty fields are filled.

Because this plugin stays silent while another SEO plugin is active, there is never a period with duplicate meta tags — import first, then deactivate the old plugin.

= AI crawler control that does not cost you visibility =

Blocking "AI bots" as one undifferentiated group is the most common way sites remove themselves from AI answers by accident. This plugin governs **19 crawlers in three groups**, each labelled with what blocking it actually costs:

* **Citation crawlers (10)** — OAI-SearchBot, ChatGPT-User, PerplexityBot, Perplexity-User, ClaudeBot, Google-Extended, DuckAssistBot, Amazonbot, Applebot, MistralAI-User. These fetch your pages so assistants can quote and link you. Blocking one removes you from that engine's answers and gains you nothing. Allowed by default.
* **Training crawlers (7)** — GPTBot, CCBot, anthropic-ai, meta-externalagent, Applebot-Extended, cohere-ai, Bytespider. These collect content for future model training. Blocking them has no effect on whether you are cited today, so this is purely an editorial choice.
* **SEO tool crawlers (2)** — AhrefsBot, SemrushBot.

The distinction that matters: **GPTBot is OpenAI's training crawler.** Blocking it does *not* remove you from ChatGPT answers — OAI-SearchBot does. You can opt out of all model training while remaining fully citable.

= SEO, AEO and GEO scoring — no API key required =

Every post gets a unified score combining SEO (40%), AEO (30%) and GEO (30%). All three are computed on your own server from your own content.

**GEO measures five signals:**

* **Passage citability (30)** — generative engines lift self-contained blocks of roughly 134–167 words. Scores how much of your content is shaped that way, plus whether a direct answer lands in the opening 60 words.
* **Evidence density (20)** — specific figures and citations to primary sources. Concrete, attributable claims get quoted; general statements do not.
* **Authority and freshness (20)** — author credentials and a recent update date.
* **Topical authority (20)** — internal linking depth.
* **Multi-modal content (10)** — text combined with images, tables or video.

**AEO measures** question-based headings, FAQ structure, answer patterns and list formatting.

= Free core features =

* **Search Appearance** — templates, robots directives and canonicals for all nine page contexts, with per-post, per-term and per-author overrides.
* **Structured data (JSON-LD)** — one connected `@graph` with stable cross-references: Organization or Person, WebSite with search box, BreadcrumbList, Article, FAQPage, ItemList on archives, Person for authors, and Speakable. Sixteen content schema types selectable per post type.
* **XML sitemaps** — posts, pages, taxonomies, authors, custom post types, images and RSS, with IndexNow notification on publish.
* **Open Graph and Twitter Cards** — with smart image fallback.
* **Breadcrumbs** — with BreadcrumbList schema, three placement modes, and a `[gatetouch_breadcrumbs]` shortcode.
* **Robots.txt manager** — visual rule editor plus the grouped AI crawler controls above.
* **Redirect manager** — 301/302/307/410 with CSV import/export and 404 logging.
* **llms.txt** — generated and served automatically.
* **security.txt** — RFC 9116 compliant.
* **Crawl optimization** — remove head bloat, disable low-value feeds, clean internal search URLs, normalize tracking parameters.
* **Hardening** — remove version exposure, disable XML-RPC, add security headers.
* **Local business** — LocalBusiness schema with address, hours, contact details and ten industry types.
* **Webmaster verification** — Google, Bing, Yandex, Baidu, Pinterest.
* **RSS content protection** — attribution appended to feed items so scraped copies link back to you.
* **Bulk media manager** — audit and fix missing image alt text library-wide.
* **Site audit** — crawl-based checks for missing metas, broken links, noindex issues and duplicates.
* **Performance tools** — clean revisions, transients and spam comments.
* **WooCommerce** — products in sitemaps, Product schema, and WooCommerce template variables.
* **Multisite** — network or per-site activation, with new sites configured automatically.

= AI features (bring your own key) =

Connect one of three providers and pay only your provider's standard rates:

* **OpenAI** — GPT-4o, GPT-4o Mini
* **Anthropic** — Claude Sonnet 4.6, Claude Haiku 4.5
* **Google Gemini** — Gemini 1.5 Pro, Gemini 1.5 Flash (free tier available)

AI features include the meta generator, bulk optimizer for hundreds of posts in background batches, content briefs, vision-based image alt text, headline analysis, social captions, competitor analysis and smart schema detection.

= Where things live =

* **Dashboard** — unified score and next actions
* **Import & Migrate** — bring data across from another SEO plugin
* **Settings** — Search Appearance, Core SEO, AI & AEO, Sitemaps & Files, Redirects, Technical, Integrations, Hardening, Business, Advanced
* **Content AI** — content briefs, bulk meta, social captions, image tools
* **Audit & Health** — full audit, AEO & GEO, link assistant, automation
* **Help & Support** — documentation and connection diagnostics

= Works with your stack =

Block editor and Classic editor, Elementor, Divi, Beaver Builder and Bricks, WooCommerce, and the major caching plugins (WP Rocket, W3 Total Cache, LiteSpeed, SG Optimizer, Kinsta).

= Privacy and security =

* API keys encrypted with AES-256-CBC using your WordPress security salts
* Keys never exposed to the frontend or sent to any server but your chosen provider
* No tracking, no analytics collection, no cookies for frontend visitors
* Every AJAX endpoint protected with nonces and capability checks
* Zero JavaScript or CSS loaded for logged-out visitors

== Installation ==

1. Upload the `gatetouch-ai-seo` folder to `/wp-content/plugins/`, or install through **Plugins → Add New**.
2. Activate the plugin through the **Plugins** menu.
3. The setup wizard opens automatically and runs in four short steps: import your existing SEO data, set your site identity, confirm the search essentials, and optionally connect an AI provider.
4. Switching from another SEO plugin? Step 1 detects it. You can also run an import at any time from **GT SEO/GEO/AEO → Import & Migrate**, which shows a badge when importable data is found.

After activation the plugin auto-configures sensible defaults, so core SEO output is correct before you change a single setting. AI generation is the only part that needs a key.

== Frequently Asked Questions ==

= Is it free? =

Yes. There is no account, no activation key, and no feature is gated behind a paid tier. AI generation requires your own AI provider key, billed to you by that provider.

= Do I need an API key? =

No. Search appearance, schema, sitemaps, breadcrumbs, redirects, robots.txt, llms.txt, hardening and the complete SEO/AEO/GEO scoring engine all work with no key at all. A key only adds content generation.

= Can I use AI features for free? =

Yes. Google Gemini 1.5 Flash has a free tier with no billing setup required. Go to **Settings → AI & AEO**, choose Gemini, and paste your free Google AI Studio key.

= Which provider produces the best results? =

All three are good. **GPT-4o** is the most consistent for titles and descriptions, **Claude Sonnet 4.6** produces the most natural-sounding copy, and **Gemini 1.5 Flash** is fastest and cheapest for large bulk runs. You can store keys for all three and switch at any time without losing data.

= Can I import from Yoast SEO or Rank Math? =

Yes — and also from All in One SEO, SEOPress, The SEO Framework and Slim SEO. Go to **GT SEO/GEO/AEO → Import & Migrate**. You get a full preview before anything is written, a rollback snapshot, and a verification pass afterwards. Nothing is deleted from your old plugin, and by default your own existing entries are never overwritten.

= Will it conflict with my current SEO plugin? =

No. While another supported SEO plugin is active, this plugin suppresses its own head output entirely, so you never get duplicate meta tags. That means you can install it, import your data, check everything, and only then deactivate the old plugin. An admin notice tells you this is happening and links to the importer.

= Should I block AI crawlers? =

Only the training ones, if that is your preference. Blocking a **citation** crawler such as OAI-SearchBot, PerplexityBot or ClaudeBot removes you from that assistant's answers and protects nothing. Blocking a **training** crawler such as GPTBot or CCBot has no effect on whether you are cited today. The plugin separates the two groups and labels each crawler with the consequence, so you can opt out of training while staying fully citable.

= What is llms.txt and do I need it? =

`llms.txt` is a proposed standard, similar in spirit to `robots.txt`, describing your site to language models. The plugin generates and serves it automatically.

Be aware of the evidence: no major AI search engine currently uses `llms.txt` to select citations, and Google has said publicly it is not used in ranking. This plugin therefore publishes it for forward compatibility but assigns it **no weight in your GEO score** — because a score that rises when you tick a box that changes nothing is not a useful score. What actually drives citation is measured by the five GEO signals instead.

= Does it slow down my site? =

No. The plugin loads zero JavaScript and zero CSS for logged-out visitors. Admin assets are enqueued only on plugin screens.

= Does it work with WooCommerce? =

Yes. Products appear in sitemaps, receive Product schema, and WooCommerce-specific template variables (`#wc_price#`, `#wc_sku#`, `#wc_stock#`, `#wc_brand#`, `#wc_short_desc#`, `#wc_rating#`, `#wc_category#`) are available in your title and description templates.

= Does it generate XML sitemaps? =

Yes — a full suite: sitemap index, posts, pages, taxonomies, authors, custom post types, images and RSS, with IndexNow notification on publish. Disable any other plugin's sitemaps before enabling these.

= Is my API key stored securely? =

Yes. Keys are encrypted with AES-256-CBC using your WordPress `SECURE_AUTH_SALT` before being written to the database, are never exposed to the frontend, and are transmitted only to the provider you selected.

= Does it support Multisite? =

Yes, both network-activated and per-site. Sites created after network activation are configured automatically.

= Where did the old menu items go? =

The menu was reorganised in 1.3.0 around how the plugin is actually used. Redirects and Sitemaps moved from Audit into **Settings**, and AI Diagnostics moved into **Help & Support**. Old URLs redirect automatically, so existing bookmarks keep working.

== Privacy Policy ==

The plugin does not track frontend visitors and does not collect analytics about your site usage. When AI features are used, post content is sent directly from your WordPress server to your chosen AI provider (OpenAI, Anthropic, or Google Gemini) using your own API key. Please review your AI provider's privacy policy for information on how they handle submitted content.

For sites subject to GDPR, the plugin's data processing is limited to:

- Storing plugin settings in the WordPress `wp_options` table
- Storing per-post SEO metadata in the WordPress `wp_postmeta` table
- Encrypted storage of user-provided API keys in `wp_options`
- If you use the support form, your name, email address, site URL, plugin version, selected request type, WordPress/PHP versions, and message are sent by your WordPress site via email to plugin support.
- If you configure an IndexNow API key, the plugin sends changed public URLs, your site host, the IndexNow key, and the key verification URL to IndexNow when search notifications are triggered.

No cookies are set for frontend visitors. No analytics are collected by the plugin.

== External Services ==

This plugin connects to the following third-party services only for features you configure or explicitly trigger. Core SEO output does not require any external account.

= OpenAI =
Used when OpenAI is selected as the AI provider. Post content and metadata are sent to generate SEO titles, descriptions, schema, and alt text.
- Service URL: https://api.openai.com
- Terms of Service: https://openai.com/policies/terms-of-use
- Privacy Policy: https://openai.com/policies/privacy-policy

= Anthropic =
Used when Anthropic Claude is selected as the AI provider.
- Service URL: https://api.anthropic.com
- Terms of Service: https://www.anthropic.com/legal/consumer-terms
- Privacy Policy: https://www.anthropic.com/privacy

= Google Gemini (Generative Language API) =
Used when Google Gemini is selected as the AI provider.
- Service URL: https://generativelanguage.googleapis.com
- Terms of Service: https://policies.google.com/terms
- Privacy Policy: https://policies.google.com/privacy

= IndexNow =
Used only when you configure an IndexNow API key and publish content or manually trigger search notifications. Sends changed public URLs, your site host, the IndexNow key, and the key verification URL to supported IndexNow search engines.
- Service URL: https://api.indexnow.org/indexnow
- Terms of Service: https://www.indexnow.org/terms
- Privacy Policy: https://www.indexnow.org/terms#privacy

= Google Analytics Tag and Google Tag Manager =
Used only when you enter a GA4 measurement ID or Google Tag Manager container ID in plugin settings. The configured tag script may load on your public site to measure visits according to your Google configuration.
- Service URLs: https://www.googletagmanager.com and https://analytics.google.com
- Terms of Service: https://policies.google.com/terms
- Privacy Policy: https://policies.google.com/privacy

= Meta Pixel =
Used only when you enter a Meta Pixel ID in plugin settings. The Meta Pixel script may load on your public site to measure page views according to your Meta configuration.
- Service URL: https://connect.facebook.net
- Terms of Service: https://www.facebook.com/legal/terms
- Privacy Policy: https://www.facebook.com/privacy/policy/

= Microsoft Advertising UET =
Used only when you enter a Bing UET tag ID in plugin settings. The Microsoft script may load on your public site to measure advertising events according to your Microsoft configuration.
- Service URL: https://bat.bing.com
- Terms of Service: https://www.microsoft.com/servicesagreement
- Privacy Policy: https://privacy.microsoft.com/privacystatement

= Microsoft Clarity =
Used only when you enter a Microsoft Clarity project ID in plugin settings. The Clarity script may load on your public site according to your Microsoft Clarity configuration.
- Service URL: https://www.clarity.ms
- Terms of Service: https://clarity.microsoft.com/terms
- Privacy Policy: https://privacy.microsoft.com/privacystatement

= LinkedIn Insight Tag =
Used only when you enter a LinkedIn Partner ID in plugin settings. The LinkedIn Insight script may load on your public site according to your LinkedIn configuration.
- Service URL: https://snap.licdn.com
- Terms of Service: https://www.linkedin.com/legal/user-agreement
- Privacy Policy: https://www.linkedin.com/legal/privacy-policy

= TikTok Pixel =
Used only when you enter a TikTok Pixel ID in plugin settings. The TikTok script may load on your public site according to your TikTok configuration.
- Service URL: https://analytics.tiktok.com
- Terms of Service: https://www.tiktok.com/legal/terms-of-service
- Privacy Policy: https://www.tiktok.com/legal/privacy-policy

= User-Provided URLs =
Used when you run competitor analysis, Open Graph testing, or site crawling. The plugin requests the URL you provide or your own site URLs to inspect public SEO metadata, headings, links, and response status.

== Screenshots ==

1. Dashboard — unified SEO, AEO and GEO score with sitemap status and next actions.
2. Search Appearance — title and description templates for every page context, with live SERP preview.
3. Import & Migrate — preview, import and verify data from six SEO plugins.
4. AI crawler control — citation crawlers and training crawlers governed separately.
5. Post editor meta box — AI title and description generator with live Google preview.
6. Bulk AI Optimizer — background batch generation across hundreds of posts.

== Changelog ==

= 1.4.1 =
* **Fixed: the post edit screen would not load** for any post created with 1.4.0s "Create draft post from brief" button. The draft stored its secondary keywords as an array where the rest of the plugin expects a comma-separated string, and the on-page analyser called explode() on it, which is fatal on PHP 8. Existing affected posts are repaired automatically on update.
* Added: a version-keyed upgrade routine. gatetouch_version was written at activation but never read, so the plugin previously had no way to repair data or apply changes on update.

= 1.4.0 =
* **New: content briefs are saved.** Every brief you generate is kept (last 30). Reopen one instantly, and asking for the same keyword twice reuses the saved brief instead of spending another API call. Press Regenerate when you actually want a fresh one.
* **New: Create draft post from a brief.** Scaffolds a post with the answer-box opening, one heading per outline section with its guidance, and a real FAQ section, then applies the brief's SEO title, meta description, focus keyword, secondary keywords, schema type and FAQ pairs to that post. No AI call, so it is instant and costs nothing.
* **New: Copy as Markdown.** Copies the whole brief (SEO, AEO, GEO, outline and FAQs) as clean Markdown for a doc, ticket or writing tool.
* **Fixed: the plugin kept asking you to import after you already had.** Importing only reads, so the old plugin's data stays detectable forever and every prompt fired again. Completed imports are now recorded per source: the menu badge counts only outstanding ones, the wizard and Import screen say "already imported", and the conflict notice switches to telling you to deactivate the old plugin instead.

= 1.3.1 =
* **Fixed: settings were being destroyed on save.** Two options were registered with a flags-only sanitizer, which rewrote every value that was not 0/1/yes/no to the literal string '0'. In practice this wiped your Organization name, logo, description, phone, email, VAT ID and founding date, and it erased the entire custom robots.txt rule table, the raw robots.txt editor content and the crawl delay. Both now use type-aware sanitizers. This is also the root cause of the Tools screen fatal error fixed in 1.3.0.
* **Fixed: sitemap priority reset to 0.** The value 0.8 was passed through absint(), which floors decimals.
* **Self-healing:** installs already damaged read those Organization fields back as empty instead of showing '0', so the setup wizard and Search Appearance no longer display a stray zero. Re-enter any values that were lost.
* Fixed: two documentation links returned 404 (Bing Webmaster verification, Microsoft Clarity setup).
* Fixed: added rel="noopener noreferrer" to 14 external links that opened in a new tab without it.
* Improved: the setup wizard no longer paints its own page background.

= 1.3.0 =
* **New: Search Appearance engine.** Every page type — posts, pages, categories, tags, authors, date archives, search results and 404s — now resolves its title, description, robots and canonical through one templating system with per-object overrides. Previously only single posts and the homepage emitted metadata.
* **New: Import & Migrate is now a real screen.** The migration engine supported six plugins but shipped with no route to it. It now has its own menu entry with a badge when importable data is detected, plus a preview, verification pass and rollback snapshot.
* **New: Citation crawlers are separated from training crawlers.** Nineteen AI crawlers, grouped by what blocking each one actually costs. Block every training crawler without losing a single AI citation.
* **Rewritten: GEO scoring.** Now measures passage citability, evidence density, authority and freshness, topical authority and multi-modal content — all computed locally. Previously 30% of the score was a single site-wide llms.txt toggle, identical on every post, and the score was capped at 30/100 without an API key.
* **Corrected: llms.txt is no longer scored.** It is still generated and served, but the published evidence shows no major AI search engine uses it to select citations, so it no longer inflates your GEO score.
* **Fixed: saving the AI & AEO tab wiped your Organization schema.** It overwrote the shared schema option instead of merging, blanking organization name, logo, type and five structured-data flags configured in Search Appearance.
* **Fixed: IndexNow could not be configured.** The only field that set the key lived in an unreachable page. It now sits in Settings → Integrations.
* **Fixed: Local Business settings were written to one option and read from another**, so they never rendered. Now editable under Settings → Business.
* **Fixed: the setup wizard discarded steps 2 and 3.** Sitemap, breadcrumb and separator choices were never saved, and "Skip" was identical to "Complete". Every step now persists, and the wizard leads with data import instead of an API key prompt.
* **Fixed: RSS content protection had no settings screen.** Now under Settings → Advanced.
* Improved: menu restructured around first-run, routine and optimization use. Redirects and Sitemaps moved out of Audit into Settings; AI Diagnostics moved into Help & Support. Old URLs redirect.
* Improved: activation defaults now seed the full structured-data graph, the AI crawler policy, and auto-schema — three of which previously seeded keys nothing ever read.
* Removed: 13 unreferenced admin page files that shipped in the package but were reachable from nowhere.

= 1.2.16 =
* Fix: Prevent duplicate meta descriptions, canonical links, robots tags, and JSON-LD when WordPress core, WordPress AI meta descriptions, or another SEO plugin also outputs head metadata.
* Improved: Detect active SEO plugin conflicts even when the plugin folder has been renamed.

= 1.2.15 =
* Added: Free crawl optimization controls for metadata cleanup, feed cleanup, internal search protection, bot directives, and URL parameter cleanup.
* Improved: Breadcrumb settings now support automatic before-content placement, manual shortcode placement with `[gatetouch_breadcrumbs]`, and per-post breadcrumb controls.
* Improved: Social image fields automatically fall back to the featured image when no custom social image is selected.
* Improved: Post editor analysis now checks the SEO title as well as the WordPress post title for focus keyword scoring and uses current editor content more reliably.

= 1.2.14 =
* Release: Updated the public plugin name, admin menu label, description, and WordPress.org directory assets.

= 1.2.13 =
* Fix: Corrected the core class autoloader so plugin activation and boot callbacks load reliably.

= 1.2.12 =
* Release: Changed the text domain from rankora-ai-seo to gatetouch-ai-seo.

= 1.2.11 =
* Release: Rebuilt the clean upload package without Git metadata or non-shipping files.

= 1.2.10 =
* Release: Changed the plugin brand name and renamed the main plugin file for the current package.

= 1.2.9 =
* Release: Updated plugin metadata and rebuilt the clean WordPress.org upload package.

= 1.2.8 =
* Release: Built a clean upload package excluding development files, Git metadata, nested archives, logs, and macOS .DS_Store files.

= 1.2.7 =
* Fix: Restored Website Audit Deep Analysis click handling with explicit script loading and visible button states.
* Fix: Added robots.txt tab-specific save feedback and preserved the active robots tab after saving.
* Fix: Aligned legacy robots.txt AI-bot blocking saves with the live robots.txt generator option keys.

= 1.2.5 =
* UI: Replaced emoji nav-tab icons with inline Tabler line icons (self-hosted SVG, no CDN dependency)
* UI: Replaced emoji AI provider labels with branded inline SVG logos (OpenAI, Anthropic, Google Gemini) in settings and setup wizard
* UI: Added flex-align and gap CSS to nav tabs for proper icon + text layout

= 1.2.4 =
* Security: Fixed custom header/body/footer script output to use wp_kses() with allowed script/noscript/style tags instead of wp_kses_post() (which incorrectly stripped <script> tags)
* Security: Wrapped all ternary CSS class and title attribute strings with esc_attr() in admin list table columns
* Code: Added phpcs:ignore annotations to log-directory protection files written to uploads directory

= 1.2.3 =
* Security: Sanitized .htaccess editor input and removed root-level backup file creation
* Security: Hardened JSON-LD output with safe WordPress script helpers and default wp_json_encode() escaping
* Security: Escaped saved custom header, body, and footer markup before frontend output
* Fix: Removed inactive inline admin script and style blocks from distributable PHP templates

= 1.2.2 =
* Fix: Added missing robots.txt rewrite registration method to prevent activation errors
* Fix: Registered robots.txt and security.txt rewrite rules consistently during activation and init
* Fix: Security.txt now supports rewrite-based requests through a dedicated GateTouch query var

= 1.2.1 =
* Security: Escaped GSC verification meta tag when pasted as full HTML tag
* Security: Custom Schema JSON-LD now decoded and re-encoded with wp_json_encode() before output
* Security: Wildcard redirect patterns now use preg_quote() to prevent regex injection
* Security: 404 log now sanitizes HTTP_REFERER, HTTP_USER_AGENT, and REMOTE_ADDR before DB insert
* Security: DB errors no longer leaked to AJAX responses; logged server-side only
* i18n: All user-facing strings wrapped in translation functions for the WordPress.org translation system
* i18n: Uses the correct gatetouch-ai-seo text domain
* Multisite: Activation now provisions all existing sites on network-activation
* Multisite: New sites added to network automatically provisioned via wpmu_new_blog hook
* Multisite: Uninstall now loops all network sites to clean tables and options
* Fix: GATETOUCH_VERSION constant now matches plugin header (1.2.1)
* Fix: Uninstall.php now drops all custom tables and clears all cron hooks
* Fix: Scratch directory debug scripts now use __DIR__-relative paths instead of hardcoded Windows paths

= 1.2.0 =
* Added: Multi-AI provider support — OpenAI (GPT-4o), Anthropic (Claude 3.5), Google Gemini
* Added: AEO Readiness Score — measure content quality for AI answer engines
* Added: llms.txt and llms-full.txt generator for AI crawler visibility
* Added: Bulk AI Optimizer for mass meta tag generation across entire site
* Added: Content AI Generator with keyword strategy, outline, and content briefs
* Added: AI Image Alt Text generator using Vision AI (OpenAI GPT-4o Vision)
* Added: Competitor Analysis — fetch and audit competitor SEO data
* Added: Headline Analyzer with AI scoring (sentiment, readability, word balance)
* Added: Social AI — platform-specific captions for LinkedIn, Facebook, X, Instagram
* Added: Security.txt generator (RFC 9116 compliant)
* Added: AES-256-CBC encryption for all stored API keys with auto-migration
* Removed: License registration requirements from bundled AI workflows
* Added: Site Audit Center with crawl-based issue detection
* Added: SEO Reporting with PDF/HTML export
* Added: Link Assistant for internal linking suggestions
* Added: Redirect Manager (301/302/307/410) with CSV import/export
* Added: Webmaster verification and optional analytics tag settings
* Added: Local SEO schema controls for LocalBusiness markup
* Added: WordPress Hardening panel — disable XML-RPC, remove WP version, security headers, clean head
* Added: Database Optimization — one-click cleanup of revisions, transients, spam, and bloat
* Added: IndexNow notifications for supported search engines
* Added: Bulk Media Meta Manager — audit and fix missing alt text across the Media Library
* Added: One-Click Fix Engine — auto-fix missing meta, alt text, schema, and sitemap issues
* Added: Data Migration — import from Yoast SEO, Rank Math, All in One SEO, SEOPress
* Added: Automation Engine — auto-linker, smart redirects, and automatic schema assignment
* Fixed: Sitemap showing "Not Active" when enabled (tolerant value parsing for 'yes'/'1')
* Fixed: twitter:description meta tag missing from page head output
* Fixed: noindex/nofollow flags not emitted as robots meta tags on frontend
* Fixed: Canonical URL tag missing from frontend output
* Fixed: AI settings page saving API keys without encryption
* Fixed: Admin notices not displaying with proper styled design
* Fixed: File permissions not validated in settings
* Improved: Real-time SEO analyzer — 15 checks with weighted scoring
* Improved: Schema engine — 15+ schema types with smart AI detection
* Improved: Sitemap generator — image, RSS, index, and chunked sitemaps

= 1.1.0 =
* Added: Open Graph and Twitter Card meta tags with smart image fallback
* Added: Breadcrumbs with JSON-LD BreadcrumbList schema
* Added: Robots.txt visual editor with AI-bot blocking
* Added: Webmaster tools verification (Google, Bing, Yandex, Baidu, Pinterest)
* Added: RSS content protection with copyright footer
* Improved: Admin UI with tabbed settings panels and responsive layout

= 1.0.0 =
* Initial release
* Meta title and description management with live SERP preview
* XML sitemap generation
* JSON-LD schema markup — Article, Organization, WebSite
* 15-point on-page SEO analysis engine

== Upgrade Notice ==

= 1.4.1 =
Fixes a fatal error that stopped the post edit screen from loading for drafts created from a content brief in 1.4.0. Affected posts are repaired automatically.

= 1.4.0 =
Content briefs are now saved and reusable, can be copied as Markdown, and can scaffold a draft post with all their SEO settings applied. Import prompts no longer repeat once an import is complete.

= 1.3.1 =
Important fix: a sanitizer bug was silently wiping your Organization schema fields and custom robots.txt rules whenever those settings were saved. Update, then re-enter your Organization details under Settings > Search Appearance and any custom robots.txt rules under Tools. Existing damaged values now read as empty rather than showing '0'.

= 1.3.0 =
Adds the Search Appearance engine for all nine page contexts, a reachable Import & Migrate screen for six SEO plugins, and separates AI citation crawlers from training crawlers. Also fixes a bug where saving the AI & AEO tab wiped Organization schema settings — if your organization name or logo was blanked previously, re-enter it under Settings → Search Appearance. GEO scoring was rewritten and no longer counts llms.txt, so post GEO scores will change. The menu was reorganised; old URLs redirect automatically.

= 1.2.0 =
Major update with AI provider support, AEO tools, and 20+ new features. All existing settings are preserved on upgrade. API keys saved before v1.2.0 are automatically migrated to encrypted storage on first use. No manual action required.
