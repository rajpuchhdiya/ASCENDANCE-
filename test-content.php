<?php
require 'wp-load.php';
$page = get_page_by_path('us-drc-partnership');
if ($page) {
    echo $page->post_content;
} else {
    echo "Page not found";
}
