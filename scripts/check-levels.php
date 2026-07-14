<?php
/**
 * Script: check-levels.php
 * Purpose: List all PMPro membership levels for tier inspection.
 * Usage: php scripts/check-levels.php
 */

$_SERVER['HTTP_HOST']   = 'localhost';
$_SERVER['REQUEST_URI'] = '/Ascendance/';
$_SERVER['REQUEST_METHOD'] = 'GET';

require_once __DIR__ . '/../wp-load.php';

global $wpdb;

$levels = $wpdb->get_results(
    "SELECT id, name, description, initial_payment, billing_amount, cycle_period, cycle_number
     FROM {$wpdb->prefix}pmpro_membership_levels
     ORDER BY id ASC"
);

if ( empty( $levels ) ) {
    echo "No PMPro membership levels found.\n";
} else {
    echo "=== PMPro Membership Levels ===\n";
    foreach ( $levels as $level ) {
        echo "ID: {$level->id} | Name: {$level->name} | Price: \${$level->initial_payment} | Billing: \${$level->billing_amount}/{$level->cycle_period}\n";
    }
}
