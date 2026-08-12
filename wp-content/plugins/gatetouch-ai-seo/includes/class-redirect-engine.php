<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- 404 logging uses a plugin-owned custom table for admin reporting.
// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Custom table identifiers are built from $wpdb->prefix and escaped before interpolation; SQL placeholders cannot represent table names.

/**
 * GateTouch AI Redirect Engine
 * 
 * Monitors 404 errors and provides AI-powered smart redirect suggestions.
 */
class GateTouch_Redirect_Engine {

    public function __construct() {
        add_action( 'template_redirect', [ $this, 'monitor_404s' ] );
    }

    /**
     * Create custom tables for 404 logs
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = esc_sql( $wpdb->prefix . 'gatetouch_404_logs' );

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            url text NOT NULL,
            referer text,
            user_agent text,
            ip_address varchar(100),
            hit_count int(11) DEFAULT 1,
            last_hit datetime DEFAULT CURRENT_TIMESTAMP,
            suggested_redirect text,
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Monitor 404 errors and log them
     */
    public function monitor_404s() {
        if ( ! is_404() ) return;

        global $wpdb;
        $table_name = esc_sql( $wpdb->prefix . 'gatetouch_404_logs' );
        $current_url = home_url( add_query_arg( [], $GLOBALS['wp']->request ) );
        
        // Skip common junk
        if ( preg_match( '/\.(php|aspx|env|git|well-known)/i', $current_url ) ) return;

        $existing = $wpdb->get_row( $wpdb->prepare( "SELECT id, hit_count FROM {$table_name} WHERE url = %s", $current_url ) );

        if ( $existing ) {
            $wpdb->update( $table_name, 
                [ 'hit_count' => $existing->hit_count + 1, 'last_hit' => current_time( 'mysql' ) ], 
                [ 'id' => $existing->id ] 
            );
        } else {
            // Suggest a redirect using AI or fuzzy matching
            $suggestion = $this->get_smart_suggestion( $current_url );
            
            $wpdb->insert( $table_name, [
                'url'                => $current_url,
                'referer'            => isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '',
                'user_agent'         => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
                'ip_address'         => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
                'suggested_redirect' => $suggestion,
                'last_hit'           => current_time( 'mysql' )
            ] );
        }
    }

    /**
     * AI-Powered Smart Suggestion logic
     */
    private function get_smart_suggestion( $url ) {
        $path = wp_parse_url( $url, PHP_URL_PATH );
        $slug = trim( $path, '/' );
        
        if ( empty( $slug ) ) return '';

        // 1. Try fuzzy match in DB first (Fast)
        global $wpdb;
        $slug_parts = explode( '/', $slug );
        $last_part  = end( $slug_parts );
        
        $match = $wpdb->get_var( $wpdb->prepare( 
            "SELECT guid FROM {$wpdb->posts} WHERE post_name LIKE %s AND post_status='publish' LIMIT 1", 
            '%' . $wpdb->esc_like( $last_part ) . '%' 
        ) );

        if ( $match ) return $match;

        // 2. If no fuzzy match, we could use AI to find the most semantically related page
        // For now, return empty and let the user handle it in the dashboard
        return '';
    }

    /**
     * Get 404 logs for the dashboard
     */
    public static function get_logs( $limit = 10 ) {
        global $wpdb;
        $table_name = esc_sql( $wpdb->prefix . 'gatetouch_404_logs' );
        $limit      = max( 1, absint( $limit ) );

        return $wpdb->get_results(
            $wpdb->prepare( "SELECT * FROM {$table_name} ORDER BY hit_count DESC LIMIT %d", $limit ),
            ARRAY_A
        );
    }
}
