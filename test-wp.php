<?php
require 'wp-load.php';
$page = get_post(33);
if ($page) {
    echo "Page 33 exists. Status: " . $page->post_status . "\n";
} else {
    echo "Page 33 does not exist.\n";
}
$page34 = get_post(34);
if ($page34) {
    echo "Page 34 exists. Status: " . $page34->post_status . "\n";
} else {
    echo "Page 34 does not exist.\n";
}
