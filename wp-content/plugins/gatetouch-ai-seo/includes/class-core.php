<?php
defined( 'ABSPATH' ) || exit;

/**
 * Boots GateTouch modules and manages frontend SEO output.
 */
class GateTouch_Core {

    /**
     * List of known conflicting SEO plugins
     */
    public static $conflicting_plugins = [
        'wordpress-seo/wp-seo.php'                     => 'Yoast SEO',
        'wordpress-seo-premium/wp-seo-premium.php'      => 'Yoast SEO Premium',
        'seo-by-rank-math/rank-math.php'                => 'Rank Math SEO',
        'all-in-one-seo-pack/all_in_one_seo_pack.php'  => 'All in One SEO',
        'seopress/seopress.php'                         => 'SEOPress',
        'seopress-pro/seopress-pro.php'                 => 'SEOPress Pro',
        'the-seo-framework/the-seo-framework.php'       => 'The SEO Framework',
        'squirrly-seo/squirrly.php'                     => 'Squirrly SEO',
        'smartcrawl-seo/wpmu-dev-seo.php'               => 'SmartCrawl SEO',
        'slim-seo/slim-seo.php'                         => 'Slim SEO',
        'autodescription/autodescription.php'           => 'The SEO Framework',
    ];

    private static $active_conflicts_cache = null;

    /**
     * Main boot method — hooked into plugins_loaded
     */
    public static function boot() {
        if ( ! self::requirements_met() ) return;

        // Runs only when the stored version differs from the shipped one.
        add_action( 'admin_init', [ __CLASS__, 'maybe_upgrade' ], 1 );

        $has_conflicts = self::has_conflicts();

        if ( $has_conflicts ) {
            add_action( 'admin_notices', [ __CLASS__, 'conflict_notice' ] );
        }

        // Multisite: provision new sites automatically when plugin is network-active
        if ( is_multisite() ) {
            add_action( 'wpmu_new_blog', [ __CLASS__, 'activate_new_blog' ] );
        }

        // Boot all modules
        if ( is_admin() ) {
            new GateTouch_Admin();
            new GateTouch_Meta_Box();
            new GateTouch_Bulk_Meta();
            new GateTouch_Admin_Columns();
            new GateTouch_Support_Panel();
        }

        // Per-object SEO editors. Term fields register on admin_init; user fields
        // hook profile screens — both are admin-only but cheap to instantiate.
        new GateTouch_Term_Meta();
        new GateTouch_User_Meta();
        new GateTouch_WooCommerce();

        new GateTouch_Sitemap();
        new GateTouch_Robots();
        new GateTouch_Llms();
        new GateTouch_Schema();
        new GateTouch_Breadcrumbs();
        new GateTouch_Social();
        new GateTouch_Redirects();
        new GateTouch_Security();
        new GateTouch_Crawl_Optimization();
        new GateTouch_Local_SEO();
        new GateTouch_Link_Assistant();
        new GateTouch_AEO();
        new GateTouch_Queue();
        new GateTouch_Schema_Engine();
        new GateTouch_Redirect_Engine();

        // Assets
        add_action( 'wp_enqueue_scripts', [ __CLASS__, 'enqueue_frontend_assets' ] );

        // Breadcrumb output. Standard placement is before post content; wp_body_open is optional for themes that support it.
        add_action( 'wp_body_open', [ __CLASS__, 'maybe_inject_breadcrumbs' ], 5 );
        add_filter( 'the_content', [ __CLASS__, 'maybe_prepend_breadcrumbs_to_content' ], 8 );

        if ( ! $has_conflicts ) {
            add_action( 'wp', [ __CLASS__, 'prepare_frontend_head' ], 0 );
        }

        // Front-end hooks
        add_action( 'wp_head', [ __CLASS__, 'output_webmaster_codes' ], 2 );
        add_action( 'wp_body_open', [ __CLASS__, 'output_body_scripts' ] );
        add_action( 'wp_footer', [ __CLASS__, 'output_footer_scripts' ] );
        if ( ! $has_conflicts ) {
            add_action( 'wp_head', [ __CLASS__, 'output_head_tags' ], 5 );
            add_action( 'wp_head', [ __CLASS__, 'output_schema' ],    99 );
        }

        if ( ! $has_conflicts ) {
            // Title filter
            add_filter( 'pre_get_document_title', [ __CLASS__, 'get_seo_title' ], 15 );
            add_filter( 'gatetouch_title_separator', function() {
                $sa = get_option( 'gatetouch_search_appearance', [] );
                return esc_html( $sa['global']['title_separator'] ?? '|' );
            } );
        }

        // RSS filtering
        add_filter( 'the_content_feed', [ __CLASS__, 'filter_rss_content' ] );
        add_filter( 'the_excerpt_rss',  [ __CLASS__, 'filter_rss_content' ] );

        // Redirections
        add_action( 'template_redirect', [ __CLASS__, 'redirect_attachment_pages' ] );

        // Auto-generate on first publish if enabled
        add_action( 'transition_post_status', [ __CLASS__, 'maybe_auto_generate' ], 10, 3 );

        // Crawl Cleanup
        add_action( 'init', [ __CLASS__, 'crawl_cleanup' ] );

        // Headless REST API Support
        add_action( 'rest_api_init', [ __CLASS__, 'register_rest_fields' ] );
    }

    /**
     * Headless WP Support: Expose SEO Data to REST API
     */
    public static function register_rest_fields() {
        $post_types = get_post_types( [ 'public' => true ] );
        foreach ( $post_types as $type ) {
            register_rest_field( $type, 'gatetouch_seo', [
                'get_callback'    => [ __CLASS__, 'get_rest_seo_data' ],
                'update_callback' => null,
                'schema'          => [
                    'description' => __( 'GT SEO/GEO/AEO metadata for this post.', 'gatetouch-ai-seo' ),
                    'type'        => 'object',
                    'context'     => [ 'view', 'edit' ],
                    'properties'  => [
                        'title'       => [ 'type' => 'string', 'description' => __( 'SEO title', 'gatetouch-ai-seo' ) ],
                        'description' => [ 'type' => 'string', 'description' => __( 'Meta description', 'gatetouch-ai-seo' ) ],
                        'robots'      => [ 'type' => 'string', 'description' => __( 'Robots directive', 'gatetouch-ai-seo' ) ],
                        'schema'      => [ 'type' => 'string', 'description' => __( 'JSON-LD schema (see wp_head output)', 'gatetouch-ai-seo' ) ],
                    ],
                ],
            ] );
        }
    }

    public static function get_rest_seo_data( $object, $field_name, $request ) {
        $post_id = $object['id'];
        $meta = get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [];
        
        // Calculate dynamic titles if not overridden
        $title = ! empty( $meta['seo_title'] ) ? $meta['seo_title'] : get_the_title($post_id) . ' ' . apply_filters('gatetouch_title_separator', '|') . ' ' . get_bloginfo('name');
        
        $robots = [];
        if ( ! empty( $meta['noindex'] ) ) $robots[] = 'noindex';
        if ( ! empty( $meta['nofollow'] ) ) $robots[] = 'nofollow';

        return [
            'title'       => $title,
            'description' => $meta['seo_description'] ?? '',
            'robots'      => empty($robots) ? 'index, follow' : implode(', ', $robots),
            'schema'      => '', // Schema is output via wp_head; not duplicated in REST
        ];
    }


    /**
     * Crawl Cleanup logic
     */
    public static function crawl_cleanup() {
        $sa = get_option( 'gatetouch_search_appearance', [] );
        if ( ( $sa['advanced']['crawl_cleanup_rss'] ?? '' ) === '1' ) {
            remove_action( 'wp_head', 'feed_links', 2 );
            remove_action( 'wp_head', 'feed_links_extra', 3 );
        }
    }

    /**
     * Keep GateTouch as the single SEO metadata owner on the frontend.
     */
    public static function prepare_frontend_head() {
        if ( is_admin() || is_feed() || wp_doing_ajax() ) {
            return;
        }

        if ( function_exists( 'wp_is_json_request' ) && wp_is_json_request() ) {
            return;
        }

        remove_action( 'wp_head', 'rel_canonical' );
        remove_action( 'wp_head', 'wp_robots', 1 );

        // The WordPress AI meta-description experiment does not detect GateTouch yet.
        add_filter( 'wpai_meta_description', [ __CLASS__, 'suppress_external_meta_description' ], PHP_INT_MAX );
    }

    public static function suppress_external_meta_description( $meta_description = '' ) {
        return '';
    }


    /**
     * Redirect attachment pages to the actual image file
     */
    public static function redirect_attachment_pages() {
        if ( ! is_attachment() ) return;
        
        $sa = get_option( 'gatetouch_search_appearance', [] );
        if ( ( $sa['image_seo']['redirect_attachments'] ?? '' ) !== '1' ) return;

        $url = wp_get_attachment_url( get_the_ID() );
        if ( $url ) {
            wp_safe_redirect( $url, 301 );
            exit;
        }
    }

    /**
     * Filter RSS content
     */
    public static function filter_rss_content( $content ) {
        if ( ! is_feed() ) return $content;
        $opts = get_option( 'gatetouch_rss_settings', [] );
        if ( empty( $opts ) ) return $content;

        global $post;
        $before = ! empty( $opts['before_content'] ) ? GateTouch_Variables::parse( $opts['before_content'], $post->ID ?? 0 ) : '';
        $after  = ! empty( $opts['after_content'] )  ? GateTouch_Variables::parse( $opts['after_content'],  $post->ID ?? 0 ) : '';

        // Add some basic HTML for formatting if not already present
        if ( $before ) $before = '<div class="gatetouch-rss-before">' . wpautop( $before ) . '</div>';
        if ( $after )  $after  = '<div class="gatetouch-rss-after">'  . wpautop( $after )  . '</div>';

        return $before . $content . $after;
    }

    /**
     * PHP & WP version requirements
     */
    private static function requirements_met() {
        global $wp_version;

        if ( version_compare( PHP_VERSION, GATETOUCH_MIN_PHP, '<' ) ) {
            add_action( 'admin_notices', function() {
                $msg = sprintf(
                    /* translators: 1: minimum PHP version, 2: current PHP version */
                    esc_html__( 'GT SEO/GEO/AEO requires PHP %1$s or higher. Your server is running PHP %2$s.', 'gatetouch-ai-seo' ),
                    esc_html( GATETOUCH_MIN_PHP ),
                    esc_html( PHP_VERSION )
                );
                echo '<div class="notice notice-error"><p><strong>GT SEO/GEO/AEO</strong> — ' . $msg . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            } );
            return false;
        }

        if ( version_compare( $wp_version, GATETOUCH_MIN_WP, '<' ) ) {
            add_action( 'admin_notices', function() {
                global $wp_version;
                $msg = sprintf(
                    /* translators: 1: minimum WordPress version, 2: current WordPress version */
                    esc_html__( 'GT SEO/GEO/AEO requires WordPress %1$s or higher. You are running %2$s.', 'gatetouch-ai-seo' ),
                    esc_html( GATETOUCH_MIN_WP ),
                    esc_html( $wp_version )
                );
                echo '<div class="notice notice-error"><p><strong>GT SEO/GEO/AEO</strong> — ' . $msg . '</p></div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            } );
            return false;
        }

        return true;
    }

    /**
     * Check for conflicting plugins
     */
    public static function has_conflicts() {
        return ! empty( self::get_active_conflicts() );
    }

    public static function get_active_conflicts() {
        if ( null !== self::$active_conflicts_cache ) {
            return self::$active_conflicts_cache;
        }

        self::load_plugin_functions();
        $active = [];

        $active_plugins = self::get_active_plugin_files();
        $installed      = function_exists( 'get_plugins' ) ? get_plugins() : [];

        foreach ( $active_plugins as $plugin_file ) {
            if ( isset( self::$conflicting_plugins[ $plugin_file ] ) ) {
                $active[ self::$conflicting_plugins[ $plugin_file ] ] = true;
                continue;
            }

            $detected = self::detect_conflicting_plugin( $plugin_file, $installed[ $plugin_file ] ?? [] );
            if ( $detected ) {
                $active[ $detected ] = true;
            }
        }

        self::$active_conflicts_cache = array_keys( $active );
        return self::$active_conflicts_cache;
    }

    private static function load_plugin_functions() {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
    }

    private static function get_active_plugin_files() {
        $active = (array) get_option( 'active_plugins', [] );

        if ( is_multisite() ) {
            $network_active = array_keys( (array) get_site_option( 'active_sitewide_plugins', [] ) );
            $active         = array_merge( $active, $network_active );
        }

        return array_values( array_unique( $active ) );
    }

    private static function detect_conflicting_plugin( $plugin_file, $plugin_data ) {
        $file        = strtolower( (string) $plugin_file );
        $name        = strtolower( (string) ( $plugin_data['Name'] ?? '' ) );
        $text_domain = strtolower( (string) ( $plugin_data['TextDomain'] ?? '' ) );

        if ( false !== strpos( $file, GATETOUCH_SLUG . '/' ) ) {
            return '';
        }

        if ( 'wp-seo.php' === basename( $file ) || 'wordpress-seo' === $text_domain || false !== strpos( $name, 'yoast seo' ) ) {
            return 'Yoast SEO';
        }

        if ( false !== strpos( $file, 'seo-by-rank-math/' ) || 'rank-math' === $text_domain || false !== strpos( $name, 'rank math' ) ) {
            return 'Rank Math SEO';
        }

        if ( false !== strpos( $file, 'all-in-one-seo-pack/' ) || false !== strpos( $file, 'all-in-one-seo-pack-pro/' ) || 'all-in-one-seo-pack' === $text_domain || false !== strpos( $name, 'all in one seo' ) ) {
            return 'All in One SEO';
        }

        if ( false !== strpos( $file, 'seopress' ) || false !== strpos( $name, 'seopress' ) ) {
            return 'SEOPress';
        }

        if ( false !== strpos( $file, 'the-seo-framework/' ) || 'autodescription' === $text_domain || false !== strpos( $name, 'the seo framework' ) ) {
            return 'The SEO Framework';
        }

        if ( false !== strpos( $file, 'squirrly-seo/' ) || false !== strpos( $name, 'squirrly' ) ) {
            return 'Squirrly SEO';
        }

        if ( false !== strpos( $file, 'smartcrawl-seo/' ) || false !== strpos( $name, 'smartcrawl' ) ) {
            return 'SmartCrawl SEO';
        }

        if ( false !== strpos( $file, 'slim-seo/' ) || false !== strpos( $name, 'slim seo' ) ) {
            return 'Slim SEO';
        }

        return '';
    }

    public static function conflict_notice() {
        $conflicts = self::get_active_conflicts();
        if ( empty( $conflicts ) ) return;
        $names = implode( ', ', array_map( 'esc_html', $conflicts ) );
        echo '<div class="notice notice-warning is-dismissible" style="border-left-color: #f59e0b; border-left-width: 4px;">';
        echo '<p style="padding: 10px 0;">';
        echo '<span style="font-weight: 800; color: #f59e0b; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 5px;">⚠️ ' . esc_html__( 'SEO Conflict Detected', 'gatetouch-ai-seo' ) . '</span><br>';
        echo wp_kses(
            sprintf(
            /* translators: %s: comma-separated list of conflicting plugin names */
                esc_html__( 'GT SEO/GEO/AEO has detected that %s is also active.', 'gatetouch-ai-seo' ),
                '<strong>' . $names . '</strong>'
            ),
            [ 'strong' => [] ]
        );
        echo ' ' . esc_html__( 'To avoid duplicate meta tags, this plugin stays silent while another SEO plugin is active — so nothing you configure here is being output yet.', 'gatetouch-ai-seo' ) . ' ';

        // Once the data is across, the remaining step is deactivating the old
        // plugin — so stop telling people to import something they already did.
        $pending = class_exists( 'GateTouch_Migration_Engine' ) ? GateTouch_Migration_Engine::pending_sources() : [];

        if ( empty( $pending ) ) {
            echo esc_html__( 'Your data has already been imported. Deactivate the other plugin when you are ready and this plugin takes over immediately.', 'gatetouch-ai-seo' );
            echo '</p><p style="padding-bottom:10px;">';
            echo '<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '" class="button button-primary" style="margin-right:8px;">' . esc_html__( 'Go to Plugins', 'gatetouch-ai-seo' ) . '</a>';
            echo '<a href="' . esc_url( admin_url( 'admin.php?page=gatetouch-migrate' ) ) . '" class="button">' . esc_html__( 'Review import', 'gatetouch-ai-seo' ) . '</a>';
        } else {
            echo esc_html__( 'Import your data first, then deactivate the other plugin.', 'gatetouch-ai-seo' );
            echo '</p><p style="padding-bottom:10px;">';
            echo '<a href="' . esc_url( admin_url( 'admin.php?page=gatetouch-migrate' ) ) . '" class="button button-primary" style="margin-right:8px;">' . esc_html__( 'Import my SEO data →', 'gatetouch-ai-seo' ) . '</a>';
            echo '<a href="' . esc_url( admin_url( 'admin.php?page=gatetouch-tools' ) ) . '" class="button">' . esc_html__( 'Conflict resolver', 'gatetouch-ai-seo' ) . '</a>';
        }

        echo '</p></div>';
    }

    public static function enqueue_frontend_assets() {
        wp_enqueue_style(
            'gatetouch-frontend',
            GATETOUCH_URL . 'assets/css/frontend.css',
            [],
            GATETOUCH_VERSION
        );
    }

    /**
     * SEO title for the current page.
     *
     * Resolution lives in GateTouch_Search_Appearance so every page type — posts,
     * terms, authors, dates, archives, search and 404 — follows one code path.
     * Escaped here because WordPress prints the result of pre_get_document_title
     * into <title> without escaping it itself.
     */
    public static function get_seo_title( $default_title = '' ) {
        $title = GateTouch_Search_Appearance::title();

        if ( '' === $title ) {
            return $default_title;
        }

        return esc_html( $title );
    }

    /**
     * Meta description for the current page (plain text, unescaped).
     */
    public static function get_seo_description() {
        return GateTouch_Search_Appearance::description();
    }

    /**
     * Filter the document title
     */
    public static function filter_title( $title ) {
        if ( ! is_singular() ) return $title;
        global $post;
        if ( ! $post ) return $title;
        $custom = GateTouch_Helpers::get_meta( $post->ID, 'meta_title' );
        if ( ! $custom ) return $title;

        // Parse variables like #site_title#, #sep#, etc.
        return esc_html( GateTouch_Variables::parse( $custom, $post->ID ) );
    }

    /**
     * Output verification and tracking codes in <head>
     */
    public static function output_webmaster_codes() {
        $opts = get_option( 'gatetouch_integrations', [] );
        if ( empty( $opts ) ) return;

        // 1. Google Search Console
        if ( ! empty( $opts['gsc_code'] ) ) {
            $code = trim( $opts['gsc_code'] );
            if ( strpos( $code, '<meta' ) !== false ) {
                // User pasted a full <meta> tag — allow only safe meta attributes
                echo wp_kses( $code, [ 'meta' => [ 'name' => [], 'content' => [] ] ] ) . "\n";
            } else {
                echo '<meta name="google-site-verification" content="' . esc_attr( $code ) . '">' . "\n";
            }
        }

        // 2. Bing Webmaster
        if ( ! empty( $opts['bing_code'] ) ) {
            echo '<meta name="msvalidate.01" content="' . esc_attr( $opts['bing_code'] ) . '">' . "\n";
        }

        // 3. Pinterest Verification
        if ( ! empty( $opts['pinterest_code'] ) ) {
            echo '<meta name="p:domain_verify" content="' . esc_attr( $opts['pinterest_code'] ) . '">' . "\n";
        }

        // 4. Yandex
        if ( ! empty( $opts['yandex_code'] ) ) {
            echo '<meta name="yandex-verification" content="' . esc_attr( $opts['yandex_code'] ) . '">' . "\n";
        }

        // 5. Baidu
        if ( ! empty( $opts['baidu_code'] ) ) {
            echo '<meta name="baidu-site-verification" content="' . esc_attr( $opts['baidu_code'] ) . '">' . "\n";
        }

        // 6. Facebook Domain Verification
        if ( ! empty( $opts['facebook_verify'] ) ) {
            echo '<meta name="facebook-domain-verification" content="' . esc_attr( $opts['facebook_verify'] ) . '">' . "\n";
        }

        // 7. Google Analytics (GA4)
        if ( ! empty( $opts['ga4_id'] ) ) {
            $id = sanitize_text_field( $opts['ga4_id'] );
            echo "<!-- Google Analytics (GT SEO/GEO/AEO) -->\n";
            wp_enqueue_script( 'gatetouch-ga4', add_query_arg( 'id', rawurlencode( $id ), 'https://www.googletagmanager.com/gtag/js' ), [], GATETOUCH_VERSION, [ 'strategy' => 'async' ] );
            wp_print_inline_script_tag( 'window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} gtag("js", new Date()); gtag("config", ' . wp_json_encode( $id ) . ');' );
        }

        // 8. Google Tag Manager
        if ( ! empty( $opts['gtm_id'] ) ) {
            $id = sanitize_text_field( $opts['gtm_id'] );
            echo "<!-- Google Tag Manager (GT SEO/GEO/AEO) -->\n";
            wp_print_inline_script_tag( '(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({"gtm.start":new Date().getTime(),event:"gtm.js"});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!="dataLayer"?"&l="+l:"";j.async=true;j.src="https://www.googletagmanager.com/gtm.js?id="+i+dl;f.parentNode.insertBefore(j,f);})(window,document,"script","dataLayer",' . wp_json_encode( $id ) . ');' );
        }

        // 9. Meta Pixel
        if ( ! empty( $opts['meta_pixel'] ) ) {
            $id = sanitize_text_field( $opts['meta_pixel'] );
            echo "<!-- Meta Pixel (GT SEO/GEO/AEO) -->\n";
            wp_print_inline_script_tag( '!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version="2.0";n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,"script","https://connect.facebook.net/en_US/fbevents.js");fbq("init", ' . wp_json_encode( $id ) . ');fbq("track", "PageView");' );
        }

        // 10. Bing UET
        if ( ! empty( $opts['bing_uet_id'] ) ) {
            $id = sanitize_text_field( $opts['bing_uet_id'] );
            echo "<!-- Bing UET (GT SEO/GEO/AEO) -->\n";
            wp_print_inline_script_tag( '(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:' . wp_json_encode( $id ) . '};o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&"loaded"!==s&&"complete"!==s||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,"script","//bat.bing.com/arm/tag.js","uetq");' );
        }

        // 11. Microsoft Clarity
        if ( ! empty( $opts['clarity_id'] ) ) {
            $id = sanitize_text_field( $opts['clarity_id'] );
            echo "<!-- Microsoft Clarity (GT SEO/GEO/AEO) -->\n";
            wp_print_inline_script_tag( '(function(c,l,a,r,i,t,y){c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);})(window, document, "clarity", "script", ' . wp_json_encode( $id ) . ');' );
        }

        // 12. LinkedIn Insight
        if ( ! empty( $opts['linkedin_id'] ) ) {
            $id = sanitize_text_field( $opts['linkedin_id'] );
            echo "<!-- LinkedIn Insight (GT SEO/GEO/AEO) -->\n";
            wp_print_inline_script_tag( '_linkedin_partner_id = ' . wp_json_encode( $id ) . '; window._linkedin_data_partner_ids = window._linkedin_data_partner_ids || []; window._linkedin_data_partner_ids.push(_linkedin_partner_id); (function(l) { if (!l.search) { return; } var b = document.getElementsByTagName("script")[0]; var s = document.createElement("script"); s.type = "text/javascript"; s.async = true; s.src = "https://snap.licdn.com/li.lms-analytics/insight.min.js"; b.parentNode.insertBefore(s, b); })(window.lintrk);' );
        }

        // 13. TikTok Pixel
        if ( ! empty( $opts['tiktok_id'] ) ) {
            $id = sanitize_text_field( $opts['tiktok_id'] );
            echo "<!-- TikTok Pixel (GT SEO/GEO/AEO) -->\n";
            wp_print_inline_script_tag( '!function (w, d, t) { w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)}; ttq.load(' . wp_json_encode( $id ) . '); ttq.page(); }(window, document, "ttq");' );
        }

        // 14. Custom header markup.
        if ( ! empty( $opts['header_scripts'] ) ) {
            echo wp_kses( wp_unslash( $opts['header_scripts'] ), self::kses_allowed_scripts() ) . "\n";
        }
    }

    public static function output_body_scripts() {
        $opts = get_option( 'gatetouch_integrations', [] );
        if ( ! empty( $opts['body_scripts'] ) ) {
            echo wp_kses( wp_unslash( $opts['body_scripts'] ), self::kses_allowed_scripts() ) . "\n";
        }
    }

    public static function output_footer_scripts() {
        $opts = get_option( 'gatetouch_integrations', [] );
        if ( ! empty( $opts['footer_scripts'] ) ) {
            echo wp_kses( wp_unslash( $opts['footer_scripts'] ), self::kses_allowed_scripts() ) . "\n";
        }
    }

    /**
     * Allowed HTML tags for custom header/body/footer script fields.
     * Extends wp_kses_post to permit <script> and <noscript> tags, which
     * are intentionally allowed here for user-configured tracking pixels
     * (manage_options capability required to save these values).
     */
    private static function kses_allowed_scripts() {
        $allowed = wp_kses_allowed_html( 'post' );
        $allowed['script'] = [
            'type'    => true,
            'src'     => true,
            'async'   => true,
            'defer'   => true,
            'id'      => true,
            'charset' => true,
        ];
        $allowed['noscript'] = [
            'id'    => true,
            'class' => true,
        ];
        $allowed['style'] = [
            'type'  => true,
            'id'    => true,
            'class' => true,
        ];
        return $allowed;
    }

    /**
     * Output all SEO meta in <head>.
     *
     * Delegates to GateTouch_Search_Appearance, which resolves the title,
     * description, robots, canonical and social tags for every page type.
     */
    public static function output_head_tags() {
        if ( self::has_conflicts() ) {
            return;
        }

        GateTouch_Search_Appearance::output_head();
    }



    /**
     * Output JSON-LD Schema in <head>
     */
    public static function output_schema() {
        // Handled by GateTouch_Schema_Engine
    }

    /**
     * Auto-generate meta on first publish
     */
    public static function maybe_auto_generate( $new_status, $old_status, \WP_Post $post ) {
        if ( ! get_option( 'gatetouch_auto_generate' ) ) return;
        if ( ! GateTouch_AI_Engine::is_api_operational() ) return;
        if ( $new_status !== 'publish' || $old_status === 'publish' ) return;
        if ( ! in_array( $post->post_type, [ 'post', 'page' ] ) ) return;

        $existing_meta = get_post_meta( $post->ID, GATETOUCH_META_KEY, true );
        if ( ! empty( $existing_meta['meta_title'] ) ) return; // Already has meta

        // Schedule generation in background (avoid slowing publish)
        wp_schedule_single_event( time() + 5, 'gatetouch_auto_generate_single', [ $post->ID ] );
    }

    /**
     * Activation — handles both single-site and multisite network activation.
     */
    public static function activate() {
        if ( is_multisite() && is_network_admin() ) {
            // Network activation: set up each existing site
            $sites = get_sites( [ 'fields' => 'ids', 'number' => 0 ] );
            foreach ( $sites as $blog_id ) {
                switch_to_blog( $blog_id );
                self::activate_site();
                restore_current_blog();
            }
            // Hook new sites created after network activation
            add_action( 'wpmu_new_blog', [ __CLASS__, 'activate_new_blog' ] );
        } else {
            self::activate_site();

            // One-shot flag consumed by GateTouch_Admin::maybe_redirect_to_setup().
            // Deliberately not set for network activation, where redirecting the
            // network admin into a single site's wizard would be wrong.
            if ( ! get_option( 'gatetouch_setup_completed' ) ) {
                update_option( 'gatetouch_activation_redirect', '1' );
            }
        }
    }

    /**
     * Run per-site activation tasks (options, tables, crons, rewrites).
     */
    public static function activate_site() {
        add_option( 'gatetouch_version',   GATETOUCH_VERSION );
        add_option( 'gatetouch_activated', time() );
        add_option( 'gatetouch_ai_model',  'gpt-4o' );

        // Apply smart defaults
        self::apply_smart_defaults();

        // Create redirects and 404 tables
        GateTouch_Redirects::create_table();
        GateTouch_Redirect_Engine::create_tables();

        // Register sitemaps, robots & llms rewrite rules and flush
        GateTouch_Sitemap::register_rewrites();
        GateTouch_Robots::register_rewrites();
        GateTouch_Llms::register_rewrites();
        flush_rewrite_rules();
    }

    /**
     * Version-keyed upgrade routine.
     *
     * gatetouch_version was written at activation but never read, so the plugin
     * had no way to repair data or introduce defaults on update. This runs once
     * per version change and is a no-op otherwise.
     */
    public static function maybe_upgrade() {
        $stored = (string) get_option( 'gatetouch_version', '' );

        if ( $stored === GATETOUCH_VERSION ) {
            return;
        }

        // 1.4.1 — brief-generated drafts stored additional_keywords as an array
        // where the rest of the plugin expects a comma-separated string. The
        // post edit screen fatals on it, so repair any post already written.
        if ( '' !== $stored && version_compare( $stored, '1.4.1', '<' ) ) {
            self::repair_additional_keywords();
        }

        update_option( 'gatetouch_version', GATETOUCH_VERSION );
    }

    /**
     * Convert any array-valued additional_keywords in post meta to the
     * comma-separated string every other code path expects.
     */
    private static function repair_additional_keywords() {
        global $wpdb;

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value LIKE %s",
                GATETOUCH_META_KEY,
                '%additional_keywords%'
            )
        );

        foreach ( (array) $rows as $row ) {
            $meta = maybe_unserialize( $row->meta_value );

            if ( ! is_array( $meta ) || ! isset( $meta['additional_keywords'] ) || ! is_array( $meta['additional_keywords'] ) ) {
                continue;
            }

            $meta['additional_keywords'] = implode( ', ', array_filter( array_map( 'trim', array_map( 'strval', $meta['additional_keywords'] ) ) ) );
            update_post_meta( (int) $row->post_id, GATETOUCH_META_KEY, $meta );
        }
    }

    /**
     * Automatically set up GateTouch when a new site is added to the network.
     */
    public static function activate_new_blog( $blog_id ) {
        switch_to_blog( $blog_id );
        self::activate_site();
        restore_current_blog();
    }

    /**
     * Smart Default Configuration Engine
     * Automatically enables important SEO, AEO, GEO, AI SEO, crawl optimization, and security settings.
     */
    public static function apply_smart_defaults() {
        // 1. General SEO Defaults
        add_option( 'gatetouch_title_separator', '|' );
        
        // 2. Security Hardening (Enable all by default)
        add_option( 'gatetouch_security_settings', [
            'remove_wp_version'   => '1',
            'remove_rsd_link'     => '1',
            'remove_wlw_manifest' => '1',
            'disable_xmlrpc'      => '1',
            'security_headers'    => '1',
        ]);

        // 3. Technical SEO & Crawl Optimization
        add_option( 'gatetouch_general_settings', [
            'disable_emojis'     => '1',
            'disable_embeds'     => '1',
            'clean_head'         => '1',
            'remove_shortlinks'  => '1',
            'remove_rest_links'  => '1',
            'remove_unnecessary_feeds' => '1',
        ]);
        add_option( 'gatetouch_crawl_optimization_settings', GateTouch_Crawl_Optimization::defaults() );

        // 4. AI SEO + AEO + GEO Defaults
        add_option( 'gatetouch_auto_generate', '1' ); // AI Metadata Generation

        // Structured data. Keys here must match GateTouch_Schema_Engine::settings(),
        // which is what actually renders the graph — 'auto_schema' used to be
        // seeded here but was read from a different option entirely, so it did
        // nothing. The real switch is gatetouch_auto_schema, set below.
        add_option( 'gatetouch_schema_settings', [
            'enabled'            => '1',
            'org_type'           => 'Organization',
            'org_name'           => get_bloginfo( 'name' ),
            'website_schema'     => '1',
            'breadcrumb_schema'  => '1',
            'author_schema'      => '1',
            'faq_automation'     => '1',
            'item_list_schema'   => '1',
            'speakable_schema'   => '1',
            'nlp_optimization'   => '1',
            'entity_mapping'     => '1',
        ]);
        add_option( 'gatetouch_auto_schema', 'yes' );

        // 5. Automated Files
        add_option( 'gatetouch_sitemap_settings', [ 'enabled' => 'yes' ] );
        add_option( 'gatetouch_llms_settings',    [ 'enabled' => 'yes' ] );

        // Robots defaults, including the AI crawler policy. Citation crawlers are
        // allowed so the site can appear in AI answers; only Bytespider — which
        // has a poor rate-limit record — is blocked out of the box. See
        // GateTouch_Robots::ai_bots() for the full rationale.
        $robots_defaults = [ 'enabled' => 'yes', 'optimize' => 'yes' ];
        if ( class_exists( 'GateTouch_Robots' ) ) {
            foreach ( GateTouch_Robots::ai_bot_defaults() as $opt_key => $bot ) {
                $robots_defaults[ $opt_key ] = $bot['default'];
            }
        }
        add_option( 'gatetouch_robots_settings', $robots_defaults );

        // 6. Breadcrumbs
        add_option( 'gatetouch_breadcrumb_settings', [
            'separator'  => '›',
            'home_label' => 'Home',
            'enabled'    => '1',
            'placement'  => 'content',
        ]);

        // 7. Social Meta
        add_option( 'gatetouch_social_settings', [
            'facebook_enabled' => '1',
            'twitter_enabled'  => '1',
            'twitter_card'     => 'summary_large_image',
        ]);
    }

    /**
     * Breadcrumb Injection Helper
     */
    public static function maybe_inject_breadcrumbs() {
        if ( ! self::should_output_breadcrumbs( 'body_open' ) ) {
            return;
        }

        echo wp_kses_post( self::render_breadcrumbs_for_current_post() );
    }

    public static function maybe_prepend_breadcrumbs_to_content( $content ) {
        if ( ! self::should_output_breadcrumbs( 'content' ) ) {
            return $content;
        }

        if ( ! in_the_loop() || ! is_main_query() || has_shortcode( $content, 'gatetouch_breadcrumbs' ) ) {
            return $content;
        }

        return self::render_breadcrumbs_for_current_post() . $content;
    }

    private static function should_output_breadcrumbs( $placement ) {
        if ( ! class_exists( 'GateTouch_Breadcrumbs' ) || ! GateTouch_Breadcrumbs::is_enabled() ) {
            return false;
        }

        if ( ! is_singular() || is_front_page() ) {
            return false;
        }

        $settings = GateTouch_Breadcrumbs::settings();
        if ( ( $settings['placement'] ?? 'content' ) !== $placement ) {
            return false;
        }

        $post_id = get_queried_object_id() ?: get_the_ID();
        return GateTouch_Breadcrumbs::is_allowed_for_post( $post_id );
    }

    private static function render_breadcrumbs_for_current_post() {
        $bc = new GateTouch_Breadcrumbs();
        $markup = $bc->render();

        if ( $markup === '' ) {
            return '';
        }

        return '<div class="gatetouch-bc-container">' . $markup . '</div>';
    }

    /**
     * Deactivation
     */
    public static function deactivate() {
        flush_rewrite_rules();
        wp_clear_scheduled_hook( 'gatetouch_scheduled_sitemap_ping' );
        wp_clear_scheduled_hook( 'gatetouch_auto_generate_single' );
    }
}
