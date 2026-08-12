<?php
defined( 'ABSPATH' ) || exit;

/**
 * Search Appearance — the single source of truth for frontend SEO output.
 *
 * Resolves the title, description, robots directives, canonical URL and social
 * tags for EVERY page type WordPress can render: the front page, the blog index,
 * every post type, every taxonomy (including WooCommerce), post type archives,
 * author archives, date archives, search results, 404s and attachments.
 *
 * Resolution order for every field:
 *   1. Per-object override  (post meta box / term fields / user profile)
 *   2. Site-wide template   (Settings → Search Appearance)
 *   3. Built-in default     (defaults() below — tuned to rank without configuration)
 */
class GateTouch_Search_Appearance {

    const OPTION = 'gatetouch_search_appearance';

    /** Memoised settings for the request. */
    private static $settings = null;

    /** Memoised context for the current query. */
    private static $context = null;

    // ─────────────────────────────────────────────────────────────────────────
    // Settings
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Built-in defaults. These are what a user gets the moment they activate the
     * plugin — no configuration required.
     */
    public static function defaults() {
        return [
            'global' => [
                'title_separator'   => '|',
                'homepage_title'    => '#site_title# #sep# #tagline#',
                'homepage_desc'     => '',
                'homepage_noindex'  => '',
                'og_default_image'  => '',
                'twitter_card'      => 'summary_large_image',
                'twitter_site'      => '',
                'facebook_app_id'   => '',
                'capitalize_titles' => '',
            ],

            // Per post type. Unknown types fall back to the 'default' entry.
            'content_types' => [
                'default' => [
                    'title'        => '#title# #sep# #site_title#',
                    'desc'         => '#excerpt#',
                    'noindex'      => '',
                    'nofollow'     => '',
                    'noarchive'    => '',
                    'noimageindex' => '',
                    'nosnippet'    => '',
                    'schema_type'  => 'Article',
                    'og_image'     => '',
                    'show_in_sitemap' => '1',
                ],
                'page' => [
                    'title'       => '#title# #sep# #site_title#',
                    'desc'        => '#excerpt#',
                    'schema_type' => 'WebPage',
                ],
                'product' => [
                    'title'       => '#title# #sep# #site_title#',
                    'desc'        => '#wc_short_desc#',
                    'schema_type' => 'Product',
                ],
                'attachment' => [
                    'title'       => '#title# #sep# #site_title#',
                    'desc'        => '#excerpt#',
                    'noindex'     => '1',
                    'schema_type' => 'ImageObject',
                ],
            ],

            // Per taxonomy. Unknown taxonomies fall back to the 'default' entry.
            'taxonomies' => [
                'default' => [
                    'title'         => '#term# #sep# #site_title#',
                    'desc'          => '#term_description#',
                    'noindex'       => '',
                    'noindex_empty' => '1',
                    'schema_type'   => 'CollectionPage',
                    'show_item_list' => '1',
                ],
                'category' => [
                    'title' => '#term# #sep# #site_title#',
                    /* translators: default meta description template for category archives. */
                    'desc'  => __( 'Browse #term_posts# about #term# on #site_title#. #term_description#', 'gatetouch-ai-seo' ),
                ],
                'post_tag' => [
                    'title' => '#term# #sep# #site_title#',
                    /* translators: default meta description template for tag archives. */
                    'desc'  => __( 'Everything tagged #term# — #term_posts# on #site_title#. #term_description#', 'gatetouch-ai-seo' ),
                ],
                'product_cat' => [
                    'title' => '#term# #sep# #site_title#',
                    /* translators: default meta description template for WooCommerce category archives. */
                    'desc'  => __( 'Shop #term# at #site_title#. #term_description#', 'gatetouch-ai-seo' ),
                    'schema_type' => 'CollectionPage',
                ],
                'product_tag' => [
                    'title' => '#term# #sep# #site_title#',
                    /* translators: default meta description template for WooCommerce tag archives. */
                    'desc'  => __( 'Shop products tagged #term# on #site_title#. #term_description#', 'gatetouch-ai-seo' ),
                ],
            ],

            // Non-taxonomy archives and special pages.
            'archives' => [
                'author' => [
                    'title'   => '#author_name# #sep# #site_title#',
                    /* translators: default meta description template for author archives. */
                    'desc'    => __( 'Articles written by #author_name#. #author_bio#', 'gatetouch-ai-seo' ),
                    'noindex' => '',
                    'noindex_single_author' => '1',
                ],
                'date' => [
                    'title'   => '#archive_date# #sep# #site_title#',
                    'desc'    => '',
                    'noindex' => '1',
                ],
                'search' => [
                    /* translators: default title template for search result pages. */
                    'title'   => __( 'Search results for "#search_query#" #sep# #site_title#', 'gatetouch-ai-seo' ),
                    'desc'    => '',
                    'noindex' => '1',
                ],
                'notfound' => [
                    /* translators: default title template for 404 pages. */
                    'title'   => __( 'Page not found #sep# #site_title#', 'gatetouch-ai-seo' ),
                    'noindex' => '1',
                ],
            ],

            // Post type archives, keyed pt_{post_type}. Populated on demand.
            'post_type_archives' => [
                'default' => [
                    'title'   => '#post_type_plural# #sep# #site_title#',
                    'desc'    => '',
                    'noindex' => '',
                ],
                'pt_product' => [
                    'title' => '#title# #sep# #site_title#',
                    /* translators: default meta description template for the WooCommerce shop page. */
                    'desc'  => __( 'Shop online at #site_title#. Browse our full product range with fast shipping and secure checkout.', 'gatetouch-ai-seo' ),
                ],
            ],

            'woocommerce' => [
                'replace_woo_schema'   => '1',
                'review_schema'        => '1',
                'default_brand'        => '',
                'noindex_cart'         => '1',
                'noindex_filtered'     => '1',
                'remove_generator'     => '1',
            ],

            'advanced' => [
                'use_meta_keywords'   => '',
                'no_paged_canonical'  => '',
                'max_snippet'         => '-1',
                'max_image_preview'   => 'large',
                'max_video_preview'   => '-1',
                'noindex_paged'       => '',
                'strip_category_base' => '',
                'crawl_cleanup_rss'   => '',
            ],

            'image_seo' => [
                'redirect_attachments' => '1',
            ],
        ];
    }

    /**
     * Full settings, defaults merged in at every level.
     */
    public static function settings() {
        if ( null !== self::$settings ) {
            return self::$settings;
        }

        $saved    = get_option( self::OPTION, [] );
        $saved    = is_array( $saved ) ? $saved : [];
        $defaults = self::defaults();

        $merged = $defaults;
        foreach ( $saved as $group => $values ) {
            if ( ! is_array( $values ) ) {
                $merged[ $group ] = $values;
                continue;
            }
            foreach ( $values as $key => $value ) {
                $merged[ $group ][ $key ] = is_array( $value ) && isset( $defaults[ $group ][ $key ] ) && is_array( $defaults[ $group ][ $key ] )
                    ? array_merge( $defaults[ $group ][ $key ], $value )
                    : $value;
            }
        }

        self::$settings = $merged;
        return self::$settings;
    }

    /**
     * Read a config entry with the group's 'default' row merged underneath it.
     *
     * @param string $group  content_types|taxonomies|archives|post_type_archives
     * @param string $key    Post type / taxonomy / archive key.
     */
    public static function group( $group, $key ) {
        $settings = self::settings();
        $rows     = $settings[ $group ] ?? [];
        $base     = $rows['default'] ?? [];
        $row      = $rows[ $key ] ?? [];

        return array_merge( $base, is_array( $row ) ? $row : [] );
    }

    public static function advanced( $key, $fallback = '' ) {
        $settings = self::settings();
        return $settings['advanced'][ $key ] ?? $fallback;
    }

    public static function global_setting( $key, $fallback = '' ) {
        $settings = self::settings();
        return $settings['global'][ $key ] ?? $fallback;
    }

    /** Clear the memoised settings (used after saving in the admin). */
    public static function flush() {
        self::$settings = null;
        self::$context  = null;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Context detection
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Describe the page currently being rendered.
     *
     * @return array{type:string, key:string, object:mixed, object_id:int}
     */
    public static function context() {
        if ( null !== self::$context ) {
            return self::$context;
        }

        $ctx = [ 'type' => 'other', 'key' => '', 'object' => null, 'object_id' => 0 ];

        // Conditional tags are only meaningful once the main query has run.
        if ( is_admin() || ! did_action( 'wp' ) ) {
            return $ctx;
        }

        if ( is_404() ) {
            $ctx['type'] = 'notfound';
            $ctx['key']  = 'notfound';
        } elseif ( is_search() ) {
            $ctx['type'] = 'search';
            $ctx['key']  = 'search';
        } elseif ( is_front_page() ) {
            // A static front page is still a singular object we can override.
            $ctx['type']      = 'front_page';
            $ctx['key']       = 'front_page';
            $ctx['object_id'] = (int) get_queried_object_id();
            $ctx['object']    = $ctx['object_id'] ? get_post( $ctx['object_id'] ) : null;
        } elseif ( is_home() ) {
            $ctx['type']      = 'blog_home';
            $ctx['key']       = 'blog_home';
            $ctx['object_id'] = (int) get_option( 'page_for_posts' );
            $ctx['object']    = $ctx['object_id'] ? get_post( $ctx['object_id'] ) : null;
        } elseif ( is_singular() ) {
            $post             = get_post( get_queried_object_id() );
            $ctx['type']      = 'singular';
            $ctx['key']       = $post ? $post->post_type : 'post';
            $ctx['object']    = $post;
            $ctx['object_id'] = $post ? $post->ID : 0;
        } elseif ( self::is_shop_archive() ) {
            // WooCommerce shop page is a post type archive backed by a real page.
            $shop_id          = (int) wc_get_page_id( 'shop' );
            $ctx['type']      = 'post_type_archive';
            $ctx['key']       = 'pt_product';
            $ctx['object']    = $shop_id > 0 ? get_post( $shop_id ) : null;
            $ctx['object_id'] = max( 0, $shop_id );
        } elseif ( is_category() || is_tag() || is_tax() ) {
            $term             = get_queried_object();
            $ctx['type']      = 'term';
            $ctx['key']       = $term instanceof \WP_Term ? $term->taxonomy : 'category';
            $ctx['object']    = $term instanceof \WP_Term ? $term : null;
            $ctx['object_id'] = $term instanceof \WP_Term ? (int) $term->term_id : 0;
        } elseif ( is_author() ) {
            $user             = get_queried_object();
            $ctx['type']      = 'author';
            $ctx['key']       = 'author';
            $ctx['object']    = $user instanceof \WP_User ? $user : null;
            $ctx['object_id'] = $user instanceof \WP_User ? (int) $user->ID : 0;
        } elseif ( is_date() ) {
            $ctx['type'] = 'date';
            $ctx['key']  = 'date';
        } elseif ( is_post_type_archive() ) {
            $pt          = get_query_var( 'post_type' );
            $pt          = is_array( $pt ) ? reset( $pt ) : $pt;
            $ctx['type'] = 'post_type_archive';
            $ctx['key']  = 'pt_' . ( $pt ?: 'post' );
        }

        self::$context = $ctx;
        return self::$context;
    }

    private static function is_shop_archive() {
        return function_exists( 'is_shop' ) && function_exists( 'wc_get_page_id' ) && is_shop();
    }

    /**
     * Per-object SEO overrides for the current context, normalised to one shape.
     */
    public static function object_meta( array $ctx = null ) {
        $ctx = $ctx ?: self::context();

        switch ( $ctx['type'] ) {
            case 'singular':
            case 'front_page':
            case 'blog_home':
            case 'post_type_archive':
                if ( ! $ctx['object_id'] ) {
                    return [];
                }
                $meta = get_post_meta( $ctx['object_id'], GATETOUCH_META_KEY, true );
                return is_array( $meta ) ? $meta : [];

            case 'term':
                return GateTouch_Term_Meta::get( $ctx['object_id'] );

            case 'author':
                return GateTouch_User_Meta::get( $ctx['object_id'] );
        }

        return [];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Field resolution
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Config row that applies to the current context.
     */
    private static function config_for( array $ctx ) {
        switch ( $ctx['type'] ) {
            case 'singular':
                return self::group( 'content_types', $ctx['key'] );
            case 'term':
                return self::group( 'taxonomies', $ctx['key'] );
            case 'post_type_archive':
                return self::group( 'post_type_archives', $ctx['key'] );
            case 'author':
            case 'date':
            case 'search':
            case 'notfound':
                return self::group( 'archives', $ctx['key'] );
        }
        return [];
    }

    /**
     * Resolved SEO title for the current page (plain text, unescaped).
     */
    public static function title() {
        $ctx    = self::context();
        $meta   = self::object_meta( $ctx );
        $config = self::config_for( $ctx );

        $template = '';

        // 1. Per-object override.
        if ( ! empty( $meta['meta_title'] ) ) {
            $template = $meta['meta_title'];
        }

        // 2. Site template.
        if ( '' === $template ) {
            if ( 'front_page' === $ctx['type'] || 'blog_home' === $ctx['type'] ) {
                $template = self::global_setting( 'homepage_title', '#site_title# #sep# #tagline#' );
            } else {
                $template = $config['title'] ?? '';
            }
        }

        // 3. Last-resort default.
        if ( '' === $template ) {
            $template = '#title# #sep# #site_title#';
        }

        $title = GateTouch_Variables::parse( $template, self::variable_subject( $ctx ) );

        // Append pagination so paged archives are not duplicate titles.
        $paged = self::paged_suffix();
        if ( $paged ) {
            $title .= ' ' . GateTouch_Variables::separator() . ' ' . $paged;
        }

        if ( '1' === self::global_setting( 'capitalize_titles' ) ) {
            $title = self::title_case( $title );
        }

        /**
         * Filter the resolved SEO title.
         *
         * @param string $title Plain text title.
         * @param array  $ctx   Resolved page context.
         */
        return apply_filters( 'gatetouch_seo_title', trim( $title ), $ctx );
    }

    /**
     * Resolved meta description for the current page (plain text, unescaped).
     */
    public static function description() {
        $ctx    = self::context();
        $meta   = self::object_meta( $ctx );
        $config = self::config_for( $ctx );

        $template = '';

        if ( ! empty( $meta['meta_description'] ) ) {
            $template = $meta['meta_description'];
        }

        if ( '' === $template ) {
            $template = ( 'front_page' === $ctx['type'] || 'blog_home' === $ctx['type'] )
                ? self::global_setting( 'homepage_desc', '' )
                : ( $config['desc'] ?? '' );
        }

        $desc = GateTouch_Variables::parse( $template, self::variable_subject( $ctx ) );

        // Fall back to something meaningful rather than emitting nothing.
        if ( '' === $desc ) {
            $desc = self::fallback_description( $ctx );
        }

        $desc = GateTouch_Helpers::truncate( $desc, 160, '…' );

        /**
         * Filter the resolved meta description.
         *
         * @param string $desc Plain text description.
         * @param array  $ctx  Resolved page context.
         */
        return apply_filters( 'gatetouch_seo_description', trim( $desc ), $ctx );
    }

    private static function fallback_description( array $ctx ) {
        switch ( $ctx['type'] ) {
            case 'front_page':
            case 'blog_home':
                return wp_strip_all_tags( get_bloginfo( 'description' ) );

            case 'singular':
                if ( $ctx['object'] instanceof \WP_Post ) {
                    return GateTouch_Variables::parse( '#excerpt#', $ctx['object'] );
                }
                return '';

            case 'term':
                if ( $ctx['object'] instanceof \WP_Term && $ctx['object']->description ) {
                    return wp_strip_all_tags( $ctx['object']->description );
                }
                return '';

            case 'author':
                if ( $ctx['object'] instanceof \WP_User && $ctx['object']->description ) {
                    return wp_strip_all_tags( $ctx['object']->description );
                }
                return '';
        }

        return '';
    }

    /**
     * The object variables should resolve against for this context.
     */
    private static function variable_subject( array $ctx ) {
        if ( $ctx['object'] ) {
            return $ctx['object'];
        }
        return 0;
    }

    /**
     * "Page 2 of 9" suffix for paginated views, empty on page 1.
     */
    private static function paged_suffix() {
        global $wp_query;

        $current = max( (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
        if ( $current < 2 ) {
            return '';
        }

        $total = isset( $wp_query->max_num_pages ) ? max( 1, (int) $wp_query->max_num_pages ) : 1;

        /* translators: 1: current page number, 2: total number of pages */
        return sprintf( __( 'Page %1$d of %2$d', 'gatetouch-ai-seo' ), $current, $total );
    }

    private static function title_case( $title ) {
        $small = [ 'a', 'an', 'and', 'as', 'at', 'but', 'by', 'for', 'in', 'nor', 'of', 'on', 'or', 'the', 'to', 'up', 'with' ];
        $words = explode( ' ', $title );

        foreach ( $words as $i => $word ) {
            if ( $i > 0 && in_array( strtolower( $word ), $small, true ) ) {
                continue;
            }
            $words[ $i ] = function_exists( 'mb_convert_case' )
                ? mb_convert_case( $word, MB_CASE_TITLE, 'UTF-8' )
                : ucfirst( $word );
        }

        return implode( ' ', $words );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Robots
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Full robots directive string for the current page.
     */
    public static function robots() {
        $ctx    = self::context();
        $meta   = self::object_meta( $ctx );
        $config = self::config_for( $ctx );

        $noindex  = false;
        $nofollow = false;
        $extra    = [];

        // Site-wide privacy setting always wins.
        if ( ! get_option( 'blog_public' ) ) {
            return 'noindex, nofollow';
        }

        // Template-level directives.
        if ( '1' === ( $config['noindex'] ?? '' ) ) {
            $noindex = true;
        }
        if ( '1' === ( $config['nofollow'] ?? '' ) ) {
            $nofollow = true;
        }
        foreach ( [ 'noarchive', 'noimageindex', 'nosnippet' ] as $directive ) {
            if ( '1' === ( $config[ $directive ] ?? '' ) ) {
                $extra[] = $directive;
            }
        }

        // Context-specific rules.
        if ( 'front_page' === $ctx['type'] || 'blog_home' === $ctx['type'] ) {
            $noindex = '1' === self::global_setting( 'homepage_noindex' );
        }

        if ( 'term' === $ctx['type'] && $ctx['object'] instanceof \WP_Term ) {
            // Empty archives are thin content — keep them out of the index.
            if ( '1' === ( $config['noindex_empty'] ?? '' ) && 0 === (int) $ctx['object']->count ) {
                $noindex = true;
            }
        }

        if ( 'author' === $ctx['type'] && '1' === ( $config['noindex_single_author'] ?? '' ) && self::is_single_author_site() ) {
            // On a one-author blog the author archive duplicates the blog index.
            $noindex = true;
        }

        if ( '1' === self::advanced( 'noindex_paged' ) && is_paged() ) {
            $noindex = true;
        }

        // Cart, checkout and account pages must never be indexed.
        if ( '1' === GateTouch_WooCommerce::setting( 'noindex_cart', '1' ) && GateTouch_WooCommerce::is_noindex_page() ) {
            $noindex = true;
        }

        // Faceted/filtered archive URLs create near-infinite duplicate crawl paths.
        if ( '1' === GateTouch_WooCommerce::setting( 'noindex_filtered', '1' ) && self::is_filtered_archive() ) {
            $noindex = true;
        }

        // Per-object overrides beat everything above.
        if ( isset( $meta['noindex'] ) && '' !== $meta['noindex'] ) {
            $noindex = (bool) $meta['noindex'];
        }
        if ( isset( $meta['nofollow'] ) && '' !== $meta['nofollow'] ) {
            $nofollow = (bool) $meta['nofollow'];
        }
        foreach ( [ 'noarchive', 'noimageindex', 'nosnippet' ] as $directive ) {
            if ( ! empty( $meta[ $directive ] ) && ! in_array( $directive, $extra, true ) ) {
                $extra[] = $directive;
            }
        }

        $robots = [
            $noindex ? 'noindex' : 'index',
            $nofollow ? 'nofollow' : 'follow',
        ];

        $robots = array_merge( $robots, $extra );

        // Preview directives. These are what unlock large thumbnails in Discover
        // and full snippets in AI Overviews — only meaningful on indexable pages.
        if ( ! $noindex && ! in_array( 'nosnippet', $extra, true ) ) {
            $max_snippet = self::advanced( 'max_snippet', '-1' );
            $max_image   = self::advanced( 'max_image_preview', 'large' );
            $max_video   = self::advanced( 'max_video_preview', '-1' );

            if ( '' !== $max_snippet ) {
                $robots[] = 'max-snippet:' . $max_snippet;
            }
            if ( '' !== $max_image && 'none' !== $max_image ) {
                $robots[] = 'max-image-preview:' . $max_image;
            }
            if ( '' !== $max_video ) {
                $robots[] = 'max-video-preview:' . $max_video;
            }
        }

        /**
         * Filter the robots directives for the current page.
         *
         * @param array $robots Directive list.
         * @param array $ctx    Resolved page context.
         */
        $robots = apply_filters( 'gatetouch_seo_robots', $robots, $ctx );

        return implode( ', ', array_unique( array_filter( $robots ) ) );
    }

    /**
     * Whether the current archive is a layered-nav / attribute-filtered view.
     */
    private static function is_filtered_archive() {
        if ( ! is_archive() && ! self::is_shop_archive() ) {
            return false;
        }

        foreach ( array_keys( $_GET ) as $key ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only inspection of query shape.
            $key = sanitize_key( $key );
            if ( 0 === strpos( $key, 'filter_' ) || 0 === strpos( $key, 'query_type_' ) || in_array( $key, [ 'min_price', 'max_price', 'rating_filter', 'orderby' ], true ) ) {
                return true;
            }
        }

        return false;
    }

    private static function is_single_author_site() {
        $count = get_transient( 'gatetouch_author_count' );
        if ( false === $count ) {
            $count = count( get_users( [ 'who' => 'authors', 'fields' => 'ID', 'number' => 5 ] ) );
            set_transient( 'gatetouch_author_count', $count, DAY_IN_SECONDS );
        }
        return (int) $count <= 1;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Canonical
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Canonical URL for the current page.
     */
    public static function canonical() {
        $ctx  = self::context();
        $meta = self::object_meta( $ctx );

        if ( ! empty( $meta['canonical'] ) ) {
            return esc_url_raw( $meta['canonical'] );
        }

        $url = '';

        switch ( $ctx['type'] ) {
            case 'front_page':
                $url = home_url( '/' );
                break;

            case 'blog_home':
                $url = $ctx['object_id'] ? get_permalink( $ctx['object_id'] ) : home_url( '/' );
                break;

            case 'singular':
                $url = $ctx['object_id'] ? get_permalink( $ctx['object_id'] ) : '';
                break;

            case 'term':
                if ( $ctx['object'] instanceof \WP_Term ) {
                    $link = get_term_link( $ctx['object'] );
                    $url  = is_wp_error( $link ) ? '' : $link;
                }
                break;

            case 'author':
                $url = $ctx['object_id'] ? get_author_posts_url( $ctx['object_id'] ) : '';
                break;

            case 'post_type_archive':
                $pt  = substr( $ctx['key'], 3 );
                $url = get_post_type_archive_link( $pt );
                $url = $url ?: '';
                break;

            case 'date':
                $url = self::date_archive_url();
                break;
        }

        if ( ! $url ) {
            $url = self::current_url();
        }

        // Paginated views canonicalise to themselves unless the site opts out.
        $paged = max( (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
        if ( $paged > 1 && '1' !== self::advanced( 'no_paged_canonical' ) ) {
            $url = self::paged_url( $url, $paged, $ctx );
        }

        /**
         * Filter the canonical URL.
         *
         * @param string $url Canonical URL.
         * @param array  $ctx Resolved page context.
         */
        return apply_filters( 'gatetouch_seo_canonical', $url, $ctx );
    }

    private static function paged_url( $url, $paged, array $ctx ) {
        if ( 'singular' === $ctx['type'] && ! is_paged() ) {
            // Multi-page post (<!--nextpage-->) rather than an archive.
            return trailingslashit( $url ) . user_trailingslashit( $paged );
        }
        return trailingslashit( $url ) . user_trailingslashit( "page/{$paged}", 'paged' );
    }

    private static function date_archive_url() {
        if ( is_day() ) {
            return get_day_link( (int) get_query_var( 'year' ), (int) get_query_var( 'monthnum' ), (int) get_query_var( 'day' ) );
        }
        if ( is_month() ) {
            return get_month_link( (int) get_query_var( 'year' ), (int) get_query_var( 'monthnum' ) );
        }
        if ( is_year() ) {
            return get_year_link( (int) get_query_var( 'year' ) );
        }
        return '';
    }

    /**
     * Absolute URL of the request currently being served.
     */
    public static function current_url() {
        global $wp;

        if ( isset( $wp->request ) && '' !== $wp->request ) {
            return home_url( user_trailingslashit( $wp->request ) );
        }

        return home_url( '/' );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Social (Open Graph / Twitter)
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Resolved social payload for the current page.
     */
    public static function social() {
        $ctx    = self::context();
        $meta   = self::object_meta( $ctx );
        $config = self::config_for( $ctx );

        $title = ! empty( $meta['og_title'] )
            ? GateTouch_Variables::parse( $meta['og_title'], self::variable_subject( $ctx ) )
            : self::title();

        $desc = ! empty( $meta['og_description'] )
            ? GateTouch_Variables::parse( $meta['og_description'], self::variable_subject( $ctx ) )
            : self::description();

        return [
            'og_type'     => self::og_type( $ctx ),
            'title'       => $title,
            'description' => $desc,
            'url'         => self::canonical(),
            'image'       => self::social_image( $ctx, $meta, $config ),
            'tw_title'    => ! empty( $meta['twitter_title'] )
                ? GateTouch_Variables::parse( $meta['twitter_title'], self::variable_subject( $ctx ) )
                : $title,
            'tw_desc'     => ! empty( $meta['twitter_description'] )
                ? GateTouch_Variables::parse( $meta['twitter_description'], self::variable_subject( $ctx ) )
                : $desc,
            'tw_card'     => $meta['twitter_card'] ?? self::global_setting( 'twitter_card', 'summary_large_image' ),
        ];
    }

    private static function og_type( array $ctx ) {
        if ( 'singular' === $ctx['type'] ) {
            if ( $ctx['object'] instanceof \WP_Post ) {
                if ( 'product' === $ctx['object']->post_type ) {
                    return 'product';
                }
                if ( 'page' === $ctx['object']->post_type ) {
                    return 'website';
                }
            }
            return 'article';
        }

        if ( 'author' === $ctx['type'] ) {
            return 'profile';
        }

        return 'website';
    }

    /**
     * Social image, walking every sensible source before giving up.
     */
    private static function social_image( array $ctx, array $meta, array $config ) {
        // 1. Explicit per-object image.
        if ( ! empty( $meta['og_image'] ) ) {
            return $meta['og_image'];
        }

        // 2. Featured image of the post / term.
        if ( $ctx['object_id'] && in_array( $ctx['type'], [ 'singular', 'front_page', 'blog_home', 'post_type_archive' ], true ) ) {
            $thumb = get_the_post_thumbnail_url( $ctx['object_id'], 'full' );
            if ( $thumb ) {
                return $thumb;
            }

            // 3. First image found in the content.
            $post = get_post( $ctx['object_id'] );
            if ( $post && preg_match( '/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $post->post_content, $m ) ) {
                return $m[1];
            }
        }

        // 4. Term thumbnail (WooCommerce category images use this).
        if ( 'term' === $ctx['type'] && $ctx['object_id'] ) {
            $thumb_id = (int) get_term_meta( $ctx['object_id'], 'thumbnail_id', true );
            if ( $thumb_id ) {
                $url = wp_get_attachment_image_url( $thumb_id, 'full' );
                if ( $url ) {
                    return $url;
                }
            }
        }

        // 5. Author avatar.
        if ( 'author' === $ctx['type'] && $ctx['object_id'] ) {
            $avatar = get_avatar_url( $ctx['object_id'], [ 'size' => 512 ] );
            if ( $avatar ) {
                return $avatar;
            }
        }

        // 6. Per-template default, then the site-wide default.
        if ( ! empty( $config['og_image'] ) ) {
            return $config['og_image'];
        }

        $global = self::global_setting( 'og_default_image', '' );
        if ( $global ) {
            return $global;
        }

        // 7. Legacy social settings option.
        $social = get_option( 'gatetouch_social_settings', [] );
        return $social['facebook']['default_img'] ?? ( $social['default_og_image'] ?? '' );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Head output
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Print the complete meta block for the current page.
     */
    public static function output_head() {
        if ( is_feed() || is_embed() ) {
            return;
        }

        $ctx = self::context();

        echo "\n<!-- GT SEO/GEO/AEO " . esc_html( GATETOUCH_VERSION ) . ' -->' . "\n";

        // Robots.
        echo '<meta name="robots" content="' . esc_attr( self::robots() ) . '" />' . "\n";

        // Description.
        $desc = self::description();
        if ( $desc ) {
            echo '<meta name="description" content="' . esc_attr( $desc ) . '" />' . "\n";
        }

        // Keywords — off by default, some regional engines still read them.
        if ( '1' === self::advanced( 'use_meta_keywords' ) ) {
            $keywords = self::keywords( $ctx );
            if ( $keywords ) {
                echo '<meta name="keywords" content="' . esc_attr( $keywords ) . '" />' . "\n";
            }
        }

        // Canonical.
        $canonical = self::canonical();
        if ( $canonical && ! is_404() && ! is_search() ) {
            echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";
        }

        // Pagination relations.
        self::output_pagination_links();

        // Social.
        self::output_social_tags( $ctx );

        echo "<!-- /GT SEO/GEO/AEO -->\n";
    }

    /**
     * Comma-separated keywords: focus keyword, additional keywords, then terms.
     */
    public static function keywords( array $ctx = null ) {
        $ctx  = $ctx ?: self::context();
        $meta = self::object_meta( $ctx );

        $keywords = [];

        if ( ! empty( $meta['focus_keyword'] ) ) {
            $keywords[] = $meta['focus_keyword'];
        }
        if ( ! empty( $meta['additional_keywords'] ) ) {
            $extra = is_array( $meta['additional_keywords'] )
                ? $meta['additional_keywords']
                : explode( ',', $meta['additional_keywords'] );
            $keywords = array_merge( $keywords, $extra );
        }

        // Categories and tags are decent keyword signals for archives.
        if ( 'singular' === $ctx['type'] && $ctx['object'] instanceof \WP_Post ) {
            foreach ( [ 'post_tag', 'product_tag' ] as $tax ) {
                $terms = get_the_terms( $ctx['object'], $tax );
                if ( $terms && ! is_wp_error( $terms ) ) {
                    $keywords = array_merge( $keywords, wp_list_pluck( $terms, 'name' ) );
                }
            }
        } elseif ( 'term' === $ctx['type'] && $ctx['object'] instanceof \WP_Term ) {
            $keywords[] = $ctx['object']->name;
        }

        $keywords = array_filter( array_map( 'trim', $keywords ) );
        $keywords = array_slice( array_unique( $keywords ), 0, 12 );

        return implode( ', ', $keywords );
    }

    private static function output_pagination_links() {
        global $wp_query;

        if ( ! isset( $wp_query->max_num_pages ) || $wp_query->max_num_pages < 2 ) {
            return;
        }

        $paged = max( 1, (int) get_query_var( 'paged' ) );
        $base  = self::canonical();

        if ( $paged > 1 ) {
            $prev = $paged === 2 ? get_pagenum_link( 1 ) : get_pagenum_link( $paged - 1 );
            if ( $prev ) {
                echo '<link rel="prev" href="' . esc_url( $prev ) . '" />' . "\n";
            }
        }

        if ( $paged < (int) $wp_query->max_num_pages ) {
            $next = get_pagenum_link( $paged + 1 );
            if ( $next ) {
                echo '<link rel="next" href="' . esc_url( $next ) . '" />' . "\n";
            }
        }

        unset( $base );
    }

    /**
     * Width/height/MIME for an image, when it lives in the media library.
     *
     * @return array{width:int, height:int, type:string}|null
     */
    private static function image_dimensions( $url ) {
        $attachment_id = attachment_url_to_postid( $url );
        if ( ! $attachment_id ) {
            return null;
        }

        $data = wp_get_attachment_image_src( $attachment_id, 'full' );
        if ( ! $data || empty( $data[1] ) ) {
            return null;
        }

        return [
            'width'  => (int) $data[1],
            'height' => (int) $data[2],
            'type'   => (string) get_post_mime_type( $attachment_id ),
        ];
    }

    private static function output_social_tags( array $ctx ) {
        $social   = self::social();
        $settings = get_option( 'gatetouch_social_settings', [] );

        $og_enabled = ( $settings['facebook']['enabled'] ?? '1' ) === '1';
        $tw_enabled = ( $settings['twitter']['enabled'] ?? '1' ) === '1';

        if ( $og_enabled ) {
            echo '<meta property="og:locale" content="' . esc_attr( get_locale() ) . '" />' . "\n";
            echo '<meta property="og:type" content="' . esc_attr( $social['og_type'] ) . '" />' . "\n";
            echo '<meta property="og:title" content="' . esc_attr( $social['title'] ) . '" />' . "\n";
            if ( $social['description'] ) {
                echo '<meta property="og:description" content="' . esc_attr( $social['description'] ) . '" />' . "\n";
            }
            echo '<meta property="og:url" content="' . esc_url( $social['url'] ) . '" />' . "\n";
            echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '" />' . "\n";

            if ( $social['image'] ) {
                echo '<meta property="og:image" content="' . esc_url( $social['image'] ) . '" />' . "\n";
                echo '<meta property="og:image:alt" content="' . esc_attr( $social['title'] ) . '" />' . "\n";

                // Dimensions let Facebook and LinkedIn render the card on first
                // scrape instead of showing a blank placeholder.
                $dimensions = self::image_dimensions( $social['image'] );
                if ( $dimensions ) {
                    echo '<meta property="og:image:width" content="' . esc_attr( $dimensions['width'] ) . '" />' . "\n";
                    echo '<meta property="og:image:height" content="' . esc_attr( $dimensions['height'] ) . '" />' . "\n";
                    if ( $dimensions['type'] ) {
                        echo '<meta property="og:image:type" content="' . esc_attr( $dimensions['type'] ) . '" />' . "\n";
                    }
                }
            }

            // Article-specific metadata.
            if ( 'article' === $social['og_type'] && $ctx['object'] instanceof \WP_Post ) {
                $post = $ctx['object'];
                echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c', $post ) ) . '" />' . "\n";
                echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c', $post ) ) . '" />' . "\n";

                $primary = GateTouch_Variables::primary_term( $post, 'category' );
                if ( $primary ) {
                    echo '<meta property="article:section" content="' . esc_attr( $primary->name ) . '" />' . "\n";
                }

                $tags = get_the_terms( $post, 'post_tag' );
                if ( $tags && ! is_wp_error( $tags ) ) {
                    foreach ( array_slice( $tags, 0, 10 ) as $tag ) {
                        echo '<meta property="article:tag" content="' . esc_attr( $tag->name ) . '" />' . "\n";
                    }
                }
            }

            if ( 'profile' === $social['og_type'] && $ctx['object'] instanceof \WP_User ) {
                echo '<meta property="profile:first_name" content="' . esc_attr( $ctx['object']->first_name ) . '" />' . "\n";
                echo '<meta property="profile:last_name" content="' . esc_attr( $ctx['object']->last_name ) . '" />' . "\n";
            }

            $app_id = self::global_setting( 'facebook_app_id', $settings['facebook']['app_id'] ?? '' );
            if ( $app_id ) {
                echo '<meta property="fb:app_id" content="' . esc_attr( $app_id ) . '" />' . "\n";
            }
        }

        if ( $tw_enabled ) {
            echo '<meta name="twitter:card" content="' . esc_attr( $social['tw_card'] ) . '" />' . "\n";
            echo '<meta name="twitter:title" content="' . esc_attr( $social['tw_title'] ) . '" />' . "\n";
            if ( $social['tw_desc'] ) {
                echo '<meta name="twitter:description" content="' . esc_attr( $social['tw_desc'] ) . '" />' . "\n";
            }
            if ( $social['image'] ) {
                echo '<meta name="twitter:image" content="' . esc_url( $social['image'] ) . '" />' . "\n";
            }

            $handle = self::global_setting( 'twitter_site', $settings['twitter']['site_handle'] ?? '' );
            if ( $handle ) {
                echo '<meta name="twitter:site" content="@' . esc_attr( ltrim( $handle, '@' ) ) . '" />' . "\n";
            }
        }

        // WooCommerce product pricing tags — read by Facebook/Pinterest catalogues.
        if ( 'product' === ( $social['og_type'] ?? '' ) && $ctx['object'] instanceof \WP_Post ) {
            GateTouch_WooCommerce::output_product_og_tags( $ctx['object']->ID );
        }
    }
}
