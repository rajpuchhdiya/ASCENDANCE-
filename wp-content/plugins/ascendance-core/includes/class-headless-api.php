<?php
/**
 * Phase 8 — Headless REST API Controller
 *
 * Class Headless_API
 * Package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Headless_API {

    /**
     * Singleton instance
     * @var Headless_API|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
    }

    /**
     * Register Headless REST Routes
     */
    public function register_rest_routes() {
        // 1. Headless Content Payload
        register_rest_route( 'ascendance/v1', '/headless/content', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rest_get_content' ),
            'permission_callback' => '__return_true',
        ) );

        // 2. Headless Paywall Decision Engine
        register_rest_route( 'ascendance/v1', '/headless/paywall', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rest_check_paywall' ),
            'permission_callback' => '__return_true',
        ) );

        // 3. Headless Entity Directory & Relationship Graph
        register_rest_route( 'ascendance/v1', '/headless/entities', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_entities' ),
            'permission_callback' => '__return_true',
        ) );

        // 4. Headless Advisory Client Portal Payload
        register_rest_route( 'ascendance/v1', '/headless/advisory', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_advisory' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ) );
    }

    /**
     * REST Endpoint: POST /ascendance/v1/headless/content
     */
    public function rest_get_content( \WP_REST_Request $request ) {
        $post_id = (int) $request->get_param( 'post_id' );
        $slug    = sanitize_title( $request->get_param( 'slug' ) );

        if ( ! $post_id && $slug ) {
            $post_obj = get_page_by_path( $slug, OBJECT, array( 'brief', 'dossier', 'update', 'entity', 'page' ) );
            if ( $post_obj ) $post_id = $post_obj->ID;
        }

        $post = get_post( $post_id );
        if ( ! $post || 'publish' !== $post->post_status ) {
            return new \WP_REST_Response( array( 'error' => 'Post not found' ), 404 );
        }

        $user_id = get_current_user_id();
        $paywall = class_exists( 'Ascendance\Core\Paywall' ) ? Paywall::get_instance() : null;
        $access  = $paywall ? $paywall->check_access( $post_id, $user_id ) : array( 'allowed' => true, 'reason' => 'allowed_public' );

        $payload = array(
            'id'            => $post->ID,
            'slug'          => $post->post_name,
            'title'         => get_the_title( $post->ID ),
            'post_type'     => $post->post_type,
            'canonical_url' => get_permalink( $post->ID ),
            'published_at'  => $post->post_date_gmt,
            'tier_access'   => get_post_meta( $post->ID, 'tier_access', true ) ?: 'public',
            'paywall_state' => $access,
            'public_teaser' => get_post_meta( $post->ID, 'public_excerpt', true ) ?: wp_strip_all_tags( $post->post_excerpt ),
        );

        // Serve full content only if allowed by Paywall Decision Engine
        if ( $access['allowed'] ) {
            $payload['content'] = apply_filters( 'the_content', $post->post_content );
        } else {
            $payload['content'] = null;
        }

        return new \WP_REST_Response( array( 'success' => true, 'data' => $payload ), 200 );
    }

    /**
     * REST Endpoint: POST /ascendance/v1/headless/paywall
     */
    public function rest_check_paywall( \WP_REST_Request $request ) {
        $post_id = (int) $request->get_param( 'post_id' );
        $user_id = get_current_user_id();

        if ( ! $post_id ) {
            return new \WP_REST_Response( array( 'error' => 'Missing post_id' ), 400 );
        }

        $paywall = class_exists( 'Ascendance\Core\Paywall' ) ? Paywall::get_instance() : null;
        $decision = $paywall ? $paywall->check_access( $post_id, $user_id ) : array( 'allowed' => true, 'reason' => 'allowed_public' );

        return new \WP_REST_Response( array( 'success' => true, 'decision' => $decision ), 200 );
    }

    /**
     * REST Endpoint: GET /ascendance/v1/headless/entities
     */
    public function rest_get_entities( \WP_REST_Request $request ) {
        $posts = get_posts( array( 'post_type' => 'entity', 'posts_per_page' => 100, 'post_status' => 'publish' ) );
        $entity_mgr = class_exists( 'Ascendance\Core\Entity_Intelligence' ) ? Entity_Intelligence::get_instance() : null;

        $data = array();
        foreach ( $posts as $p ) {
            $rels = $entity_mgr ? $entity_mgr->get_entity_relationships( $p->ID ) : array();
            $data[] = array(
                'id'            => $p->ID,
                'slug'          => $p->post_name,
                'name'          => $p->post_title,
                'official_name' => get_post_meta( $p->ID, 'official_name', true ) ?: $p->post_title,
                'country'       => get_post_meta( $p->ID, 'country', true ) ?: 'Global',
                'relationships' => $rels,
            );
        }

        return new \WP_REST_Response( array( 'success' => true, 'entities' => $data ), 200 );
    }

    /**
     * REST Endpoint: GET /ascendance/v1/headless/advisory
     */
    public function rest_get_advisory( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $advisory = class_exists( 'Ascendance\Core\Advisory_Portal' ) ? Advisory_Portal::get_instance() : null;

        if ( ! $advisory ) {
            return new \WP_REST_Response( array( 'error' => 'Advisory Portal unavailable' ), 500 );
        }

        $client_id = $advisory->get_user_client_id( $user_id );
        if ( ! $client_id && ! current_user_can( 'manage_options' ) ) {
            return new \WP_REST_Response( array( 'error' => 'Unauthorized' ), 403 );
        }

        $engagements = get_posts( array( 'post_type' => 'advisory_engagement', 'posts_per_page' => 20, 'meta_key' => 'client_id', 'meta_value' => $client_id ) );
        $eng_data = array();
        foreach ( $engagements as $e ) {
            $eng_data[] = array(
                'id'     => $e->ID,
                'title'  => $e->post_title,
                'status' => get_post_meta( $e->ID, 'engagement_status', true ) ?: 'active',
            );
        }

        return new \WP_REST_Response( array( 'success' => true, 'client_id' => $client_id, 'engagements' => $eng_data ), 200 );
    }
}
