<?php
define('WP_USE_THEMES', false);
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
require_once(dirname(__DIR__) . '/wp-load.php');

use Ascendance\Core\Recommendation_Engine;

echo "=== RECOMMENDATION SYSTEM VERIFICATION ===\n\n";

// 1. Configure test scoring parameters in options
update_option('ascendance_rec_topic_score', 10);
update_option('ascendance_rec_region_score', 10);
update_option('ascendance_rec_exact_bonus', 20);
update_option('ascendance_rec_freshness_bonus', 5);
update_option('ascendance_rec_freshness_days', 7);

echo "1. Set Admin Scoring Parameters:\n";
echo "Topic Match Score: " . get_option('ascendance_rec_topic_score') . "\n";
echo "Region Match Score: " . get_option('ascendance_rec_region_score') . "\n";
echo "Exact Match Bonus: " . get_option('ascendance_rec_exact_bonus') . "\n";
echo "Freshness Bonus: " . get_option('ascendance_rec_freshness_bonus') . "\n";
echo "Freshness Window (Days): " . get_option('ascendance_rec_freshness_days') . "\n\n";

// 2. Set user preferences
$user_id = 1; // Testing with admin user
$test_topics = array(37, 38); // e.g. Climate, Tech
$test_regions = array(47, 45); // e.g. Global, Sahel
update_user_meta($user_id, 'preferred_topics', $test_topics);
update_user_meta($user_id, 'preferred_regions', $test_regions);

echo "2. Simulated User Preferences:\n";
echo "Preferred Topics: " . implode(', ', $test_topics) . "\n";
echo "Preferred Regions: " . implode(', ', $test_regions) . "\n\n";

// 3. Score calculation logic check
$rec_engine = Recommendation_Engine::get_instance();

// Fetch some posts to score
$args = array(
    'post_type'      => array('brief', 'dossier', 'update'),
    'posts_per_page' => 10,
    'post_status'    => 'publish',
);
$query = new WP_Query($args);

if ($query->have_posts()) {
    echo "3. Scoring Candidate Posts:\n";
    while ($query->have_posts()) {
        $query->the_post();
        $post_id = get_the_ID();
        $score_details = $rec_engine->calculate_post_score($post_id, $user_id);
        
        $post_topics = wp_get_post_terms($post_id, 'topic', array('fields' => 'names'));
        $post_regions = wp_get_post_terms($post_id, 'region', array('fields' => 'names'));
        
        echo sprintf(
            "Post [%d] '%s'\n  Type: %s\n  Topics: %s\n  Regions: %s\n",
            $post_id,
            get_the_title(),
            get_post_type(),
            implode(', ', $post_topics) ?: 'None',
            implode(', ', $post_regions) ?: 'None'
        );
        echo sprintf(
            "  SCORING Breakdown: Total = %d | Topic Match = %d | Region Match = %d | Exact Match Bonus = %d | Freshness Bonus = %d\n\n",
            $score_details['total_score'],
            $score_details['topic_score'],
            $score_details['region_score'],
            $score_details['exact_bonus'],
            $score_details['freshness_bonus']
        );
    }
    wp_reset_postdata();
} else {
    echo "No posts found to test with.\n\n";
}

// 4. Test Ranked Recommendations query
echo "4. Querying Ranked Recommendations (Top 5):\n";
$ranked = $rec_engine->get_ranked_recommendations($user_id, array('brief', 'dossier', 'update'), 5);

if (!empty($ranked)) {
    foreach ($ranked as $rank => $item) {
        $post = $item['post'];
        $score_details = $item['score_details'];
        echo sprintf(
            "[%d] Score: %d | Title: '%s' | Date: %s | Type: %s\n",
            $rank + 1,
            $score_details['total_score'],
            $post->post_title,
            $post->post_date,
            $post->post_type
        );
    }
} else {
    echo "No recommendations retrieved.\n";
}
