<?php
/**
 * Script: create-test-subscribers.php
 * Purpose: Creates three test subscriber accounts — one per membership tier.
 *
 * Users created:
 *   subscriber.essential@ascendance.test   → Essential (Level 1,  $150/mo)
 *   subscriber.professional@ascendance.test → Professional (Level 2, $299/mo)
 *   subscriber.enterprise@ascendance.test   → Enterprise (Level 3, $599/mo)
 *
 * Run once from the project root:
 *   php scripts/create-test-subscribers.php
 */

$_SERVER['HTTP_HOST']      = 'localhost';
$_SERVER['REQUEST_URI']    = '/Ascendance/';
$_SERVER['REQUEST_METHOD'] = 'GET';

require_once __DIR__ . '/../wp-load.php';

// ── Test User Definitions ─────────────────────────────────────────────────────
$test_users = [
    [
        'username'    => 'sub_essential',
        'email'       => 'subscriber.essential@ascendance.test',
        'password'    => 'Essential@Test2026!',
        'display'     => 'Alex Chen (Essential)',
        'first_name'  => 'Alex',
        'last_name'   => 'Chen',
        'level_id'    => 1,
        'level_name'  => 'Essential',
    ],
    [
        'username'    => 'sub_professional',
        'email'       => 'subscriber.professional@ascendance.test',
        'password'    => 'Professional@Test2026!',
        'display'     => 'Sarah Mitchell (Professional)',
        'first_name'  => 'Sarah',
        'last_name'   => 'Mitchell',
        'level_id'    => 2,
        'level_name'  => 'Professional',
    ],
    [
        'username'    => 'sub_enterprise',
        'email'       => 'subscriber.enterprise@ascendance.test',
        'password'    => 'Enterprise@Test2026!',
        'display'     => 'James Thornton (Enterprise)',
        'first_name'  => 'James',
        'last_name'   => 'Thornton',
        'level_id'    => 3,
        'level_name'  => 'Enterprise',
    ],
];

// ── PMPro availability check ──────────────────────────────────────────────────
if ( ! function_exists( 'pmpro_changeMembershipLevel' ) ) {
    echo "ERROR: Paid Memberships Pro is not active. Cannot assign membership levels.\n";
    exit( 1 );
}

echo "\n=== Ascendance — Create Test Subscribers ===\n\n";

foreach ( $test_users as $def ) {
    echo "── {$def['level_name']} Tier ──────────────────────────\n";

    // ── 1. Check if user already exists ──────────────────────────────────────
    $existing_id = username_exists( $def['username'] );
    if ( ! $existing_id ) {
        $existing_id = email_exists( $def['email'] );
    }

    if ( $existing_id ) {
        echo "  ⚠  User already exists (ID: {$existing_id}) — updating password & level.\n";
        $user_id = $existing_id;

        // Update password
        wp_set_password( $def['password'], $user_id );
        echo "  ✔  Password reset.\n";
    } else {
        // ── 2. Create the WordPress user ──────────────────────────────────────
        $user_id = wp_create_user(
            $def['username'],
            $def['password'],
            $def['email']
        );

        if ( is_wp_error( $user_id ) ) {
            echo "  ✘  Failed to create user: " . $user_id->get_error_message() . "\n\n";
            continue;
        }

        echo "  ✔  WordPress user created (ID: {$user_id}).\n";

        // Set profile meta
        wp_update_user( [
            'ID'           => $user_id,
            'display_name' => $def['display'],
            'first_name'   => $def['first_name'],
            'last_name'    => $def['last_name'],
            'role'         => 'subscriber',
        ] );
        echo "  ✔  Profile meta set ({$def['display']}).\n";
    }

    // ── 3. Assign PMPro membership level ─────────────────────────────────────
    $level_set = pmpro_changeMembershipLevel( $def['level_id'], $user_id, 'admin_changed' );

    if ( $level_set ) {
        echo "  ✔  Membership level assigned: {$def['level_name']} (Level ID: {$def['level_id']}).\n";
    } else {
        echo "  ✘  Failed to assign membership level {$def['level_id']}.\n";
    }

    // ── 4. Summary line ───────────────────────────────────────────────────────
    echo "  📧 Email:    {$def['email']}\n";
    echo "  🔑 Password: {$def['password']}\n";
    echo "\n";
}

echo "=== Done ===\n\n";
echo "Login URL: http://localhost/Ascendance/login/\n\n";
