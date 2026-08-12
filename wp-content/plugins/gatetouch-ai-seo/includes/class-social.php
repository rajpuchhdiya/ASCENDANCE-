<?php
defined( 'ABSPATH' ) || exit;

/**
 * Handles social metadata settings and Open Graph tag testing.
 */
class GateTouch_Social {

    public function __construct() {
        // Social meta is output through class-core.php output_head_tags()
        // This class handles social-specific settings
        add_action( 'wp_ajax_gatetouch_test_og', [ $this, 'ajax_test_og' ] );
    }

    /**
     * Get default OG image (global setting)
     */
    public static function get_default_og_image() {
        $opts = get_option( 'gatetouch_social_settings', [] );
        return $opts['default_og_image'] ?? '';
    }

    /**
     * Test OG tags for a URL via AJAX
     */
    public function ajax_test_og() {
        check_ajax_referer( 'gatetouch_ajax', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( __( 'Permission denied.', 'gatetouch-ai-seo' ) );

        $url      = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
        $response = wp_remote_get( $url, [ 'timeout' => 10 ] );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        $body  = wp_remote_retrieve_body( $response );
        $tags  = [];
        preg_match_all( '/<meta\s+(?:property|name)=["\'](?:og:|twitter:)[^"\']*["\'][^>]*>/i', $body, $matches );
        foreach ( $matches[0] as $tag ) {
            $tags[] = $tag;
        }

        wp_send_json_success( [ 'tags' => $tags, 'count' => count( $tags ) ] );
    }
}
