<?php
define('WP_USE_THEMES', false);
require_once(__DIR__ . '/../wp-load.php');

$terms = get_terms(array(
    'taxonomy' => 'topic',
    'hide_empty' => false,
));

if (is_wp_error($terms)) {
    echo "Error: " . $terms->get_error_message() . "\n";
    exit;
}

echo "ID,Name,Slug,Count\n";
foreach ($terms as $term) {
    echo "{$term->term_id},\"{$term->name}\",\"{$term->slug}\",{$term->count}\n";
}
