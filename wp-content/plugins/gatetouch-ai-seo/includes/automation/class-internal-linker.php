<?php
defined( 'ABSPATH' ) || exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Internal-link automation does a bounded metadata lookup for candidate targets.
// phpcs:disable WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in -- Candidate lookup excludes the current post from a one-item fallback result set.

/**
 * AI-Powered Auto-Internal Linker
 */
class GateTouch_Internal_Linker {

    public function __construct() {
        // Automation only on publish
        add_action( 'save_post', [ $this, 'process_post' ], 20, 3 );
    }

    /**
     * Analyze post and insert contextual links
     */
    public function process_post( $post_id, WP_Post $post, $update ) {
        // Basic safety checks
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! $update || wp_is_post_revision( $post_id ) ) return;
        if ( $post->post_status !== 'publish' ) return;
        if ( ! in_array( $post->post_type, [ 'post', 'page' ], true ) ) return;

        // Configuration check
        $enabled = get_option( 'gatetouch_auto_linker', 'no' );
        if ( $enabled !== 'yes' ) return;

        // Prevent infinite loops
        static $processed = [];
        if ( in_array( $post_id, $processed ) ) return;
        $processed[] = $post_id;

        $content = $post->post_content;
        
        // Don't over-link
        $existing_links = preg_match_all( '/<a\s+[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $content, $matches );
        if ( $existing_links > 5 ) return;

        // 1. Extract Entities from the new post
        $entities_res = GateTouch_AI_Engine::extract_entities( $content );
        $entities     = $entities_res['entities'] ?? [];
        if ( empty( $entities ) ) return;

        $links_added = 0;
        $max_links   = 3;

        foreach ( $entities as $entity ) {
            if ( $links_added >= $max_links ) break;

            // 2. Find a relevant post for this entity
            $target_post = $this->find_link_target( $entity, $post_id );
            if ( ! $target_post ) continue;

            // 3. Insert link into content (First occurrence only to be safe)
            // We use a regex that avoids existing links and tags
            $pattern = '/(?<!<a[^>]*?)\b(' . preg_quote( $entity, '/' ) . ')\b(?![^<]*?<\/a>)/i';
            $replacement = '<a href="' . get_permalink( $target_post->ID ) . '">$1</a>';
            
            $new_content = preg_replace( $pattern, $replacement, $content, 1, $count );
            
            if ( $count > 0 ) {
                $content = $new_content;
                $links_added++;
            }
        }

        // 4. Update the post if links were added
        if ( $links_added > 0 ) {
            wp_update_post( [
                'ID'           => $post_id,
                'post_content' => $content,
            ] );
        }
    }

    /**
     * Search for a post that matches the entity
     */
    private function find_link_target( $entity, $exclude_id ) {
        global $wpdb;
        
        // Search by focus keyword first (meta)
        $results = $wpdb->get_results( $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} 
             WHERE meta_key = %s 
             AND meta_value LIKE %s 
             AND post_id != %d 
             LIMIT 1",
            GATETOUCH_META_KEY,
            '%' . $wpdb->esc_like( $entity ) . '%',
            $exclude_id
        ) );

        if ( ! empty( $results ) ) {
            $post = get_post( $results[0]->post_id );
            if ( $post && $post->post_status === 'publish' ) return $post;
        }

        // Fallback: search by title
        $args = [
            's'              => $entity,
            'post_type'      => [ 'post', 'page' ],
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'post__not_in'   => [ $exclude_id ],
        ];
        $posts = get_posts( $args );
        
        return ! empty( $posts ) ? $posts[0] : null;
    }
}

new GateTouch_Internal_Linker();
