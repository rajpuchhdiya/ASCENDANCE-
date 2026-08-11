<?php
/**
 * Phase 6 — Secure Advisory Client Portal Architecture
 *
 * Class Advisory_Portal
 * Package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Advisory_Portal {

    /**
     * Singleton instance
     * @var Advisory_Portal|null
     */
    private static $instance = null;

    /**
     * Secret key salt for token signing
     */
    private $secret_salt = '';

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
        $this->secret_salt = defined( 'AUTH_KEY' ) ? AUTH_KEY : 'asc_advisory_secret_key_2026';
        $this->init_hooks();
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'register_cpts' ) );
        add_action( 'init', array( $this, 'add_portal_rewrite_rules' ) );
        add_filter( 'query_vars', array( $this, 'register_query_vars' ) );
        add_action( 'template_redirect', array( $this, 'handle_portal_routing' ) );
        add_action( 'wp_head', array( $this, 'inject_portal_security_headers' ) );
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
    }

    /**
     * Register Custom Post Types for Advisory Portal
     */
    public function register_cpts() {
        // 1. Advisory Client CPT
        register_post_type( 'advisory_client', array(
            'labels'              => array(
                'name'          => __( 'Advisory Clients', 'ascendance-core' ),
                'singular_name' => __( 'Advisory Client', 'ascendance-core' ),
            ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'ascendance-mission-control',
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'supports'            => array( 'title', 'editor', 'custom-fields' ),
            'has_archive'         => false,
            'rewrite'             => false,
        ) );

        // 2. Advisory Engagement CPT
        register_post_type( 'advisory_engagement', array(
            'labels'              => array(
                'name'          => __( 'Advisory Engagements', 'ascendance-core' ),
                'singular_name' => __( 'Advisory Engagement', 'ascendance-core' ),
            ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'ascendance-mission-control',
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'supports'            => array( 'title', 'editor', 'custom-fields' ),
            'has_archive'         => false,
            'rewrite'             => false,
        ) );

        // 3. Advisory Document Vault CPT
        register_post_type( 'advisory_doc', array(
            'labels'              => array(
                'name'          => __( 'Vault Documents', 'ascendance-core' ),
                'singular_name' => __( 'Vault Document', 'ascendance-core' ),
            ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'ascendance-mission-control',
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'supports'            => array( 'title', 'editor', 'custom-fields' ),
            'has_archive'         => false,
            'rewrite'             => false,
        ) );

        // 4. Advisory Deliverable CPT
        register_post_type( 'advisory_deliverable', array(
            'labels'              => array(
                'name'          => __( 'Advisory Deliverables', 'ascendance-core' ),
                'singular_name' => __( 'Advisory Deliverable', 'ascendance-core' ),
            ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'ascendance-mission-control',
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'supports'            => array( 'title', 'editor', 'custom-fields' ),
            'has_archive'         => false,
            'rewrite'             => false,
        ) );
    }

    /**
     * Add rewrite rules for /advisory-portal/
     */
    public function add_portal_rewrite_rules() {
        add_rewrite_rule( '^advisory-portal(?:/([^/]+))?/?$', 'index.php?advisory_portal_page=$matches[1]', 'top' );
    }

    /**
     * Register query vars
     */
    public function register_query_vars( $vars ) {
        $vars[] = 'advisory_portal_page';
        return $vars;
    }

    /**
     * Handle template redirect and security headers for /advisory-portal/
     */
    public function handle_portal_routing() {
        $page = get_query_var( 'advisory_portal_page' );
        if ( $page !== false && ( is_page( 'advisory-portal' ) || strpos( $_SERVER['REQUEST_URI'], '/advisory-portal' ) !== false ) ) {
            // Private Cache Control Headers
            if ( ! headers_sent() ) {
                header( 'Cache-Control: private, no-cache, no-store, must-revalidate' );
                header( 'Pragma: no-cache' );
                header( 'Expires: 0' );
            }

            // Authentication Check
            if ( ! is_user_logged_in() ) {
                wp_safe_redirect( wp_login_url( home_url( '/advisory-portal/' ) ) );
                exit;
            }

            // Authorization Check
            $user_id   = get_current_user_id();
            $client_id = $this->get_user_client_id( $user_id );
            if ( ! $client_id && ! current_user_can( 'manage_options' ) ) {
                $this->log_audit_event( $user_id, 0, 0, 0, 'access_denied', 'User lacks advisory client membership' );
                wp_die( __( 'Access Denied: You do not have authorization to access the Advisory Client Portal.', 'ascendance-core' ), 'Access Denied', array( 'response' => 403 ) );
            }
        }
    }

    /**
     * Inject Security Headers & Robots Metadata for Portal
     */
    public function inject_portal_security_headers() {
        if ( strpos( $_SERVER['REQUEST_URI'], '/advisory-portal' ) !== false ) {
            echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Authorization & Membership Helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Get Client ID for a user
     */
    public function get_user_client_id( $user_id ) {
        if ( ! $user_id ) return 0;
        return (int) get_user_meta( $user_id, 'asc_advisory_client_id', true );
    }

    /**
     * Assign User to Advisory Client
     */
    public function assign_user_to_client( $user_id, $client_id, $role = 'client_viewer' ) {
        update_user_meta( $user_id, 'asc_advisory_client_id', (int) $client_id );
        update_user_meta( $user_id, 'asc_advisory_role', sanitize_text_field( $role ) );
        $this->log_audit_event( $user_id, $client_id, 0, 0, 'user_assigned', "Assigned role $role" );
        return true;
    }

    /**
     * Check if user can access client
     */
    public function user_can_access_client( $user_id, $client_id ) {
        if ( current_user_can( 'manage_options' ) ) return true;
        $user_client = $this->get_user_client_id( $user_id );
        return ( $user_client === (int) $client_id && $client_id > 0 );
    }

    /**
     * Check if user can access engagement
     */
    public function user_can_access_engagement( $user_id, $engagement_id ) {
        if ( current_user_can( 'manage_options' ) ) return true;
        $eng_client_id = (int) get_post_meta( $engagement_id, 'client_id', true );
        return $this->user_can_access_client( $user_id, $eng_client_id );
    }

    /**
     * Check if user can access document
     */
    public function user_can_access_document( $user_id, $doc_id ) {
        if ( current_user_can( 'manage_options' ) ) return true;
        $eng_id = (int) get_post_meta( $doc_id, 'engagement_id', true );
        if ( ! $eng_id ) return false;
        
        // Check document expiration
        $expiry = get_post_meta( $doc_id, 'expiry_date', true );
        if ( ! empty( $expiry ) && strtotime( $expiry ) < time() ) {
            return false;
        }

        return $this->user_can_access_engagement( $user_id, $eng_id );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Protected Download Tokens & Watermarking
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Generate Cryptographically Signed Single-Use Download Token
     */
    public function generate_download_token( $user_id, $doc_id ) {
        if ( ! $this->user_can_access_document( $user_id, $doc_id ) ) {
            return false;
        }

        $eng_id    = (int) get_post_meta( $doc_id, 'engagement_id', true );
        $client_id = (int) get_post_meta( $eng_id, 'client_id', true );
        $time      = time();
        $random    = wp_generate_password( 16, false );

        $payload = implode( '|', array( $user_id, $client_id, $eng_id, $doc_id, $time, $random ) );
        $sig     = hash_hmac( 'sha256', $payload, $this->secret_salt );
        $token   = 'asc_tok_' . md5( $payload . $sig );

        $token_data = array(
            'user_id'       => $user_id,
            'client_id'     => $client_id,
            'engagement_id' => $eng_id,
            'doc_id'        => $doc_id,
            'created'       => $time,
            'signature'     => $sig,
        );

        set_transient( $token, $token_data, 15 * MINUTE_IN_SECONDS );
        $this->log_audit_event( $user_id, $client_id, $eng_id, $doc_id, 'token_generated', 'Generated 15-min download token' );

        return $token;
    }

    /**
     * Verify & Consume Download Token (Single-Use)
     */
    public function verify_and_consume_token( $token, $doc_id, $user_id ) {
        $data = get_transient( $token );
        if ( ! $data || ! is_array( $data ) ) {
            return false;
        }

        // Validate Token Parameters
        if ( (int) $data['user_id'] !== (int) $user_id || (int) $data['doc_id'] !== (int) $doc_id ) {
            $this->log_audit_event( $user_id, 0, 0, $doc_id, 'token_failed', 'Token parameter mismatch' );
            return false;
        }

        // Delete Token Immediately (Single-Use Enforcement)
        delete_transient( $token );

        // Log Audit Download Event
        $this->log_audit_event( $user_id, $data['client_id'], $data['engagement_id'], $doc_id, 'document_downloaded', 'Token verified & document downloaded' );

        return $data;
    }

    /**
     * Generate Client Watermark String
     */
    public function generate_watermark_text( $client_id, $engagement_id, $user_id ) {
        $client_name = get_the_title( $client_id ) ?: 'Advisory Client';
        $eng_title   = get_the_title( $engagement_id ) ?: 'Advisory Engagement';
        $user_obj    = get_userdata( $user_id );
        $user_email  = $user_obj ? $user_obj->user_email : 'user@client.com';
        $timestamp   = date( 'Y-m-d H:i:s T' );

        return sprintf( "CONFIDENTIAL — Prepared exclusively for %s | Engagement: %s | User: %s | %s", $client_name, $eng_title, $user_email, $timestamp );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Single-Use Magic Link Invitation Engine
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Generate Magic Link Token (24 Hours TTL)
     */
    public function generate_magic_link( $user_id, $engagement_id = 0 ) {
        $client_id = $this->get_user_client_id( $user_id );
        if ( ! $client_id ) return false;

        $time   = time();
        $random = wp_generate_password( 24, false );
        $sig    = hash_hmac( 'sha256', $user_id . '|' . $time . '|' . $random, $this->secret_salt );
        $token  = 'asc_magic_' . md5( $sig );

        $magic_data = array(
            'user_id'       => $user_id,
            'client_id'     => $client_id,
            'engagement_id' => $engagement_id,
            'created'       => $time,
        );

        set_transient( $token, $magic_data, DAY_IN_SECONDS );
        $this->log_audit_event( $user_id, $client_id, $engagement_id, 0, 'magic_link_created', 'Generated 24-hr magic link' );

        return home_url( '/advisory-portal/?magic_token=' . $token );
    }

    /**
     * Verify Magic Link Token & Authenticate (Single-Use)
     */
    public function verify_and_consume_magic_link( $token ) {
        $data = get_transient( $token );
        if ( ! $data || ! is_array( $data ) ) {
            return false;
        }

        // Single-Use Enforcement
        delete_transient( $token );

        $user_id = (int) $data['user_id'];
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );

        $this->log_audit_event( $user_id, $data['client_id'], $data['engagement_id'], 0, 'magic_link_used', 'Authenticated via magic link' );

        return $data;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Audit Logging Engine
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Log Audit Event to DB Options Queue
     */
    public function log_audit_event( $user_id, $client_id, $engagement_id, $doc_id, $action, $details = '' ) {
        $logs = get_option( 'asc_advisory_audit_logs', array() );
        if ( ! is_array( $logs ) ) $logs = array();

        $entry = array(
            'timestamp'     => current_time( 'mysql' ),
            'user_id'       => (int) $user_id,
            'client_id'     => (int) $client_id,
            'engagement_id' => (int) $engagement_id,
            'doc_id'        => (int) $doc_id,
            'action'        => sanitize_text_field( $action ),
            'details'       => sanitize_text_field( $details ),
            'ip'            => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( $_SERVER['REMOTE_ADDR'] ) : '127.0.0.1',
        );

        array_unshift( $logs, $entry );
        if ( count( $logs ) > 500 ) {
            $logs = array_slice( $logs, 0, 500 );
        }

        update_option( 'asc_advisory_audit_logs', $logs, false );
    }

    /**
     * Get Audit Logs
     */
    public function get_audit_logs( $client_id = 0, $limit = 50 ) {
        $logs = get_option( 'asc_advisory_audit_logs', array() );
        if ( ! is_array( $logs ) ) return array();

        if ( $client_id > 0 ) {
            $filtered = array();
            foreach ( $logs as $l ) {
                if ( (int) $l['client_id'] === (int) $client_id ) {
                    $filtered[] = $l;
                }
            }
            $logs = $filtered;
        }

        return array_slice( $logs, 0, $limit );
    }

    // ──────────────────────────────────────────────────────────────────────────
    // REST API Routes
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Register REST API routes
     */
    public function register_rest_routes() {
        register_rest_route( 'ascendance/v1', '/advisory/clients', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_clients' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ) );

        register_rest_route( 'ascendance/v1', '/advisory/engagements', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_engagements' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ) );

        register_rest_route( 'ascendance/v1', '/advisory/documents', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_documents' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ) );

        register_rest_route( 'ascendance/v1', '/advisory/documents/token', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rest_create_token' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ) );

        register_rest_route( 'ascendance/v1', '/advisory/deliverables', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_deliverables' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ) );

        register_rest_route( 'ascendance/v1', '/advisory/audit-logs', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_audit_logs' ),
            'permission_callback' => function() { return is_user_logged_in(); },
        ) );
    }

    /**
     * REST Endpoint: GET /ascendance/v1/advisory/clients
     */
    public function rest_get_clients( \WP_REST_Request $request ) {
        $user_id   = get_current_user_id();
        $client_id = $this->get_user_client_id( $user_id );

        if ( ! $client_id && ! current_user_can( 'manage_options' ) ) {
            return new \WP_REST_Response( array( 'error' => 'Unauthorized' ), 403 );
        }

        $query_args = array( 'post_type' => 'advisory_client', 'posts_per_page' => 20 );
        if ( ! current_user_can( 'manage_options' ) ) {
            $query_args['p'] = $client_id;
        }

        $posts = get_posts( $query_args );
        $data  = array();
        foreach ( $posts as $p ) {
            $data[] = array(
                'id'       => $p->ID,
                'name'     => $p->post_title,
                'status'   => get_post_meta( $p->ID, 'client_status', true ) ?: 'active',
                'tier'     => get_post_meta( $p->ID, 'advisory_tier', true ) ?: 'enterprise',
            );
        }

        return new \WP_REST_Response( array( 'success' => true, 'clients' => $data ), 200 );
    }

    /**
     * REST Endpoint: GET /ascendance/v1/advisory/engagements
     */
    public function rest_get_engagements( \WP_REST_Request $request ) {
        $user_id   = get_current_user_id();
        $client_id = $this->get_user_client_id( $user_id );

        if ( ! $client_id && ! current_user_can( 'manage_options' ) ) {
            return new \WP_REST_Response( array( 'error' => 'Unauthorized' ), 403 );
        }

        $posts = get_posts( array(
            'post_type'      => 'advisory_engagement',
            'posts_per_page' => 50,
            'meta_key'       => 'client_id',
            'meta_value'     => $client_id,
        ) );

        $data = array();
        foreach ( $posts as $p ) {
            $data[] = array(
                'id'          => $p->ID,
                'title'       => $p->post_title,
                'status'      => get_post_meta( $p->ID, 'engagement_status', true ) ?: 'active',
                'start_date'  => get_post_meta( $p->ID, 'start_date', true ) ?: date( 'Y-m-d' ),
            );
        }

        return new \WP_REST_Response( array( 'success' => true, 'engagements' => $data ), 200 );
    }

    /**
     * REST Endpoint: GET /ascendance/v1/advisory/documents
     */
    public function rest_get_documents( \WP_REST_Request $request ) {
        $user_id   = get_current_user_id();
        $client_id = $this->get_user_client_id( $user_id );

        if ( ! $client_id && ! current_user_can( 'manage_options' ) ) {
            return new \WP_REST_Response( array( 'error' => 'Unauthorized' ), 403 );
        }

        $doc_posts = get_posts( array( 'post_type' => 'advisory_doc', 'posts_per_page' => 100 ) );
        $data = array();
        foreach ( $doc_posts as $d ) {
            if ( $this->user_can_access_document( $user_id, $d->ID ) ) {
                $eng_id = (int) get_post_meta( $d->ID, 'engagement_id', true );
                $data[] = array(
                    'id'            => $d->ID,
                    'title'         => $d->post_title,
                    'engagement_id' => $eng_id,
                    'version'       => get_post_meta( $d->ID, 'doc_version', true ) ?: '1.0',
                    'confidential'  => get_post_meta( $d->ID, 'confidentiality', true ) ?: 'restricted',
                );
            }
        }

        return new \WP_REST_Response( array( 'success' => true, 'documents' => $data ), 200 );
    }

    /**
     * REST Endpoint: POST /ascendance/v1/advisory/documents/token
     */
    public function rest_create_token( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $doc_id  = (int) $request->get_param( 'doc_id' );

        if ( ! $doc_id || ! $this->user_can_access_document( $user_id, $doc_id ) ) {
            return new \WP_REST_Response( array( 'error' => 'Forbidden' ), 403 );
        }

        $token = $this->generate_download_token( $user_id, $doc_id );
        if ( ! $token ) {
            return new \WP_REST_Response( array( 'error' => 'Failed to generate token' ), 500 );
        }

        return new \WP_REST_Response( array(
            'success'   => true,
            'token'     => $token,
            'download'  => home_url( "/wp-json/ascendance/v1/advisory/documents/download?token=$token&doc_id=$doc_id" ),
        ), 200 );
    }

    /**
     * REST Endpoint: GET /ascendance/v1/advisory/deliverables
     */
    public function rest_get_deliverables( \WP_REST_Request $request ) {
        $user_id   = get_current_user_id();
        $client_id = $this->get_user_client_id( $user_id );

        if ( ! $client_id && ! current_user_can( 'manage_options' ) ) {
            return new \WP_REST_Response( array( 'error' => 'Unauthorized' ), 403 );
        }

        $del_posts = get_posts( array( 'post_type' => 'advisory_deliverable', 'posts_per_page' => 50 ) );
        $data = array();
        foreach ( $del_posts as $dp ) {
            $eng_id = (int) get_post_meta( $dp->ID, 'engagement_id', true );
            if ( $this->user_can_access_engagement( $user_id, $eng_id ) ) {
                $data[] = array(
                    'id'          => $dp->ID,
                    'title'       => $dp->post_title,
                    'status'      => get_post_meta( $dp->ID, 'deliverable_status', true ) ?: 'delivered',
                    'due_date'    => get_post_meta( $dp->ID, 'due_date', true ) ?: date( 'Y-m-d' ),
                );
            }
        }

        return new \WP_REST_Response( array( 'success' => true, 'deliverables' => $data ), 200 );
    }

    /**
     * REST Endpoint: GET /ascendance/v1/advisory/audit-logs
     */
    public function rest_get_audit_logs( \WP_REST_Request $request ) {
        $user_id   = get_current_user_id();
        $client_id = $this->get_user_client_id( $user_id );

        if ( ! $client_id && ! current_user_can( 'manage_options' ) ) {
            return new \WP_REST_Response( array( 'error' => 'Unauthorized' ), 403 );
        }

        $logs = $this->get_audit_logs( current_user_can( 'manage_options' ) ? 0 : $client_id, 50 );
        return new \WP_REST_Response( array( 'success' => true, 'logs' => $logs ), 200 );
    }
}
