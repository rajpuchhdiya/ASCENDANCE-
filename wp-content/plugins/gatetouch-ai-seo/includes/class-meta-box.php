<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Editor link suggestions exclude the current post from a small candidate set.
// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Editor guidance intentionally queries plugin metadata for related SEO context.

/**
 * Adds the GateTouch SEO editor panel and handles post-level AJAX actions.
 */
class GateTouch_Meta_Box {

    public function __construct() {
        add_action( 'add_meta_boxes',        [ $this, 'register' ] );
        add_action( 'save_post',             [ $this, 'save' ], 10, 2 );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ], 5 );

        add_action( 'wp_ajax_gatetouch_generate_meta', [ $this, 'ajax_generate' ] );
        add_action( 'wp_ajax_gatetouch_analyze_seo',   [ $this, 'ajax_analyze' ] );
        add_action( 'wp_ajax_gatetouch_generate_faq',  [ $this, 'ajax_faq' ] );
        add_action( 'wp_ajax_gatetouch_improve_meta',  [ $this, 'ajax_improve' ] );
        add_action( 'wp_ajax_gatetouch_save_meta_ajax', [ $this, 'ajax_save' ] );
        add_action( 'wp_ajax_gatetouch_fetch_links',    [ $this, 'ajax_links' ] );
        add_action( 'wp_ajax_gatetouch_smart_schema',   [ $this, 'ajax_smart_schema' ] );
        add_action( 'wp_ajax_gatetouch_ai_image',       [ $this, 'ajax_ai_image' ] );
        add_action( 'wp_ajax_gatetouch_ai_points',      [ $this, 'ajax_ai_points' ] );
        add_action( 'wp_ajax_gatetouch_ai_social',      [ $this, 'ajax_ai_social' ] );
        add_action( 'wp_ajax_gatetouch_analyze_headline', [ $this, 'ajax_headline_analyzer' ] );
        add_action( 'wp_ajax_gatetouch_optimize_content', [ $this, 'ajax_optimize_content' ] );
        add_action( 'wp_ajax_gatetouch_get_guidance',     [ $this, 'ajax_get_guidance' ] );

    }

    public function register() {
        $adv = get_option( 'gatetouch_advanced_settings', [] );
        if ( isset( $adv['tru_seo_score'] ) && $adv['tru_seo_score'] === '' ) return;

        $post_types = array_merge(
            [ 'post', 'page', 'product' ], // Explicitly include product
            array_keys( get_post_types( [ 'public' => true, '_builtin' => false ] ) )
        );
        
        foreach ( $post_types as $pt ) {
            add_meta_box(
                'gatetouch_meta_box',
                'GT SEO/GEO/AEO — Optimization Engine',
                [ $this, 'render' ], // Restore original PHP renderer
                $pt,
                'normal',
                'high',
                [ '__back_compat_meta_box' => false ]
            );
        }

    }

    public function render( \WP_Post $post ) {
        wp_nonce_field( 'gatetouch_save_meta', 'gatetouch_nonce' );
        $meta     = get_post_meta( $post->ID, GATETOUCH_META_KEY, true ) ?: [];
        // Always run analysis so that basic metrics like word count are calculated initially
        $analysis = GateTouch_Analysis::analyze( $post->ID, $meta['focus_keyword'] ?? '' );
        $has_key   = GateTouch_AI_Engine::is_api_operational();

        include GATETOUCH_PATH . 'admin/views/meta-box-template.php';
    }

    public function save( $post_id, \WP_Post $post ) {
        if ( ! isset( $_POST['gatetouch_nonce'] ) ) return;
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gatetouch_nonce'] ) ), 'gatetouch_save_meta' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;
        if ( wp_is_post_revision( $post_id ) ) return;

        $existing = get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [];

        $text_fields = [
            'meta_title', 'focus_keyword', 'canonical', 'og_title', 'og_image',
            'og_video', 'og_type', 'article_section', 'article_tags',
            'twitter_card', 'twitter_title', 'schema_type',
        ];
        foreach ( $text_fields as $f ) {
            if ( 'og_image' === $f ) {
                continue;
            }

            if ( isset( $_POST[ 'gatetouch_' . $f ] ) ) {
                $existing[ $f ] = sanitize_text_field( wp_unslash( $_POST[ 'gatetouch_' . $f ] ) );
            }
        }

        if ( isset( $_POST['gatetouch_og_image'] ) ) {
            $submitted_og_image = esc_url_raw( wp_unslash( $_POST['gatetouch_og_image'] ) );
            $fallback_og_image  = esc_url_raw( wp_unslash( $_POST['gatetouch_og_image_fallback'] ?? '' ) );
            $existing_og_image  = $existing['og_image'] ?? '';

            if ( $submitted_og_image === $fallback_og_image && empty( $existing_og_image ) ) {
                $existing['og_image'] = '';
            } else {
                $existing['og_image'] = $submitted_og_image;
            }
        }

        $textarea_fields = [ 'meta_description', 'og_description', 'twitter_description', 'custom_schema', 'key_points', 'social_posts' ];
        foreach ( $textarea_fields as $f ) {
            if ( isset( $_POST[ 'gatetouch_' . $f ] ) ) {
                $existing[ $f ] = sanitize_textarea_field( wp_unslash( $_POST[ 'gatetouch_' . $f ] ) );
            }
        }

        // Checkboxes (Cornerstone is now default ON for all important content)
        $existing['noindex']        = isset( $_POST['gatetouch_noindex'] ) ? '1' : '';
        $existing['nofollow']       = isset( $_POST['gatetouch_nofollow'] ) ? '1' : '';
        $existing['is_cornerstone'] = '1'; 
        $existing['breadcrumbs_enabled'] = isset( $_POST['gatetouch_breadcrumbs_enabled'] ) ? '1' : '';

        // Additional Keywords
        if ( isset( $_POST['gatetouch_additional_keywords'] ) ) {
            $existing['additional_keywords'] = sanitize_text_field( wp_unslash( $_POST['gatetouch_additional_keywords'] ) );
        }

        // FAQs
        $faq_q = array_map( 'sanitize_text_field', wp_unslash( (array) ( $_POST['gatetouch_faq_q'] ?? [] ) ) );
        $faq_a = array_map( 'sanitize_textarea_field', wp_unslash( (array) ( $_POST['gatetouch_faq_a'] ?? [] ) ) );
        $faqs  = [];
        foreach ( $faq_q as $i => $q ) {
            if ( ! empty( $q ) && ! empty( $faq_a[ $i ] ) ) {
                $faqs[] = [ 'question' => $q, 'answer' => $faq_a[ $i ] ];
            }
        }
        $existing['faqs'] = $faqs;

        update_post_meta( $post_id, GATETOUCH_META_KEY, $existing );

        // Trigger analysis update to ensure Site Audit and Columns are in sync
        require_once GATETOUCH_PATH . 'includes/class-analysis.php';
        GateTouch_Analysis::analyze( $post_id, $existing['focus_keyword'] ?? '' );
    }

    public function ajax_generate() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );
        if ( ! GateTouch_AI_Engine::is_api_operational() ) wp_send_json_error( __( 'AI API is not configured or in Safe Mode.', 'gatetouch-ai-seo' ) );

        $post_id = intval( $_POST['post_id'] ?? 0 );
        if ( ! $post_id ) wp_send_json_error( __( 'Invalid post ID.', 'gatetouch-ai-seo' ) );

        $result = GateTouch_AI_Engine::generate_meta( $post_id );
        if ( isset( $result['error'] ) ) wp_send_json_error( $result['error'] );

        $existing = get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [];

        // Map AI secondary_keywords to plugin additional_keywords
        if ( ! empty( $result['secondary_keywords'] ) && is_array( $result['secondary_keywords'] ) ) {
            $result['additional_keywords'] = implode( ', ', $result['secondary_keywords'] );
        }

        $merged = array_merge( $existing, array_filter( $result, function( $v ) { return $v !== null && $v !== ''; } ) );

        // Store the AI detected schema type for automation
        if ( ! empty( $result['schema_type'] ) ) {
            $merged['ai_detected_schema'] = $result['schema_type'];
        }

        update_post_meta( $post_id, GATETOUCH_META_KEY, $merged );

        // Apply AI-suggested post title so the focus keyword appears in the WP post title
        // (the SEO score checks post_title, not meta_title, for keyword presence).
        $new_post_title = '';
        if ( ! empty( $result['post_title_suggestion'] ) ) {
            $new_post_title = sanitize_text_field( $result['post_title_suggestion'] );
            wp_update_post( [
                'ID'         => $post_id,
                'post_title' => $new_post_title,
            ] );
        }

        // Apply slug suggestion for draft/pending posts only — avoid breaking live URLs.
        $post_status = get_post_status( $post_id );
        if ( ! empty( $result['slug_suggestion'] ) && in_array( $post_status, [ 'draft', 'pending', 'auto-draft' ], true ) ) {
            wp_update_post( [
                'ID'        => $post_id,
                'post_name' => sanitize_title( $result['slug_suggestion'] ),
            ] );
        }

        $analysis = GateTouch_Analysis::analyze( $post_id, $result['focus_keyword'] ?? '', [ 'content' => GateTouch_Analysis::capture_rendered_template( $post_id ) ] );
        wp_send_json_success( [ 'meta' => $merged, 'analysis' => $analysis, 'new_post_title' => $new_post_title ] );
    }

    public function ajax_analyze() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $post_id = intval( $_POST['post_id'] ?? 0 );
        $keyword = sanitize_text_field( wp_unslash( $_POST['keyword'] ?? '' ) );
        
        $args = [];
        // Prioritize rendered HTML from the browser (crucial for PHP templates)
        if ( ! empty( $_POST['rendered_html'] ) ) {
            $args['content'] = wp_kses_post( wp_unslash( $_POST['rendered_html'] ) );
        } elseif ( isset( $_POST['content'] ) ) {
            $args['content'] = wp_kses_post( wp_unslash( $_POST['content'] ) );
        }

        if ( isset( $_POST['title'] ) ) {
            $title = trim( sanitize_text_field( wp_unslash( $_POST['title'] ) ) );
            if ( $title !== '' ) {
                $args['title'] = $title;
            }
        }
        if ( isset( $_POST['featured_image_id'] ) ) {
            $featured_image_id = intval( $_POST['featured_image_id'] );
            if ( $featured_image_id > 0 ) {
                $args['featured_image_id'] = $featured_image_id;
            }
        }

        $result = GateTouch_Analysis::analyze( $post_id, $keyword, $args );
        wp_send_json_success( $result );
    }

    public function ajax_faq() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );
        if ( ! GateTouch_AI_Engine::is_api_operational() ) wp_send_json_error( __( 'AI API is not configured.', 'gatetouch-ai-seo' ) );

        $post_id = intval( $_POST['post_id'] ?? 0 );
        $result  = GateTouch_AI_Engine::generate_faq( $post_id );
        if ( isset( $result['error'] ) ) wp_send_json_error( $result['error'] );

        $existing         = get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [];
        $existing['faqs'] = $result['faqs'] ?? [];
        update_post_meta( $post_id, GATETOUCH_META_KEY, $existing );
        wp_send_json_success( $result );
    }

    public function ajax_improve() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );
        if ( ! GateTouch_AI_Engine::is_api_operational() ) wp_send_json_error( __( 'AI API is not configured.', 'gatetouch-ai-seo' ) );

        $post_id  = intval( $_POST['post_id'] ?? 0 );
        $existing = get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [];

        if ( empty( $existing['meta_title'] ) && empty( $existing['meta_description'] ) ) {
            wp_send_json_error( __( 'No existing meta to improve. Use Generate first.', 'gatetouch-ai-seo' ) );
        }

        $result = GateTouch_AI_Engine::improve_meta( $post_id, $existing );
        if ( isset( $result['error'] ) ) wp_send_json_error( $result['error'] );

        $merged = array_merge( $existing, array_filter( $result, function($v) { return $v !== null && $v !== ''; } ) );
        update_post_meta( $post_id, GATETOUCH_META_KEY, $merged );
        wp_send_json_success( $merged );
    }

    public function ajax_links() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $post_id = intval( $_POST['post_id'] ?? 0 );
        $content = wp_kses_post( wp_unslash( $_POST['content'] ?? '' ) );

        // Get recent posts to suggest from
        $recent = get_posts( [
            'post_type'      => [ 'post', 'page' ],
            'post_status'    => 'publish',
            'posts_per_page' => 25,
            'post__not_in'   => [ $post_id ],
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );

        $site_posts = array_map( function($p) { return [ 'id' => $p->ID, 'title' => $p->post_title ]; }, $recent );
        
        $result = GateTouch_AI_Engine::find_internal_links( $content, $site_posts );
        if ( isset( $result['error'] ) ) wp_send_json_error( $result['error'] );

        // Add permalinks to results
        if ( ! empty( $result['suggestions'] ) ) {
            foreach ( $result['suggestions'] as &$s ) {
                $s['url'] = get_permalink( $s['post_id'] );
            }
        }

        wp_send_json_success( $result );
    }

    public function ajax_smart_schema() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $post_id = intval( $_POST['post_id'] ?? 0 );
        $result  = GateTouch_AI_Engine::generate_advanced_schema( $post_id );
        
        if ( isset( $result['error'] ) ) wp_send_json_error( $result['error'] );

        $existing = get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [];
        $existing['schema_type']   = $result['detected_type'] ?? 'Article';
        $existing['custom_schema'] = wp_json_encode( $result['schema_json'] ?? [], JSON_PRETTY_PRINT );
        
        update_post_meta( $post_id, GATETOUCH_META_KEY, $existing );
        wp_send_json_success( $existing );
    }

    public function ajax_save() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $post_id = isset( $_POST['post_id'] ) ? intval( wp_unslash( $_POST['post_id'] ) ) : 0;
        $field   = isset( $_POST['field'] ) ? sanitize_key( wp_unslash( $_POST['field'] ) ) : '';
        $value   = isset( $_POST['value'] ) ? sanitize_textarea_field( wp_unslash( $_POST['value'] ) ) : '';

        if ( ! $post_id || ! $field ) wp_send_json_error( __( 'Missing data.', 'gatetouch-ai-seo' ) );

        $existing = get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [];
        
        $textarea_fields = [ 'meta_description', 'og_description', 'twitter_description', 'custom_schema', 'key_points', 'social_posts' ];
        
        if ( 'is_cornerstone' === $field ) {
            $existing[ $field ] = ( '1' === $value || 'true' === $value ) ? '1' : '';
        } elseif ( in_array( $field, $textarea_fields ) ) {
            $existing[ $field ] = sanitize_textarea_field( $value );
        } else {
            $existing[ $field ] = sanitize_text_field( $value );
        }

        update_post_meta( $post_id, GATETOUCH_META_KEY, $existing );
        wp_send_json_success();
    }

    public function enqueue( $hook ) {
        if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) return;

        $adv = get_option( 'gatetouch_advanced_settings', [] );
        if ( isset( $adv['tru_seo_score'] ) && $adv['tru_seo_score'] === '' ) return;

        wp_enqueue_media();
        wp_enqueue_style( 'gatetouch-meta-box', GATETOUCH_URL . 'assets/css/meta-box.css', [], GATETOUCH_VERSION );

        // Restore Original Scripts
        wp_enqueue_script(
            'gatetouch-meta-box',
            GATETOUCH_URL . 'assets/js/meta-box.js',
            [ 'jquery', 'lodash', 'wp-i18n', 'wp-api-fetch' ],
            GATETOUCH_VERSION,
            true
        );

        $this->localize_scripts( 'gatetouch-meta-box' );
    }

    /**
     * Enqueue Elementor-specific integration
     */
    public function enqueue_elementor() {
        // ...
    }

    /**
     * Centralized script localization
     */
    private function localize_scripts( $handle ) {
        wp_localize_script( $handle, 'gatetouchData', [
            'ajax_url'    => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'gatetouch_ajax' ),
            'post_id'     => get_the_ID(),
            'meta_key'    => GATETOUCH_META_KEY,
            'has_api_key' => GateTouch_AI_Engine::is_api_operational() ? '1' : '0',
            'api_valid'   => GateTouch_AI_Engine::is_api_valid() ? '1' : '0',
            'strings'     => [
                'generating'  => __( 'AI is generating your meta tags...', 'gatetouch-ai-seo' ),
                'improving'   => __( 'AI is improving your meta tags...', 'gatetouch-ai-seo' ),
                'analyzing'   => __( 'Running SEO analysis...', 'gatetouch-ai-seo' ),
                'extracting'  => __( 'AI is extracting FAQs...', 'gatetouch-ai-seo' ),
                'done'        => __( 'Done! ✓', 'gatetouch-ai-seo' ),
                'error'       => __( 'An error occurred. Check your API key.', 'gatetouch-ai-seo' ),
                'no_api_key'  => __( 'Add your AI provider API key in GT SEO/GEO/AEO → AI Settings first.', 'gatetouch-ai-seo' ),
                'confirm_del' => __( 'Remove this FAQ?', 'gatetouch-ai-seo' ),
            ],
        ] );
    }

    public function ajax_ai_image() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );
        $prompt = isset( $_POST['prompt'] ) ? sanitize_text_field( wp_unslash( $_POST['prompt'] ) ) : '';
        if ( ! $prompt ) wp_send_json_error( __( 'Prompt is required.', 'gatetouch-ai-seo' ) );
        $result = GateTouch_AI_Engine::generate_ai_image( $prompt );
        if ( isset( $result['error'] ) ) wp_send_json_error( $result['error'] );
        wp_send_json_success( $result );
    }

    public function ajax_ai_points() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        $post_id = isset( $_POST['post_id'] ) ? intval( wp_unslash( $_POST['post_id'] ) ) : 0;
        if ( ! current_user_can( 'edit_post', $post_id ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );
        $result = GateTouch_AI_Engine::generate_key_points( $post_id );
        if ( isset( $result['error'] ) ) wp_send_json_error( $result['error'] );
        wp_send_json_success( $result );
    }

    public function ajax_ai_social() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        $post_id = intval( $_POST['post_id'] ?? 0 );
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );
        }
        $result = GateTouch_AI_Engine::generate_social_posts( $post_id );
        
        if ( isset( $result['error'] ) ) wp_send_json_error( $result['error'] );

        // Persist to meta for the Social AI dashboard
        $meta = get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [];
        $meta['social_ai'] = [
            'linkedin' => $result['linkedin'] ?? '',
            'facebook' => $result['facebook'] ?? '',
            'twitter'  => $result['twitter'] ?? '',
            'last_gen' => time()
        ];
        update_post_meta( $post_id, GATETOUCH_META_KEY, $meta );

        wp_send_json_success( $result );
    }

    public function ajax_headline_analyzer() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $headline = isset( $_POST['headline'] ) ? sanitize_text_field( wp_unslash( $_POST['headline'] ) ) : '';
        if ( ! $headline ) wp_send_json_error( __( 'Headline is required.', 'gatetouch-ai-seo' ) );

        $result = GateTouch_AI_Engine::analyze_headline( $headline );
        if ( isset( $result['error'] ) ) wp_send_json_error( $result['error'] );

        wp_send_json_success( $result );
    }

    public function ajax_optimize_content() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $post_id = isset( $_POST['post_id'] ) ? intval( wp_unslash( $_POST['post_id'] ) ) : 0;
        $content = wp_kses_post( wp_unslash( $_POST['content'] ?? '' ) );
        $keyword = sanitize_text_field( wp_unslash( $_POST['keyword'] ?? '' ) );

        if ( ! $content ) wp_send_json_error( __( 'Content is required.', 'gatetouch-ai-seo' ) );

        $result = GateTouch_AI_Engine::optimize_content( $content, $keyword );
        if ( isset( $result['error'] ) ) wp_send_json_error( $result['error'] );

        wp_send_json_success( $result );
    }

    public function ajax_get_meta() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        $post_id = intval( $_POST['post_id'] ?? 0 );
        if ( ! $post_id ) wp_send_json_error();
        if ( ! current_user_can( 'edit_post', $post_id ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $meta = get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [];
        wp_send_json_success( $meta );
    }

    public function ajax_save_bulk() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error();

        $post_id = isset( $_POST['post_id'] ) ? intval( wp_unslash( $_POST['post_id'] ) ) : 0;
        $meta    = isset( $_POST['meta'] ) && is_array( $_POST['meta'] ) ? map_deep( wp_unslash( $_POST['meta'] ), 'sanitize_textarea_field' ) : [];
        if ( ! $post_id ) wp_send_json_error();

        // Security: Sanitize incoming data
        $sanitized = [];
        foreach ( $meta as $k => $v ) {
            $sanitized[ sanitize_key( $k ) ] = sanitize_textarea_field( $v );
        }

        update_post_meta( $post_id, GATETOUCH_META_KEY, $sanitized );
        wp_send_json_success();
    }

    public function ajax_get_guidance() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'edit_posts' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );
        $key = isset( $_POST['key'] ) ? sanitize_key( wp_unslash( $_POST['key'] ) ) : '';
        if ( ! $key ) wp_send_json_error();

        $guidance = GateTouch_SEO_Library::get_guidance( $key );
        wp_send_json_success( $guidance );
    }

}
