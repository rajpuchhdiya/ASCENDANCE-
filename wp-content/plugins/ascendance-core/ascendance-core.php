<?php
/**
 * Plugin Name: Ascendance Core
 * Plugin URI: https://github.com/raj/ascendance-core
 * Description: Core platform architecture helper for Ascendance. Registers CPTs, Taxonomies, ACF Fields, Custom Paywall, Member Dashboard logic, AEO/GEO crawlers, and GA4 tracking.
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
