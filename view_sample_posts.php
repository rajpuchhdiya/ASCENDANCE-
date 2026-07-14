<?php
require_once 'wp-load.php';
$post_ids = [12, 46];
foreach ($post_ids as $id) {
    $p = get_post($id);
    if ($p) {
        echo "===========================================\n";
        echo "ID: " . $p->ID . " | Title: " . $p->post_title . "\n";
        echo "Tier Access: " . get_field('tier_access', $p->ID) . "\n";
        echo "Analytical Claim: " . get_field('analytical_claim', $p->ID) . "\n";
        echo "Public Excerpt: " . get_field('public_excerpt', $p->ID) . "\n";
        echo "Executive Summary: " . get_field('executive_summary', $p->ID) . "\n";
        echo "--- CONTENT ---\n";
        echo $p->post_content . "\n";
    }
}
