<?php
defined( 'ABSPATH' ) || exit;

/**
 * Registers and serves llms.txt documents for AI crawler discovery.
 */
class GateTouch_Llms {

    public function __construct() {
        add_action( 'init',              [ $this, 'register_rewrites' ] );
        add_filter( 'query_vars',        [ $this, 'add_query_vars' ] );
        add_action( 'template_redirect', [ $this, 'serve_llms' ] );
        add_action( 'template_redirect', [ $this, 'serve_llms_full' ] );
        add_action( 'save_post',         [ $this, 'bust_cache' ] );
        add_action( 'deleted_post',      [ $this, 'bust_cache' ] );
    }

    /**
     * Explicitly register rewrite rules for llms.txt endpoints
     */
    public static function register_rewrites() {
        add_rewrite_rule( 'llms\.txt$',      'index.php?gatetouch_llm=base', 'top' );
        add_rewrite_rule( 'llms-full\.txt$', 'index.php?gatetouch_llm=full', 'top' );
    }

    public function add_query_vars( $vars ) {
        $vars[] = 'gatetouch_llm';
        return $vars;
    }

    public function serve_llms() {
        $llm          = get_query_var( 'gatetouch_llm' );
        $url_path     = wp_parse_url( home_url( '/llms.txt' ), PHP_URL_PATH );
        $request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        $request_path = strtok( $request_uri, '?' );
        
        if ( $llm !== 'base' && $url_path !== $request_path && $request_path !== '/llms.txt' ) return;

        $opts = get_option( 'gatetouch_llms_settings', [] );
        if ( ( $opts['enable_llms_txt'] ?? 'no' ) !== 'yes' ) {
            status_header( 404 );
            exit;
        }

        $cached = get_transient( 'gatetouch_llms_txt' );
        if ( ! $cached ) {
            $cached = $this->build_llms( $opts );
            set_transient( 'gatetouch_llms_txt', $cached, HOUR_IN_SECONDS );
        }

        nocache_headers();
        header( 'Content-Type: text/plain; charset=UTF-8' );
        echo $cached; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        exit;
    }

    public function serve_llms_full() {
        $llm          = get_query_var( 'gatetouch_llm' );
        $url_path     = wp_parse_url( home_url( '/llms-full.txt' ), PHP_URL_PATH );
        $request_uri  = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
        $request_path = strtok( $request_uri, '?' );
        
        if ( $llm !== 'full' && $url_path !== $request_path && $request_path !== '/llms-full.txt' ) return;

        $opts = get_option( 'gatetouch_llms_settings', [] );
        if ( ( $opts['enable_llms_full_txt'] ?? 'no' ) !== 'yes' ) {
            status_header( 404 );
            exit;
        }

        $cached = get_transient( 'gatetouch_llms_full_txt' );
        if ( ! $cached ) {
            $cached = $this->build_llms_full( $opts );
            set_transient( 'gatetouch_llms_full_txt', $cached, HOUR_IN_SECONDS );
        }

        nocache_headers();
        header( 'Content-Type: text/plain; charset=UTF-8' );
        echo $cached; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        exit;
    }

    private function build_llms( $opts ) {
        $site_name = get_bloginfo( 'name' );
        $home_url  = trailingslashit( home_url() );
        $lines     = [];

        $lines[] = "# {$site_name}";
        $lines[] = '';

        $desc = $opts['site_description'] ?? get_bloginfo( 'description' );
        if ( $desc ) {
            $lines[] = "> {$desc}";
            $lines[] = '';
        }

        if ( ! empty( $opts['llms_custom_intro'] ) ) {
            $lines[] = $opts['llms_custom_intro'];
            $lines[] = '';
        }

        // Core Pages
        $lines[]      = '## Core Pages';
        $lines[]      = '';
        $core_slugs   = [ 'about', 'about-us', 'services', 'products', 'contact', 'team', 'portfolio', 'pricing' ];
        $found_pages  = 0;
        foreach ( $core_slugs as $slug ) {
            $page = get_page_by_path( $slug );
            if ( $page && $page->post_status === 'publish' ) {
                $url      = get_permalink( $page->ID );
                $seo_meta = get_post_meta( $page->ID, GATETOUCH_META_KEY, true );
                $desc_str = $seo_meta['meta_description'] ?? wp_trim_words( wp_strip_all_tags( $page->post_content ), 20, '...' );
                $lines[]  = "- [{$page->post_title}]({$url}): {$desc_str}";
                $found_pages++;
            }
        }
        if ( $found_pages === 0 ) {
            $lines[] = "- [Home]({$home_url}): " . get_bloginfo( 'description' );
        }
        $lines[] = '';

        // WooCommerce products
        if ( class_exists( 'WooCommerce' ) ) {
            $products = get_posts( [ 'post_type' => 'product', 'posts_per_page' => 20, 'post_status' => 'publish' ] );
            if ( ! empty( $products ) ) {
                $lines[] = '## Products';
                $lines[] = '';
                foreach ( $products as $product ) {
                    $url     = get_permalink( $product->ID );
                    $excerpt = wp_trim_words( wp_strip_all_tags( $product->post_content ), 20, '...' );
                    $lines[] = "- [{$product->post_title}]({$url}): {$excerpt}";
                }
                $lines[] = '';
            }
        }

        // Best Articles
        $max_posts = intval( $opts['llms_max_posts'] ?? 20 );
        $posts = get_posts( [
            'post_type'      => 'post',
            'post_status'    => 'publish',
            'posts_per_page' => $max_posts,
            'orderby'        => 'comment_count',
            'order'          => 'DESC',
        ] );

        if ( ! empty( $posts ) ) {
            $lines[] = '## Articles & Guides';
            $lines[] = '';
            foreach ( $posts as $post ) {
                $url      = get_permalink( $post->ID );
                $seo_meta = get_post_meta( $post->ID, GATETOUCH_META_KEY, true );
                $desc_str = ! empty( $seo_meta['meta_description'] )
                    ? $seo_meta['meta_description']
                    : wp_trim_words( wp_strip_all_tags( $post->post_content ), 25, '...' );
                $lines[]  = "- [{$post->post_title}]({$url}): {$desc_str}";
            }
            $lines[] = '';
        }

        // Custom post types
        $included_cpts = (array) ( $opts['llms_include_cpts'] ?? [] );
        $cpts          = get_post_types( [ 'public' => true, '_builtin' => false ], 'objects' );
        foreach ( $cpts as $cpt ) {
            if ( ! in_array( $cpt->name, $included_cpts, true ) ) continue;
            $cpt_posts = get_posts( [ 'post_type' => $cpt->name, 'post_status' => 'publish', 'posts_per_page' => 10 ] );
            if ( empty( $cpt_posts ) ) continue;
            $lines[] = '## ' . $cpt->labels->name;
            $lines[] = '';
            foreach ( $cpt_posts as $cpt_post ) {
                $url     = get_permalink( $cpt_post->ID );
                $excerpt = wp_trim_words( wp_strip_all_tags( $cpt_post->post_content ), 20, '...' );
                $lines[] = "- [{$cpt_post->post_title}]({$url}): {$excerpt}";
            }
            $lines[] = '';
        }

        $lines[] = '## Excluded Areas';
        $lines[] = '';
        $lines[] = '- The following areas contain private user data and should not be indexed or trained on: /wp-admin/, /cart/, /checkout/, /my-account/, /wp-login.php';
        $lines[] = '';
        $lines[] = '---';
        $lines[] = 'Generated by GT SEO/GEO/AEO on ' . gmdate( 'Y-m-d' ) . ' | ' . $home_url;

        return implode( "\n", $lines );
    }

    private function build_llms_full( $opts ) {
        $site_name = get_bloginfo( 'name' );
        $max       = intval( $opts['llms_full_max_pages'] ?? 5 );

        $output  = "# {$site_name} — Full Content Digest\n\n";
        $output .= "> Complete Markdown content of key pages for AI inference and citation.\n\n---\n\n";

        $top_posts = get_posts( [
            'post_type'      => [ 'post', 'page' ],
            'post_status'    => 'publish',
            'posts_per_page' => $max,
            'orderby'        => 'comment_count',
            'order'          => 'DESC',
        ] );

        foreach ( $top_posts as $post ) {
            $title   = $post->post_title;
            $url     = get_permalink( $post->ID );
            $content = wp_strip_all_tags( $post->post_content );
            $content = preg_replace( '/\n{3,}/', "\n\n", $content );
            $content = trim( $content );

            $output .= "## {$title}\n\n";
            $output .= "**URL:** {$url}  \n";
            $output .= "**Published:** " . get_the_date( 'Y-m-d', $post->ID ) . "\n\n";
            $output .= $content . "\n\n---\n\n";
        }

        return $output;
    }

    public function bust_cache() {
        delete_transient( 'gatetouch_llms_txt' );
        delete_transient( 'gatetouch_llms_full_txt' );
    }
}
