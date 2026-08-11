<?php
/**
 * MU plugin: Security hardening enforcement
 * - Adds strict security headers
 * - Protects wp-login.php and provides a relocatable login slug
 * - Validates hCaptcha on login when configured via .env
 * - Provides simple Wordfence/2FA checks and admin notices
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Abort if called directly
}

// 1) Security headers — skip framing restrictions on WP admin/customizer context
add_action( 'send_headers', function() {
    header_remove( 'X-Powered-By' );
    header( 'X-Content-Type-Options: nosniff' );
    header( 'Referrer-Policy: no-referrer-when-downgrade' );
    header( 'Permissions-Policy: geolocation=(), microphone=(), camera=()' );
    header( "Strict-Transport-Security: max-age=63072000; includeSubDomains; preload" );

    // Allow framing within same origin — needed for WP Customizer preview iframe
    // Do NOT set DENY or frame-ancestors 'none' as it breaks the Customizer
    if ( ! is_admin() && ! isset( $_GET['customize_changeset_uuid'] ) ) {
        header( 'X-Frame-Options: SAMEORIGIN' );
        header( "Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' https:; frame-ancestors 'self';" );
    }
} );

// 2) Hidden login / relocate — REMOVED
// This was a custom hide-login implementation.
// Use a dedicated plugin (e.g. WPS Hide Login) to configure this instead.

// 3) hCaptcha server-side check for logins (optional)
$hcaptcha_secret = getenv( 'HCAPTCHA_SECRET' );
if ( $hcaptcha_secret ) {
    add_filter( 'authenticate', function( $user, $username, $password ) use ( $hcaptcha_secret ) {
        // Only check on login form POST
        if ( ! empty( $_POST['h-captcha-response'] ) ) {
            $resp = wp_remote_post( 'https://hcaptcha.com/siteverify', array(
                'body' => array(
                    'secret'   => $hcaptcha_secret,
                    'response' => sanitize_text_field( wp_unslash( $_POST['h-captcha-response'] ) ),
                    'remoteip' => $_SERVER['REMOTE_ADDR'],
                ),
                'timeout' => 10,
            ) );

            if ( is_wp_error( $resp ) ) {
                return new WP_Error( 'hcaptcha_error', __( '<strong>ERROR</strong>: hCaptcha verification failed.' ) );
            }

            $body = wp_remote_retrieve_body( $resp );
            $data = json_decode( $body );
            if ( empty( $data->success ) ) {
                return new WP_Error( 'hcaptcha_failed', __( '<strong>ERROR</strong>: hCaptcha validation failed.' ) );
            }
        } else {
            return new WP_Error( 'hcaptcha_missing', __( '<strong>ERROR</strong>: Please complete the captcha.' ) );
        }

        return $user;
    }, 30, 3 );

    // Add hCaptcha widget to login form
    add_action( 'login_form', function() {
        $sitekey = getenv( 'HCAPTCHA_SITEKEY' );
        if ( $sitekey ) {
            echo '<div class="h-captcha" data-sitekey="' . esc_attr( $sitekey ) . '"></div>';
            echo '<script src="https://hcaptcha.com/1/api.js" async defer></script>';
        }
    } );
}

// 4) Basic Wordfence + 2FA checks: show admin notice if missing
add_action( 'admin_notices', function() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Wordfence
    include_once ABSPATH . 'wp-admin/includes/plugin.php';
    if ( ! is_plugin_active( 'wordfence/wordfence.php' ) ) {
        echo '<div class="notice notice-error"><p><strong>Security:</strong> Wordfence is not active. Install and activate Wordfence for host-level firewall and scanning.</p></div>';
    }

    // Two-factor plugin check (commonly: two-factor/two-factor.php)
    if ( ! is_plugin_active( 'two-factor/two-factor.php' ) ) {
        echo '<div class="notice notice-warning"><p><strong>Security:</strong> Two-Factor plugin not active. Enable 2FA for admin accounts.</p></div>';
    }
});

// 5) Deny access to sensitive files via PHP for extra safety
add_filter( 'mod_rewrite_rules', function( $rules ) {
    $protect = "# Protect wp-config and env\n<IfModule mod_rewrite.c>\nRewriteRule (^|/)(?:\.env|wp-config.php) - [F,L]\n</IfModule>\n";
    return $protect . $rules;
} );

// End
