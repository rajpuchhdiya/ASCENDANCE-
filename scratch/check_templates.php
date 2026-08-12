<?php
define('ABSPATH', 'C:/XAMPP/htdocs/Ascendance/');
require_once 'wp-load.php';

$slugs_to_check = [
    'drc-sovereign-rating',
    'us-drc-partnership',
    'lobito-corridor',
    'regulatory-reform-tracker',
    'sar-registry',
    'spa-glossary',
    'cami-registry',
];

foreach ($slugs_to_check as $slug) {
    $page = get_page_by_path($slug);
    if ($page) {
        $tpl = get_page_template_slug($page->ID);
        echo "Page: {$slug} (ID:{$page->ID}) => Template: " . ($tpl ?: 'default') . PHP_EOL;
    } else {
        echo "Page: {$slug} => NOT FOUND in WordPress" . PHP_EOL;
    }
}
