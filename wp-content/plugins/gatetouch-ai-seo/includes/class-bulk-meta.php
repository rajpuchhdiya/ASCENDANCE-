<?php
defined( 'ABSPATH' ) || exit;

/**
 * Handles bulk meta generation, inline edits, and bulk metadata cleanup.
 */
class GateTouch_Bulk_Meta {

    public function __construct() {
        add_action( 'wp_ajax_gatetouch_bulk_get_posts',      [ $this, 'ajax_get_posts' ] );
        add_action( 'wp_ajax_gatetouch_bulk_generate_one',   [ $this, 'ajax_generate_one' ] );
        add_action( 'wp_ajax_gatetouch_bulk_batch_generate', [ $this, 'ajax_batch_generate' ] );
        add_action( 'wp_ajax_gatetouch_bulk_save_inline',    [ $this, 'ajax_save_inline' ] );
        add_action( 'wp_ajax_gatetouch_bulk_delete_meta',    [ $this, 'ajax_delete_meta' ] );
    }

    public function ajax_get_posts() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $post_type = isset( $_POST['post_type'] ) ? sanitize_key( wp_unslash( $_POST['post_type'] ) ) : 'post';
        $filter    = isset( $_POST['filter'] ) ? sanitize_key( wp_unslash( $_POST['filter'] ) ) : 'all';
        $search    = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
        $paged     = isset( $_POST['paged'] ) ? max( 1, intval( wp_unslash( $_POST['paged'] ) ) ) : 1;
        $per_page  = 50; // Increased for enterprise bulk view

        $args = [
            'post_type'      => $post_type,
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        ];

        if ( $search ) {
            $args['s'] = $search;
        }

        // Support for Woo and other CPTs
        if ( 'product' === $post_type && ! post_type_exists( 'product' ) ) {
            wp_send_json_error( __( 'WooCommerce is not active.', 'gatetouch-ai-seo' ) );
        }

        $query = new WP_Query( $args );
        $total = $query->found_posts;
        $posts = $query->posts;

        $data = [];
        foreach ( $posts as $post ) {
            $meta    = get_post_meta( $post->ID, GATETOUCH_META_KEY, true ) ?: [];
            $has_meta = ! empty( $meta['meta_title'] ) && ! empty( $meta['meta_description'] );

            if ( $filter === 'missing' && $has_meta ) continue;
            if ( $filter === 'has_meta' && ! $has_meta ) continue;

            $clean_content = strip_shortcodes( wp_strip_all_tags( $post->post_content ) );
            $word_count    = count( preg_split( '/\s+/u', $clean_content, -1, PREG_SPLIT_NO_EMPTY ) );

            $data[] = [
                'id'                      => $post->ID,
                'title'                   => $post->post_title,
                'edit_url'                => get_edit_post_link( $post->ID, 'raw' ),
                'view_url'                => get_permalink( $post->ID ),
                'meta_title'              => $meta['meta_title']       ?? '',
                'meta_description'        => $meta['meta_description'] ?? '',
                'meta_title_parsed'       => GateTouch_Variables::parse( $meta['meta_title'] ?? '', $post->ID ),
                'meta_description_parsed' => GateTouch_Variables::parse( $meta['meta_description'] ?? '', $post->ID ),
                'focus_keyword'           => $meta['focus_keyword']    ?? '',
                'schema_type'             => $meta['schema_type']      ?? '',
                'has_meta'                => $has_meta,
                'word_count'              => $word_count,
                'modified'                => get_the_modified_date( 'Y-m-d', $post->ID ),
            ];
        }

        wp_send_json_success( [
            'posts'       => $data,
            'total'       => $total,
            'total_pages' => ceil( $total / $per_page ),
            'current'     => $paged,
        ] );
    }

    public function ajax_batch_generate() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $post_ids = isset( $_POST['post_ids'] ) && is_array( $_POST['post_ids'] ) ? array_map( 'intval', wp_unslash( $_POST['post_ids'] ) ) : [];
        if ( empty( $post_ids ) ) wp_send_json_error( __( 'No IDs provided.', 'gatetouch-ai-seo' ) );

        $results = [];
        foreach ( $post_ids as $post_id ) {
            $res = GateTouch_AI_Engine::generate_meta( $post_id );
            if ( isset( $res['error'] ) ) {
                $results[$post_id] = [ 'success' => false, 'error' => $res['error'] ];
                continue;
            }

            $meta = get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [];
            $merged = array_merge( $meta, array_filter( $res ) );
            update_post_meta( $post_id, GATETOUCH_META_KEY, $merged );

            require_once GATETOUCH_PATH . 'includes/class-analysis.php';
            \GateTouch_Analysis::analyze( $post_id, $merged['focus_keyword'] ?? '' );

            $results[$post_id] = [
                'success'                 => true,
                'meta_title'              => $merged['meta_title']       ?? '',
                'meta_description'        => $merged['meta_description'] ?? '',
                'meta_title_parsed'       => GateTouch_Variables::parse( $merged['meta_title'] ?? '', $post_id ),
                'meta_description_parsed' => GateTouch_Variables::parse( $merged['meta_description'] ?? '', $post_id ),
                'focus_keyword'           => $merged['focus_keyword']    ?? '',
            ];
        }

        wp_send_json_success( $results );
    }

    public function ajax_generate_one() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $post_id  = isset( $_POST['post_id'] ) ? intval( wp_unslash( $_POST['post_id'] ) ) : 0;
        $mode     = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'generate';
        $existing = isset( $_POST['existing'] ) && is_array( $_POST['existing'] ) ? array_map( 'sanitize_textarea_field', wp_unslash( $_POST['existing'] ) ) : [];

        if ( ! $post_id ) wp_send_json_error( __( 'Invalid post ID.', 'gatetouch-ai-seo' ) );

        if ( $mode === 'improve' ) {
            $result = GateTouch_AI_Engine::improve_meta( $post_id, (array) $existing );
        } else {
            $result = GateTouch_AI_Engine::generate_meta( $post_id );
        }

        if ( isset( $result['error'] ) ) wp_send_json_error( $result['error'] );

        $meta = get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [];
        $merged = array_merge( $meta, array_filter( $result ) );
        update_post_meta( $post_id, GATETOUCH_META_KEY, $merged );

        require_once GATETOUCH_PATH . 'includes/class-analysis.php';
        \GateTouch_Analysis::analyze( $post_id, $merged['focus_keyword'] ?? '' );

        wp_send_json_success( [
            'post_id'                 => $post_id,
            'meta_title'              => $merged['meta_title']       ?? '',
            'meta_description'        => $merged['meta_description'] ?? '',
            'meta_title_parsed'       => GateTouch_Variables::parse( $merged['meta_title'] ?? '', $post_id ),
            'meta_description_parsed' => GateTouch_Variables::parse( $merged['meta_description'] ?? '', $post_id ),
            'focus_keyword'           => $merged['focus_keyword']    ?? '',
            'mode'                    => $mode,
        ] );
    }

    public function ajax_save_inline() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $post_id = isset( $_POST['post_id'] ) ? intval( wp_unslash( $_POST['post_id'] ) ) : 0;
        $field   = isset( $_POST['field'] ) ? sanitize_key( wp_unslash( $_POST['field'] ) ) : '';
        $value   = sanitize_textarea_field( wp_unslash( $_POST['value'] ?? '' ) );

        if ( ! $post_id || ! $field ) wp_send_json_error( __( 'Invalid params.', 'gatetouch-ai-seo' ) );

        $allowed = [ 'meta_title', 'meta_description', 'focus_keyword', 'schema_type' ];
        if ( ! in_array( $field, $allowed, true ) ) wp_send_json_error( __( 'Field not allowed.', 'gatetouch-ai-seo' ) );

        $meta          = get_post_meta( $post_id, GATETOUCH_META_KEY, true ) ?: [];
        $meta[ $field ] = $value;
        update_post_meta( $post_id, GATETOUCH_META_KEY, $meta );

        require_once GATETOUCH_PATH . 'includes/class-analysis.php';
        \GateTouch_Analysis::analyze( $post_id, $meta['focus_keyword'] ?? '' );

        wp_send_json_success( [ 'saved' => true, 'post_id' => $post_id, 'field' => $field ] );
    }

    public function ajax_delete_meta() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $post_id = isset( $_POST['post_id'] ) ? intval( wp_unslash( $_POST['post_id'] ) ) : 0;
        if ( ! $post_id ) wp_send_json_error( __( 'Invalid post ID.', 'gatetouch-ai-seo' ) );

        delete_post_meta( $post_id, GATETOUCH_META_KEY );
        wp_send_json_success( [ 'deleted' => true ] );
    }
}
