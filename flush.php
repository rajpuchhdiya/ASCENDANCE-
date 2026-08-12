<?php
require 'wp-load.php';
update_option('show_on_front', 'posts');
update_option('page_on_front', 0);
update_option('page_for_posts', 0);
flush_rewrite_rules();
echo "Updated options and flushed rewrite rules.\n";
