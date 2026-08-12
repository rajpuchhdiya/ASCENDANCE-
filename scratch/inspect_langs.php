<?php
define('WP_USE_THEMES', false);
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
require_once(__DIR__ . '/../wp-load.php');

if (function_exists('pll_get_term_language')) {
    echo "Polylang is active!\n";
} else {
    echo "Polylang is not active or function not available.\n";
}

$terms = get_terms(array(
    'taxonomy' => 'topic',
    'hide_empty' => false,
));

echo "ID,Name,Slug,Lang\n";
foreach ($terms as $term) {
    $lang = function_exists('pll_get_term_language') ? pll_get_term_language($term->term_id) : 'N/A';
    echo "{$term->term_id},\"{$term->name}\",\"{$term->slug}\",\"{$lang}\"\n";
}
