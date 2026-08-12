<?php
// Test Script for STAGE 2C — FINAL PMPRO + STRIPE NATIVE BILLING ARCHITECTURE
// Run this via CLI: wp eval-file scratch/test_stage2c_pmpro_stripe_flow.php

echo "==================================================\n";
echo "STAGE 2C: PMPRO NATIVE STRIPE BILLING ARCHITECTURE\n";
echo "==================================================\n\n";

$pass_count = 0;
$fail_count = 0;

function assert_test( $name, $condition, $fail_msg = '' ) {
    global $pass_count, $fail_count;
    if ( $condition ) {
        echo "[PASS] " . $name . "\n";
        $pass_count++;
    } else {
        echo "[FAIL] " . $name . ( $fail_msg ? " - " . $fail_msg : "" ) . "\n";
        $fail_count++;
    }
}

// 1. PMPro active
assert_test( "PMPro is active", defined( 'PMPRO_VERSION' ) );

// 2. Stripe gateway active
$gateway = get_option( 'pmpro_gateway' );
assert_test( "PMPro Gateway is Stripe", $gateway === 'stripe', "Current gateway: " . $gateway );

// 3, 4, 6, 7, 8, 9. Levels and pricing
if ( function_exists( 'pmpro_getAllLevels' ) ) {
    $levels = pmpro_getAllLevels(true, true);
    $essential = isset($levels[1]) ? $levels[1] : null;
    $professional = isset($levels[2]) ? $levels[2] : null;

    assert_test( "Essential Level exists (ID 1)", $essential !== null );
    if ( $essential ) {
        assert_test( "Essential initial price is 150", floatval($essential->initial_payment) === 150.0, "Found: " . $essential->initial_payment );
        assert_test( "Essential billing amount is 150", floatval($essential->billing_amount) === 150.0, "Found: " . $essential->billing_amount );
        assert_test( "Essential billing cycle is 1 Month", $essential->cycle_number == 1 && $essential->cycle_period == 'Month', "Found: " . $essential->cycle_number . ' ' . $essential->cycle_period );
    }

    assert_test( "Professional Level exists (ID 2)", $professional !== null );
    if ( $professional ) {
        assert_test( "Professional initial price is 299", floatval($professional->initial_payment) === 299.0, "Found: " . $professional->initial_payment );
        assert_test( "Professional billing amount is 299", floatval($professional->billing_amount) === 299.0, "Found: " . $professional->billing_amount );
        assert_test( "Professional billing cycle is 1 Month", $professional->cycle_number == 1 && $professional->cycle_period == 'Month', "Found: " . $professional->cycle_number . ' ' . $professional->cycle_period );
    }
} else {
    echo "[FAIL] PMPro functions not found.\n";
    $fail_count += 7;
}

// Verify page.php does not contain simpay
$page_php = file_get_contents( get_stylesheet_directory() . '/page.php' );
assert_test( "page.php has no WP Simple Pay references", strpos( $page_php, 'simpay' ) === false );

// Verify page-pricing.php does not contain simpay
$pricing_php = file_get_contents( get_stylesheet_directory() . '/page-pricing.php' );
assert_test( "page-pricing.php has no WP Simple Pay references", strpos( $pricing_php, 'simpay' ) === false );

// Verify functions.php has no WP Simple Pay redirects
$functions_php = file_get_contents( get_stylesheet_directory() . '/functions.php' );
assert_test( "functions.php redirects use ?level=", strpos( $functions_php, '?level=' ) !== false && strpos( $functions_php, '?plan=essential' ) === false );

// Check Stripe Secret Keys are not hardcoded
assert_test( "No hardcoded Stripe secrets in page.php", strpos( $page_php, 'sk_test_' ) === false && strpos( $page_php, 'sk_live_' ) === false );
assert_test( "No hardcoded Stripe secrets in functions.php", strpos( $functions_php, 'sk_test_' ) === false && strpos( $functions_php, 'sk_live_' ) === false );

echo "\n==================================================\n";
echo "TEST RESULTS: $pass_count Passed | $fail_count Failed\n";
echo "==================================================\n";

if ( $fail_count > 0 ) {
    exit(1);
} else {
    exit(0);
}
