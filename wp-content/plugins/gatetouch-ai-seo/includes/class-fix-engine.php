<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One-click remediation performs bounded lookup of posts missing plugin SEO metadata.

/**
 * Runs one-click remediation tasks for detected SEO issues.
 */
class GateTouch_Fix_Engine {

    /**
     * Entry point for one-click fixes
     */
    public static function fix( $issue_id, $args = [] ) {
        switch ( $issue_id ) {
            case 'missing_meta':
                return self::fix_missing_meta( $args );
            case 'missing_alt':
                return self::fix_missing_alt( $args );
            case 'no_sitemap':
                return self::fix_no_sitemap();
            case 'no_schema':
                return self::fix_no_schema( $args );
            default:
                return [ 'success' => false, 'error' => 'Unknown fix ID.' ];
        }
    }

    /**
     * Generate missing meta for all posts or specific IDs
     */
    private static function fix_missing_meta( $args ) {
        $post_ids = $args['post_ids'] ?? [];
        if ( empty( $post_ids ) ) {
            global $wpdb;
            $post_ids = $wpdb->get_col( $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_status='publish' AND post_type IN ('post','page') AND ID NOT IN (SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key=%s)",
                GATETOUCH_META_KEY
            ) );
        }

        $batch_limit = $args['batch_size'] ?? 5;
        $count = 0;
        foreach ( $post_ids as $id ) {
            $meta = GateTouch_AI_Engine::generate_meta( (int) $id );
            if ( ! isset( $meta['error'] ) ) {
                update_post_meta( $id, GATETOUCH_META_KEY, $meta );
                
                // Trigger analysis to update scores and checks for the dashboard
                require_once GATETOUCH_PATH . 'includes/class-analysis.php';
                GateTouch_Analysis::analyze( $id );

                $count++;
            }
            if ( $count >= $batch_limit ) break;
        }

        return [
            'success' => true,
            'fixed'   => $count,
            /* translators: %d: number of optimized pages */
            'message' => sprintf( __( 'Optimized %d pages with AI.', 'gatetouch-ai-seo' ), $count )
        ];
    }

    /**
     * Fix missing alt text using Vision AI
     */
    private static function fix_missing_alt( $args ) {
        $attachment_id = $args['attachment_id'] ?? 0;
        if ( ! $attachment_id ) return [ 'success' => false, 'error' => 'No image specified.' ];

        $url = wp_get_attachment_url( $attachment_id );
        if ( ! $url ) return [ 'success' => false, 'error' => 'Image URL not found.' ];

        $prompt = "Analyze this image and provide a highly descriptive, SEO-optimized alt text (max 125 chars) and a brief caption. Respond ONLY with valid JSON: {\"alt_text\": \"...\", \"caption\": \"...\"}";
        $result = GateTouch_AI_Engine::call_vision( $url, $prompt );

        if ( isset( $result['alt_text'] ) ) {
            update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $result['alt_text'] ) );
            return [ 'success' => true, 'alt' => $result['alt_text'] ];
        }

        return [ 'success' => false, 'error' => $result['error'] ?? 'Vision analysis failed.' ];
    }

    /**
     * Automatically configure sitemaps
     */
    private static function fix_no_sitemap() {
        $opts = [
            'enabled'        => 'yes',
            'posts_per_page' => 1000,
            'exclude_posts'  => '',
            'post_types'     => [ 'post', 'page' ],
            'taxonomies'     => [ 'category', 'post_tag' ],
            'ping_google'    => '1',
            'ping_bing'      => '1',
        ];
        update_option( 'gatetouch_sitemap_settings', $opts );
        GateTouch_Sitemap::register_rewrites();
        flush_rewrite_rules();

        return [ 'success' => true, 'message' => 'Sitemaps have been enabled and configured.' ];
    }

    /**
     * Generate AI Schema for a post
     */
    private static function fix_no_schema( $args ) {
        $post_id = $args['post_id'] ?? 0;
        if ( ! $post_id ) return [ 'success' => false, 'error' => 'No post specified.' ];

        $result = GateTouch_AI_Engine::generate_advanced_schema( $post_id );
        if ( isset( $result['schema_json'] ) ) {
            $meta = get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [];
            $meta['schema_type'] = $result['detected_type'] ?? 'Article';
            $meta['custom_schema'] = $result['schema_json'];
            update_post_meta( $post_id, GATETOUCH_META_KEY, $meta );
            return [ 'success' => true ];
        }

        return [ 'success' => false, 'error' => $result['error'] ?? 'Schema generation failed.' ];
    }
}
