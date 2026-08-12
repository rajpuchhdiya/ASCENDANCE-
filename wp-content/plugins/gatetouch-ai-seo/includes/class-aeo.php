<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- llms.txt lists recently optimized content by the plugin metadata key.

/**
 * Serves AEO-oriented machine-readable content endpoints.
 */
class GateTouch_AEO {

    public function __construct() {
        add_action( 'init', [ $this, 'register_rewrites' ] );
        add_filter( 'query_vars', [ $this, 'register_query_vars' ] );
        add_action( 'template_redirect', [ $this, 'render_llms_txt' ] );
    }

    private static function keyword_matches( $haystack, $keyword ) {
        $normalize = static function( $text ) {
            $text = html_entity_decode( wp_strip_all_tags( (string) $text ), ENT_QUOTES, get_bloginfo( 'charset' ) );
            $text = function_exists( 'remove_accents' ) ? remove_accents( $text ) : $text;
            $text = mb_strtolower( $text );
            $text = preg_replace( '/[^\p{L}\p{N}]+/u', ' ', $text );
            return trim( preg_replace( '/\s+/u', ' ', $text ) );
        };

        $keyword_norm = $normalize( $keyword );
        if ( $keyword_norm === '' ) {
            return false;
        }

        $haystack_norm = ' ' . $normalize( $haystack ) . ' ';
        if ( preg_match( '/\s' . preg_quote( $keyword_norm, '/' ) . '\s/u', $haystack_norm ) ) {
            return true;
        }

        $terms = preg_split( '/\s+/u', $keyword_norm, -1, PREG_SPLIT_NO_EMPTY );
        $terms = array_values( array_filter( array_unique( $terms ), function( $term ) {
            return mb_strlen( $term ) > 2 || is_numeric( $term ) || in_array( $term, [ 'ai', 'ui', 'ux' ], true );
        } ) );

        if ( empty( $terms ) ) {
            return false;
        }

        $matched = 0;
        foreach ( $terms as $term ) {
            if ( preg_match( '/\s' . preg_quote( $term, '/' ) . '\s/u', $haystack_norm ) ) {
                $matched++;
            }
        }

        return ( $matched / count( $terms ) ) >= ( count( $terms ) <= 2 ? 1 : 0.8 );
    }

    public function register_query_vars( $vars ) {
        $vars[] = 'gatetouch_aeo';
        return $vars;
    }

    /**
     * Register rewrite rule for llms.txt
     */
    public function register_rewrites() {
        add_rewrite_rule( 'llms\.txt$', 'index.php?gatetouch_aeo=llms', 'top' );
        add_rewrite_tag( '%gatetouch_aeo%', '([^&]+)' );
    }

    /**
     * Render the llms.txt file
     */
    public function render_llms_txt() {
        if ( get_query_var( 'gatetouch_aeo' ) !== 'llms' ) return;

        header( 'Content-Type: text/plain; charset=utf-8' );
        
        $site_name = wp_strip_all_tags( get_bloginfo( 'name' ) );
        $tagline   = wp_strip_all_tags( get_bloginfo( 'description' ) );
        
        echo esc_html( "# {$site_name}\n" );
        echo esc_html( "> {$tagline}\n\n" );
        
        echo esc_html( "## Key Information\n" );
        echo esc_html( '- Site URL: ' ) . esc_url( home_url() ) . "\n";
        echo esc_html( '- Contact: ' . sanitize_email( get_option( 'admin_email' ) ) . "\n\n" );

        echo esc_html( "## Top Content\n" );
        
        $posts = get_posts([
            'post_type'      => ['post', 'page'],
            'post_status'    => 'publish',
            'posts_per_page' => 50,
            'meta_key'       => GATETOUCH_META_KEY,
            'orderby'        => 'date',
            'order'          => 'DESC'
        ]);

        foreach ( $posts as $p ) {
            $meta = get_post_meta( $p->ID, GATETOUCH_META_KEY, true );
            $desc = ! empty( $meta['meta_description'] ) ? $meta['meta_description'] : wp_trim_words( $p->post_content, 20 );
            
            echo esc_html( '- [' . wp_strip_all_tags( $p->post_title ) . '](' );
            echo esc_url( get_permalink( $p->ID ) );
            echo esc_html( '): ' . wp_strip_all_tags( $desc ) . "\n" );
        }

        echo esc_html( "\n## About GT SEO/GEO/AEO\n" );
        echo esc_html__( 'This site is optimized for AI Search Engines and LLMs using GT SEO/GEO/AEO.', 'gatetouch-ai-seo' ) . ' ';
        echo esc_html__( 'We provide high-density semantic content for cognitive search bots.', 'gatetouch-ai-seo' );

        exit;
    }

    /**
     * Check if a page is AEO ready
     */
    public static function check_readiness( $post_id ) {
        $meta = get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [];
        $post = get_post( $post_id );
        $content = $post->post_content;

        $score = 0;
        $max   = 0;
        $tips  = [];

        // 1. Structured Data (Schema)
        $max += 25;
        if ( ! empty( $meta['schema_type'] ) ) {
            $score += 25;
        } else {
            $tips[] = 'Add Schema markup to help AI bots understand entity relationships.';
        }

        // 2. Clear Headers (Answer Format)
        $max += 25;
        if ( preg_match( '/<h[234][^>]*>.*\?<\/h[234]>/is', $content ) || preg_match( '/\b(how|what|why|best|top|guide|compare)\b/i', wp_strip_all_tags( $content ) ) ) {
            $score += 25;
        } else {
            $tips[] = 'Use question-based subheadings (H2-H4) to trigger AI Answer Boxes.';
        }

        // 3. Focus Keyword Density in Intro
        $max += 20;
        $kw = $meta['focus_keyword'] ?? '';
        if ( $kw && self::keyword_matches( substr( wp_strip_all_tags( $content ), 0, 700 ), $kw ) ) {
            $score += 20;
        } else {
            $tips[] = 'Place your focus keyword in the first paragraph for faster intent detection.';
        }

        // 4. FAQ presence
        $max += 15;
        if ( ! empty( $meta['faqs'] ) ) {
            $score += 15;
        } else {
            $tips[] = 'Add an FAQ section. LLMs prioritize sites with clear Q&A patterns.';
        }

        // 5. Semantic Depth (Meta Keywords)
        $max += 15;
        if ( ! empty( $meta['additional_keywords'] ) ) {
            $score += 15;
        } else {
            $tips[] = 'Add secondary keywords to provide more semantic context to AI bots.';
        }

        $final = $max > 0 ? round( ( $score / $max ) * 100 ) : 0;

        return [
            'score' => $final,
            'tips'  => $tips
        ];
    }
}
