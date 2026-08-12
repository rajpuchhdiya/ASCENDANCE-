<?php
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/Ascendance/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
require_once dirname(__DIR__) . '/wp-load.php';

echo "=== Permalink Configuration ===\n";
$structure = get_option('permalink_structure');
echo "Permalink Structure: " . ($structure ? $structure : 'Default (Plain)') . "\n\n";

echo "=== Post Permalinks ===\n";
$posts = [12, 46];
foreach ($posts as $id) {
    $p = get_post($id);
    if ($p) {
        echo "Post ID $id:\n";
        echo "  Title: " . $p->post_title . "\n";
        echo "  Type: " . $p->post_type . "\n";
        echo "  Status: " . $p->post_status . "\n";
        echo "  Slug: " . $p->post_name . "\n";
        echo "  Permalink: " . get_permalink($id) . "\n";
    } else {
        echo "Post ID $id not found.\n";
    }
}
echo "\n";

echo "Flushing rewrite rules...\n";
global $wp_rewrite;
$wp_rewrite->init();
flush_rewrite_rules(true);
echo "Rewrite rules flushed successfully!\n";

echo "\nNew Permalinks after flush:\n";
foreach ($posts as $id) {
    $p = get_post($id);
    if ($p) {
        echo "Post ID $id Permalink: " . get_permalink($id) . "\n";
    }
}
