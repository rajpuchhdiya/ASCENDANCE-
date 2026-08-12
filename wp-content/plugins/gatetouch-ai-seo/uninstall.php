<?php
/**
 * Fired when the plugin is deleted/uninstalled.
 * Removes all plugin data from the database.
 */
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Uninstall cleanup intentionally removes plugin metadata and custom tables.
// phpcs:disable WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Uninstall cleanup targets exact plugin meta keys.

global $wpdb;

$gatetouch_all_options = [
    'gatetouch_version',
    'gatetouch_title_separator',
    'gatetouch_openai_key',
    'gatetouch_anthropic_key',
    'gatetouch_gemini_key',
    'gatetouch_ai_model',
    'gatetouch_ai_provider',
    'gatetouch_auto_generate',
    'gatetouch_auto_redirect',
    'gatetouch_smart_redirects',
    'gatetouch_twitter_site',
    'gatetouch_homepage_meta',
    'gatetouch_sitemap_settings',
    'gatetouch_robots_settings',
    'gatetouch_llms_settings',
    'gatetouch_security_txt_settings',
    'gatetouch_social_settings',
    'gatetouch_schema_settings',
    'gatetouch_general_settings',
    'gatetouch_security_settings',
    'gatetouch_search_appearance',
    'gatetouch_redirects',
    'gatetouch_integrations',
    'gatetouch_rss_settings',
    'gatetouch_last_ping',
    'gatetouch_activated',
    'gatetouch_activation_redirect',
    'gatetouch_setup_completed',
    'gatetouch_search_appearance',
    'gatetouch_local_seo_settings',
    'gatetouch_breadcrumb_settings',
    'gatetouch_license',
];

$gatetouch_all_crons = [
    'gatetouch_scheduled_sitemap_ping',
    'gatetouch_auto_generate_single',
    'gatetouch_daily_events',
    'gatetouch_license_ping',
];

if ( is_multisite() ) {
    // Clean up every site in the network
    $gatetouch_sites = get_sites( [ 'fields' => 'ids', 'number' => 0 ] );
    foreach ( $gatetouch_sites as $gatetouch_site_id ) {
        switch_to_blog( $gatetouch_site_id );
        gatetouch_uninstall_site( $wpdb, $gatetouch_all_options, $gatetouch_all_crons );
        restore_current_blog();
    }
} else {
    gatetouch_uninstall_site( $wpdb, $gatetouch_all_options, $gatetouch_all_crons );
}

/**
 * Remove all GateTouch data for a single site.
 */
function gatetouch_uninstall_site( $wpdb, $options, $crons ) {
    // 1. Options
    foreach ( $options as $opt ) {
        delete_option( $opt );
    }

    // 2. Post meta
    $wpdb->delete( $wpdb->postmeta, [ 'meta_key' => '_gatetouch_meta' ], [ '%s' ] );

    // 3. Term meta
    $wpdb->delete( $wpdb->termmeta, [ 'meta_key' => '_gatetouch_meta_title' ], [ '%s' ] );
    $wpdb->delete( $wpdb->termmeta, [ 'meta_key' => '_gatetouch_meta_description' ], [ '%s' ] );

    // 4. Custom tables
    $tables = [
        $wpdb->prefix . 'gatetouch_redirects',
        $wpdb->prefix . 'gatetouch_404_logs',
        $wpdb->prefix . 'gatetouch_registrations',
        $wpdb->prefix . 'gatetouch_redirect_log',
    ];
    foreach ( $tables as $table ) {
        $gatetouch_table = esc_sql( $table );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Table names are generated from the WordPress table prefix during uninstall.
        $wpdb->query( "DROP TABLE IF EXISTS `{$gatetouch_table}`" );
    }

    // 5. Scheduled events
    foreach ( $crons as $hook ) {
        wp_clear_scheduled_hook( $hook );
    }
}

// 6. Flush rewrite rules
flush_rewrite_rules();
