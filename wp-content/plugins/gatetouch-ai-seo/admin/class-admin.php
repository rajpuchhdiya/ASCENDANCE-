<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- Admin redirects/read-only screen state use sanitized GET parameters and do not change settings.
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin exports, transient cleanup, media stats, and dashboard aggregates require bounded database operations.
// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Media-library filters intentionally query attachment alt-text metadata for admin optimization views.

/**
 * Registers GateTouch admin screens, settings, assets, and AJAX handlers.
 */
class GateTouch_Admin {

    public function __construct() {
        add_action( 'admin_menu',            [ $this, 'register_menus' ] );
        add_action( 'admin_init',            [ $this, 'maybe_redirect_to_setup' ] );
        add_action( 'admin_init',            [ $this, 'maybe_handle_legacy_redirects' ] );
        add_action( 'admin_init',            [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_global' ] );
        add_action( 'admin_notices',         [ $this, 'show_whats_new' ] );
        add_filter( 'plugin_action_links_' . plugin_basename( GATETOUCH_FILE ), [ $this, 'plugin_links' ] );
        add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
        add_action( 'wp_ajax_gatetouch_ping_now', [ $this, 'ajax_ping' ] );
        add_action( 'wp_ajax_gatetouch_dismiss_notice',      [ $this, 'ajax_dismiss_notice' ] );
        add_action( 'wp_ajax_gatetouch_generate_homepage_meta', [ $this, 'ajax_homepage_meta' ] );
        add_action( 'wp_ajax_gatetouch_export_meta', [ $this, 'ajax_export' ] );
        add_action( 'wp_ajax_gatetouch_import_meta', [ $this, 'ajax_import' ] );
        add_action( 'wp_ajax_gatetouch_flush_rewrites', [ $this, 'ajax_flush_rewrites' ] );
        add_action( 'wp_ajax_gatetouch_save_robots', [ $this, 'ajax_save_robots' ] );
        add_action( 'wp_ajax_gatetouch_save_htaccess', [ $this, 'ajax_save_htaccess' ] );
        add_action( 'wp_ajax_gatetouch_reset_settings', [ $this, 'ajax_reset_settings' ] );
        add_action( 'wp_ajax_gatetouch_generate_report', [ $this, 'ajax_generate_report' ] );
        add_action( 'wp_ajax_gatetouch_analyze_competitor', [ $this, 'ajax_analyze_competitor' ] );
        add_action( 'wp_ajax_gatetouch_save_expert_mode',   [ $this, 'ajax_save_expert_mode' ] );
        // ajax_run_scan() existed but was never hooked, so the dashboard's
        // "Run full audit" button posted to an action nothing answered.
        add_action( 'wp_ajax_gatetouch_run_scan',           [ $this, 'ajax_run_scan' ] );
        add_action( 'wp_ajax_gatetouch_scan_images',        [ $this, 'ajax_scan_images' ] );
        add_action( 'wp_ajax_gatetouch_process_image_alt', [ $this, 'ajax_process_image_alt' ] );
        add_action( 'wp_ajax_gatetouch_ai_image',           [ $this, 'ajax_ai_image' ] );
        add_action( 'wp_ajax_gatetouch_fetch_audit_results', [ $this, 'ajax_fetch_audit_results' ] );
        add_action( 'wp_ajax_gatetouch_run_homepage_audit', [ $this, 'ajax_run_homepage_audit' ] );
        add_action( 'wp_ajax_gatetouch_fetch_aeo_health',   [ $this, 'ajax_fetch_aeo_health' ] );
        add_action( 'wp_ajax_gatetouch_fix_geo_signal',     [ $this, 'ajax_fix_geo_signal'   ] );
        add_action( 'wp_ajax_gatetouch_generate_brief',     [ $this, 'ajax_generate_brief'   ] );
        add_action( 'wp_ajax_gatetouch_brief_library',      [ $this, 'ajax_brief_library'    ] );
        add_action( 'wp_ajax_gatetouch_brief_delete',       [ $this, 'ajax_brief_delete'     ] );
        add_action( 'wp_ajax_gatetouch_brief_create_post',  [ $this, 'ajax_brief_create_post' ] );
        add_action( 'wp_ajax_gatetouch_fetch_link_data',    [ $this, 'ajax_fetch_link_data' ] );
        add_action( 'wp_ajax_gatetouch_get_all_audit_ids',  [ $this, 'ajax_get_all_audit_ids' ] );
        add_action( 'wp_ajax_gatetouch_audit_single_post',  [ $this, 'ajax_audit_single_post' ] );
        add_action( 'wp_ajax_gatetouch_get_debug_logs',     [ $this, 'ajax_get_debug_logs' ] );
        add_action( 'wp_ajax_gatetouch_clear_debug_logs',   [ $this, 'ajax_clear_debug_logs' ] );
        add_action( 'wp_ajax_gatetouch_optimize_db',        [ $this, 'ajax_optimize_db' ] );
        add_action( 'wp_ajax_gatetouch_bulk_queue',         [ $this, 'ajax_bulk_queue' ] );
        add_action( 'wp_ajax_gatetouch_bulk_progress',      [ $this, 'ajax_bulk_progress' ] );
        add_action( 'wp_ajax_gatetouch_trigger_crawl',      [ $this, 'ajax_trigger_crawl' ] );
        add_action( 'wp_ajax_gatetouch_get_crawl_status',   [ $this, 'ajax_get_crawl_status' ] );
        add_action( 'wp_ajax_gatetouch_get_audit_id_list',  [ $this, 'ajax_get_audit_id_list' ] );
        add_action( 'wp_ajax_gatetouch_fetch_media_bulk',   [ $this, 'ajax_fetch_media_bulk' ] );
        add_action( 'wp_ajax_gatetouch_fetch_media_stats',  [ $this, 'ajax_fetch_media_stats' ] );
        add_action( 'wp_ajax_gatetouch_update_media_meta',  [ $this, 'ajax_update_media_meta' ] );
        
        // Setup Wizard AJAX
        add_action( 'wp_ajax_gatetouch_setup_validate_api', [ $this, 'ajax_setup_validate' ] );
        add_action( 'wp_ajax_gatetouch_complete_setup',     [ $this, 'ajax_complete_setup' ] );
        add_action( 'wp_ajax_gatetouch_reset_safe_mode',    [ $this, 'ajax_reset_safe_mode' ] );
        add_action( 'wp_ajax_gatetouch_migrate_source',     [ $this, 'ajax_migrate_source' ] );
        add_action( 'wp_ajax_gatetouch_migration_analyze',  [ $this, 'ajax_migration_analyze' ] );
        add_action( 'wp_ajax_gatetouch_migration_batch',    [ $this, 'ajax_migration_batch' ] );
        add_action( 'wp_ajax_gatetouch_migration_verify',   [ $this, 'ajax_migration_verify' ] );
        add_action( 'wp_ajax_gatetouch_migration_rollback', [ $this, 'ajax_migration_rollback' ] );
        
        add_action( 'wp_dashboard_setup', [ $this, 'dashboard_widget' ] );
    }

    public function ajax_trigger_crawl() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        // Clear old state for a fresh crawl
        delete_transient( 'gatetouch_crawl_summary' );
        delete_transient( 'gatetouch_crawl_state' );

        require_once GATETOUCH_PATH . 'includes/class-crawler.php';

        // Run first batch SYNCHRONOUSLY so data appears immediately in the UI
        $crawler   = new \GateTouch_Crawler();
        $crawler->run( home_url(), 40, [] ); // 40 URLs per batch
        $new_state = $crawler->get_state();

        if ( empty( $new_state['queue'] ) ) {
            // Entire site crawled in one pass
            $new_state['status']   = 'completed';
            $new_state['last_run'] = time();
            set_transient( 'gatetouch_crawl_summary', $new_state, YEAR_IN_SECONDS );
        } else {
            // More pages remain — save partial results now and queue the rest
            $new_state['status'] = 'running';
            set_transient( 'gatetouch_crawl_state',   $new_state, DAY_IN_SECONDS );
            set_transient( 'gatetouch_crawl_summary', $new_state, YEAR_IN_SECONDS );
            require_once GATETOUCH_PATH . 'includes/class-queue.php';
            \GateTouch_Queue::add( 'crawl_site', [] );
        }

        wp_send_json_success( __( 'Crawl started', 'gatetouch-ai-seo' ) );
    }

    public function ajax_get_crawl_status() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        require_once GATETOUCH_PATH . 'includes/class-crawler.php';
        wp_send_json_success( \GateTouch_Crawler::get_summary() );
    }

    public function ajax_run_scan() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $results = GateTouch_Analyzer::run_scan();
        wp_send_json_success( $results );
    }

    public function ajax_generate_report() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $type = sanitize_key( $_POST['type'] ?? 'csv' );
        
        if ( 'pdf' === $type ) {
            $report = GateTouch_Reporting::generate_pdf_audit();
            wp_send_json_success( $report );
        } else {
            wp_send_json_success( [ 'success' => true, 'message' => __( 'Exporting...', 'gatetouch-ai-seo' ) ] );
        }
    }

    public function ajax_analyze_competitor() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        if ( ! GateTouch_AI_Engine::has_api_key() ) {
            wp_send_json_error( [
                'locked'       => true,
                'gate'         => 'api_key',
                'settings_url' => admin_url( 'admin.php?page=gatetouch-settings&tab=ai' ),
                'message'      => __( 'No API key configured. Add your AI provider key in Settings → AI & AEO.', 'gatetouch-ai-seo' ),
            ] );
        }

        $url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
        if ( ! $url ) wp_send_json_error( __( 'Invalid URL.', 'gatetouch-ai-seo' ) );

        require_once GATETOUCH_PATH . 'includes/class-analysis.php';
        $results = GateTouch_Analysis::analyze_external_url( $url );
        
        if ( isset( $results['error'] ) ) {
            wp_send_json_error( $results['error'] );
        }

        wp_send_json_success( $results );
    }

    public function ajax_analyze_headline() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $headline = isset( $_POST['headline'] ) ? sanitize_text_field( wp_unslash( $_POST['headline'] ) ) : '';
        if ( ! $headline ) wp_send_json_error( __( 'Invalid headline.', 'gatetouch-ai-seo' ) );

        require_once GATETOUCH_PATH . 'includes/class-headline-analyzer.php';
        $results = GateTouch_Headline_Analyzer::analyze( $headline );
        
        if ( isset( $results['error'] ) ) {
            wp_send_json_error( $results['error'] );
        }

        wp_send_json_success( $results );
    }

    public function ajax_save_expert_mode() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $val = isset( $_POST['val'] ) ? sanitize_text_field( wp_unslash( $_POST['val'] ) ) : '0';
        update_option( 'gatetouch_expert_mode', $val );
        wp_send_json_success();
    }

    /**
     * Add GateTouch to the Admin Bar
     */
    public function admin_bar_node( \WP_Admin_Bar $wp_admin_bar ) {
        $adv = get_option( 'gatetouch_advanced_settings', [] );
        if ( isset( $adv['admin_bar_menu'] ) && $adv['admin_bar_menu'] === '' ) return;

        $wp_admin_bar->add_node( [
            'id'    => 'gatetouch-admin-bar',
            'title' => '<span class="ab-icon" style="top:2px;display:inline-flex;align-items:center;">' . GateTouch_Helpers::icon( 'rocket', 16 ) . '</span> ' . __( 'GT SEO/GEO/AEO', 'gatetouch-ai-seo' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static trusted SVG
            'href'  => admin_url( 'admin.php?page=gatetouch' ),
            'meta'  => [ 'title' => __( 'Go to GT SEO/GEO/AEO Dashboard', 'gatetouch-ai-seo' ) ],
        ] );
        
        $wp_admin_bar->add_node( [
            'parent' => 'gatetouch-admin-bar',
            'id'     => 'gatetouch-ab-bulk',
            'title'  => __( 'Bulk Optimizer', 'gatetouch-ai-seo' ),
            'href'   => admin_url( 'admin.php?page=gatetouch-content-ai' ),
        ] );
    }

    /**
     * Add GateTouch to the Dashboard
     */
    public function dashboard_widget() {
        $adv = get_option( 'gatetouch_advanced_settings', [] );
        if ( isset( $adv['dashboard_widgets'] ) && $adv['dashboard_widgets'] === '' ) return;

        wp_add_dashboard_widget(
            'gatetouch_dashboard_widget',
            __( 'GT SEO/GEO/AEO — Overview', 'gatetouch-ai-seo' ),
            function() {
                $total_posts = (int) wp_count_posts( 'post' )->publish;
                $total_pages = (int) wp_count_posts( 'page' )->publish;
                global $wpdb;
                $optimized = (int) $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = %s",
                        GATETOUCH_META_KEY
                    )
                );
                $percent = round( ( $optimized / max( 1, $total_posts + $total_pages ) ) * 100 );
                ?>
                <div style="padding:10px 0;">
                    <div style="font-size:24px; font-weight:800; color:#6366f1; margin-bottom:5px;"><?php echo esc_html( $percent ); ?>%</div>
                    <div style="font-size:12px; font-weight:700; color:#64748b; text-transform:uppercase;"><?php esc_html_e( 'Site Optimization Coverage', 'gatetouch-ai-seo' ); ?></div>
                    <div style="margin-top:15px; display:flex; gap:10px;">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Open Dashboard', 'gatetouch-ai-seo' ); ?></a>
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch-content-ai' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Bulk Run AI', 'gatetouch-ai-seo' ); ?></a>
                    </div>
                </div>
                <?php
            }
        );
    }

    public function register_menus() {
        $icon = 'data:image/svg+xml;base64,' . base64_encode(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">'
            . '<path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" '
            . 'stroke="#a5b4fc" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
        );

        add_menu_page(
            __( 'GT - AI SEO/GEO/AEO Optimizer', 'gatetouch-ai-seo' ),
            'GT SEO/GEO/AEO',
            'manage_options',
            'gatetouch',
            [ $this, 'page_dashboard' ],
            $icon,
            60
        );

        // Ordered to match how the plugin is actually used: arrive at the
        // Dashboard, import from whatever you were running before, configure
        // once, then live in Content AI and Audit.
        $subpages = [
            [ 'gatetouch',            __( 'Dashboard',        'gatetouch-ai-seo' ), 'page_dashboard'  ],
            [ 'gatetouch-migrate',    __( 'Import & Migrate', 'gatetouch-ai-seo' ), 'page_migrate'    ],
            [ 'gatetouch-settings',   __( 'Settings',         'gatetouch-ai-seo' ), 'page_settings'   ],
            [ 'gatetouch-content-ai', __( 'Content AI',       'gatetouch-ai-seo' ), 'page_content_ai' ],
            [ 'gatetouch-audit',      __( 'Audit & Health',   'gatetouch-ai-seo' ), 'page_audit'      ],
            [ 'gatetouch-help',       __( 'Help & Support',   'gatetouch-ai-seo' ), 'page_help_center' ],
        ];

        foreach ( $subpages as [ $slug, $title, $method ] ) {
            $menu_title = $title;

            // Badge only what is still worth doing. The importer never deletes
            // from the old plugin, so its data stays detectable forever — badging
            // raw detections would nag about an import already completed.
            if ( 'gatetouch-migrate' === $slug && class_exists( 'GateTouch_Migration_Engine' ) ) {
                $pending = GateTouch_Migration_Engine::pending_sources();
                if ( ! empty( $pending ) ) {
                    $menu_title .= ' <span class="update-plugins count-' . count( $pending ) . '"><span class="update-count">'
                        . count( $pending ) . '</span></span>';
                }
            }

            add_submenu_page(
                'gatetouch',
                'GT SEO/GEO/AEO — ' . $title,
                $menu_title,
                'manage_options',
                $slug,
                [ $this, $method ]
            );
        }

        // Diagnostics is a support tool, not a workflow stage. It stays
        // registered so existing links keep working, but it now lives as a tab
        // inside Help & Support rather than occupying a top-level slot.
        add_submenu_page(
            null,
            /* translators: %s: admin page title */
            sprintf( __( 'GT SEO/GEO/AEO — %s', 'gatetouch-ai-seo' ), __( 'AI Diagnostics', 'gatetouch-ai-seo' ) ),
            __( 'AI Diagnostics', 'gatetouch-ai-seo' ),
            'manage_options',
            'gatetouch-diagnostics',
            [ $this, 'page_diagnostics' ]
        );

        // Hidden Setup Wizard
        add_submenu_page(
            null,
            /* translators: %s: admin page title */
            sprintf( __( 'GT SEO/GEO/AEO — %s', 'gatetouch-ai-seo' ), __( 'Setup Wizard', 'gatetouch-ai-seo' ) ),
            __( 'Setup Wizard', 'gatetouch-ai-seo' ),
            'manage_options',
            'gatetouch-setup-wizard',
            [ $this, 'page_setup_wizard' ]
        );

        // Bulk Optimizer moved to Content AI tab

        add_submenu_page(
            null,
            /* translators: %s: admin page title */
            sprintf( __( 'GT SEO/GEO/AEO — %s', 'gatetouch-ai-seo' ), __( 'Media Alt Assistant', 'gatetouch-ai-seo' ) ),
            __( 'Media Alt Assistant', 'gatetouch-ai-seo' ),
            'manage_options',
            'gatetouch-media-bulk',
            [ $this, 'page_media_bulk' ]
        );

        // Legacy redirects handled dynamically in maybe_handle_legacy_redirects()

        add_submenu_page(
            null,
            /* translators: %s: admin page title */
            sprintf( __( 'GT SEO/GEO/AEO — %s', 'gatetouch-ai-seo' ), __( 'Link Assistant', 'gatetouch-ai-seo' ) ),
            __( 'Link Assistant', 'gatetouch-ai-seo' ),
            'manage_options',
            'gatetouch-links',
            [ $this, 'page_links' ]
        );

        add_submenu_page(
            null,
            /* translators: %s: admin page title */
            sprintf( __( 'GT SEO/GEO/AEO — %s', 'gatetouch-ai-seo' ), __( 'Conflict Resolver', 'gatetouch-ai-seo' ) ),
            __( 'Conflict Resolver', 'gatetouch-ai-seo' ),
            'manage_options',
            'gatetouch-tools',
            [ $this, 'page_tools' ]
        );

        add_submenu_page(
            null,
            /* translators: %s: admin page title */
            sprintf( __( 'GT SEO/GEO/AEO — %s', 'gatetouch-ai-seo' ), __( 'Advanced Settings', 'gatetouch-ai-seo' ) ),
            __( 'Advanced Settings', 'gatetouch-ai-seo' ),
            'manage_options',
            'gatetouch-advanced',
            [ $this, 'page_advanced' ]
        );
    }

    public function register_settings() {
        // Simple text fields.
        $text_fields = [
            'gatetouch_general_group'    => [ 'gatetouch_title_separator', 'gatetouch_twitter_site', 'gatetouch_expert_mode' ],
            'gatetouch_ai_group'         => [ 'gatetouch_openai_key', 'gatetouch_ai_model', 'gatetouch_auto_generate' ],
            'gatetouch_automation_group' => [ 'gatetouch_auto_linker', 'gatetouch_smart_redirects', 'gatetouch_auto_schema', 'gatetouch_auto_alt' ],
        ];
        foreach ( $text_fields as $group => $options ) {
            foreach ( $options as $opt ) {
                register_setting( $group, $opt, [ 'type' => 'string', 'sanitize_callback' => 'sanitize_text_field' ] );
            }
        }

        // Array options — each has a dedicated, type-aware sanitizer.
        $array_map = [
            [ 'gatetouch_general_group', 'gatetouch_homepage_meta',        [ 'GateTouch_Helpers', 'sanitize_homepage_meta' ] ],
            [ 'gatetouch_general_group', 'gatetouch_general_settings',     [ 'GateTouch_Helpers', 'sanitize_flags_array' ] ],
            [ 'gatetouch_general_group', 'gatetouch_breadcrumb_settings',  [ 'GateTouch_Helpers', 'sanitize_breadcrumb_settings' ] ],
            [ 'gatetouch_general_group', 'gatetouch_security_settings',    [ 'GateTouch_Helpers', 'sanitize_flags_array' ] ],
            [ 'gatetouch_sitemap_group', 'gatetouch_sitemap_settings',     [ 'GateTouch_Helpers', 'sanitize_sitemap_settings' ] ],
            [ 'gatetouch_social_group',  'gatetouch_social_settings',      [ 'GateTouch_Helpers', 'sanitize_social_settings' ] ],
            // These two are NOT flags-only. sanitize_flags_array() collapsed every
            // Organization field and the whole custom robots rule table to '0'.
            [ 'gatetouch_schema_group',  'gatetouch_schema_settings',      [ 'GateTouch_Helpers', 'sanitize_schema_settings' ] ],
            [ 'gatetouch_robots_group',  'gatetouch_robots_settings',      [ 'GateTouch_Helpers', 'sanitize_robots_settings' ] ],
            [ 'gatetouch_robots_group',  'gatetouch_crawl_optimization_settings', [ 'GateTouch_Crawl_Optimization', 'sanitize' ] ],
            [ 'gatetouch_llms_group',    'gatetouch_llms_settings',        [ 'GateTouch_Helpers', 'sanitize_llms_settings' ] ],
            [ 'gatetouch_security_grp',  'gatetouch_security_txt_settings',[ 'GateTouch_Helpers', 'sanitize_security_txt_settings' ] ],
            [ 'gatetouch_webmaster_group','gatetouch_webmaster_settings',  [ 'GateTouch_Helpers', 'sanitize_webmaster_settings' ] ],
        ];
        foreach ( $array_map as [ $group, $opt, $cb ] ) {
            register_setting( $group, $opt, [ 'type' => 'array', 'sanitize_callback' => $cb ] );
        }
    }

    public function enqueue_global( $hook ) {
        if ( ! GateTouch_Helpers::is_gatetouch_page( $hook ) ) return;

        wp_enqueue_style(
            'gatetouch-admin',
            GATETOUCH_URL . 'assets/css/admin-global.css',
            [],
            GATETOUCH_VERSION
        );
        wp_enqueue_script(
            'gatetouch-admin',
            GATETOUCH_URL . 'assets/js/admin-global.js',
            [ 'jquery' ],
            GATETOUCH_VERSION,
            true
        );

        wp_localize_script( 'gatetouch-admin', 'gatetouchAdmin', [
            'ajax_url'        => admin_url( 'admin-ajax.php' ),
            'nonce'           => wp_create_nonce( 'gatetouch_ajax' ),
            'setup_nonce'     => wp_create_nonce( 'gatetouch_setup_nonce' ),
            'active_provider' => get_option( 'gatetouch_ai_provider', 'openai' ),
            'support_url'     => admin_url( 'admin.php?page=gatetouch-help' ),
            'has_api_key'     => GateTouch_AI_Engine::is_api_operational() ? '1' : '0',
            'home_url'        => home_url( '/' ),
            'help_url'        => admin_url( 'admin.php?page=gatetouch-help' ),
            'strings'         => [
                'confirm_delete' => __( 'Are you sure you want to delete this redirect?', 'gatetouch-ai-seo' ),
                'saving'         => __( 'Saving...', 'gatetouch-ai-seo' ),
                'saved'          => __( 'Saved ✓', 'gatetouch-ai-seo' ),
                'error'          => __( 'Error. Try again.', 'gatetouch-ai-seo' ),
                'generating'     => __( 'Generating...', 'gatetouch-ai-seo' ),
                'pinging'        => __( 'Pinging search engines...', 'gatetouch-ai-seo' ),
                'pinged'         => __( 'Search engines notified!', 'gatetouch-ai-seo' ),
            ],
        ] );

        // Search Appearance: variable picker, live counters, separator picker.
        // The media library is needed for the logo / default social image pickers.
        if ( strpos( $hook, 'gatetouch-settings' ) !== false ) {
            wp_enqueue_media();
            wp_enqueue_style(
                'gatetouch-search-appearance',
                GATETOUCH_URL . 'assets/css/search-appearance.css',
                [ 'gatetouch-admin' ],
                GATETOUCH_VERSION
            );
            wp_enqueue_script(
                'gatetouch-search-appearance',
                GATETOUCH_URL . 'assets/js/search-appearance.js',
                [],
                GATETOUCH_VERSION,
                true
            );
        }

        if ( strpos( $hook, 'gatetouch-help' ) !== false ) {
            wp_enqueue_script(
                'gatetouch-help-center',
                GATETOUCH_URL . 'assets/js/help-center.js',
                [ 'gatetouch-admin' ],
                GATETOUCH_VERSION,
                true
            );
        }

        // Import & Migrate. The screen shipped with its script written but never
        // enqueued — every button on it was inert — and with no stylesheet at all.
        if ( strpos( $hook, 'gatetouch-migrate' ) !== false ) {
            wp_enqueue_style(
                'gatetouch-migration',
                GATETOUCH_URL . 'assets/css/migration.css',
                [ 'gatetouch-admin' ],
                GATETOUCH_VERSION
            );
            wp_enqueue_script(
                'gatetouch-migration',
                GATETOUCH_URL . 'assets/js/migration.js',
                [ 'gatetouch-admin' ],
                GATETOUCH_VERSION,
                true
            );
        }

        if ( strpos( $hook, 'gatetouch-bulk' ) !== false || strpos( $hook, 'gatetouch-content-ai' ) !== false ) {
            wp_enqueue_script(
                'gatetouch-bulk',
                GATETOUCH_URL . 'assets/js/bulk-meta.js',
                [ 'jquery', 'gatetouch-admin' ],
                GATETOUCH_VERSION,
                true
            );
        }

        if ( strpos( $hook, 'gatetouch-audit' ) !== false ) {
            $analysis_js = GATETOUCH_PATH . 'assets/js/analysis.js';
            wp_enqueue_script(
                'gatetouch-analysis-js',
                GATETOUCH_URL . 'assets/js/analysis.js',
                [ 'jquery', 'gatetouch-admin' ],
                file_exists( $analysis_js ) ? (string) filemtime( $analysis_js ) : GATETOUCH_VERSION,
                true
            );
        }
    }

    // ── Pages ─────────────────────────────────────────────────

    /**
     * Send the user to the setup wizard once, immediately after activation.
     *
     * This fires on a one-shot flag set at activation and cleared the first time
     * it is honoured. It must NOT key off "setup not yet completed" and bounce
     * every plugin page back here: the wizard links out to Import & Migrate,
     * Settings and Help, and a blanket redirect makes each of those links —
     * including the wizard's own primary "Review & import" button — appear to do
     * nothing, because the destination immediately returns to the wizard.
     */
    public function maybe_redirect_to_setup() {
        if ( ! get_option( 'gatetouch_activation_redirect' ) ) return;

        if ( ! current_user_can( 'manage_options' ) ) return;
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;
        if ( defined( 'DOING_CRON' ) && DOING_CRON ) return;
        if ( is_network_admin() ) return;

        // Activating several plugins at once should not hijack the result page.
        if ( isset( $_GET['activate-multi'] ) ) {
            delete_option( 'gatetouch_activation_redirect' );
            return;
        }

        // Consume the flag first, so a redirect loop can never occur.
        delete_option( 'gatetouch_activation_redirect' );

        wp_safe_redirect( admin_url( 'admin.php?page=gatetouch-setup-wizard' ) );
        exit;
    }

    /**
     * Redirect users navigating to legacy slugs cleanly to new tabbed interfaces
     */
    public function maybe_handle_legacy_redirects() {
        if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) return;
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) return;

        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
        // Point straight at the current home rather than hopping via the old
        // Audit tabs, which would then redirect again.
        if ( $page === 'gatetouch-redirects' ) {
            wp_safe_redirect( admin_url( 'admin.php?page=gatetouch-settings&tab=redirects' ) );
            exit;
        }
        if ( $page === 'gatetouch-sitemap' ) {
            wp_safe_redirect( admin_url( 'admin.php?page=gatetouch-settings&tab=files' ) );
            exit;
        }
        if ( $page === 'gatetouch-ai' ) {
            wp_safe_redirect( admin_url( 'admin.php?page=gatetouch-settings&tab=ai' ) );
            exit;
        }
        if ( $page === 'gatetouch-bulk' ) {
            wp_safe_redirect( admin_url( 'admin.php?page=gatetouch-content-ai' ) );
            exit;
        }
        // Redirects and Sitemaps moved out of Audit — they are configuration,
        // not findings. Keep the old tab URLs working.
        if ( $page === 'gatetouch-audit' ) {
            $tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';
            if ( 'redirects' === $tab || 'sitemap' === $tab ) {
                wp_safe_redirect( admin_url( 'admin.php?page=gatetouch-settings&tab=' . ( 'sitemap' === $tab ? 'files' : 'redirects' ) ) );
                exit;
            }
        }
    }

    public function page_dashboard() {
        include GATETOUCH_PATH . 'admin/pages/dashboard.php';
    }

    public function page_audit() {
        include GATETOUCH_PATH . 'admin/pages/audit-center.php';
    }

    public function page_content_ai() {
        include GATETOUCH_PATH . 'admin/pages/content-ai.php';
    }

    public function page_settings() {
        include GATETOUCH_PATH . 'admin/pages/settings-center.php';
    }

    public function page_migrate() {
        include GATETOUCH_PATH . 'admin/pages/migration.php';
    }


    public function page_media_bulk() {
        include GATETOUCH_PATH . 'admin/pages/bulk-media-manager.php';
    }

    // page_sitemap() removed — it was registered in no menu and pointed at a
    // duplicate of sitemap-settings.php, which is now the Sitemaps & Files tab.

    public function page_links() {
        include GATETOUCH_PATH . 'admin/pages/link-assistant.php';
    }

    public function page_tools() {
        include GATETOUCH_PATH . 'admin/pages/tools.php';
    }

    public function page_advanced() {
        include GATETOUCH_PATH . 'admin/pages/advanced-settings.php';
    }



    public function page_diagnostics() {
        include GATETOUCH_PATH . 'admin/pages/api-diagnostics.php';
    }

    public function page_help_center() {
        include GATETOUCH_PATH . 'admin/pages/help-center.php';
    }

    public function page_setup_wizard() {
        include GATETOUCH_PATH . 'admin/pages/get-started.php';
    }

    // ── AJAX ──────────────────────────────────────────────────
    public function ajax_flush_rules() {
        check_ajax_referer( 'gatetouch_admin', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        GateTouch_Sitemap::register_rewrites();
        flush_rewrite_rules();
        
        wp_send_json_success( __( 'SEO Rewrite rules flushed successfully! Robots.txt and Sitemaps should now be accessible.', 'gatetouch-ai-seo' ) );
    }

    public function ajax_ping_search() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        $sitemap = new GateTouch_Sitemap();
        $result  = $sitemap->ping_search_engines();
        wp_send_json_success( [ 'time' => current_time( 'mysql' ), 'result' => $result ] );
    }

    public function ajax_ping() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        $sitemap = new GateTouch_Sitemap();
        $result  = $sitemap->ping_search_engines();
        wp_send_json_success( [ 'time' => current_time( 'mysql' ), 'result' => $result ] );
    }

    public function ajax_homepage_meta() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );
        $result = GateTouch_AI_Engine::generate_homepage_meta();
        if ( isset( $result['error'] ) ) wp_send_json_error( $result['error'] );
        $existing = get_option( 'gatetouch_homepage_meta', [] );
        $merged   = array_merge( $existing, array_filter( $result ) );
        update_option( 'gatetouch_homepage_meta', $merged );
        wp_send_json_success( $merged );
    }

    public function ajax_export() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        global $wpdb;
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
                GATETOUCH_META_KEY
            )
        );

        $export = [];
        foreach ( $rows as $row ) {
            $post = get_post( $row->post_id );
            if ( ! $post ) continue;
            $export[] = [
                'post_id'   => $row->post_id,
                'post_url'  => get_permalink( $row->post_id ),
                'post_type' => $post->post_type,
                'meta'      => maybe_unserialize( $row->meta_value ),
            ];
        }

        wp_send_json_success( [ 'data' => $export, 'count' => count( $export ) ] );
    }

    public function ajax_import() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $raw = isset( $_POST['import_data'] ) ? sanitize_textarea_field( wp_unslash( $_POST['import_data'] ) ) : '';
        $data = json_decode( $raw, true );

        if ( ! is_array( $data ) ) wp_send_json_error( __( 'Invalid JSON data.', 'gatetouch-ai-seo' ) );

        $imported = 0;
        foreach ( $data as $item ) {
            $post_id = intval( $item['post_id'] ?? 0 );
            $meta    = $item['meta'] ?? [];
            if ( $post_id && is_array( $meta ) && get_post( $post_id ) ) {
                update_post_meta( $post_id, GATETOUCH_META_KEY, $meta );
                $imported++;
            }
        }

        wp_send_json_success( [ 'imported' => $imported ] );
    }

    public function ajax_migrate_source() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $source = sanitize_key( $_POST['source'] ?? '' );
        if ( empty( $source ) ) {
            wp_send_json_error( __( 'Missing migration source.', 'gatetouch-ai-seo' ) );
        }

        $result = GateTouch_Migration_Engine::migrate( $source );
        if ( ! empty( $result['success'] ) ) {
            wp_send_json_success( $result );
        } else {
            wp_send_json_error( $result['error'] ?? __( 'Migration failed.', 'gatetouch-ai-seo' ) );
        }
    }

    /**
     * Dry run — report what a migration would import, without writing anything.
     */
    public function ajax_migration_analyze() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );
        }

        $source = isset( $_POST['source'] ) ? sanitize_key( wp_unslash( $_POST['source'] ) ) : '';
        $result = GateTouch_Migration_Engine::analyze( $source );

        if ( empty( $result['success'] ) ) {
            wp_send_json_error( $result['error'] ?? __( 'Analysis failed.', 'gatetouch-ai-seo' ) );
        }

        wp_send_json_success( $result );
    }

    /**
     * Process a single migration batch. The browser calls this in a loop so a
     * large site never hits the PHP time limit.
     */
    public function ajax_migration_batch() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );
        }

        $source = isset( $_POST['source'] ) ? sanitize_key( wp_unslash( $_POST['source'] ) ) : '';

        $types = [];
        if ( isset( $_POST['types'] ) && is_array( $_POST['types'] ) ) {
            $types = array_map( 'sanitize_key', wp_unslash( $_POST['types'] ) );
        }

        $options = [
            'overwrite' => ! empty( $_POST['overwrite'] ) && 'true' === sanitize_text_field( wp_unslash( $_POST['overwrite'] ) ),
            'types'     => $types,
            'cursor'    => [
                'stage'  => isset( $_POST['stage'] ) ? absint( $_POST['stage'] ) : 0,
                'offset' => isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0,
            ],
        ];

        $result = GateTouch_Migration_Engine::run_batch( $source, $options );

        if ( empty( $result['success'] ) ) {
            wp_send_json_error( $result['error'] ?? __( 'Migration batch failed.', 'gatetouch-ai-seo' ) );
        }

        wp_send_json_success( $result );
    }

    /**
     * Re-read source and destination and confirm the migration landed.
     */
    public function ajax_migration_verify() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );
        }

        $source = isset( $_POST['source'] ) ? sanitize_key( wp_unslash( $_POST['source'] ) ) : '';
        $result = GateTouch_Migration_Engine::verify( $source );

        if ( empty( $result['success'] ) ) {
            wp_send_json_error( $result['error'] ?? __( 'Verification failed.', 'gatetouch-ai-seo' ) );
        }

        wp_send_json_success( $result );
    }

    /**
     * Restore the settings snapshot taken before the last migration.
     */
    public function ajax_migration_rollback() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );
        }

        $result = GateTouch_Migration_Engine::rollback_settings();

        if ( empty( $result['success'] ) ) {
            wp_send_json_error( $result['error'] ?? __( 'Rollback failed.', 'gatetouch-ai-seo' ) );
        }

        wp_send_json_success( $result );
    }

    public function ajax_flush_rewrites() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        GateTouch_Sitemap::register_rewrites();
        flush_rewrite_rules();
        wp_send_json_success( [ 'flushed' => true ] );
    }

    public function plugin_links( $links ) {
        $custom = [
            '<a href="' . esc_url( admin_url( 'admin.php?page=gatetouch' ) ) . '">Dashboard</a>',
            '<a href="' . esc_url( admin_url( 'admin.php?page=gatetouch-settings&tab=ai' ) )
            . '" style="color:#6366f1;font-weight:700;">AI Settings</a>',
        ];
        return array_merge( $custom, $links );
    }

    public function plugin_row_meta( $links, $file ) {
        if ( plugin_basename( GATETOUCH_FILE ) === $file ) {
            $links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=gatetouch-help' ) ) . '">Documentation</a>';
            $links[] = '<a href="https://wordpress.org/support/plugin/gatetouch-ai-seo/" target="_blank" rel="noopener noreferrer">Support</a>';
        }
        return $links;
    }
    public function show_whats_new() {
        if ( get_option( 'gatetouch_dismissed_2_0', 'no' ) === 'yes' ) return;
        $screen = get_current_screen();
        if ( ! $screen || strpos( $screen->id, 'gatetouch' ) === false ) return;

        ?>
        <div id="gatetouch-whats-new" class="notice notice-info is-dismissible" style="border-left: 4px solid #6366f1; background: #fff; padding: 20px; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); margin-top: 20px; border: 1px solid #e2e8f0; border-left: 4px solid #6366f1;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="background: linear-gradient(135deg, #6366f1, #a855f7); color: #fff; width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 4px 12px rgba(99,102,241,0.3);"><?php echo wp_kses( GateTouch_Helpers::icon( 'rocket', 30 ), GateTouch_Helpers::svg_kses_allowed() ); ?></div>
                <div>
                    <h2 style="margin: 0 0 4px; font-weight: 800; color: #1e293b; font-size: 18px;">Welcome to GT SEO/GEO/AEO: The AI Optimization Era</h2>
                    <p style="margin: 0; color: #64748b; font-size: 14px; line-height: 1.5;">We've upgraded your SEO engine with <strong>Cognitive Search Intent</strong>, <strong>AI Link Assistant</strong>, and <strong>Vision AI 2.0</strong>. Optimize for AI search engines (SGE, ChatGPT) in one click.</p>
                    <div style="margin-top: 12px; display: flex; gap: 12px;">
                        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gatetouch' ) ); ?>" class="button button-primary" style="background: #6366f1; border: none; font-weight: 700; border-radius: 6px;">Explore 2.0 Dashboard</a>
                        <button type="button" id="gatetouch-dismiss-v2" class="button button-secondary" style="border-radius: 6px;">Got it, thanks!</button>
                    </div>
                </div>
            </div>
            <?php // dismiss script enqueued via admin-global.js ?>
        </div>
        <?php
    }

    public function ajax_dismiss_notice() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );
        update_option( 'gatetouch_dismissed_2_0', 'yes' );
        wp_send_json_success();
    }

    public function ajax_save_robots() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $rules_input = isset( $_POST['rules'] ) && is_array( $_POST['rules'] ) ? map_deep( wp_unslash( $_POST['rules'] ), 'sanitize_text_field' ) : [];
        $rules = array_map(
            static function ( $rule ) {
                if ( ! is_array( $rule ) ) {
                    return [ 'ua' => '', 'dir' => '', 'val' => '' ];
                }

                return [
                    'ua'  => sanitize_text_field( $rule['ua'] ?? '' ),
                    'dir' => sanitize_key( $rule['dir'] ?? '' ),
                    'val' => sanitize_text_field( $rule['val'] ?? '' ),
                ];
            },
            $rules_input
        );
        // Whether the legacy four-checkbox AI bot list took part in this request.
        // The Tools screen no longer renders it — AI crawler policy lives in
        // Settings → Sitemaps & Files — so an absent field must mean "leave the
        // crawler settings alone", never "allow everything again".
        $has_legacy_ai_bots = isset( $_POST['ai_bots'] ) && is_array( $_POST['ai_bots'] );
        $ai_bots = $has_legacy_ai_bots ? array_map( 'sanitize_text_field', wp_unslash( $_POST['ai_bots'] ) ) : [];
        $block_search = ! empty( $_POST['block_search'] ) && sanitize_text_field( wp_unslash( $_POST['block_search'] ) ) !== '0';

        $opts = get_option( 'gatetouch_robots_settings', [] );
        if ( ! is_array( $opts ) ) {
            $opts = [];
        }
        $opts['custom_rules'] = $rules;
        $opts['block_internal_search'] = $block_search;
        $opts['robots_mode'] = 'auto';

        if ( $has_legacy_ai_bots ) {
            $opts['ai_bots'] = $ai_bots;
        }

        $legacy_bot_keys = [
            'GPTBot'          => 'allow_gptbot',
            'Google-Extended' => 'allow_google_ext',
            'ClaudeBot'       => 'allow_claudebot',
            'CCBot'           => 'allow_ccbot',
        ];
        if ( $has_legacy_ai_bots ) {
            $blocked_ai_bots = array_flip( $ai_bots );
            foreach ( $legacy_bot_keys as $bot_name => $option_key ) {
                $opts[ $option_key ] = isset( $blocked_ai_bots[ $bot_name ] ) ? 'no' : 'yes';
            }
        }

        update_option( 'gatetouch_robots_settings', $opts );
        wp_send_json_success();
    }

    public function ajax_save_htaccess() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $content = filter_input(
            INPUT_POST,
            'content',
            FILTER_CALLBACK,
            [ 'options' => [ 'GateTouch_Helpers', 'sanitize_htaccess_content' ] ]
        );
        $content = is_string( $content ) ? $content : '';
        require_once ABSPATH . 'wp-admin/includes/file.php';

        $file = trailingslashit( get_home_path() ) . '.htaccess';

        WP_Filesystem();
        global $wp_filesystem;

        if ( ! $wp_filesystem ) {
            wp_send_json_error( __( 'The WordPress filesystem API is unavailable.', 'gatetouch-ai-seo' ) );
        }

        $file_exists = $wp_filesystem->exists( $file );
        if ( ( $file_exists && ! $wp_filesystem->is_writable( $file ) ) || ( ! $file_exists && ! $wp_filesystem->is_writable( dirname( $file ) ) ) ) {
            wp_send_json_error( __( 'The .htaccess file is not writable.', 'gatetouch-ai-seo' ) );
        }

        $result = $wp_filesystem->put_contents( $file, $content, FS_CHMOD_FILE );
        if ( $result === false ) {
            wp_send_json_error( __( 'Failed to write to .htaccess file.', 'gatetouch-ai-seo' ) );
        }

        wp_send_json_success();
    }

    public function ajax_reset_settings() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $modules = isset( $_POST['modules'] ) && is_array( $_POST['modules'] ) ? array_map( 'sanitize_key', wp_unslash( $_POST['modules'] ) ) : [];
        if ( in_array( 'all', $modules ) ) {
            $options = [
                'gatetouch_general_settings', 'gatetouch_sitemap_settings', 'gatetouch_social_settings',
                'gatetouch_schema_settings', 'gatetouch_robots_settings', 'gatetouch_breadcrumb_settings',
                'gatetouch_webmaster_settings', 'gatetouch_advanced_settings', 'gatetouch_llms_settings',
                'gatetouch_crawl_optimization_settings'
            ];
            foreach ( $options as $opt ) delete_option( $opt );
        } else {
            $map = [
                'general'    => 'gatetouch_general_settings',
                'sitemaps'   => 'gatetouch_sitemap_settings',
                'social'     => 'gatetouch_social_settings',
                'schema'     => 'gatetouch_schema_settings',
                'robots'     => 'gatetouch_robots_settings',
                'crawl'      => 'gatetouch_crawl_optimization_settings',
                'breadcrumbs'=> 'gatetouch_breadcrumb_settings',
                'webmaster'  => 'gatetouch_webmaster_settings',
            ];
            foreach ( $modules as $mod ) {
                if ( isset( $map[$mod] ) ) delete_option( $map[$mod] );
            }
        }
        wp_send_json_success();
    }

    public function ajax_scan_images() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        global $wpdb;
        $ids = $wpdb->get_col( "
            SELECT p.ID 
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON (p.ID = pm.post_id AND pm.meta_key = '_wp_attachment_image_alt')
            WHERE p.post_type = 'attachment' 
            AND p.post_mime_type LIKE 'image/%'
            AND (pm.meta_value IS NULL OR pm.meta_value = '')
            LIMIT 500
        " );

        wp_send_json_success( $ids );
    }

    public function ajax_process_image_alt() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $id = intval( $_POST['id'] ?? 0 );
        if ( ! $id ) wp_send_json_error();

        $url = wp_get_attachment_url( $id );
        if ( ! $url ) wp_send_json_error();

        $current_title = get_the_title( $id );
        $needs_title_fix = empty( trim( $current_title ) );

        if ( ! $needs_title_fix ) {
            // Check for generic/messy titles or unusual names
            $generic_patterns = [
                'whatsapp', 'screenshot', 'gpt', 'img_', 'dsc', 'image', 'picture', 
                'photo', 'download', 'upload', 'file', 'attachment', 'telegram', 
                'facebook', 'instagram', 'camera', 'mobile', 'web'
            ];
            foreach ( $generic_patterns as $pattern ) {
                if ( stripos( $current_title, $pattern ) !== false ) {
                    $needs_title_fix = true;
                    break;
                }
            }
            
            // Also fix if title looks like a purely numeric ID or long random string
            if ( ! $needs_title_fix && preg_match( '/^[0-9_\-\s]{3,}$/', $current_title ) ) {
                $needs_title_fix = true;
            }
        }

        $prompt = "Analyze this image and generate:
        1. A professional, descriptive, and SEO-optimized Alt-Text (under 15 words).
        2. A creative and relevant Caption for the image.
        3. A short, professional, and SEO-friendly Title (3-6 words).
        
        Respond ONLY with a valid JSON object in this format:
        {\"alt_text\": \"...\", \"caption\": \"...\", \"title\": \"...\"}";

        require_once GATETOUCH_PATH . 'includes/class-ai-engine.php';
        $res = GateTouch_AI_Engine::call_vision( $url, $prompt );

        if ( isset( $res['error'] ) ) {
            wp_send_json_error( $res['error'] );
        }

        $alt     = sanitize_text_field( $res['alt_text'] ?? '' );
        $caption = sanitize_text_field( $res['caption'] ?? '' );
        $title   = sanitize_text_field( $res['title'] ?? '' );

        if ( $alt ) {
            update_post_meta( $id, '_wp_attachment_image_alt', $alt );
            
            $post_update = [ 'ID' => $id ];
            if ( $caption ) $post_update['post_excerpt'] = $caption;
            
            // Only update title if it was detected as generic or if it's extremely short
            if ( $needs_title_fix && $title ) {
                $post_update['post_title'] = $title;
            }

            wp_update_post( $post_update );

            // Calculate score for UI
            $final_title = ( $needs_title_fix && $title ) ? $title : $current_title;
            $score = 0;
            if ( $alt ) $score += 40;
            if ( $final_title && ! strpos( $final_title, '-' ) && ! strpos( $final_title, '_' ) ) $score += 30;
            if ( $caption ) $score += 30;

            wp_send_json_success( [ 
                'alt'      => $alt,
                'caption'  => $caption,
                'title'    => $final_title,
                'score'    => $score
            ] );
        }

        wp_send_json_error( __( 'Failed to generate image metadata.', 'gatetouch-ai-seo' ) );
    }

    public function ajax_ai_image() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $prompt = isset( $_POST['prompt'] ) ? sanitize_text_field( wp_unslash( $_POST['prompt'] ) ) : '';
        if ( ! $prompt ) wp_send_json_error();

        require_once GATETOUCH_PATH . 'includes/class-ai-engine.php';
        $res = GateTouch_AI_Engine::generate_ai_image( $prompt );
        
        if ( isset( $res['error'] ) ) {
            wp_send_json_error( $res['error'] );
        }

        wp_send_json_success( $res );
    }

    public function ajax_fetch_audit_results() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $paged = isset( $_POST['paged'] ) ? max( 1, (int) $_POST['paged'] ) : 1;
        $type  = isset( $_POST['audit_type'] ) ? sanitize_key( $_POST['audit_type'] ) : 'all';
        $post_types = ( $type === 'all' ) ? ['post', 'page', 'product'] : [$type];

        $version = get_option( 'gatetouch_audit_cache_version', 1 );
        $cache_key = 'gatetouch_audit_page_v' . $version . '_' . $paged . '_' . $type;
        $results = get_transient( $cache_key );

        if ( false === $results ) {
            $query = new \WP_Query( [
                'post_type'      => $post_types,
                'post_status'    => 'publish',
                'posts_per_page' => 20,
                'paged'          => $paged,
            ] );

            $results = [];
            if ( $query->have_posts() ) {
                while ( $query->have_posts() ) {
                    $query->the_post();
                    $p_id = get_the_ID();
                    $meta = get_post_meta( $p_id, GATETOUCH_META_KEY, true ) ?: [];
                    
                    $schema_type = $meta['schema_type'] ?? 'None';
                    if ($schema_type === 'custom') $schema_type = 'Custom AI';
                    
                    $results[] = [
                        'id'       => $p_id,
                        'title'    => esc_html( get_the_title() ),
                        'url'      => esc_url( get_the_permalink() ),
                        'score'    => isset($meta['score']) ? (int)$meta['score'] : 0,
                        'schema'   => esc_html( ucfirst($schema_type) ),
                        'edit_url' => esc_url( get_edit_post_link($p_id, 'raw') ),
                        'checks'   => isset($meta['checks']) && is_array($meta['checks']) ? $meta['checks'] : [],
                    ];
                }
                wp_reset_postdata();
            }
            
            // Cache for 1 hour to prevent DB spam on large sites
            set_transient( $cache_key, $results, HOUR_IN_SECONDS );
        }

        wp_send_json_success( $results );
    }

    public function ajax_run_homepage_audit() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $home_id = (int) get_option( 'page_on_front' );
        if ( ! $home_id ) {
            $latest = get_posts( [ 'posts_per_page' => 1 ] );
            $home_id = ! empty( $latest ) ? $latest[0]->ID : 0;
        }

        require_once GATETOUCH_PATH . 'includes/class-analysis.php';
        require_once GATETOUCH_PATH . 'includes/class-analyzer.php';
        
        $analysis = $home_id ? \GateTouch_Analysis::analyze( $home_id ) : null;
        if ( ! $analysis ) wp_send_json_error( __( 'Could not find homepage.', 'gatetouch-ai-seo' ) );

        // Add site-wide technical issues
        $analysis['site_issues'] = \GateTouch_Analyzer::detect_issues();
        $analysis['edit_url']    = get_edit_post_link( $home_id, 'raw' );

        wp_send_json_success( $analysis );
    }

    public function ajax_fetch_aeo_health() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        require_once GATETOUCH_PATH . 'includes/class-scoring-engine.php';

        $top_posts = get_posts( [ 'post_type' => [ 'post', 'page' ], 'numberposts' => 15 ] );

        $rows      = [];
        $geo_total = 0;
        $aeo_total = 0;
        $count     = 0;

        foreach ( $top_posts as $p ) {
            $audit = GateTouch_Scoring_Engine::audit_post( $p->ID );

            $aeo_score = $audit['aeo']['score'] ?? 0;
            $geo_score = $audit['geo']['score'] ?? 0;
            $aeo_tip   = $audit['aeo']['tips'][0] ?? '';
            $geo_tip   = $audit['geo']['tips'][0] ?? '';

            $aeo_color = $aeo_score >= 80 ? '#10b981' : ( $aeo_score >= 50 ? '#f59e0b' : '#ef4444' );
            $geo_color = $geo_score >= 80 ? '#a855f7' : ( $geo_score >= 50 ? '#f59e0b' : '#ef4444' );

            $geo_total += $geo_score;
            $aeo_total += $aeo_score;
            $count++;

            $rows[] = [
                'id'        => $p->ID,
                'title'     => esc_html( $p->post_title ),
                'url'       => esc_url( get_permalink( $p->ID ) ),
                'edit_url'  => esc_url( (string) get_edit_post_link( $p->ID ) ),
                'aeo_score' => $aeo_score,
                'aeo_color' => $aeo_color,
                'aeo_tip'   => esc_html( $aeo_tip ),
                'geo_score' => $geo_score,
                'geo_color' => $geo_color,
                'geo_tip'   => esc_html( $geo_tip ),
            ];
        }

        $llm_enabled    = ( get_option( 'gatetouch_llms_settings', [] )['enable_llms_txt'] ?? 'no' ) === 'yes';
        $avg_geo        = $count ? round( $geo_total / $count ) : 0;
        $avg_aeo        = $count ? round( $aeo_total / $count ) : 0;

        wp_send_json_success( [
            'rows'        => $rows,
            'avg_geo'     => $avg_geo,
            'avg_aeo'     => $avg_aeo,
            'llm_enabled' => $llm_enabled,
        ] );
    }

    public function ajax_fix_geo_signal() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $signal = sanitize_key( $_POST['signal'] ?? '' );

        if ( $signal === 'llms_txt' ) {
            $settings = get_option( 'gatetouch_llms_settings', [] );
            $settings['enable_llms_txt'] = 'yes';
            update_option( 'gatetouch_llms_settings', $settings );
            flush_rewrite_rules();
            wp_send_json_success( [ 'message' => 'llms.txt enabled. AI crawlers can now discover your content.' ] );
        }

        wp_send_json_error( __( 'Unknown signal.', 'gatetouch-ai-seo' ) );
    }



    public function ajax_generate_brief() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $keyword = sanitize_text_field( wp_unslash( $_POST['keyword'] ?? '' ) );
        if ( strlen( $keyword ) < 2 ) wp_send_json_error( __( 'Please enter a keyword or topic.', 'gatetouch-ai-seo' ) );

        require_once GATETOUCH_PATH . 'includes/class-content-brief.php';

        $force = ! empty( $_POST['force'] );

        // Serve from the library unless a fresh run was asked for — every brief
        // is a paid API call, so silently regenerating the same keyword is waste.
        if ( ! $force ) {
            $cached = GateTouch_Content_Brief::cached( $keyword );
            if ( $cached ) {
                wp_send_json_success( [
                    'brief'    => $cached,
                    'markdown' => GateTouch_Content_Brief::to_markdown( $cached ),
                    'id'       => GateTouch_Content_Brief::key_for( $keyword ),
                    'cached'   => true,
                    'library'  => GateTouch_Content_Brief::library_index(),
                ] );
            }
        }

        if ( ! GateTouch_AI_Engine::is_api_operational() ) {
            wp_send_json_error( __( 'AI API is not configured. Go to Settings → AI & AEO to add your API key.', 'gatetouch-ai-seo' ) );
        }

        $result = GateTouch_Content_Brief::generate( $keyword, home_url() );

        if ( isset( $result['error'] ) ) {
            wp_send_json_error( $result['error'] );
        }

        $id = GateTouch_Content_Brief::remember( $keyword, $result );

        wp_send_json_success( [
            'brief'    => $result,
            'markdown' => GateTouch_Content_Brief::to_markdown( $result ),
            'id'       => $id,
            'cached'   => false,
            'library'  => GateTouch_Content_Brief::library_index(),
        ] );
    }

    /** Return one saved brief, or the whole library index. */
    public function ajax_brief_library() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        require_once GATETOUCH_PATH . 'includes/class-content-brief.php';

        $id = sanitize_key( wp_unslash( $_POST['id'] ?? '' ) );
        if ( '' === $id ) {
            wp_send_json_success( [ 'library' => GateTouch_Content_Brief::library_index() ] );
        }

        $brief = GateTouch_Content_Brief::from_library( $id );
        if ( ! $brief ) {
            wp_send_json_error( __( 'That brief is no longer saved.', 'gatetouch-ai-seo' ) );
        }

        wp_send_json_success( [
            'brief'    => $brief,
            'markdown' => GateTouch_Content_Brief::to_markdown( $brief ),
            'id'       => $id,
            'cached'   => true,
        ] );
    }

    public function ajax_brief_delete() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        require_once GATETOUCH_PATH . 'includes/class-content-brief.php';

        $id = sanitize_key( wp_unslash( $_POST['id'] ?? '' ) );
        GateTouch_Content_Brief::forget( $id );

        wp_send_json_success( [ 'library' => GateTouch_Content_Brief::library_index() ] );
    }

    /** Scaffold a draft post from a saved brief. */
    public function ajax_brief_create_post() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( __( 'You do not have permission to create posts.', 'gatetouch-ai-seo' ) );

        require_once GATETOUCH_PATH . 'includes/class-content-brief.php';

        $id    = sanitize_key( wp_unslash( $_POST['id'] ?? '' ) );
        $brief = GateTouch_Content_Brief::from_library( $id );

        if ( ! $brief ) {
            wp_send_json_error( __( 'That brief is no longer saved. Generate it again.', 'gatetouch-ai-seo' ) );
        }

        $result = GateTouch_Content_Brief::create_draft( $brief );

        if ( isset( $result['error'] ) ) {
            wp_send_json_error( $result['error'] );
        }

        wp_send_json_success( $result );
    }

    public function ajax_fetch_link_data() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        require_once GATETOUCH_PATH . 'includes/class-link-assistant.php';
        $data = \GateTouch_Link_Assistant::get_site_links_summary();
        wp_send_json_success( $data );
    }

    public function ajax_setup_validate() {
        // Accept both nonces (setup wizard uses a different one)
        $nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
        if ( ! wp_verify_nonce( $nonce, 'gatetouch_ajax' ) && ! wp_verify_nonce( $nonce, 'gatetouch_setup_nonce' ) ) {
            wp_send_json_error( __( 'Security check failed.', 'gatetouch-ai-seo' ) );
        }
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $key      = sanitize_text_field( wp_unslash( $_POST['key']      ?? '' ) );
        $model    = sanitize_text_field( wp_unslash( $_POST['model']    ?? '' ) );
        $provider = sanitize_key( wp_unslash( $_POST['provider'] ?? 'openai' ) );

        if ( ! $key ) {
            $key = GateTouch_AI_Engine::get_key( $provider );
        } else {
            GateTouch_AI_Engine::update_key( $key, $provider );
            if ( $model ) {
                update_option( 'gatetouch_ai_model', $model );
            }
        }

        if ( ! $key ) {
            wp_send_json_error( __( 'API key is required.', 'gatetouch-ai-seo' ) );
        }

        $res = GateTouch_AI_Engine::validate_api_connection( $key, $provider );
        if ( $res['success'] ) {
            wp_send_json_success();
        } else {
            wp_send_json_error( $res['error'] );
        }
    }

    /**
     * Persist wizard state.
     *
     * Called on every step transition (finish=0) and on completion (finish=1).
     * Only keys actually present in the request are written, so advancing past a
     * step never clobbers a setting that step did not own.
     */
    public function ajax_complete_setup() {
        check_ajax_referer( 'gatetouch_setup_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        // ── Step 2: site identity → Organization entity in the schema graph ──
        if ( isset( $_POST['org_name'] ) || isset( $_POST['org_type'] ) || isset( $_POST['org_logo'] ) ) {
            $schema = get_option( 'gatetouch_schema_settings', [] );
            $schema = is_array( $schema ) ? $schema : [];

            if ( isset( $_POST['org_type'] ) ) {
                $org_type  = sanitize_text_field( wp_unslash( $_POST['org_type'] ) );
                $allowed   = [ 'Organization', 'Person', 'LocalBusiness', 'OnlineStore' ];
                $schema['org_type'] = in_array( $org_type, $allowed, true ) ? $org_type : 'Organization';
            }
            if ( isset( $_POST['org_name'] ) ) {
                $schema['org_name'] = sanitize_text_field( wp_unslash( $_POST['org_name'] ) );
            }
            if ( isset( $_POST['org_logo'] ) ) {
                $schema['org_logo'] = esc_url_raw( wp_unslash( $_POST['org_logo'] ) );
            }

            update_option( 'gatetouch_schema_settings', $schema );
        }

        // ── Step 3: search essentials ────────────────────────────────────────
        if ( isset( $_POST['sitemap_enabled'] ) ) {
            $sitemap = get_option( 'gatetouch_sitemap_settings', [] );
            $sitemap = is_array( $sitemap ) ? $sitemap : [];
            $sitemap['enabled'] = ( '1' === $_POST['sitemap_enabled'] ) ? 'yes' : 'no';
            update_option( 'gatetouch_sitemap_settings', $sitemap );
        }

        if ( isset( $_POST['breadcrumbs_enabled'] ) ) {
            $bc = get_option( 'gatetouch_breadcrumb_settings', [] );
            $bc = is_array( $bc ) ? $bc : [];
            $bc['enabled'] = ( '1' === $_POST['breadcrumbs_enabled'] ) ? '1' : '0';
            update_option( 'gatetouch_breadcrumb_settings', $bc );
        }

        if ( isset( $_POST['separator'] ) ) {
            $sep     = sanitize_text_field( wp_unslash( $_POST['separator'] ) );
            $allowed = [ '-', '|', '»', '•', '·', '—' ];
            if ( in_array( $sep, $allowed, true ) ) {
                // Search Appearance is the authority; the standalone option is a
                // legacy fallback that must be kept in step to avoid two truths.
                $sa = get_option( GateTouch_Search_Appearance::OPTION, [] );
                $sa = is_array( $sa ) ? $sa : [];
                $sa['global'] = isset( $sa['global'] ) && is_array( $sa['global'] ) ? $sa['global'] : [];
                $sa['global']['title_separator'] = $sep;
                update_option( GateTouch_Search_Appearance::OPTION, $sa );
                update_option( 'gatetouch_title_separator', $sep );
            }
        }

        // Only mark setup done when the wizard is actually finished or skipped.
        if ( ! empty( $_POST['finish'] ) ) {
            update_option( 'gatetouch_setup_completed', '1' );

            // Sitemap/robots rules may have been toggled — make them live now.
            GateTouch_Sitemap::register_rewrites();
            GateTouch_Robots::register_rewrites();
            flush_rewrite_rules();
        }

        wp_send_json_success();
    }

    public function ajax_reset_safe_mode() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        delete_option( 'gatetouch_api_error_count' );
        update_option( 'gatetouch_api_status', 'pending' );
        wp_send_json_success();
    }


    public function ajax_get_all_audit_ids() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $ids = get_posts( [
            'post_type'      => ['post', 'page', 'product'],
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ] );

        if ( empty($ids) ) wp_send_json_success( 0 );

        // Initialize progress tracker
        update_option( 'gatetouch_bulk_progress', [ 'total' => count($ids), 'current' => 0 ] );

        // Chunk into batches of 10 for safe background processing
        $chunks = array_chunk( $ids, 10 );
        foreach ( $chunks as $chunk ) {
            GateTouch_Queue::add( 'bulk_audit', [ 'post_ids' => $chunk ] );
        }

        wp_send_json_success( count($ids) );
    }

    public function ajax_get_audit_id_list() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $ids = get_posts( [
            'post_type'      => ['post', 'page', 'product'],
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ] );

        wp_send_json_success( $ids ?: [] );
    }

    public function ajax_audit_single_post() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
        if ( ! $post_id ) wp_send_json_error( __( 'No ID', 'gatetouch-ai-seo' ) );

        require_once GATETOUCH_PATH . 'includes/class-analysis.php';
        $analysis = \GateTouch_Analysis::analyze( $post_id );
        
        // Save score and checks to meta for fast retrieval later
        $meta = get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [];
        $meta['score'] = $analysis['score'];
        
        $all_checks = array_merge(
            isset($analysis['site_issues']) && is_array($analysis['site_issues']) ? $analysis['site_issues'] : [],
            isset($analysis['checks']) && is_array($analysis['checks']) ? $analysis['checks'] : []
        );
        $meta['checks'] = $all_checks;
        
        update_post_meta( $post_id, GATETOUCH_META_KEY, $meta );

        // Flush the audit transients to ensure fresh data on dashboard reload
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_gatetouch_audit_page_%'" );
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_gatetouch_audit_page_%'" );

        wp_send_json_success( $analysis );
    }

    public function ajax_get_debug_logs() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        
        require_once GATETOUCH_PATH . 'includes/class-logger.php';
        wp_send_json_success( GateTouch_Logger::get_logs() );
    }

    public function ajax_clear_debug_logs() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        
        require_once GATETOUCH_PATH . 'includes/class-logger.php';
        GateTouch_Logger::clear();
        wp_send_json_success();
    }

    public function ajax_optimize_db() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        
        require_once GATETOUCH_PATH . 'includes/class-performance-engine.php';
        $res = GateTouch_Performance_Engine::run_optimization();
        wp_send_json_success( $res );
    }

    public function ajax_bulk_queue() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        if ( ! GateTouch_AI_Engine::has_api_key() ) {
            wp_send_json_error( [
                'locked'       => true,
                'gate'         => 'api_key',
                'settings_url' => admin_url( 'admin.php?page=gatetouch-settings&tab=ai' ),
                'message'      => __( 'No API key configured. Add your AI provider key in Settings → AI & AEO.', 'gatetouch-ai-seo' ),
            ] );
        }

        $post_ids = array_map( 'intval', (array) ( $_POST['post_ids'] ?? [] ) );
        if ( empty( $post_ids ) ) wp_send_json_error( __( 'No posts selected.', 'gatetouch-ai-seo' ) );

        // Split into batches of 20 for the queue
        $batches = array_chunk( $post_ids, 20 );
        foreach ( $batches as $batch ) {
            GateTouch_Queue::add( 'bulk_meta', [ 'post_ids' => $batch ] );
        }

        update_option( 'gatetouch_bulk_progress', [ 'total' => count( $post_ids ), 'current' => 0 ] );
        wp_send_json_success();
    }

    public function ajax_bulk_progress() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );
        $progress = get_option( 'gatetouch_bulk_progress' );
        wp_send_json_success( $progress );
    }

    public function ajax_fetch_media_bulk() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $tab    = isset( $_POST['tab'] ) ? sanitize_text_field( wp_unslash( $_POST['tab'] ) ) : 'overview';
        $paged  = isset( $_POST['paged'] ) ? max( 1, (int) sanitize_text_field( wp_unslash( $_POST['paged'] ) ) ) : 1;
        $search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
        
        $args = [
            'post_type'      => 'attachment',
            'post_status'    => 'any',
            'post_mime_type' => 'image',
            'posts_per_page' => 20,
            'paged'          => $paged,
            's'              => $search,
            'orderby'        => 'date',
            'order'          => 'DESC'
        ];

        if ( $tab === 'missing' ) {
            $args['meta_query'] = [
                [
                    'key'     => '_wp_attachment_image_alt',
                    'value'   => '',
                    'compare' => '='
                ]
            ];
        }

        $query = new \WP_Query( $args );
        $items = [];

        foreach ( $query->posts as $post ) {
            $alt     = get_post_meta( $post->ID, '_wp_attachment_image_alt', true );
            $caption = $post->post_excerpt;
            $title   = $post->post_title;
            
            // Calculate a pseudo-SEO score
            $score = 0;
            if ( $alt ) $score += 40;
            if ( $title && ! strpos( $title, '-' ) && ! strpos( $title, '_' ) ) $score += 30;
            if ( $caption ) $score += 30;

            $items[] = [
                'id'       => $post->ID,
                'filename' => basename( get_attached_file( $post->ID ) ),
                'thumb'    => wp_get_attachment_thumb_url( $post->ID ),
                'alt'      => $alt,
                'title'    => $title,
                'caption'  => $caption,
                'size'     => size_format( @filesize( get_attached_file( $post->ID ) ) ?: 0 ),
                'date'     => get_the_date( 'M j, Y', $post->ID ),
                'score'    => $score
            ];
        }

        wp_send_json_success( [
            'items' => $items,
            'total' => $query->found_posts
        ] );
    }

    public function ajax_fetch_media_stats() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $total_images = count( get_posts( [ 'post_type' => 'attachment', 'post_status' => 'any', 'posts_per_page' => -1, 'post_mime_type' => 'image' ] ) );
        $missing_alt  = count( get_posts( [ 'post_type' => 'attachment', 'post_status' => 'any', 'posts_per_page' => -1, 'post_mime_type' => 'image', 'meta_query' => [ [ 'key' => '_wp_attachment_image_alt', 'value' => '', 'compare' => '=' ] ] ] ) );
        $optimized    = $total_images - $missing_alt;
        $health_score = $total_images > 0 ? round( ( $optimized / $total_images ) * 100 ) : 100;

        wp_send_json_success( [
            'total'     => $total_images,
            'missing'   => $missing_alt,
            'optimized' => $optimized,
            'health'    => $health_score
        ] );
    }

    public function ajax_update_media_meta() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $id      = isset( $_POST['attachment_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['attachment_id'] ) ) : 0;
        $alt     = isset( $_POST['alt'] ) ? sanitize_text_field( wp_unslash( $_POST['alt'] ) ) : '';
        $title   = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
        $caption = isset( $_POST['caption'] ) ? sanitize_text_field( wp_unslash( $_POST['caption'] ) ) : '';

        if ( ! $id ) {
            wp_send_json_error( __( 'Attachment ID is required.', 'gatetouch-ai-seo' ) );
        }

        update_post_meta( $id, '_wp_attachment_image_alt', $alt );
        
        wp_update_post( [
            'ID'           => $id,
            'post_title'   => $title,
            'post_excerpt' => $caption
        ] );

        // Calculate new score for instant UI feedback
        $score = 0;
        if ( $alt ) $score += 40;
        if ( $title && ! strpos( $title, '-' ) && ! strpos( $title, '_' ) ) $score += 30;
        if ( $caption ) $score += 30;

        wp_send_json_success( [
            'score'  => $score,
            'status' => $alt ? 'optimized' : 'missing'
        ] );
    }
}
