<?php
defined( 'ABSPATH' ) || exit;

/**
 * Free crawl-budget optimization controls for WordPress frontend output.
 */
class GateTouch_Crawl_Optimization {

    public function __construct() {
        if ( is_admin() ) {
            return;
        }

        add_action( 'init', [ $this, 'cleanup_head_output' ], 20 );
        add_action( 'template_redirect', [ $this, 'maybe_block_feed' ], 0 );
        add_action( 'template_redirect', [ $this, 'maybe_filter_search' ], 1 );
        add_action( 'template_redirect', [ $this, 'maybe_cleanup_url_params' ], 2 );
        add_filter( 'wp_headers', [ $this, 'filter_headers' ] );
        add_action( 'send_headers', [ $this, 'remove_php_headers' ], 100 );
    }

    public static function defaults() {
        return [
            'remove_shortlinks'              => '1',
            'remove_rest_links'              => '1',
            'remove_rsd_wlw_links'           => '1',
            'remove_oembed_links'            => '1',
            'remove_generator_tag'           => '1',
            'remove_pingback_header'         => '1',
            'remove_powered_by_header'       => '1',
            'remove_wp_json_discovery'       => '',
            'remove_emoji_scripts'           => '1',
            'disable_global_feeds'           => '',
            'disable_global_comment_feeds'   => '',
            'disable_post_comments_feeds'    => '',
            'disable_author_feeds'           => '',
            'disable_post_type_feeds'        => '',
            'disable_category_feeds'         => '',
            'disable_tag_feeds'              => '',
            'disable_custom_taxonomy_feeds'  => '',
            'disable_search_feeds'           => '',
            'disable_atom_rdf_feeds'         => '',
            'block_adsbot'                   => '',
            'block_google_extended'          => '',
            'block_gptbot'                   => '',
            'block_ccbot'                    => '',
            'block_claudebot'                => '',
            'filter_search_terms'            => '',
            'max_search_length'              => '50',
            'filter_search_special_chars'    => '',
            'filter_search_spam_patterns'    => '',
            'redirect_pretty_search'         => '',
            'prevent_search_crawling'        => '',
            'clean_ga_params'                => '',
            'remove_unregistered_params'     => '',
            'allowed_url_params'             => '',
        ];
    }

    public static function settings() {
        $settings = get_option( 'gatetouch_crawl_optimization_settings', [] );
        return wp_parse_args( is_array( $settings ) ? $settings : [], self::defaults() );
    }

    public static function sanitize( $value ) {
        $value    = is_array( $value ) ? $value : [];
        $defaults = self::defaults();
        $clean    = [];

        foreach ( $defaults as $key => $default ) {
            if ( 'max_search_length' === $key ) {
                $clean[ $key ] = (string) max( 1, min( 250, absint( $value[ $key ] ?? $default ) ) );
            } elseif ( 'allowed_url_params' === $key ) {
                $params = preg_split( '/[\s,]+/', (string) ( $value[ $key ] ?? '' ), -1, PREG_SPLIT_NO_EMPTY );
                $params = array_map( 'sanitize_key', $params );
                $clean[ $key ] = implode( ', ', array_values( array_unique( array_filter( $params ) ) ) );
            } else {
                $clean[ $key ] = ! empty( $value[ $key ] ) ? '1' : '';
            }
        }

        return $clean;
    }

    public static function sections() {
        return [
            'metadata' => [
                'title'       => __( 'Remove Unwanted Metadata', 'gatetouch-ai-seo' ),
                'description' => __( 'Remove low-value discovery links and headers from the page source.', 'gatetouch-ai-seo' ),
                'fields'      => [
                    'remove_shortlinks'        => __( 'Remove shortlinks', 'gatetouch-ai-seo' ),
                    'remove_rest_links'        => __( 'Remove REST API discovery links', 'gatetouch-ai-seo' ),
                    'remove_rsd_wlw_links'     => __( 'Remove RSD and WLW manifest links', 'gatetouch-ai-seo' ),
                    'remove_oembed_links'      => __( 'Remove oEmbed discovery links', 'gatetouch-ai-seo' ),
                    'remove_generator_tag'     => __( 'Remove WordPress generator tag', 'gatetouch-ai-seo' ),
                    'remove_pingback_header'   => __( 'Remove Pingback HTTP header', 'gatetouch-ai-seo' ),
                    'remove_powered_by_header' => __( 'Remove X-Powered-By HTTP header', 'gatetouch-ai-seo' ),
                ],
            ],
            'feeds' => [
                'title'       => __( 'Disable Unwanted Content Formats', 'gatetouch-ai-seo' ),
                'description' => __( 'Turn off feeds that can waste crawl budget or expose thin duplicate URLs.', 'gatetouch-ai-seo' ),
                'fields'      => [
                    'disable_global_feeds'          => __( 'Remove global feeds', 'gatetouch-ai-seo' ),
                    'disable_global_comment_feeds'  => __( 'Remove global comment feeds', 'gatetouch-ai-seo' ),
                    'disable_post_comments_feeds'   => __( 'Remove post comments feeds', 'gatetouch-ai-seo' ),
                    'disable_author_feeds'          => __( 'Remove author feeds', 'gatetouch-ai-seo' ),
                    'disable_post_type_feeds'       => __( 'Remove post type feeds', 'gatetouch-ai-seo' ),
                    'disable_category_feeds'        => __( 'Remove category feeds', 'gatetouch-ai-seo' ),
                    'disable_tag_feeds'             => __( 'Remove tag feeds', 'gatetouch-ai-seo' ),
                    'disable_custom_taxonomy_feeds' => __( 'Remove custom taxonomy feeds', 'gatetouch-ai-seo' ),
                    'disable_search_feeds'          => __( 'Remove search results feeds', 'gatetouch-ai-seo' ),
                    'disable_atom_rdf_feeds'        => __( 'Remove Atom/RDF feeds', 'gatetouch-ai-seo' ),
                ],
            ],
            'resources' => [
                'title'       => __( 'Remove Unused Resources', 'gatetouch-ai-seo' ),
                'description' => __( 'Reduce frontend scripts and crawler discovery surfaces.', 'gatetouch-ai-seo' ),
                'fields'      => [
                    'remove_emoji_scripts'     => __( 'Remove emoji scripts and styles', 'gatetouch-ai-seo' ),
                    'remove_wp_json_discovery' => __( 'Discourage crawling of the WP JSON API', 'gatetouch-ai-seo' ),
                ],
            ],
            'bots' => [
                'title'       => __( 'Block Unwanted Bots', 'gatetouch-ai-seo' ),
                'description' => __( 'Add robots.txt disallow rules for crawlers that may not help organic search.', 'gatetouch-ai-seo' ),
                'fields'      => [
                    'block_adsbot'          => __( 'Prevent Google AdsBot from crawling', 'gatetouch-ai-seo' ),
                    'block_google_extended' => __( 'Prevent Google Gemini and Vertex AI bots from crawling', 'gatetouch-ai-seo' ),
                    'block_gptbot'          => __( 'Prevent OpenAI GPTBot from crawling', 'gatetouch-ai-seo' ),
                    'block_ccbot'           => __( 'Prevent Common Crawl CCBot from crawling', 'gatetouch-ai-seo' ),
                    'block_claudebot'       => __( 'Prevent Anthropic ClaudeBot from crawling', 'gatetouch-ai-seo' ),
                ],
            ],
            'search' => [
                'title'       => __( 'Internal Site Search Cleanup', 'gatetouch-ai-seo' ),
                'description' => __( 'Filter spammy internal search URLs and optionally keep search URLs in raw query format.', 'gatetouch-ai-seo' ),
                'fields'      => [
                    'filter_search_terms'         => __( 'Filter long search terms', 'gatetouch-ai-seo' ),
                    'filter_search_special_chars' => __( 'Filter searches with emojis and special characters', 'gatetouch-ai-seo' ),
                    'filter_search_spam_patterns' => __( 'Filter searches with common spam patterns', 'gatetouch-ai-seo' ),
                    'redirect_pretty_search'      => __( 'Redirect pretty /search/ URLs to ?s= format', 'gatetouch-ai-seo' ),
                    'prevent_search_crawling'     => __( 'Prevent crawling of internal site search URLs', 'gatetouch-ai-seo' ),
                ],
            ],
            'url' => [
                'title'       => __( 'Advanced URL Cleanup', 'gatetouch-ai-seo' ),
                'description' => __( 'Remove tracking and unknown URL parameters with safe 301 redirects.', 'gatetouch-ai-seo' ),
                'fields'      => [
                    'clean_ga_params'            => __( 'Optimize Google Analytics and ad tracking parameters', 'gatetouch-ai-seo' ),
                    'remove_unregistered_params' => __( 'Remove unregistered URL parameters', 'gatetouch-ai-seo' ),
                ],
            ],
        ];
    }

    public function cleanup_head_output() {
        $opts = self::settings();

        if ( ! empty( $opts['remove_shortlinks'] ) ) {
            remove_action( 'wp_head', 'wp_shortlink_wp_head' );
            remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
        }
        if ( ! empty( $opts['remove_rest_links'] ) ) {
            remove_action( 'wp_head', 'rest_output_link_wp_head' );
            remove_action( 'template_redirect', 'rest_output_link_header', 11 );
        }
        if ( ! empty( $opts['remove_rsd_wlw_links'] ) ) {
            remove_action( 'wp_head', 'rsd_link' );
            remove_action( 'wp_head', 'wlwmanifest_link' );
        }
        if ( ! empty( $opts['remove_oembed_links'] ) ) {
            remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
            remove_action( 'wp_head', 'wp_oembed_add_host_js' );
        }
        if ( ! empty( $opts['remove_generator_tag'] ) ) {
            add_filter( 'the_generator', '__return_empty_string' );
            remove_action( 'wp_head', 'wp_generator' );
        }
        if ( ! empty( $opts['remove_emoji_scripts'] ) ) {
            remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
            remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
            remove_action( 'wp_print_styles', 'print_emoji_styles' );
            remove_action( 'admin_print_styles', 'print_emoji_styles' );
            remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
            remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
            remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
        }
        if ( ! empty( $opts['disable_global_feeds'] ) || ! empty( $opts['disable_global_comment_feeds'] ) ) {
            remove_action( 'wp_head', 'feed_links', 2 );
        }
        if (
            ! empty( $opts['disable_post_comments_feeds'] )
            || ! empty( $opts['disable_author_feeds'] )
            || ! empty( $opts['disable_post_type_feeds'] )
            || ! empty( $opts['disable_category_feeds'] )
            || ! empty( $opts['disable_tag_feeds'] )
            || ! empty( $opts['disable_custom_taxonomy_feeds'] )
            || ! empty( $opts['disable_search_feeds'] )
        ) {
            remove_action( 'wp_head', 'feed_links_extra', 3 );
        }
    }

    public function filter_headers( $headers ) {
        $opts = self::settings();

        if ( ! empty( $opts['remove_pingback_header'] ) ) {
            unset( $headers['X-Pingback'] );
        }

        return $headers;
    }

    public function remove_php_headers() {
        $opts = self::settings();

        if ( ! empty( $opts['remove_powered_by_header'] ) && ! headers_sent() ) {
            header_remove( 'X-Powered-By' );
        }
        if ( ! empty( $opts['remove_pingback_header'] ) && ! headers_sent() ) {
            header_remove( 'X-Pingback' );
        }
    }

    public function maybe_block_feed() {
        if ( is_admin() || ! is_feed() ) {
            return;
        }

        $opts = self::settings();
        $feed = (string) get_query_var( 'feed' );

        if ( ! empty( $opts['disable_atom_rdf_feeds'] ) && in_array( $feed, [ 'atom', 'rdf', 'rdf2' ], true ) ) {
            $this->block_feed_response();
        }
        if ( ! empty( $opts['disable_global_comment_feeds'] ) && is_comment_feed() && ! is_singular() ) {
            $this->block_feed_response();
        }
        if ( ! empty( $opts['disable_post_comments_feeds'] ) && is_comment_feed() && is_singular() ) {
            $this->block_feed_response();
        }
        if ( ! empty( $opts['disable_author_feeds'] ) && is_author() ) {
            $this->block_feed_response();
        }
        if ( ! empty( $opts['disable_post_type_feeds'] ) && is_post_type_archive() ) {
            $this->block_feed_response();
        }
        if ( ! empty( $opts['disable_category_feeds'] ) && is_category() ) {
            $this->block_feed_response();
        }
        if ( ! empty( $opts['disable_tag_feeds'] ) && is_tag() ) {
            $this->block_feed_response();
        }
        if ( ! empty( $opts['disable_custom_taxonomy_feeds'] ) && is_tax() ) {
            $this->block_feed_response();
        }
        if ( ! empty( $opts['disable_search_feeds'] ) && is_search() ) {
            $this->block_feed_response();
        }
        if ( ! empty( $opts['disable_global_feeds'] ) && ! is_comment_feed() && ! is_singular() && ! is_archive() && ! is_search() ) {
            $this->block_feed_response();
        }
    }

    public function maybe_filter_search() {
        if ( is_admin() || ! is_search() ) {
            return;
        }

        $opts  = self::settings();
        $query = get_search_query( false );

        if ( ! empty( $opts['redirect_pretty_search'] ) && empty( $_GET['s'] ) && $query !== '' ) {
            wp_safe_redirect( add_query_arg( 's', $query, home_url( '/' ) ), 301 );
            exit;
        }

        if ( $this->is_invalid_search_query( $query, $opts ) ) {
            status_header( 404 );
            nocache_headers();
            wp_die(
                esc_html__( 'This search query was blocked by crawl optimization rules.', 'gatetouch-ai-seo' ),
                esc_html__( 'Search blocked', 'gatetouch-ai-seo' ),
                [ 'response' => 404 ]
            );
        }
    }

    public function maybe_cleanup_url_params() {
        if ( is_admin() || wp_doing_ajax() || is_feed() || 'GET' !== ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
            return;
        }

        $opts = self::settings();
        if ( empty( $_GET ) || ( empty( $opts['clean_ga_params'] ) && empty( $opts['remove_unregistered_params'] ) ) ) {
            return;
        }

        $tracking = [
            'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content', 'utm_id',
            'gclid', 'dclid', 'fbclid', 'msclkid', 'mc_cid', 'mc_eid', 'igshid', '_ga',
        ];
        $allowed = array_merge(
            [
                's', 'p', 'page_id', 'cat', 'tag', 'author', 'year', 'monthnum', 'day', 'name', 'paged',
                'feed', 'post_type', 'preview', 'preview_id', 'preview_nonce', 'attachment_id', 'cpage',
                'rest_route', '_wpnonce', 'nonce', 'customize_changeset_uuid', 'customize_theme',
                'elementor-preview', 'doing_wp_cron', 'replytocom', 'wc-ajax', 'add-to-cart', 'orderby',
                'filter', 'min_price', 'max_price', 'rating_filter', 'lang',
            ],
            $this->allowed_url_params( $opts )
        );

        $clean = [];
        foreach ( $_GET as $key => $value ) {
            $key = sanitize_key( wp_unslash( $key ) );
            if ( $key === '' ) {
                continue;
            }

            if ( ! empty( $opts['clean_ga_params'] ) && in_array( $key, $tracking, true ) ) {
                continue;
            }
            if ( ! empty( $opts['remove_unregistered_params'] ) && ! in_array( $key, $allowed, true ) ) {
                continue;
            }

            $clean[ $key ] = map_deep( wp_unslash( $value ), 'sanitize_text_field' );
        }

        if ( count( $clean ) === count( $_GET ) ) {
            return;
        }

        $request_path = isset( $GLOBALS['wp']->request ) ? '/' . ltrim( (string) $GLOBALS['wp']->request, '/' ) : '/';
        $path         = wp_parse_url( home_url( $request_path ), PHP_URL_PATH );
        $url  = home_url( $path ?: '/' );
        if ( ! empty( $clean ) ) {
            $url = add_query_arg( $clean, $url );
        }

        wp_safe_redirect( $url, 301 );
        exit;
    }

    public static function robots_disallow_groups() {
        $opts   = self::settings();
        $groups = [];

        if ( ! empty( $opts['prevent_search_crawling'] ) ) {
            $groups['*'][] = '/?s=';
            $groups['*'][] = '/search/';
        }
        if ( ! empty( $opts['remove_wp_json_discovery'] ) ) {
            $groups['*'][] = '/wp-json/';
        }
        if ( ! empty( $opts['block_adsbot'] ) ) {
            $groups['AdsBot-Google'][]        = '/';
            $groups['AdsBot-Google-Mobile'][] = '/';
        }
        if ( ! empty( $opts['block_google_extended'] ) ) {
            $groups['Google-Extended'][] = '/';
        }
        if ( ! empty( $opts['block_gptbot'] ) ) {
            $groups['GPTBot'][] = '/';
        }
        if ( ! empty( $opts['block_ccbot'] ) ) {
            $groups['CCBot'][] = '/';
        }
        if ( ! empty( $opts['block_claudebot'] ) ) {
            $groups['ClaudeBot'][] = '/';
        }

        foreach ( $groups as $agent => $paths ) {
            $groups[ $agent ] = array_values( array_unique( $paths ) );
        }

        return $groups;
    }

    private function block_feed_response() {
        status_header( 404 );
        nocache_headers();
        wp_die(
            esc_html__( 'This feed has been disabled by crawl optimization settings.', 'gatetouch-ai-seo' ),
            esc_html__( 'Feed disabled', 'gatetouch-ai-seo' ),
            [ 'response' => 404 ]
        );
    }

    private function is_invalid_search_query( $query, $opts ) {
        $query = trim( (string) $query );

        $query_length = function_exists( 'mb_strlen' ) ? mb_strlen( $query ) : strlen( $query );

        if ( ! empty( $opts['filter_search_terms'] ) && $query_length > (int) ( $opts['max_search_length'] ?? 50 ) ) {
            return true;
        }
        if ( ! empty( $opts['filter_search_special_chars'] ) && preg_match( '/[<>{}\[\]\\\\]|[\x{1F000}-\x{1FAFF}]/u', $query ) ) {
            return true;
        }
        if ( ! empty( $opts['filter_search_spam_patterns'] ) && preg_match( '/(https?:\/\/|www\.|casino|viagra|porn|payday|loan|crypto|base64|eval\(|wp-login|xmlrpc|\.php)/i', $query ) ) {
            return true;
        }

        return false;
    }

    private function allowed_url_params( $opts ) {
        $params = preg_split( '/[\s,]+/', (string) ( $opts['allowed_url_params'] ?? '' ), -1, PREG_SPLIT_NO_EMPTY );
        return array_values( array_unique( array_filter( array_map( 'sanitize_key', $params ) ) ) );
    }
}
