/* global gatetouchAdmin */
// Documentation Database
const gatetouchDocs = [
    {
        id: 'intro',
        category: 'Getting Started',
        icon: '🚀',
        title: '1. Introduction to GateTouch',
        content: `
# Introduction to GateTouch

Welcome to the **GateTouch** enterprise documentation. This plugin is built to be the most advanced, performance-oriented SEO solution available for WordPress, fully integrated with cutting-edge Artificial Intelligence.

## What GateTouch Does
GateTouch replaces legacy SEO plugins by combining deep technical SEO architectures with automated AI intelligence. Instead of manually writing meta descriptions, checking keyword density, or guessing schema types, GateTouch automates the entire process while giving you full granular control over technical implementation.

## Core Capabilities

### 1. Technical SEO Architecture
*   **Dynamic XML Sitemaps:** Auto-generated, index-optimized sitemaps supporting Images, Videos, News, and hierarchical structures without overloading the database.
*   **Robots.txt Manager:** Deep control over bot crawling, AI scrapers, and indexation budgets.
*   **Redirect Management:** An enterprise-grade regex-supported redirection engine for 301/302s to preserve link equity during site migrations.
*   **Advanced Schema.org Engine:** Fully compliant JSON-LD structured data injection covering Articles, Products, Local Business, and FAQs.

### 2. AI-Powered AEO (Answer Engine Optimization)
*   **Vision AI 2.0:** Automatically scans your media library to generate descriptive, SEO-optimized Alt-Text using visual recognition.
*   **Semantic Content AI:** Evaluates content against LSI (Latent Semantic Indexing) keywords and generates precise, click-worthy Meta Titles and Descriptions.
*   **Auto-Linking Engine:** AI identifies contextual internal linking opportunities to create highly relevant content clusters and improve dwell time.

### 3. Automation & Scale
*   **Bulk AI Generation:** An asynchronous queuing system capable of processing 10,000+ posts in the background. Generate missing meta descriptions, optimize old content, and scale your SEO strategy effortlessly.
*   **Smart Auto-Schema:** The plugin analyzes content intent and automatically applies the best JSON-LD schema type (e.g., auto-detecting a recipe vs. a software review).

## Who Should Use GateTouch?
*   **Website Owners:** Automate technical SEO without needing to learn code.
*   **SEO Experts:** Gain granular control over crawls, indexation, regex redirects, and advanced structured data.
*   **Developers:** Extend functionality using native WordPress hooks, filters, and our robust API architecture.
*   **Agencies:** Manage multiple client sites faster through bulk optimization and reliable diagnostic tools.

<div class="rhc-alert rhc-alert-info">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    <div><strong>Tip:</strong> The easiest way to get started is to use the <a href="?page=gatetouch-setup-wizard">First Setup Wizard</a>, which will configure the baseline settings optimal for 90% of websites.</div>
</div>
        `
    },
    {
        id: 'install',
        category: 'Getting Started',
        icon: '⚙️',
        title: '2. Installation Guide',
        content: `
# Installation & Server Requirements

To ensure GateTouch runs flawlessly, especially when utilizing background Bulk AI processing, your server environment must meet specific criteria.

## Server Requirements

*   **WordPress Version:** 6.0 or higher.
*   **PHP Version:** 7.4 minimum (PHP 8.1+ highly recommended for optimal performance).
*   **Database:** MySQL 5.7+ or MariaDB 10.3+.
*   **PHP Memory Limit:** 256MB minimum (512MB+ recommended for large ecommerce sites or heavy bulk processing).

## Step-by-Step Installation

1.  **Upload the Plugin:** Navigate to your WordPress Dashboard > **Plugins** > **Add New** > **Upload Plugin**.
2.  **Select the ZIP file:** Choose the \`gatetouch-ai-seo.zip\` file you downloaded.
3.  **Install & Activate:** Click **Install Now**, wait for the process to finish, and click **Activate Plugin**.

## Checking Your Environment
GateTouch includes a built-in System Diagnostics tool.
1. Go to **GateTouch > Settings > Technical**.
2. Review the **System Status** box. Red warnings indicate environment issues that could prevent AI functionality from running.

<div class="rhc-alert rhc-alert-warning">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
    <div><strong>Important: cURL extension required.</strong> Your PHP installation must have the cURL extension enabled to communicate with the AI API and ping search engines. If you receive API connection errors, verify cURL is active in your PHP configuration.</div>
</div>

## Troubleshooting Installation Issues

*   **"The uploaded file exceeds the upload_max_filesize directive in php.ini"**
    *Fix:* Increase \`upload_max_filesize\` and \`post_max_size\` in your server's \`php.ini\` file, or upload the extracted plugin folder directly to \`/wp-content/plugins/\` via SFTP.
*   **Plugin Activation Fatal Error**
    *Fix:* This usually means you are running an outdated version of PHP (older than 7.4). Upgrade your PHP version via your hosting control panel.
        `
    },
    {
        id: 'setup-wizard',
        category: 'Getting Started',
        icon: '🪤',
        title: '3. First Setup Wizard',
        content: `
# First Setup Wizard

The Setup Wizard is designed to configure your technical SEO foundation and connect your AI provider in under 5 minutes.

## Accessing the Wizard
Upon first activation, you are automatically redirected to the Setup Wizard. If you skipped it, you can access it anytime by visiting \`wp-admin/admin.php?page=gatetouch-setup-wizard\`.

## Step 1: AI Provider Setup
This is the core of GateTouch's automation engine.
*   **Why it matters:** Without an API key, AI generation (Bulk Meta, AI Image Alt, AI FAQs) will be disabled.
*   **Action:** Choose your AI provider and paste your API key. The wizard will ping the API to validate it.

## Step 2: Site Basics & Indexing Controls
*   **Site Type:** Choose whether you are a Blog, Ecommerce Store, or Local Business. GateTouch adjusts default schemas accordingly.
*   **Search Engine Visibility:** Ensure "Discourage search engines from indexing this site" is **unchecked** if you want to rank on Google.
*   **AI Bot Crawling:** Decide if you want to allow AI scrapers (like ChatGPT-User or Anthropic) to crawl your site for training data.

## Step 3: SEO Defaults
Configure how Titles are structured.
*   **Title Separator:** Choose your preferred separator (e.g., \`-\`, \`|\`, \`•\`).
*   **Homepage Meta:** Set your primary site title and fallback meta description.

## Step 4: Social Profiles
Link your brand's social media accounts.
*   **Why it matters:** This populates the \`Organization\` schema and Open Graph tags, ensuring a consistent Knowledge Graph representation when users search for your brand.

## Step 5: Automation Toggles
Enable core background automation:
*   **Auto-generate empty metas:** Automatically generates a meta description upon publishing if left empty.
*   **Auto Schema:** Let GateTouch guess the best schema for new posts.
*   **Auto Image Alt:** Run Vision AI on newly uploaded media to generate alt tags automatically.
        `
    },
    {
        id: 'ai-providers',
        category: 'Configuration',
        icon: '🔑',
        title: '4. AI Provider & API Setup',
        content: `
# AI Provider & API Key Setup

GateTouch supports three AI providers. Select the one that suits your budget and preference — all support full SEO meta generation, schema markup, FAQ generation, and content analysis.

Go to **GateTouch → Settings → AI & AEO** to configure your provider.

---

## 🤖 Option 1 — OpenAI (GPT-4o)

**Best for:** Highest quality output, DALL-E image generation support.

### How to Get an OpenAI Key
1. Visit platform.openai.com and sign up or log in.
2. Go to **Settings → Billing** and add at least $5 in credits.
3. Navigate to **API Keys** and click **Create new secret key**.
4. Name it "GateTouch" and copy the key (starts with \`sk-proj-...\`).
5. Paste it into **GateTouch → Settings → AI & AEO → OpenAI API Key** and click **Test + Save**.

### Supported Models
*   **GPT-4o** — Recommended. Best quality, fast, ~$0.01-0.02 per optimization.
*   **GPT-4o Mini** — Very affordable, great quality. ~$0.001 per optimization.
*   **GPT-4 Turbo** — High quality for complex tasks.
*   **GPT-3.5 Turbo** — Legacy support, very cheap but lower quality.

<div class="rhc-alert rhc-alert-danger">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    <div><strong>429 "Insufficient Quota" Error:</strong> Your OpenAI account has run out of prepaid credits. Add billing at platform.openai.com. A ChatGPT Plus subscription does NOT cover API usage.</div>
</div>

---

## 🧠 Option 2 — Anthropic Claude

**Best for:** Nuanced writing quality, instruction-following, great for long-form content analysis.

### How to Get an Anthropic Key
1. Visit console.anthropic.com and sign up.
2. Go to **Settings → Billing** and add credits (minimum $5).
3. Navigate to **Settings → API Keys** and click **Create Key**.
4. Copy the key (starts with \`sk-ant-api...\`).
5. Paste it into **GateTouch → Settings → AI & AEO → Anthropic API Key** and click **Test + Save**.

### Supported Models
*   **Claude Sonnet 4.6** — Recommended. Excellent quality + speed.
*   **Claude Haiku 4.5** — Fastest and cheapest.
*   **Claude Opus 4.7** — Most powerful for complex tasks.

---

## ✨ Option 3 — Google Gemini

**Best for:** Getting started for free. Gemini 1.5 Flash offers a free tier.

### How to Get a Gemini Key
1. Visit aistudio.google.com — sign in with your Google account.
2. Click **Get API Key** in the top navigation, then **Create API Key**.
3. Copy the key (starts with \`AIza...\`).
4. Paste it into **GateTouch → Settings → AI & AEO → Google Gemini Key** and click **Test + Save**.

### Supported Models
*   **Gemini 1.5 Pro** — Best quality. Generous free tier.
*   **Gemini 1.5 Flash** — Fastest. Recommended for bulk tasks on the free tier.
*   **Gemini 2.0 Flash** — Latest generation with improved reasoning.

<div class="rhc-alert rhc-alert-info">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    <div><strong>Free Tier:</strong> Gemini 1.5 Flash offers 15 requests/minute free. Perfect if you are just starting out and want to test GateTouch without any billing setup.</div>
</div>

---

## Security & Privacy

Your API keys are encrypted with AES-256-CBC before storage and are never sent to GateTouch servers. They are only used to make direct API calls from your WordPress server to the respective AI provider.
        `
    },
    {
        id: 'dashboard',
        category: 'Interface',
        icon: '📊',
        title: '5. Dashboard Overview',
        content: `
# Dashboard Overview

The GateTouch Dashboard is your command center. It provides a real-time, bird's-eye view of your website's entire SEO and AI health profile.

## Key Metrics & Cards

1.  **Site Optimization Coverage:** Displays the percentage of your posts/pages that have GateTouch meta data actively set and optimized. Your goal is to keep this above 90%.
2.  **API Health Status:** Real-time ping to the AI API confirming connection stability, latency, and model availability.
3.  **Active Issues / Warnings:** The dashboard queries the audit engine to bubble up critical technical SEO issues that need your immediate attention.

## Health Status Indicators
*   **Green (Healthy):** The module is functioning perfectly.
*   **Yellow (Warning):** A non-critical feature is missing.
*   **Red (Critical):** A feature is broken and actively harming your SEO.
        `
    },
    {
        id: 'seo-analyzer',
        category: 'Features',
        icon: '🔍',
        title: '6. SEO Analyzer Documentation',
        content: `
# SEO Analyzer System

The GateTouch Analyzer is a real-time diagnostic engine that runs locally in your browser when editing a post, and globally during full site audits. It ensures your content meets strict on-page SEO standards.

## How the Scoring System Works
The analyzer calculates a score out of 100 based on a weighted algorithm of critical SEO factors. Achieving a score of 80+ indicates the post is highly optimized.

### 1. Title Analysis
*   **Check:** Is the title between 40 and 60 characters? Does it contain the focus keyword?
*   **Fix:** Use the AI Auto-Generate button to create an optimized title.

### 2. Meta Description Analysis
*   **Check:** Is the description between 120 and 160 characters? Does it accurately summarize the intent?

### 3. Content Quality & Keyword Density
*   **Check:** Calculates the exact keyword density within the \`the_content\`. Flags if the density is too low (< 0.5%) or dangerously high (> 2.5%).

### 4. Heading Structure
*   **Check:** Ensures there is exactly one \`<h1>\` tag on the page. Ensures \`<h2>\` and \`<h3>\` tags are nested logically.

### 5. Image Alt Analysis
*   **Check:** Scans the content for \`<img>\` tags lacking an \`alt\` attribute.
*   **Fix:** Click the "Run Vision AI" button to automatically scan the images and generate alt-texts.

### 6. Technical Validation
*   **Canonical:** Verifies the canonical URL is self-referencing to prevent duplicate content penalties.
*   **Open Graph:** Ensures Facebook and Twitter specific meta tags are present.
        `
    },
    {
        id: 'bulk-meta',
        category: 'Features',
        icon: '⚡',
        title: '7. Bulk Meta Generation',
        content: `
# Bulk Meta Generation

The Bulk Optimizer allows you to generate high-quality AI Meta Titles, Descriptions, and Alt-Texts for thousands of posts simultaneously, in the background.

## How the Queue System Works
1.  **Selection:** You select the Post Type and the status (e.g., Only items missing meta descriptions).
2.  **Batching:** GateTouch chunks the workload into small batches (e.g., 5 posts per request).
3.  **Processing:** The browser sends an AJAX request. The server queries the AI API, saves the data to the database, and responds with progress.
4.  **Resilience:** If the AI API throws a 429 Rate Limit error, the Bulk Optimizer catches the error, pauses the queue for 20 seconds, and retries the batch automatically.

## Running a Bulk Operation

1. Navigate to **GateTouch > Settings > Bulk Optimizer**.
2. Select the target **Post Type**.
3. Choose the **Action** (e.g., Generate Meta Descriptions).
4. Click **Start Bulk Run**.

<div class="rhc-alert rhc-alert-warning">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
    <div><strong>Leave the Tab Open:</strong> The queue is driven by browser AJAX. If you close the tab or navigate away, the bulk generation process will pause. It will resume exactly where it left off when you return.</div>
</div>

## Troubleshooting
*   **Stuck at XX%:** Check the API Diagnostics page. Your AI API key may have hit its hard billing limit.
*   **500 Internal Server Error:** Your server's PHP memory limit is likely exhausted. Increase \`WP_MEMORY_LIMIT\` to \`256M\` in \`wp-config.php\`.
        `
    },
    {
        id: 'sitemaps',
        category: 'Technical',
        icon: '🗺️',
        title: '8. XML Sitemap System',
        content: `
# XML Sitemap System

## Architecture & Generation
GateTouch splits sitemaps into smaller, manageable chunks (default: 1,000 URLs per sub-sitemap).

*   **Sitemap Index:** Located at \`yourdomain.com/sitemap_index.xml\`. This is the file you submit to Google Search Console.
*   **Sub-sitemaps:** Segmented by post type (e.g., \`post-sitemap1.xml\`, \`product-sitemap1.xml\`).

## Sitemaps Configuration
Navigate to **GateTouch > Settings > Technical > XML Sitemaps**.

*   **Enable/Disable:** Toggle specific post types or taxonomies.
*   **Auto Updates & Search Notifications:** When you publish a new post, GateTouch automatically updates the sitemap cache and notifies supported search engines through IndexNow when an API key is configured.

## Troubleshooting 404 Errors on Sitemaps
1. Ensure your permalinks are set to "Post name" in WordPress Settings > Permalinks.
2. Go to GateTouch Settings > Core SEO and click **Flush Rewrite Rules**.
        `
    },
    {
        id: 'robots',
        category: 'Technical',
        icon: '🤖',
        title: '9. Robots.txt Manager',
        content: `
# Robots.txt Manager

The \`robots.txt\` file is a set of instructions telling web crawlers (like Googlebot) which pages they are allowed or forbidden to crawl.

## Modifying Robots.txt
Go to **GateTouch > Settings > Technical**.
GateTouch dynamically generates a virtual \`robots.txt\` file — no FTP access needed.

### AI Bot Control
GateTouch includes a toggle to automatically inject \`Disallow\` directives for known AI user-agents (e.g., \`ChatGPT-User\`, \`GPTBot\`, \`CCBot\`, \`Claude-Web\`).

<div class="rhc-alert rhc-alert-danger">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    <div><strong>Danger Zone:</strong> Never add \`Disallow: /\` unless you explicitly want to de-index your entire website.</div>
</div>
        `
    },
    {
        id: 'schema',
        category: 'Technical',
        icon: '🧩',
        title: '10. Schema Markup System',
        content: `
# Schema Markup System

Schema (Structured Data) translates your content into a highly structured JSON-LD format that search engines use to understand context and generate "Rich Results".

## Supported Schema Types
1.  **Article / NewsArticle:** Default for blog posts.
2.  **Product:** Automatically extracted for WooCommerce.
3.  **FAQPage:** Automatically generated when you use the AI FAQ tool.
4.  **LocalBusiness:** Set up globally in Core Settings.
5.  **Organization:** Defines your brand, logo, and social profiles.
6.  **BreadcrumbList:** Dynamically built to help Google understand site architecture.

## Validating Schema
After applying schema, always validate it using the Google Rich Results Test.
        `
    },
    {
        id: 'redirects',
        category: 'Technical',
        icon: '🔀',
        title: '11. Redirect Manager',
        content: `
# Redirect Manager

## Types of Redirects
*   **301 Permanent:** Tells search engines the page has moved forever. Link equity is passed. Use this 99% of the time.
*   **302 Temporary:** Link equity is NOT passed. Use this only for short-term maintenance.
*   **410 Content Deleted:** Tells search engines the content is gone and forces immediate de-indexation.

## Creating a Redirect
1. Go to **GateTouch > Redirects**.
2. **Source URL:** Enter the relative path (e.g., \`/old-blog-post/\`).
3. **Destination URL:** Enter the full absolute URL.
4. **Type:** Select 301 Permanent.
5. Click Add.

## 404 Error Monitoring
GateTouch logs 404 Not Found errors. Review the 404 log weekly and create 301 redirects for high-traffic broken URLs.
        `
    },
    {
        id: 'social',
        category: 'Features',
        icon: '📱',
        title: '12. Social SEO',
        content: `
# Social SEO

## Open Graph & Twitter Cards
GateTouch automatically generates Open Graph (\`og:\`) and Twitter Card (\`twitter:\`) meta tags for every post.
*   **OG Image:** GateTouch automatically falls back to the Featured Image. If no Featured Image is set, it falls back to the Global Site Logo set in GateTouch Core Settings.

## Troubleshooting Missing Images
If you share a link on Facebook and the image is missing:
1. Go to the Facebook Sharing Debugger.
2. Paste your URL and click "Scrape Again".
        `
    },
    {
        id: 'woocommerce',
        category: 'Features',
        icon: '🛒',
        title: '13. WooCommerce SEO',
        content: `
# WooCommerce SEO

## Product Schema Automation
GateTouch automatically hooks into WooCommerce to generate robust \`Product\` JSON-LD schema.
*   It pulls the exact Price, Currency, Stock Status (InStock/OutOfStock).
*   It aggregates WooCommerce native reviews to generate the \`AggregateRating\` schema.

## Image SEO for Ecommerce
Use the Bulk AI Vision tool to automatically generate descriptive Alt-Texts for all your product gallery images.

## Ecommerce Best Practices
1.  **Noindex Thin Archives:** Ensure \`product_tag\` taxonomies are set to \`noindex\` unless you have highly optimized tag pages.
2.  **Breadcrumbs:** Enable GateTouch breadcrumbs on product pages.
        `
    },
    {
        id: 'ai-features',
        category: 'AI Capabilities',
        icon: '🧠',
        title: '14. AI Features Documentation',
        content: `
# AI Features Documentation

## 1. AI Meta Title & Description Generator
Located in the GateTouch meta box below your post editor.
*   **How it works:** It reads the entire \`post_content\`, understands the core topic, extracts LSI keywords, and formulates high-CTR metadata.

## 2. AI FAQ Generator
*   **How it works:** Click "Generate FAQs". The AI analyzes your topic and predicts the 3-5 most common questions users ask. It writes the answers and automatically applies \`FAQPage\` JSON-LD schema.

## 3. Vision AI Image Alt-Text
*   **How it works:** Click "Run Vision AI" in the media analyzer. GateTouch analyzes the image using your configured AI provider and returns a concise, descriptive alt-text.

## 4. Semantic Content Analysis (AEO)
*   **How it works:** The GateTouch analyzer checks if your content provides direct, factual answers formatted in bullet points or bolded summaries.
        `
    },
    {
        id: 'issues',
        category: 'Technical',
        icon: '⚠️',
        title: '15. Issue & Warning System',
        content: `
# Issue & Warning System

## Severity Levels
*   **Critical (Red):** Direct damage to SEO. Examples: Search visibility discouraged, API key missing, canonical tags broken.
*   **Warning (Yellow):** Missed opportunities. Examples: Low keyword density, missing social fallback images.
*   **Info (Blue):** General recommendations.

## Auto-Fix System
Many warnings have a "Fix Now" button on the Dashboard. Clicking this will trigger an AJAX routine that automatically corrects the setting.
        `
    },
    {
        id: 'performance',
        category: 'Technical',
        icon: '🏎️',
        title: '16. Performance Optimization',
        content: `
# Performance Optimization

GateTouch is built to have zero frontend footprint. It does not load heavy CSS or JS files for unauthenticated visitors.

## Caching Compatibility
GateTouch is 100% compatible with page caching plugins (WP Rocket, W3TC, Redis Object Cache).

## Async Bulk Processing
The Bulk Optimizer avoids utilizing \`wp-cron\` because cron jobs on cheap hosting are unreliable. Instead, it uses an AJAX-driven Queue that chunks requests.
        `
    },
    {
        id: 'security',
        category: 'Technical',
        icon: '🔒',
        title: '17. Security Documentation',
        content: `
# Security Documentation

## API Security
Your AI provider API keys are encrypted with AES-256-CBC and stored in the WordPress \`wp_options\` table. Keys are never exposed to the frontend, never sent to GateTouch servers, and are only used to make direct calls from your server to the respective AI provider endpoint.

## Nonce Validation & Permissions
Every single AJAX request and POST action in GateTouch is protected by:
1.  **WordPress Nonces:** Prevents Cross-Site Request Forgery (CSRF).
2.  **Capability Checks:** Only users with the \`manage_options\` capability (Administrators) can trigger AI generation or modify settings.

## Data Sanitization
All outputs are strictly sanitized using \`esc_html()\`, \`esc_url()\`, and \`wp_kses_post()\` to prevent XSS vulnerabilities.
        `
    },
    {
        id: 'troubleshooting',
        category: 'Support',
        icon: '🛠️',
        title: '18. Troubleshooting Guide',
        content: `
# Troubleshooting Guide

## 1. AI API Connection Failed

**For OpenAI:**
*   **Fix 1:** Verify your key at platform.openai.com.
*   **Fix 2:** Check your billing. OpenAI API is prepaid. A $0.00 balance causes 429 errors.
*   **Fix 3:** Check cURL status in GateTouch Settings → Technical.

**For Anthropic:**
*   **Fix 1:** Verify your key starts with \`sk-ant-api...\` at console.anthropic.com.
*   **Fix 2:** Check billing credits at console.anthropic.com.

**For Google Gemini:**
*   **Fix 1:** Verify your key starts with \`AIza...\` at aistudio.google.com.
*   **Fix 2:** Check if you have hit the free-tier rate limit (15 req/min).

## 2. Bulk Generation is Stuck
*   **Fix:** Open your browser's Developer Tools (F12) > Console tab. Look for red AJAX errors. Refresh the page to resume the queue.

## 3. Schema Not Appearing in Google Test
*   **Fix 1:** Ensure you aren't running a caching plugin that strips \`<script type="application/ld+json">\` tags.
*   **Fix 2:** Check if another SEO plugin (Yoast, RankMath) is still active.

## 4. Blank Settings Page (White Screen)
*   **Fix:** Enable \`WP_DEBUG\` in your \`wp-config.php\` file to see the exact error. Ensure your server is running PHP 7.4 or higher.
        `
    },
    {
        id: 'faq',
        category: 'Support',
        icon: '❓',
        title: '19. FAQ Section',
        content: `
# Frequently Asked Questions

### Do I have to pay for the AI?
The GateTouch plugin is completely free — you only pay the AI provider directly for API usage. Google Gemini has a **free tier** that lets you start without any billing.

### Which AI provider should I choose?
- **Google Gemini** — Start here if you want a free option (Gemini 1.5 Flash has a free tier).
- **Anthropic Claude** — Best writing quality for nuanced content. Very cost-efficient.
- **OpenAI GPT-4o** — Top overall quality. Required for AI image generation (DALL-E).

### Will GateTouch conflict with Yoast or RankMath?
**YES.** You should never run two SEO plugins simultaneously. If you are migrating to GateTouch, use the Import Wizard to pull your existing data from Yoast/RankMath, and then deactivate the old plugin.

### Can I use GateTouch on WooCommerce?
Absolutely. GateTouch is highly optimized for WooCommerce, injecting correct Product schema and offering specific transactional AI prompts for product descriptions.

### What happens if I deactivate GateTouch?
If you deactivate the plugin, the meta tags will stop rendering on your live site. However, the data is safely stored in your database and will return if you reactivate.
        `
    },
    {
        id: 'developer',
        category: 'Developer',
        icon: '💻',
        title: '20. Developer Documentation',
        content: `
# Developer Documentation

## PHP Hooks & Filters

### Modifying Meta Data
\`\`\`php
add_filter( 'gatetouch_title', 'custom_dynamic_title' );
function custom_dynamic_title( $title ) {
    if ( is_singular( 'product' ) ) {
        return $title . ' - Updated ' . gmdate('Y');
    }
    return $title;
}
\`\`\`

### Extending the Schema Engine
\`\`\`php
add_filter( 'gatetouch_schema_data', 'add_custom_schema_property', 10, 2 );
function add_custom_schema_property( $schema, $post_id ) {
    if ( isset( $schema['@type'] ) && $schema['@type'] === 'Article' ) {
        $schema['publisher']['foundingDate'] = '2010';
    }
    return $schema;
}
\`\`\`

## Architecture & Data Storage
GateTouch stores its post-level data in a single optimized serialized array in the \`wp_postmeta\` table under the key \`_gatetouch_meta\`.

## Interacting with the AI Queue
\`\`\`php
if ( class_exists( 'GateTouch_Queue' ) ) {
    GateTouch_Queue::add( 'generate_meta', [
        'post_id' => 123,
        'type'    => 'description'
    ] );
}
\`\`\`
        `
    }
];

// UI Controller
document.addEventListener('DOMContentLoaded', function() {
    var navContainer = document.getElementById('rhc-nav-container');
    var contentArea  = document.getElementById('rhc-content-area');
    var searchInput  = document.getElementById('rhc-search-input');

    if ( ! navContainer || ! contentArea || ! searchInput ) return;

    // Group docs by category
    var categories = {};
    gatetouchDocs.forEach(function(doc) {
        if (!categories[doc.category]) categories[doc.category] = [];
        categories[doc.category].push(doc);
    });

    // Render Navigation
    function renderNav(filter) {
        filter = filter || '';
        navContainer.innerHTML = '';
        var lowerFilter = filter.toLowerCase();
        var found = false;

        Object.keys(categories).forEach(function(category) {
            var filteredDocs = categories[category].filter(function(doc) {
                return doc.title.toLowerCase().indexOf(lowerFilter) !== -1 ||
                       doc.content.toLowerCase().indexOf(lowerFilter) !== -1;
            });

            if (filteredDocs.length > 0) {
                found = true;
                var catHeader = document.createElement('div');
                catHeader.className = 'rhc-nav-category';
                catHeader.innerText = category;
                navContainer.appendChild(catHeader);

                filteredDocs.forEach(function(doc) {
                    var item = document.createElement('div');
                    item.className = 'rhc-nav-item';
                    item.innerHTML = '<span>' + doc.icon + '</span> <span>' + doc.title + '</span>';
                    item.dataset.id = doc.id;
                    item.addEventListener('click', function() { loadContent(doc.id); });
                    navContainer.appendChild(item);
                });
            }
        });

        if (!found) {
            navContainer.innerHTML = '<div style="padding: 20px; color: #94a3b8; font-size: 13px; text-align: center;">No results found.</div>';
        }
    }

    // Basic Markdown Parser
    function parseMarkdown(text) {
        var html = text;
        html = html.replace(/^### (.*$)/gim, '<h3>$1</h3>');
        html = html.replace(/^## (.*$)/gim, '<h2>$1</h2>');
        html = html.replace(/^# (.*$)/gim, '<h1>$1</h1>');
        html = html.replace(/\*\*(.*?)\*\*/gim, '<strong>$1</strong>');
        html = html.replace(/```([a-z]*)\n([\s\S]*?)```/gim, '<pre><code>$2</code></pre>');
        html = html.replace(/`(.*?)`/gim, '<code>$1</code>');
        html = html.replace(/^\* (.*$)/gim, '<li>$1</li>');
        html = html.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');
        html = html.replace(/<\/ul>\n<ul>/gim, '');
        html = html.replace(/^([^<\n].*)$/gim, '<p>$1</p>');
        html = html.replace(/<p><\/p>/gim, '');
        return html;
    }

    // Load Content
    function loadContent(id) {
        var doc = gatetouchDocs.filter(function(d) { return d.id === id; })[0];
        if (!doc) return;

        document.querySelectorAll('.rhc-nav-item').forEach(function(el) { el.classList.remove('active'); });
        var activeNav = document.querySelector('.rhc-nav-item[data-id="' + id + '"]');
        if (activeNav) activeNav.classList.add('active');

        var supportUrl = (typeof gatetouchAdmin !== 'undefined' && gatetouchAdmin.support_url)
            ? gatetouchAdmin.support_url
            : 'admin.php?page=gatetouch-help';

        contentArea.innerHTML =
            '<div class="rhc-content animate-fade-in">' +
            parseMarkdown(doc.content) +
            '<div style="margin-top:60px; padding-top:20px; border-top:1px solid #e2e8f0;">' +
            '<div id="riq-helpful-wrap" style="display:flex; justify-content:space-between; align-items:center; color:#64748b; font-size:14px;">' +
            '<div style="display:flex; align-items:center; gap:12px;">' +
            '<span style="font-weight:600; color:#475569;">Was this article helpful?</span>' +
            '<button id="riq-helpful-yes" style="display:inline-flex;align-items:center;gap:5px;background:#ecfdf5;color:#065f46;border:1.5px solid #a7f3d0;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;">👍 Yes, helpful</button>' +
            '<button id="riq-helpful-no" style="display:inline-flex;align-items:center;gap:5px;background:#f8fafc;color:#64748b;border:1.5px solid #e2e8f0;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;font-family:inherit;">👎 Needs improvement</button>' +
            '</div>' +
            '<div style="font-size:12px; color:#94a3b8;">GateTouch Docs</div>' +
            '</div>' +
            '<div id="riq-helpful-thanks" style="display:none; margin-top:14px; padding:14px 18px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; font-size:13px; color:#1e40af; font-weight:600;">' +
            '<span id="riq-helpful-thanks-msg"></span>' +
            '<a href="' + supportUrl + '" style="color:#6366f1; margin-left:8px; font-weight:700;">Open Support →</a>' +
            '</div>' +
            '</div>' +
            '</div>';

        contentArea.scrollTop = 0;
    }

    searchInput.addEventListener('input', function(e) {
        renderNav(e.target.value);
    });

    renderNav();

    var urlParams = new URLSearchParams(window.location.search);
    var docId = urlParams.get('id');
    if (docId && gatetouchDocs.some(function(d) { return d.id === docId; })) {
        loadContent(docId);
    } else if (gatetouchDocs.length > 0) {
        loadContent(gatetouchDocs[0].id);
    }

    contentArea.addEventListener('click', function(e) {
        var target = e.target.closest('#riq-helpful-yes, #riq-helpful-no');
        if (!target) return;

        var isYes  = target.id === 'riq-helpful-yes';
        var wrap   = document.getElementById('riq-helpful-wrap');
        var thanks = document.getElementById('riq-helpful-thanks');
        var msg    = document.getElementById('riq-helpful-thanks-msg');

        target.style.background   = isYes ? '#065f46' : '#991b1b';
        target.style.color        = '#fff';
        target.style.borderColor  = isYes ? '#065f46' : '#991b1b';
        var otherId = isYes ? 'riq-helpful-no' : 'riq-helpful-yes';
        var other   = document.getElementById(otherId);
        if (other) { other.style.opacity = '0.4'; other.style.pointerEvents = 'none'; }

        msg.textContent = isYes
            ? '🎉 Thank you! Glad this helped.'
            : '📝 Thanks for your feedback. Our team will improve this article.';
        thanks.style.display     = 'block';
        thanks.style.background  = isYes ? '#ecfdf5' : '#fef2f2';
        thanks.style.borderColor = isYes ? '#a7f3d0' : '#fca5a5';
        thanks.style.color       = isYes ? '#065f46' : '#991b1b';
    });
});
