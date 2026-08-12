<?php
/**
 * Plugin Name: Ascendance Core
 * Plugin URI: https://github.com/raj/ascendance-core
 * Description: Core platform architecture helper for Ascendance. Registers CPTs, Taxonomies, ACF Fields, Custom Paywall, Member Dashboard logic, AEO/GEO crawlers, GA4 tracking, and Google reCAPTCHA v3 protection.
 * Version: 1.0.0
 * Author: Raj
 * Author URI: https://github.com/raj
 * License: GPL2
 * Text Domain: ascendance-core
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define( 'ASCENDANCE_CORE_VERSION', '1.0.0' );
define( 'ASCENDANCE_CORE_PATH', plugin_dir_path( __FILE__ ) );
define( 'ASCENDANCE_CORE_URL', plugin_dir_url( __FILE__ ) );

/**
 * Autoloader for namespaced classes within Ascendance\Core
 */
spl_autoload_register( function ( $class ) {
    // Only load classes inside our namespace
    $prefix = 'Ascendance\\Core\\';
    $len = strlen( $prefix );
    
    if ( strncmp( $prefix, $class, $len ) !== 0 ) {
        return;
    }
    
    // Get relative class name
    $relative_class = substr( $class, $len );
    
    // Map namespace separators to directory separators, convert class name to lowercase file format class-classname.php
    $parts = explode( '\\', $relative_class );
    $class_name = 'class-' . strtolower( str_replace( '_', '-', array_pop( $parts ) ) ) . '.php';
    
    $subdir = '';
    if ( ! empty( $parts ) ) {
        $subdir = implode( '/', array_map( 'strtolower', $parts ) ) . '/';
    }
    
    $file = ASCENDANCE_CORE_PATH . 'includes/' . $subdir . $class_name;
    
    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );

/**
 * Suppress translation notices triggered too early by third-party plugins (e.g. Paid Memberships Pro in WP 6.7)
 */
add_filter( 'doing_it_wrong_trigger_error', function( $trigger, $function_name, $message, $version ) {
    if ( '_load_textdomain_just_in_time' === $function_name && strpos( $message, 'paid-memberships-pro' ) !== false ) {
        return false;
    }
    return $trigger;
}, 10, 4 );

/**
 * Initialize core components
 */
add_action( 'plugins_loaded', function() {
    // 1. CPT & Taxonomy registration
    if ( class_exists( 'Ascendance\Core\CPT_Taxonomy' ) ) {
        Ascendance\Core\CPT_Taxonomy::get_instance();
    }
    
    // 2. Programmatic ACF registration
    if ( class_exists( 'Ascendance\Core\ACF_Fields' ) ) {
        Ascendance\Core\ACF_Fields::get_instance();
    }
    
    // 3. Subscription Paywall trigger
    if ( class_exists( 'Ascendance\Core\Paywall' ) ) {
        Ascendance\Core\Paywall::get_instance();
    }

    // 4. Member Dashboard features
    if ( class_exists( 'Ascendance\Core\Member_Dashboard' ) ) {
        Ascendance\Core\Member_Dashboard::get_instance();
    }

    // 5. Search & SEO config hooks
    if ( class_exists( 'Ascendance\Core\Search_SEO' ) ) {
        Ascendance\Core\Search_SEO::get_instance();
    }

    // 6. Entity Intelligence Architecture
    if ( class_exists( 'Ascendance\Core\Entity_Intelligence' ) ) {
        Ascendance\Core\Entity_Intelligence::get_instance();
    }

    // 6. AEO / GEO generator feeds
    if ( class_exists( 'Ascendance\Core\AEO_GEO' ) ) {
        Ascendance\Core\AEO_GEO::get_instance();
    }

    // 7. GA4 Analytics dataLayer injections
    if ( class_exists( 'Ascendance\Core\Analytics' ) ) {
        Ascendance\Core\Analytics::get_instance();
    }

    // 8. AI Editorial Studio workflow
    if ( class_exists( 'Ascendance\Core\AI_Studio' ) ) {
        Ascendance\Core\AI_Studio::get_instance();
    }

    // 9. Mission Control Admin Dashboard
    if ( class_exists( 'Ascendance\Core\Mission_Control' ) ) {
        Ascendance\Core\Mission_Control::get_instance();
    }

    // 10. Stripe Plugin Adapter
    if ( class_exists( 'Ascendance\Core\Stripe_Plugin_Adapter' ) ) {
        Ascendance\Core\Stripe_Plugin_Adapter::get_instance();
    }

    // 11. MailerLite Newsletter Integration
    if ( class_exists( 'Ascendance\Core\Newsletter' ) ) {
        Ascendance\Core\Newsletter::get_instance();
    }

    // 12. Native next-gen Image Optimizer
    if ( class_exists( 'Ascendance\Core\Image_Optimizer' ) ) {
        Ascendance\Core\Image_Optimizer::get_instance();
    }

    // 13. Recommendation Engine for priority-based personalization feed
    if ( class_exists( 'Ascendance\Core\Recommendation_Engine' ) ) {
        Ascendance\Core\Recommendation_Engine::get_instance();
    }

    // 14. Native Login Security (Hidden Login URL) if WPS Hide Login is inactive
    if ( class_exists( 'Ascendance\Core\Login_Security' ) && ! class_exists( 'WPS_Hide_Login' ) ) {
        Ascendance\Core\Login_Security::get_instance();
    }

    // 15. Google reCAPTCHA v3 — invisible, score-based protection on login, registration, and lost-password
    if ( class_exists( 'Ascendance\Core\ReCaptcha' ) ) {
        Ascendance\Core\ReCaptcha::get_instance();
    }

    // 16. Secure Advisory Client Portal
    if ( class_exists( 'Ascendance\Core\Advisory_Portal' ) ) {
        Ascendance\Core\Advisory_Portal::get_instance();
    }

    // 17. AI Workflow Image Generation Studio
    if ( class_exists( 'Ascendance\Core\AI_Image_Studio' ) ) {
        Ascendance\Core\AI_Image_Studio::get_instance();
    }

    // 18. Headless REST API Controller
    if ( class_exists( 'Ascendance\Core\Headless_API' ) ) {
        Ascendance\Core\Headless_API::get_instance();
    }
} );

/**
 * Run rewrite rules flush upon activation
 */
register_activation_hook( __FILE__, function() {
    // Include dependency and register CPTs before flush
    require_once ASCENDANCE_CORE_PATH . 'includes/class-cpt-taxonomy.php';
    Ascendance\Core\CPT_Taxonomy::get_instance()->register_content_types();
    flush_rewrite_rules();
} );

/**
 * Run rewrite rules flush upon deactivation
 */
register_deactivation_hook( __FILE__, function() {
    flush_rewrite_rules();
} );
