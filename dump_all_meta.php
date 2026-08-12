<?php
require_once 'wp-load.php';
$post_id = 1005;

// Dump all meta
$meta = get_post_meta($post_id);
echo "=== POST META ===\n";
foreach ($meta as $key => $values) {
    foreach ($values as $value) {
        if (strpos($value, 'Home') !== false || strpos($value, 'rsaquo') !== false || strpos($value, '›') !== false) {
            echo "Key: $key | Value contains breadcrumb hint!\n";
            echo "Value: " . substr($value, 0, 200) . "\n\n";
        }
    }
}

// Dump the post content itself to search for it
$post = get_post($post_id);
echo "=== POST CONTENT ===\n";
echo $post->post_content . "\n";
