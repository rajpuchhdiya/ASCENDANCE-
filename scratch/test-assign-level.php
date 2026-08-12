<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/Ascendance/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require_once dirname(__DIR__) . '/wp-load.php';

global $wpdb;
// Enable error output for wpdb
$wpdb->show_errors();

$user_id = 3; // Let's use the essential subscriber user ID
$level_id = 1; // Essential level

echo "Attempting to assign level $level_id to user $user_id...\n";

// Check if PMPro functions are defined
if (!function_exists('pmpro_changeMembershipLevel')) {
    echo "pmpro_changeMembershipLevel function not found.\n";
    exit(1);
}

// Clear any previous error
$wpdb->last_error = '';

$result = pmpro_changeMembershipLevel($level_id, $user_id, 'admin_changed');

if ($result) {
    echo "SUCCESS: Level assigned successfully.\n";
} else {
    echo "FAIL: Level assignment returned false.\n";
    if (!empty($wpdb->last_error)) {
        echo "Database Error: " . $wpdb->last_error . "\n";
    } else {
        echo "No database error recorded. Checking PMPro state...\n";
        // Check if the user already has this level active
        if (pmpro_hasMembershipLevel($level_id, $user_id)) {
            echo "Note: User already has this level active!\n";
        } else {
            echo "User does NOT have this level. Investigating pmpro_memberships_users table...\n";
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}pmpro_memberships_users'");
            if ($table_exists) {
                echo "Table {$wpdb->prefix}pmpro_memberships_users exists.\n";
                // Let's dump the columns
                $columns = $wpdb->get_results("DESCRIBE {$wpdb->prefix}pmpro_memberships_users");
                print_r($columns);
            } else {
                echo "ERROR: Table {$wpdb->prefix}pmpro_memberships_users does NOT exist!\n";
            }
        }
    }
}
