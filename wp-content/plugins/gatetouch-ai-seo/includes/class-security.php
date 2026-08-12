<?php
defined( 'ABSPATH' ) || exit;

/**
 * Applies optional WordPress hardening and security-header settings.
 */
class GateTouch_Security {

    public function __construct() {
        $opts = get_option( 'gatetouch_security_settings', [] );

        if ( ! empty( $opts['remove_wp_version'] ) ) {
            add_filter( 'the_generator', '__return_empty_string' );
            remove_action( 'wp_head', 'wp_generator' );
        }
        if ( ! empty( $opts['remove_rsd_link'] ) ) {
            remove_action( 'wp_head', 'rsd_link' );
        }
        if ( ! empty( $opts['remove_wlw_manifest'] ) ) {
            remove_action( 'wp_head', 'wlwmanifest_link' );
        }
        if ( ! empty( $opts['disable_xmlrpc'] ) ) {
            add_filter( 'xmlrpc_enabled', '__return_false' );
        }
        if ( ! empty( $opts['security_headers'] ) ) {
            add_action( 'send_headers', [ $this, 'add_security_headers' ] );
        }

        // Always: protect REST API user enumeration
        add_filter( 'rest_endpoints', [ $this, 'protect_user_endpoint' ] );
    }

    public function add_security_headers() {
        if ( ! is_admin() ) {
            header( 'X-Content-Type-Options: nosniff' );
            header( 'X-Frame-Options: SAMEORIGIN' );
            header( 'Referrer-Policy: strict-origin-when-cross-origin' );
            header( 'X-XSS-Protection: 1; mode=block' );
            header( 'Permissions-Policy: camera=(), microphone=(), geolocation=()' );
        }
    }

    public function protect_user_endpoint( $endpoints ) {
        if ( ! is_user_logged_in() ) {
            unset( $endpoints['/wp/v2/users'] );
            unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
        }
        return $endpoints;
    }
}
