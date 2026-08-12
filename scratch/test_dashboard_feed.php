<?php
define('WP_USE_THEMES', false);
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
require_once(__DIR__ . '/../wp-load.php');

$user_id = 1; // Assuming admin user ID is 1

// Topics we want to select: Climate (37), Technology & Innovation (38)
// Regions we want to select: Global (47), Sahel (45)
$selected_topics = array(37, 38);
$selected_regions = array(47, 45);

echo "1. Saving simulated preferences for User ID {$user_id}...\n";
update_user_meta($user_id, 'preferred_topics', $selected_topics);
update_user_meta($user_id, 'preferred_regions', $selected_regions);
echo "Preferences saved successfully!\n\n";

// 2. Retrieve and verify preferences
$saved_topics = get_user_meta($user_id, 'preferred_topics', true);
$saved_regions = get_user_meta($user_id, 'preferred_regions', true);

echo "2. Retrieved saved preferences:\n";
echo "Preferred Topics: " . implode(', ', $saved_topics) . "\n";
echo "Preferred Regions: " . implode(', ', $saved_regions) . "\n\n";

// 3. Query recommended feed items based on these preferences
echo "3. Querying recommended feed items...\n";
$tax_query = array('relation' => 'OR');

if (!empty($saved_topics)) {
    $tax_query[] = array(
        'taxonomy' => 'topic',
        'field'    => 'term_id',
        'terms'    => $saved_topics,
    );
}

if (!empty($saved_regions)) {
    $tax_query[] = array(
        'taxonomy' => 'region',
        'field'    => 'term_id',
        'terms'    => $saved_regions,
    );
}

$args = array(
    'post_type'      => array('brief', 'dossier'),
    'posts_per_page' => 5,
    'post_status'    => 'publish',
);

if (count($tax_query) > 1) {
    $args['tax_query'] = $tax_query;
}

$query = new WP_Query($args);

if ($query->have_posts()) {
    echo "Found " . $query->found_posts . " matching recommendations:\n";
    while ($query->have_posts()) {
        $query->the_post();
        $post_type = get_post_type();
        echo "  - [" . strtoupper($post_type) . "] " . get_the_title() . "\n";
        
        // Show associated topics and regions
        $post_topics = wp_get_post_terms(get_the_ID(), 'topic', array('fields' => 'names'));
        $post_regions = wp_get_post_terms(get_the_ID(), 'region', array('fields' => 'names'));
        echo "    Topics: " . implode(', ', $post_topics) . "\n";
        echo "    Regions: " . implode(', ', $post_regions) . "\n";
    }
    wp_reset_postdata();
} else {
    echo "No matching recommended posts found.\n";
}
