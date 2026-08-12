<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/Ascendance/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require_once dirname(__DIR__) . '/wp-load.php';

echo "=== Theme & Template ===\n";
echo "Active Theme: " . get_option('current_theme') . "\n";
echo "Stylesheet: " . get_option('stylesheet') . "\n";
echo "Template: " . get_option('template') . "\n\n";

echo "=== WPS Hide Login Status ===\n";
$whl_page = get_option('whl_page');
echo "WHL Page Option (slug): " . ($whl_page ? $whl_page : '[Not Set / Empty]') . "\n";
$is_whl_active = is_plugin_active('wps-hide-login/wps-hide-login.php');
echo "WHL Plugin Active: " . ($is_whl_active ? 'Yes' : 'No') . "\n\n";

echo "=== PMPro (Paid Memberships Pro) Settings ===\n";
$levels = pmpro_getAllLevels(true, true);
echo "Available Levels (" . count($levels) . "):\n";
foreach ($levels as $l) {
    echo "- ID: " . $l->id . " | Name: " . $l->name . " | Cost: " . $l->initial_payment . "\n";
}

echo "\n=== General Site Stats ===\n";
$user_count = count_users();
echo "Total Users: " . $user_count['total_users'] . "\n";
foreach ($user_count['avail_roles'] as $role => $count) {
    echo "- Role '$role': $count\n";
}
