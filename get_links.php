<?php
require("wp-load.php");
$args = array("post_type" => "any", "posts_per_page" => -1);
$posts = get_posts($args);
foreach($posts as $post) {
    echo $post->post_title . " | " . get_permalink($post->ID) . "\n";
}
?>
