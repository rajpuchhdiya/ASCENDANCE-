<?php
define('WP_USE_THEMES', false);
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
require_once(__DIR__ . '/../wp-load.php');

// Define duplicates to merge
// Format: Name => array(Keep ID, Delete ID)
$duplicates = array(
    'Critical Minerals' => array('keep' => 19, 'delete' => 102),
    'Geopolitics'       => array('keep' => 20, 'delete' => 105),
    'Infrastructure'    => array('keep' => 23, 'delete' => 106),
    'Investment'        => array('keep' => 30, 'delete' => 107),
);

global $wpdb;

echo "Starting merging process for topic taxonomy...\n\n";

foreach ($duplicates as $name => $ids) {
    $keep_id = $ids['keep'];
    $delete_id = $ids['delete'];
    
    echo "Processing '{$name}': Keeping ID {$keep_id}, Deleting ID {$delete_id}\n";
    
    // 1. Find posts associated with the term to be deleted
    $post_ids = get_objects_in_term($delete_id, 'topic');
    if (!empty($post_ids) && !is_wp_error($post_ids)) {
        echo "  Found " . count($post_ids) . " posts assigned to ID {$delete_id}. Reassigning to ID {$keep_id}...\n";
        foreach ($post_ids as $post_id) {
            // Append the kept term to the post
            wp_set_object_terms($post_id, array((int)$keep_id), 'topic', true);
            // Remove the deleted term from the post
            wp_remove_object_terms($post_id, array((int)$delete_id), 'topic');
            echo "    Reassigned post ID {$post_id}\n";
        }
    } else {
        echo "  No posts assigned to ID {$delete_id}.\n";
    }

    // 2. Update user meta preferred_topics referencing the deleted term ID
    // preferred_topics is serialized array of term IDs in the usermeta table
    $meta_rows = $wpdb->get_results("SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'preferred_topics'");
    foreach ($meta_rows as $row) {
        $user_id = $row->user_id;
        $preferred = maybe_unserialize($row->meta_value);
        if (is_array($preferred) && in_array($delete_id, $preferred)) {
            echo "  User ID {$user_id} has preferred topic ID {$delete_id}. Updating to {$keep_id}...\n";
            // Remove delete_id
            $preferred = array_diff($preferred, array($delete_id));
            // Add keep_id if not already present
            if (!in_array($keep_id, $preferred)) {
                $preferred[] = $keep_id;
            }
            // Reset keys and update
            $preferred = array_values(array_map('intval', $preferred));
            update_user_meta($user_id, 'preferred_topics', $preferred);
        }
    }

    // 3. Delete the duplicate term
    echo "  Deleting term ID {$delete_id}...\n";
    $result = wp_delete_term($delete_id, 'topic');
    if (is_wp_error($result)) {
        echo "  Error deleting term ID {$delete_id}: " . $result->get_error_message() . "\n";
    } else {
        echo "  Successfully deleted term ID {$delete_id}.\n";
    }
    
    echo "\n";
}

echo "Merging process completed successfully!\n";
