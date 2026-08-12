<?php
require_once('wp-load.php');
$posts = get_posts(['post_type' => 'dossier', 'posts_per_page' => 10, 'orderby' => 'ID', 'order' => 'DESC']);
foreach($posts as $p) {
    echo "- " . $p->post_title . ": " . get_permalink($p->ID) . "\n";
}
