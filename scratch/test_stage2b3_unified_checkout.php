<?php
/**
 * Test Stage 2B.3 Unified Checkout Architecture
 */
require 'wp-load.php';

$errors = 0;

function assert_test($condition, $message) {
    global $errors;
    if ($condition) {
        echo "[PASS] $message\n";
    } else {
        echo "[FAIL] $message\n";
        $errors++;
    }
}

echo "Running Stage 2B.3 Unified Checkout Tests...\n\n";

// 1. Unified checkout template is no longer a separate file (injected in page.php)
assert_test(strpos(file_get_contents($theme_dir . '/page.php'), 'membership-checkout') !== false, "Unified checkout injected into page.php");

// 2. Legacy checkouts are removed
assert_test(!file_exists($theme_dir . '/page-essential-checkout.php'), "Legacy essential checkout template removed");
assert_test(!file_exists($theme_dir . '/page-professional-checkout.php'), "Legacy professional checkout template removed");

// 3. Form ID resolution
$simpay_essential    = intval( get_option( 'ascendance_simpay_form_essential', 0 ) );
$simpay_professional = intval( get_option( 'ascendance_simpay_form_professional', 0 ) );
assert_test($simpay_essential > 0, "Essential Form ID is configured ($simpay_essential)");
assert_test($simpay_professional > 0, "Professional Form ID is configured ($simpay_professional)");

// 4. Prices match WP Simple Pay configuration
$essential_amount = get_post_meta($simpay_essential, '_simpay_amount', true);
$professional_amount = get_post_meta($simpay_professional, '_simpay_amount', true);

assert_test((int)$essential_amount === 150 || (is_array($essential_amount) && (int)$essential_amount[0] === 150), "Essential price matches WP Simple Pay ($150)");
assert_test((int)$professional_amount === 299 || (is_array($professional_amount) && (int)$professional_amount[0] === 299), "Professional price matches WP Simple Pay ($299)");

// 5. Test frontend routing (via WP HTTP API to check response codes/redirects)
$site_url = home_url();

// Essential redirect
$response = wp_remote_get($site_url . '/essential-checkout/', array('redirection' => 0));
$code = wp_remote_retrieve_response_code($response);
$location = wp_remote_retrieve_header($response, 'location');
assert_test($code == 301 || $code == 302, "Legacy essential-checkout returns redirect ($code)");
assert_test(strpos($location, 'plan=essential') !== false, "Legacy essential-checkout redirects to ?plan=essential ($location)");

// Professional redirect
$response = wp_remote_get($site_url . '/professional-checkout/', array('redirection' => 0));
$code = wp_remote_retrieve_response_code($response);
$location = wp_remote_retrieve_header($response, 'location');
assert_test($code == 301 || $code == 302, "Legacy professional-checkout returns redirect ($code)");
assert_test(strpos($location, 'plan=professional') !== false, "Legacy professional-checkout redirects to ?plan=professional ($location)");

// Checkout page redirect
$response = wp_remote_get($site_url . '/checkout/?plan=essential', array('redirection' => 0));
$code = wp_remote_retrieve_response_code($response);
$location = wp_remote_retrieve_header($response, 'location');
assert_test($code == 301 || $code == 302, "/checkout/?plan=essential returns redirect ($code)");
assert_test(strpos($location, 'membership-checkout') !== false, "/checkout/ redirects to /membership-checkout/ ($location)");


// Check for hardcoded PMPro dependencies
$functions_php = file_get_contents($theme_dir . '/functions.php');
$pricing_php = file_get_contents($theme_dir . '/page-pricing.php');
assert_test(strpos($pricing_php, 'pmpro_url') === false, "No PMPro URL dependency in pricing page");

echo "\nTests complete. Errors: $errors\n";
if ($errors > 0) {
    exit(1);
}
exit(0);
