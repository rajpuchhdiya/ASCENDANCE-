<?php
/**
 * Plugin Name:       GT - AI SEO/GEO/AEO Optimizer
 * Description:       AI-powered SEO, GEO, and AEO optimization suite for WordPress with metadata, schema, sitemaps, llms.txt, and optional AI generation using your own OpenAI, Anthropic, or Google Gemini API key.
 * Version:           1.4.1
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Author:            Parbat Pithiya
 * Author URI:        https://profiles.wordpress.org/gatetouch/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gatetouch-ai-seo
 * Domain Path:       /languages
 * Tags:              seo, ai seo, meta tags, schema, sitemap, open graph, AEO, GEO, AI search optimization, llms txt
 *
 * @package GateTouch_AI_SEO
 */

defined( 'ABSPATH' ) || exit;

// ── Constants ────────────────────────────────────────────────────────────────
define( 'GATETOUCH_VERSION',  '1.4.1' );
define( 'GATETOUCH_FILE',     __FILE__ );
define( 'GATETOUCH_PATH',     plugin_dir_path( __FILE__ ) );
define( 'GATETOUCH_URL',      plugin_dir_url( __FILE__ ) );
define( 'GATETOUCH_SLUG',     'gatetouch-ai-seo' );
define( 'GATETOUCH_PREFIX',   'gatetouch_' );
define( 'GATETOUCH_MIN_WP',   '5.7' );
define( 'GATETOUCH_MIN_PHP',  '7.4' );
define( 'GATETOUCH_META_KEY', '_gatetouch_meta' );

// ── Modern Code Architecture: Lazy Autoloader ─────────────────────────────
spl_autoload_register( function( $class ) {
    // Only autoload GateTouch (GateTouch_) classes
    $prefix = 'GateTouch_';
    if ( strpos( $class, $prefix ) !== 0 ) {
        return;
    }

    $class_name = strtolower( str_replace( '_', '-', substr( $class, strlen( $prefix ) ) ) );
    
    // Exception mapping for legacy non-standard file names
    $exceptions = [
        'indexing-api'       => 'automation/class-indexing.php',
        'alt-text-generator' => 'automation/class-alt-text-engine.php',
        'support-panel'      => 'class-support-panel.php',
    ];

    if ( isset( $exceptions[ $class_name ] ) ) {
        $file_name = $exceptions[ $class_name ];
    } else {
        $file_name = 'class-' . $class_name . '.php';
    }

    // Directories to scan
    $paths = [
        GATETOUCH_PATH . 'includes/',
        GATETOUCH_PATH . 'admin/',
        GATETOUCH_PATH . 'includes/automation/',
        GATETOUCH_PATH . 'includes/migration/',
    ];

    foreach ( $paths as $path ) {
        if ( file_exists( $path . $file_name ) ) {
            require_once $path . $file_name;
            return;
        }
    }
} );

// ── Activation / Deactivation Hooks ─────────────────────────────────────────
register_activation_hook(   __FILE__, [ 'GateTouch_Core', 'activate' ] );
register_deactivation_hook( __FILE__, [ 'GateTouch_Core', 'deactivate' ] );

// ── Boot ─────────────────────────────────────────────────────────────────────
add_action( 'plugins_loaded', [ 'GateTouch_Core', 'boot' ], 5 );
