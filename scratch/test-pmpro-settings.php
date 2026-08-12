<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/Ascendance/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require_once dirname(__DIR__) . '/wp-load.php';

echo "=== PMPro Settings in DB ===\n";
echo "Gateway: " . get_option('pmpro_gateway') . "\n";
echo "Environment: " . get_option('pmpro_gateway_environment') . "\n";
echo "Stripe Publishable Key: " . get_option('pmpro_stripe_publishablekey') . "\n";
echo "Stripe Secret Key: " . get_option('pmpro_stripe_secretkey') . "\n";
echo "Stripe Webhook Secret: " . get_option('pmpro_stripe_webhook_secret') . "\n";
echo "Sandbox Stripe Publishable Key: " . get_option('pmpro_sandbox_stripe_publishablekey') . "\n";
echo "Sandbox Stripe Secret Key: " . get_option('pmpro_sandbox_stripe_secretkey') . "\n";
echo "Live Stripe Publishable Key: " . get_option('pmpro_live_stripe_publishablekey') . "\n";
echo "Live Stripe Secret Key: " . get_option('pmpro_live_stripe_secretkey') . "\n";

echo "\n=== Overridden Settings (after filters) ===\n";
echo "Gateway: " . apply_filters('option_pmpro_gateway', get_option('pmpro_gateway')) . "\n";
echo "Environment: " . apply_filters('option_pmpro_gateway_environment', get_option('pmpro_gateway_environment')) . "\n";
echo "Stripe Publishable Key: " . apply_filters('option_pmpro_stripe_publishablekey', get_option('pmpro_stripe_publishablekey')) . "\n";
echo "Stripe Secret Key: " . apply_filters('option_pmpro_stripe_secretkey', get_option('pmpro_stripe_secretkey')) . "\n";
echo "Stripe Webhook Secret: " . apply_filters('option_pmpro_stripe_webhook_secret', get_option('pmpro_stripe_webhook_secret')) . "\n";
