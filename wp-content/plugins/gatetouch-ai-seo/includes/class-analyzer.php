<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Site audit aggregates plugin SEO metadata counts for admin reporting.

/**
 * GateTouch Analyzer
 * 
 * Deep SEO Audit & Issue Detection System.
 * Transforms basic warnings into developer-grade actionable guidance.
 */
class GateTouch_Analyzer {

    private const CACHE_KEY = 'gatetouch_site_audit_results';

    public static function run_scan() {
        $post_types = get_post_types( [ 'public' => true ], 'names' );
        $total_content = 0;
        foreach ( $post_types as $pt ) {
            $total_content += (int) wp_count_posts( $pt )->publish;
        }

        global $wpdb;
        $with_meta = (int) $wpdb->get_var(
            $wpdb->prepare( "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key=%s", GATETOUCH_META_KEY )
        );

        $sample_posts = get_posts([
            'post_type'      => ['post', 'page'],
            'post_status'    => 'publish',
            'posts_per_page' => 10,
            'orderby'        => 'rand',
        ]);

        $seo_total = 0;
        $aeo_total = 0;
        $geo_total = 0;
        $count     = 0;

        require_once GATETOUCH_PATH . 'includes/class-scoring-engine.php';

        foreach ( $sample_posts as $p ) {
            $audit = GateTouch_Scoring_Engine::audit_post( $p->ID );
            $seo_total += $audit['seo']['score'] ?? 0;
            $aeo_total += $audit['aeo']['score'] ?? 0;
            $geo_total += $audit['geo']['score'] ?? 0;
            $count++;
        }

        $results = [
            'scores' => [
                'seo'   => max( 0, min( 100, $count > 0 ? round( $seo_total / $count ) : 0 ) ),
                'aeo'   => max( 0, min( 100, $count > 0 ? round( $aeo_total / $count ) : 0 ) ),
                'geo'   => max( 0, min( 100, $count > 0 ? round( $geo_total / $count ) : 0 ) ),
                'tech'  => self::calculate_tech_score(),
            ],
            'issues'    => self::detect_issues(),
            'stats'     => [
                'total_content' => $total_content,
                'optimized'     => $with_meta,
                'missing'       => max( 0, $total_content - $with_meta ),
            ],
            'last_scan' => current_time( 'mysql' ),
        ];

        set_transient( self::CACHE_KEY, $results, DAY_IN_SECONDS );
        return $results;
    }

    public static function get_results() {
        $results = get_transient( self::CACHE_KEY );
        if ( ! $results ) {
            return self::run_scan();
        }
        return $results;
    }

    /**
     * Detect site-wide SEO issues with deep guidance.
     */
    public static function detect_issues() {
        $issues = [];

        // ── 1. MISSING METADATA ────────────────────────────────────────────────
        global $wpdb;
        $total = (int) wp_count_posts( 'post' )->publish + (int) wp_count_posts( 'page' )->publish;
        $optimized = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key=%s", GATETOUCH_META_KEY ) );
        
        if ( $optimized < $total ) {
            $missing = $total - $optimized;
            $issues[] = [
                'id'            => 'missing_meta',
                'priority'      => 'High',
                'type'          => 'error',
                'module'        => 'content',
                /* translators: %d: number of pages missing metadata */
                'title'         => sprintf( __( '%d Pages Missing Meta Data', 'gatetouch-ai-seo' ), $missing ),
                /* translators: %d: number of public pages missing metadata */
                'explanation'   => sprintf( __( 'We detected %d public pages that do not have custom AI-optimized titles or descriptions set. Currently, search engines are forced to "guess" your content snippet, which often leads to poor results.', 'gatetouch-ai-seo' ), $missing ),
                'seo_impact'    => [
                    'Lower Click-Through Rate (CTR) in search results.',
                    'Reduced keyword relevance signals to Google.',
                    'Unoptimized social sharing previews.'
                ],
                'owner_impact'  => __( 'Potential customers might see confusing snippets in Google and skip clicking on your website.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Use the Bulk AI Generator to automatically create optimized meta data for all missing pages at once.', 'gatetouch-ai-seo' ),
                'fix_developer' => __( 'Ensure your theme or other plugins are not overriding the WP <title> and <meta name="description"> tags. GT SEO/GEO/AEO uses standard WP hooks to inject this data.', 'gatetouch-ai-seo' ),
                'code_example'  => "<meta name=\"description\" content=\"Your AI-optimized description here.\">",
                'dev_details'   => [
                    'Affected Count' => $missing,
                    'Table'          => 'wp_postmeta',
                    'Meta Key'       => GATETOUCH_META_KEY
                ],
                'best_practices' => __( 'Titles should be 50-60 characters. Descriptions should be 145-160 characters.', 'gatetouch-ai-seo' ),
                'action_btn'     => [
                    'text' => __( 'Bulk Generate with AI', 'gatetouch-ai-seo' ),
                    'link' => admin_url( 'admin.php?page=gatetouch-help&id=seo-analyzer' )
                ],
                'learn_more'     => admin_url( 'admin.php?page=gatetouch-help&id=seo-analyzer' )
            ];
        }

        // ── 2. SITEMAP STATUS ──────────────────────────────────────────────────
        // Accepts 'yes', '1', or any truthy value — not just 'yes' — to handle different save formats.
        $sitemap_opts  = get_option( 'gatetouch_sitemap_settings', [] );
        $enabled_raw   = $sitemap_opts['enabled'] ?? null;
        $sitemap_on    = ( $enabled_raw !== null ) ? ! in_array( $enabled_raw, [ 'no', '0', '', false ], true ) : true;
        $index_on      = in_array( $sitemap_opts['enable_sitemap_index'] ?? 'no', [ 'yes', '1' ], true );
        $is_active     = $sitemap_on || $index_on;

        if ( ! $is_active ) {
            $issues[] = [
                'id'            => 'missing_sitemap',
                'priority'      => 'High',
                'type'          => 'warning',
                'module'        => 'technical',
                'title'         => __( 'XML Sitemap Not Active', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'A sitemap acts as a roadmap for search engines. Without it, crawlers may take longer to find new content or miss deep pages entirely.', 'gatetouch-ai-seo' ),
                'seo_impact'    => [
                    'Delayed indexing of new blog posts and products.',
                    'Incomplete crawling of site structure.',
                    'Poor visibility for orphan pages.'
                ],
                'owner_impact'  => __( 'Your new content might not show up in Google search results for several days or weeks.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Enable the GT SEO/GEO/AEO XML sitemap in your settings. It is lightweight and built for enterprise speed.', 'gatetouch-ai-seo' ),
                'fix_developer' => __( 'Our sitemap engine handles virtual rewrites. Ensure your .htaccess or Nginx config allows for virtual sitemap URLs.', 'gatetouch-ai-seo' ),
                'code_example'  => "Sitemap: " . home_url('/sitemap_index.xml'),
                'dev_details'   => [
                    'Target URL' => '/sitemap_index.xml',
                    'Engine'     => 'GateTouch_Sitemap'
                ],
                'best_practices' => __( 'Submit your sitemap index URL to Google Search Console for faster indexing.', 'gatetouch-ai-seo' ),
                'action_btn'     => [
                    'text' => __( 'Enable Sitemap Now', 'gatetouch-ai-seo' ),
                    'link' => admin_url( 'admin.php?page=gatetouch-settings&tab=tech' )
                ],
                'learn_more'     => admin_url( 'admin.php?page=gatetouch-help&id=sitemaps' )
            ];
        }

        // ── 3. AI ENGINE CONNECTIVITY ──────────────────────────────────────────
        if ( ! GateTouch_AI_Engine::is_api_operational() ) {
            $issues[] = [
                'id'            => 'api_disconnected',
                'priority'      => 'Critical',
                'type'          => 'error',
                'module'        => 'ai',
                'title'         => __( 'AI Engine Disconnected', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'The core AI engine is either not configured or has entered Safe Mode due to repeated connection failures. All automated optimization features are currently paused.', 'gatetouch-ai-seo' ),
                'seo_impact'    => [
                    'Loss of real-time search intent detection.',
                    'Disabled AI Meta generation.',
                    'Inability to analyze content NLP depth.'
                ],
                'owner_impact'  => __( 'You are missing out on the competitive advantage of AI-optimized content.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Check your AI provider API key in the Diagnostics tab and ensure your account has active credits.', 'gatetouch-ai-seo' ),
                'fix_developer' => __( 'Verify outgoing server requests to your AI provider (api.openai.com, api.anthropic.com, or generativelanguage.googleapis.com) are not blocked by your firewall.', 'gatetouch-ai-seo' ),
                'dev_details'   => [
                    'Provider' => get_option( 'gatetouch_ai_provider', 'openai' ),
                    'Status'   => get_option( 'gatetouch_api_status' ),
                ],
                'action_btn'     => [
                    'text' => __( 'Run Connection Diagnostics', 'gatetouch-ai-seo' ),
                    'link' => admin_url( 'admin.php?page=gatetouch-diagnostics' )
                ],
                'learn_more'     => admin_url( 'admin.php?page=gatetouch-help&id=troubleshooting' )
            ];
        }

        // ── 5. SEARCH ENGINE VISIBILITY (ROBOTS) ───────────────────────────
        $is_public = (int) get_option( 'blog_public', 1 );
        if ( $is_public === 0 ) {
            $issues[] = [
                'id'            => 'search_engines_discouraged',
                'priority'      => 'Critical',
                'type'          => 'error',
                'module'        => 'technical',
                'title'         => __( 'Search Engines Are Blocked (Robots.txt)', 'gatetouch-ai-seo' ),
                'explanation'   => __( 'Your WordPress settings are actively blocking Google and other search engines from indexing your site via a virtual robots.txt rule.', 'gatetouch-ai-seo' ),
                'seo_impact'    => [
                    'Zero visibility in Google search results.',
                    'Virtual robots.txt emits "Disallow: /".',
                    'Meta robots tags emit "noindex, nofollow".'
                ],
                'owner_impact'  => __( 'No one will be able to find your website on search engines. You will receive zero organic traffic.', 'gatetouch-ai-seo' ),
                'fix_beginner'  => __( 'Go to Settings > Reading and uncheck the box that says "Discourage search engines from indexing this site".', 'gatetouch-ai-seo' ),
                'fix_developer' => __( 'Ensure no physical robots.txt file in the root directory is overriding WordPress default settings.', 'gatetouch-ai-seo' ),
                'dev_details'   => [
                    'WP Option' => 'blog_public',
                    'Value'     => $is_public
                ],
                'action_btn'     => [
                    'text' => __( 'Fix Visibility Settings', 'gatetouch-ai-seo' ),
                    'link' => admin_url( 'options-reading.php' )
                ],
                'learn_more'     => admin_url( 'admin.php?page=gatetouch-help&id=robots' )
            ];
        }

        return $issues;
    }

    public static function calculate_tech_score() {
        $score = 50; 
        if ( ! empty( get_option( 'gatetouch_sitemap_settings' ) ) ) $score += 20;
        if ( ! empty( get_option( 'gatetouch_schema_settings' ) ) ) $score += 20;
        if ( ! empty( get_option( 'gatetouch_webmaster_settings' ) ) ) $score += 10;
        return min( 100, $score );
    }
}
