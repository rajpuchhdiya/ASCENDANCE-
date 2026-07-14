<?php
/**
 * MU plugin: Performance Tuning & Optimization
 * 
 * - Disables native WordPress emojis (saves an HTTP request and JS execution time)
 * - Limits Heartbeat API frequency to save server CPU resources
 * - Disables XML-RPC to save processing overhead and security surface
 * - Cleans head tags by removing unnecessary metadata (generator, wlwmanifest, shortlinks, RSD)
 * - Adds dns-prefetch and preconnect headers for Stripe & Brevo endpoints
 * - Optimizes script loading with async/defer helpers
 *
 * @package Ascendance\Core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Abort if called directly
}

/**
 * 1) Disable Emojis
 */
add_action( 'init', function() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    
    // Remove from TinyMCE
    add_filter( 'tiny_mce_plugins', function( $plugins ) {
        if ( is_array( $plugins ) ) {
            return array_diff( $plugins, array( 'wpemoji' ) );
        }
        return array();
    } );
    
    // Remove emoji DNS prefetch
    add_filter( 'wp_resource_hints', function( $hints, $relation_type ) {
        if ( 'dns-prefetch' === $relation_type ) {
            $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/14.0.0/svg/' );
            $hints = array_diff( $hints, array( $emoji_svg_url ) );
        }
        return $hints;
    }, 10, 2 );
} );

/**
 * 2) Limit WordPress Heartbeat API frequency
 */
add_filter( 'heartbeat_settings', function( $settings ) {
    // Set frequency to 60 seconds (default is 15s or 60s depending on focus)
    $settings['interval'] = 60;
    return $settings;
} );

/**
 * 3) Disable XML-RPC (reduces server load and security vectors)
 */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_action( 'xmlrpc_call', function() {
    wp_die( 'XML-RPC is disabled on this platform for security and performance.', 'XML-RPC Disabled', array( 'response' => 403 ) );
} );

/**
 * 4) Clean up <head> tags
 */
add_action( 'init', function() {
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wlwmanifest_link' );
    remove_action( 'wp_head', 'wp_generator' );
    remove_action( 'wp_head', 'wp_shortlink_wp_head', 10 );
    remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
    remove_action( 'wp_head', 'feed_links', 2 );
    remove_action( 'wp_head', 'feed_links_extra', 3 );
    remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
    remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
    remove_action( 'wp_head', 'wp_oembed_add_host_js' );
} );

/**
 * 5) Preconnect & DNS Prefetch to third-party endpoints
 */
add_action( 'wp_head', function() {
    echo '<!-- Performance Preconnect Hints -->' . "\n";
    // Stripe Payments
    echo '<link rel="dns-prefetch" href="https://js.stripe.com">' . "\n";
    echo '<link rel="preconnect" href="https://js.stripe.com" crossorigin>' . "\n";
    echo '<link rel="preconnect" href="https://api.stripe.com" crossorigin>' . "\n";
    
    // Brevo / Newsletter API endpoint
    echo '<link rel="dns-prefetch" href="https://api.brevo.com">' . "\n";
    echo '<link rel="preconnect" href="https://api.brevo.com" crossorigin>' . "\n";
    
    // Google Tag Manager (if enabled)
    echo '<link rel="dns-prefetch" href="https://www.googletagmanager.com">' . "\n";
    echo '<link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>' . "\n";
}, 1 );

/**
 * 6) Async / Defer Script Loader (Except JQuery and other dependency anchors)
 */
add_filter( 'script_loader_tag', function( $tag, $handle, $src ) {
    // Only apply in frontend
    if ( is_admin() ) {
        return $tag;
    }
    
    // List scripts to defer
    $defer_scripts = array(
        'font-awesome',
        'ascendance-js',
        'ascendance-pages',
        'wp-embed'
    );
    
    if ( in_array( $handle, $defer_scripts, true ) ) {
        return str_replace( ' src', ' defer="defer" src', $tag );
    }
    
    return $tag;
}, 10, 3 );

/**
 * 7) Disable SSL Verification for WordPress.org requests on local environments to prevent secure connection errors
 */
add_filter( 'http_request_args', function( $args, $url ) {
    $host = parse_url( $url, PHP_URL_HOST );
    if ( $host && ( strpos( $host, 'wordpress.org' ) !== false || strpos( $host, 'wp.org' ) !== false ) ) {
        $args['sslverify'] = false;
    }
    return $args;
}, 10, 2 );

