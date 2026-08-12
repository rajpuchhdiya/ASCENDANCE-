<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Admin-triggered cleanup and database health actions intentionally inspect and remove WordPress bloat rows.

/**
 * GateTouch Performance Optimization Engine
 * 
 * Cleans up WordPress bloat and optimizes database for faster SEO crawling.
 */
class GateTouch_Performance_Engine {

    /**
     * Run a full optimization cycle
     */
    public static function run_optimization() {
        $results = [
            'revisions'  => self::clean_revisions(),
            'transients' => self::clean_transients(),
            'bloat'      => self::clean_bloat(),
            'spam'       => self::clean_spam_comments(),
        ];

        update_option( 'gatetouch_last_performance_optimization', current_time( 'mysql' ) );
        return $results;
    }

    private static function clean_revisions() {
        global $wpdb;
        $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='revision'" );
        $wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type='revision'" );
        return (int) $count;
    }

    private static function clean_transients() {
        global $wpdb;
        $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'" );
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%' OR option_name LIKE '_site_transient_%'" );
        return (int) $count;
    }

    private static function clean_spam_comments() {
        global $wpdb;
        $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved='spam' OR comment_approved='trash'" );
        $wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_approved='spam' OR comment_approved='trash'" );
        return (int) $count;
    }

    private static function clean_bloat() {
        // This usually involves disabling scripts, handled via options in class-core.php
        return 1;
    }

    /**
     * Get summary of database health
     */
    public static function get_health_stats() {
        global $wpdb;
        return [
            'total_revisions' => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='revision'" ),
            'total_spam'      => (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved='spam'" ),
            'db_size'         => self::get_db_size(),
        ];
    }

    private static function get_db_size() {
        global $wpdb;
        $size = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT SUM(data_length + index_length) FROM information_schema.TABLES WHERE table_schema = %s',
                DB_NAME
            )
        );
        return size_format( $size );
    }
}
