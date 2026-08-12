<?php
defined( 'ABSPATH' ) || exit;

/**
 * Writes GateTouch diagnostic messages to the plugin log file.
 */
class GateTouch_Logger {

    private const LOG_FILE = 'gatetouch-errors.log';

    /**
     * Log a message
     */
    public static function log( $message, $level = 'INFO' ) {
        if ( ! self::is_logging_enabled() && $level === 'INFO' ) return;

        $upload_dir = wp_upload_dir();
        $log_dir    = $upload_dir['basedir'] . '/gatetouch-logs';

        if ( ! file_exists( $log_dir ) ) {
            wp_mkdir_p( $log_dir );
            // Protect the log directory from direct web access.
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- WP_Filesystem is unavailable during logging; uploads dir is the correct location per WP guidelines.
            file_put_contents( $log_dir . '/index.php', '<?php // Silence is golden' );
            // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Same as above.
            file_put_contents( $log_dir . '/.htaccess', 'deny from all' );
        }

        $log_path = $log_dir . '/' . self::LOG_FILE;
        $entry    = sprintf( "[%s] [%s] %s" . PHP_EOL, current_time( 'mysql' ), strtoupper( $level ), $message );

        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Writes to the plugin log file only when logging is enabled.
        error_log( $entry, 3, $log_path );
    }

    /**
     * Log an error
     */
    public static function error( $message ) {
        self::log( $message, 'ERROR' );
    }

    /**
     * Log a warning
     */
    public static function warning( $message ) {
        self::log( $message, 'WARNING' );
    }

    /**
     * Log AI activity
     */
    public static function ai( $message ) {
        self::log( $message, 'AI' );
    }

    private static function is_logging_enabled() {
        return (bool) get_option( 'gatetouch_enable_logging', false );
    }

    /**
     * Get the log content
     */
    public static function get_logs( $limit = 100 ) {
        $upload_dir = wp_upload_dir();
        $log_path   = $upload_dir['basedir'] . '/gatetouch-logs/' . self::LOG_FILE;

        if ( ! file_exists( $log_path ) ) return '';

        $lines = file( $log_path );
        $lines = array_slice( $lines, -$limit );
        return implode( '', $lines );
    }

    /**
     * Clear logs
     */
    public static function clear() {
        $upload_dir = wp_upload_dir();
        $log_path   = $upload_dir['basedir'] . '/gatetouch-logs/' . self::LOG_FILE;
        if ( file_exists( $log_path ) ) {
            wp_delete_file( $log_path );
        }
    }
}
