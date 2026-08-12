<?php
require 'wp-load.php';
global $wpdb;
$pages = $wpdb->get_results("SELECT ID, post_title, post_status, post_type FROM {$wpdb->posts} WHERE post_type = 'page'");
if (empty($pages)) {
    echo "No pages found in the database.\n";
} else {
    echo "Found " . count($pages) . " pages.\n";
    foreach ($pages as $p) {
        echo "ID: {$p->ID} | Title: {$p->post_title} | Status: {$p->post_status}\n";
    }
}
