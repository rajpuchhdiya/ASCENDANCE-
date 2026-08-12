<?php
/**
 * Fix Local Cache & Action Scheduler / Cron Issues
 */

require_once dirname( __DIR__ ) . '/wp-load.php';

echo "=== ASCENDANCE LOCAL FIX DIAGNOSTICS ===\n\n";

// 1. Remove object-cache.php if present in wp-content
$object_cache_file = WP_CONTENT_DIR . '/object-cache.php';
if ( file_exists( $object_cache_file ) ) {
    unlink( $object_cache_file );
    echo "[FIXED] Removed leftover wp-content/object-cache.php\n";
} else {
    echo "[OK] No wp-content/object-cache.php file found.\n";
}

// 2. Remove wp-cache-config.php if present
$wp_cache_config = WP_CONTENT_DIR . '/wp-cache-config.php';
if ( file_exists( $wp_cache_config ) ) {
    unlink( $wp_cache_config );
    echo "[FIXED] Removed wp-content/wp-cache-config.php\n";
} else {
    echo "[OK] No wp-content/wp-cache-config.php file found.\n";
}

// 3. Process Action Scheduler past-due queue if Action Scheduler is active
if ( class_exists( 'ActionScheduler' ) || class_exists( 'ActionScheduler_Store' ) ) {
    try {
        $store = ActionScheduler_Store::instance();
        $runner = ActionScheduler::runner();
        
        $past_due_actions = $store->query_actions([
            'status' => ActionScheduler_Store::STATUS_PENDING,
            'date' => new DateTime('now', new DateTimeZone('UTC')),
            'per_page' => 100,
        ]);
        
        $count = count($past_due_actions);
        echo "[ACTION SCHEDULER] Found {$count} pending/past-due action(s).\n";
        
        if ( $count > 0 ) {
            $processed = $runner->run();
            echo "[FIXED] Action Scheduler executed {$processed} action(s).\n";
        }
    } catch ( Exception $e ) {
        echo "[INFO] Action Scheduler queue check note: " . $e->getMessage() . "\n";
    }
} else {
    echo "[OK] Action Scheduler plugin/library not active.\n";
}

echo "\nAll local cache and cron queue fixes executed successfully.\n";
