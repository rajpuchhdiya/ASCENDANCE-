<?php
require 'C:/XAMPP/htdocs/Ascendance/wp-load.php';

$page = get_page_by_path('latest');
if ($page) {
    echo 'Page EXISTS: ID=' . $page->ID . ' template=' . get_post_meta($page->ID, '_wp_page_template', true) . PHP_EOL;
} else {
    echo 'Page NOT FOUND for slug: latest' . PHP_EOL;
    // Create the page
    $new_page_id = wp_insert_post([
        'post_title'   => 'Latest',
        'post_name'    => 'latest',
        'post_status'  => 'publish',
        'post_type'    => 'page',
        'post_content' => '',
    ]);
    if (!is_wp_error($new_page_id)) {
        update_post_meta($new_page_id, '_wp_page_template', 'page-intelligence.php');
        echo 'Created page ID=' . $new_page_id . ' with template page-intelligence.php' . PHP_EOL;
    } else {
        echo 'Error creating page: ' . $new_page_id->get_error_message() . PHP_EOL;
    }
}

echo PHP_EOL . '--- All published pages ---' . PHP_EOL;
$pages = get_pages(['number' => 60, 'post_status' => 'publish']);
foreach ($pages as $p) {
    $tpl = get_post_meta($p->ID, '_wp_page_template', true);
    echo $p->ID . ' | /' . $p->post_name . '/ | ' . ($tpl ?: 'default') . PHP_EOL;
}
