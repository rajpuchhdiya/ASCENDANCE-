<?php
require('wp-load.php');
$pages = get_option('pmpro_pages');
if ($pages) {
    foreach($pages as $key => $id) {
        echo $key . ': ' . get_post_field('post_name', $id) . "\n";
    }
} else {
    echo "pmpro_pages option is empty or false.\n";
}
