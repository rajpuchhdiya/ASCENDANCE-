<?php
defined( 'ABSPATH' ) || exit;

/**
 * Injects GateTouch frontend meta tags, titles, schema, and breadcrumbs.
 */
class GateTouch_Frontend {

    public function __construct() {
        // Core Meta Injection
        add_action( 'wp_head', [ $this, 'inject_meta_tags' ], 1 );
        
        // Title Tag Support
        add_filter( 'pre_get_document_title', [ $this, 'custom_title' ], 15 );
        add_filter( 'wp_title', [ $this, 'custom_title' ], 15 );
    }

    /**
     * Overrides the document title with the AI-optimized title
     */
    public function custom_title( $title ) {
        if ( ! is_singular() ) return $title;

        $meta = get_post_meta( get_the_ID(), GATETOUCH_META_KEY, true );
        if ( ! empty( $meta['meta_title'] ) ) {
            return $meta['meta_title'];
        }

        return $title;
    }

    /**
     * Injects Meta Description, OG tags, and Schema into wp_head
     */
    public function inject_meta_tags() {
        if ( ! is_singular() ) return;

        $post_id = get_the_ID();
        $meta    = get_post_meta( $post_id, GATETOUCH_META_KEY, true );
        if ( empty( $meta ) ) return;

        echo "\n<!-- GT SEO/GEO/AEO Start -->\n";

        // 1. Robots directives
        $noindex  = ! empty( $meta['noindex'] );
        $nofollow = ! empty( $meta['nofollow'] );
        if ( $noindex || $nofollow ) {
            $robots = implode( ', ', array_filter( [ $noindex ? 'noindex' : '', $nofollow ? 'nofollow' : '' ] ) );
            echo '<meta name="robots" content="' . esc_attr( $robots ) . '" />' . "\n";
        }

        // 2. Canonical URL
        $canonical = ! empty( $meta['canonical'] ) ? $meta['canonical'] : get_permalink( $post_id );
        echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";

        // 3. Basic Meta
        if ( ! empty( $meta['meta_description'] ) ) {
            echo '<meta name="description" content="' . esc_attr( $meta['meta_description'] ) . '" />' . "\n";
        }

        // 4. Open Graph (Social)
        echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '" />' . "\n";
        echo '<meta property="og:type" content="' . esc_attr( $meta['og_type'] ?? 'article' ) . '" />' . "\n";
        echo '<meta property="og:title" content="' . esc_attr( $meta['og_title'] ?: ($meta['meta_title'] ?: get_the_title()) ) . '" />' . "\n";
        echo '<meta property="og:description" content="' . esc_attr( $meta['og_description'] ?: ($meta['meta_description'] ?: '') ) . '" />' . "\n";
        echo '<meta property="og:url" content="' . esc_url( get_permalink() ) . '" />' . "\n";
        echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '" />' . "\n";

        // Smart Image Fallback
        $og_image = $meta['og_image'] ?? '';
        if ( empty( $og_image ) ) {
            // 1. Try Featured Image
            $og_image = get_the_post_thumbnail_url( $post_id, 'full' );
            
            // 2. Fallback to first image in content
            if ( empty( $og_image ) ) {
                $post_content = get_post_field( 'post_content', $post_id );
                if ( preg_match( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post_content, $matches ) ) {
                    $og_image = $matches[1];
                }
            }
        }

        if ( ! empty( $og_image ) ) {
            echo '<meta property="og:image" content="' . esc_url( $og_image ) . '" />' . "\n";
            echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '" />' . "\n";
        }

        // 5. Twitter
        echo '<meta name="twitter:card" content="' . esc_attr( $meta['twitter_card'] ?? 'summary_large_image' ) . '" />' . "\n";
        echo '<meta name="twitter:title" content="' . esc_attr( $meta['twitter_title'] ?: ($meta['meta_title'] ?: get_the_title()) ) . '" />' . "\n";
        echo '<meta name="twitter:description" content="' . esc_attr( $meta['twitter_description'] ?: ($meta['og_description'] ?: ($meta['meta_description'] ?: '')) ) . '" />' . "\n";
        
        // 6. Schema AI (JSON-LD) — decode and re-encode to guarantee valid, safe JSON
        if ( ! empty( $meta['custom_schema'] ) ) {
            $decoded = json_decode( $meta['custom_schema'] );
            if ( json_last_error() === JSON_ERROR_NONE ) {
                GateTouch_Helpers::print_json_ld( $decoded, 'gatetouch-schema' );
            }
        }

        echo "<!-- GT SEO/GEO/AEO End -->\n\n";
    }
}

// GateTouch_Frontend is instantiated by GateTouch_Core::boot() only — do not auto-instantiate here.
