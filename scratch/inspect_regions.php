<?php
define('WP_USE_THEMES', false);
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
require_once(__DIR__ . '/../wp-load.php');

$terms = get_terms(array(
    'taxonomy' => 'region',
    'hide_empty' => false,
));

echo "ID,Name,Slug,Count,Lang\n";
foreach ($terms as $term) {
    $lang = function_exists('pll_get_term_language') ? pll_get_term_language($term->term_id) : 'N/A';
    echo "{$term->term_id},\"{$term->name}\",\"{$term->slug}\",{$term->count},\"{$lang}\"\n";
}
