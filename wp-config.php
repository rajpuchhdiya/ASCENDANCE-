<?php
/**
 * The base configuration for WordPress
 *
 * This file handles environment-specific settings (local, staging, production)
 * by loading configuration variables from a secure .env file.
 *
 * @package WordPress
 * @subpackage Ascendance
 */

// 0. PHP Version Guard (Required: PHP 8.2+)
if ( version_compare( PHP_VERSION, '8.2.0', '<' ) ) {
    wp_die( 'This platform requires PHP version 8.2.0 or higher. Current version is ' . PHP_VERSION );
}

// 1. Lightweight Native .env Parser (looks in current dir and parent dirs for security outside web root)
$env_file = null;
$paths_to_check = array(
    __DIR__ . '/.env',
    dirname( __DIR__ ) . '/.env',
    dirname( dirname( __DIR__ ) ) . '/.env'
);

foreach ( $paths_to_check as $path ) {
    if ( file_exists( $path ) ) {
        $env_file = $path;
        break;
    }
}

if ( $env_file ) {
    $lines = file( $env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
    foreach ( $lines as $line ) {
        $line = trim( $line );
        // Skip comments and empty lines
        if ( empty( $line ) || strpos( $line, '#' ) === 0 ) {
            continue;
        }
        
        // Parse key/value
        if ( strpos( $line, '=' ) !== false ) {
            list( $name, $value ) = explode( '=', $line, 2 );
            $name  = trim( $name );
            $value = trim( $value );
            // Strip wrapping quotes
            $value = trim( $value, '"\'' );
            
            // Define constant if not already defined
            if ( ! defined( $name ) ) {
                define( $name, $value );
            }
            putenv( "{$name}={$value}" );
            $_ENV[ $name ] = $value;
            $_SERVER[ $name ] = $value;
        }
    }
}

// 2. Default Configuration Fallbacks (if not declared in .env)
if ( ! defined( 'WP_ENVIRONMENT_TYPE' ) ) {
    define( 'WP_ENVIRONMENT_TYPE', 'production' );
}

if ( ! defined( 'DB_NAME' ) )     define( 'DB_NAME', 'ascendance' );
if ( ! defined( 'DB_USER' ) )     define( 'DB_USER', 'root' );
if ( ! defined( 'DB_PASSWORD' ) ) define( 'DB_PASSWORD', '' );
if ( ! defined( 'DB_HOST' ) )     define( 'DB_HOST', 'localhost' );
if ( ! defined( 'DB_CHARSET' ) )  define( 'DB_CHARSET', 'utf8mb4' );
if ( ! defined( 'DB_COLLATE' ) )  define( 'DB_COLLATE', '' );

// 3. Environment-Aware Debugging Configuration
switch ( WP_ENVIRONMENT_TYPE ) {
    case 'local':
        define( 'WP_DEBUG', true );
        define( 'WP_DEBUG_LOG', true );
        define( 'WP_DEBUG_DISPLAY', true );
        define( 'SCRIPT_DEBUG', true );
        define( 'SAVEQUERIES', true );
        break;

    case 'staging':
        define( 'WP_DEBUG', true );
        define( 'WP_DEBUG_LOG', true );
        define( 'WP_DEBUG_DISPLAY', false );
        define( 'SCRIPT_DEBUG', false );
        define( 'SAVEQUERIES', false );
        break;

    case 'production':
    default:
        define( 'WP_DEBUG', false );
        define( 'WP_DEBUG_LOG', false );
        define( 'WP_DEBUG_DISPLAY', false );
        define( 'SCRIPT_DEBUG', false );
        define( 'SAVEQUERIES', false );
        break;
}

// 4. Security & Hardening Constants
define( 'DISALLOW_FILE_EDIT', true ); // Disable dashboard Theme/Plugin editor

if ( in_array( WP_ENVIRONMENT_TYPE, array( 'staging', 'production' ), true ) ) {
    define( 'DISALLOW_FILE_MODS', true );  // Disable theme/plugin installs/updates (forces Git-based workflow)
    define( 'FORCE_SSL_ADMIN', true );    // Force HTTPS
    define( 'WP_AUTO_UPDATE_CORE', false ); // Manage updates strictly through Git deployment
} else {
    define( 'DISALLOW_FILE_MODS', false ); // Allow changes locally for development convenience
    define( 'FORCE_SSL_ADMIN', false );
    define( 'WP_AUTO_UPDATE_CORE', false );
}

// Enable object/production caching in production
if ( 'production' === WP_ENVIRONMENT_TYPE ) {
    define( 'WP_CACHE', true );
}

// 5. Authentication Unique Keys and Salts (Defaults if not set in .env)
if ( ! defined( 'AUTH_KEY' ) )         define( 'AUTH_KEY',         '0-KFp,1kx-5$d4P[%Uf2Y:L}?;&!GcN$$@gAkkeD!%Uc@ H&1mMKDYjIF|i/Al-M' );
if ( ! defined( 'SECURE_AUTH_KEY' ) )  define( 'SECURE_AUTH_KEY',  'mQIr{w>Lo/2FOC}7vR6i0W8-ne+G%&FWg(^g/5fp%X-N3tD1fT18GV0HYX{#I~yS' );
if ( ! defined( 'LOGGED_IN_KEY' ) )    define( 'LOGGED_IN_KEY',    'jy!<C3[.D,n<(9*#uRt8:0aN3HpA>(`o@YLo;O`2P2SeO&jCUqMet=Z*%z>:eo*3' );
if ( ! defined( 'NONCE_KEY' ) )        define( 'NONCE_KEY',        'ea8C4CRahfz;PQSkTr!?$qKr-Tj=zA:Zy]P=Lr99&9?*(-QB?@|#?;j6=gXyNZ^)' );
if ( ! defined( 'AUTH_SALT' ) )        define( 'AUTH_SALT',        '8CeM&5|kiW_V_0cp*}jm%eehV)B?E*JJ)`K;Zcb(5>0fd=Y>wfLc6B@)kh<j=z3*' );
if ( ! defined( 'SECURE_AUTH_SALT' ) ) define( 'SECURE_AUTH_SALT', 'e,|BBVv]3;rZoyfy)^Tp#BUg#Z6Q1G_`.n<oXVp}T)@;San 61=~JP<gEh >BE./' );
if ( ! defined( 'LOGGED_IN_SALT' ) )   define( 'LOGGED_IN_SALT',   'Msvyx;9YLy=00I4qlN,d rWi$m^;+IQ[TT@i+ MY{m n)v`xAQ{!I11j<sj`dMu<' );
if ( ! defined( 'NONCE_SALT' ) )       define( 'NONCE_SALT',       'f#3%&ODu5kncxG`b+yU>q5&<7b985gBY{KSh<$H7tYZ?rVy)^x7zSk ;]Ai^MX=}' );

// 6. Database Table Prefix
$table_prefix = 'wp_';

// 7. Absolute path to the WordPress directory
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/' );
}

// 8. Sets up WordPress vars and included files
require_once ABSPATH . 'wp-settings.php';
