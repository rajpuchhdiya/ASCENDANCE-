<?php
// Run via WP-CLI: php C:\wp-cli\wp-cli.phar eval-file scripts/launch_production.php --path='C:\XAMPP\htdocs\Ascendance'
// IMPORTANT: This script does NOT change secrets. Ensure production keys are already in .env or environment.

if (!defined('WP_CLI')) {
    // Running via php eval-file; ensure WP environment loaded
}

// Basic safety checks
if (!file_exists(ABSPATH . '.env')) {
    echo "Warning: .env not found at " . ABSPATH . ". Proceed only if environment variables are set elsewhere.\n";
}

// 1) Flush rewrite rules
if (function_exists('flush_rewrite_rules')) {
    flush_rewrite_rules(true);
    echo "Flushed rewrite rules.\n";
} else {
    echo "flush_rewrite_rules not available (are you in WP context?).\n";
}

// 2) Clear object cache if WP-CLI cache commands available
if (class_exists('WP_CLI')) {
    echo "WP-CLI present. You can run cache commands manually if needed.\n";
}

// 3) Ensure Wordfence protecting
if (class_exists('wfConfig')) {
    wfConfig::set('firewallEnabled', true);
    echo "Set Wordfence firewallEnabled = true\n";
}

// 4) Ping sitemaps
$sitemap = (get_home_url() . '/sitemap.xml');
$endpoints = [
    'https://www.google.com/ping?sitemap=' . urlencode($sitemap),
    'https://www.bing.com/webmaster/ping.aspx?siteMap=' . urlencode($sitemap)
];
foreach ($endpoints as $u) {
    echo "Pinging: $u\n";
    $resp = wp_remote_get($u, array('timeout' => 10, 'sslverify' => false));
    if (is_wp_error($resp)) {
        echo "Ping failed: " . $resp->get_error_message() . "\n";
    } else {
        echo "Ping HTTP status: " . wp_remote_retrieve_response_code($resp) . "\n";
    }
}

// 5) Start a Wordfence scan (non-remote if remote not configured)
if (class_exists('wfScanEngine') && class_exists('wfScanner')) {
    try {
        wfScanEngine::startScan(false, wfScanner::SCAN_TYPE_QUICK);
        echo "Started quick Wordfence scan.\n";
    } catch (Exception $e) {
        echo "Could not start Wordfence scan: " . $e->getMessage() . "\n";
    }
}

// 6) Basic smoke checks (HTTP status checks for key endpoints)
$paths = [ '/', '/wp-login.php', '/wp-admin/', '/sitemap.xml', '/checkout', '/my-account' ];
$home = get_home_url();
$results = [];
foreach ($paths as $p) {
    $url = rtrim($home, '/') . $p;
    $r = wp_remote_head($url, array('timeout' => 10, 'sslverify' => false));
    if (is_wp_error($r)) {
        $results[$url] = 'ERROR: ' . $r->get_error_message();
    } else {
        $results[$url] = 'HTTP ' . wp_remote_retrieve_response_code($r);
    }
    echo "$url -> " . $results[$url] . "\n";
}

// 7) Write results to file for post-launch review
$log = ABSPATH . 'logs/launch_' . date('Ymd_His') . '.log';
@mkdir(dirname($log), 0755, true);
file_put_contents($log, "Launch run at " . date('c') . "\n\n" . print_r($results, true));

echo "Launch helper finished. Log written to: $log\n";
