<?php
/**
 * Content Migration Utility Script
 * 
 * This script runs via PHP CLI to migrate legacy Elementor-based posts and pages 
 * into the custom post types (brief, update, dossier) with clean block editor markup.
 * 
 * Usage: php scripts/migrate-legacy.php [options]
 * Options:
 *   --dry-run                 Simulate the migration without modifying the database.
 *   --post-id=<id>            Migrate a specific post ID.
 *   --source-type=<type>      Source post type (default: post).
 *   --target-type=<type>      Target custom post type (brief|update|dossier, default: brief).
 *   --tier=<tier-slug>        Taxonomy tier to assign (public|essential|professional).
 *   --topic=<topic-slug>      Topic taxonomy slug to assign.
 *   --region=<region-slug>    Region taxonomy slug to assign.
 * 
 * @package Ascendance\Core
 */

// Ensure this runs in CLI only
if ( PHP_SAPI !== 'cli' ) {
    die( "This script can only be run from the command line.\n" );
}

// Bootstrap WordPress
$wp_load_path = dirname( __DIR__ ) . '/wp-load.php';
if ( ! file_exists( $wp_load_path ) ) {
    die( "Error: wp-load.php not found at {$wp_load_path}\n" );
}
require_once $wp_load_path;

// Parse command line arguments
$options = getopt( '', array(
    'dry-run',
    'post-id::',
    'source-type::',
    'target-type::',
    'tier::',
    'topic::',
    'region::'
) );

$dry_run     = isset( $options['dry-run'] );
$post_id     = isset( $options['post-id'] ) ? (int) $options['post-id'] : null;
$source_type = isset( $options['source-type'] ) ? $options['source-type'] : 'post';
$target_type = isset( $options['target-type'] ) ? $options['target-type'] : 'brief';
$tier_slug   = isset( $options['tier'] ) ? $options['tier'] : 'essential';
$topic_slug  = isset( $options['topic'] ) ? $options['topic'] : null;
$region_slug = isset( $options['region'] ) ? $options['region'] : null;

echo "=======================================================\n";
echo "    ASCENDANCE INTEL - CONTENT MIGRATION TOOL          \n";
echo "=======================================================\n";
echo "Mode:          " . ( $dry_run ? "DRY-RUN (Simulating)" : "LIVE (Writing to Database)" ) . "\n";
echo "Source Type:   {$source_type}\n";
echo "Target Type:   {$target_type}\n";
echo "Default Tier:  {$tier_slug}\n";
if ( $post_id ) {
    echo "Filtering ID:  {$post_id}\n";
}
echo "-------------------------------------------------------\n";

// Verify target post type is valid
$valid_targets = array( 'brief', 'update', 'dossier' );
if ( ! in_array( $target_type, $valid_targets ) ) {
    die( "Error: Invalid target post type. Must be one of: " . implode( ', ', $valid_targets ) . "\n" );
}

// Query source posts
$query_args = array(
    'post_type'      => $source_type,
    'posts_per_page' => -1,
    'post_status'    => 'any',
);

if ( $post_id ) {
    $query_args['p'] = $post_id;
}

$source_posts = get_posts( $query_args );

if ( empty( $source_posts ) ) {
    echo "No source posts found matching the criteria.\n";
    exit(0);
}

echo "Found " . count( $source_posts ) . " post(s) to migrate.\n\n";

$migrated_count = 0;
$failed_count   = 0;
$redirects      = array();

foreach ( $source_posts as $post ) {
    echo "Processing Post ID: {$post->ID} | Title: '{$post->post_title}'\n";
    
    // 1. Clean content (Strip Elementor junk, convert to Gutenberg blocks)
    $clean_content = clean_elementor_content( $post->post_content );
    
    // Calculate post excerpt if empty
    $excerpt = $post->post_excerpt;
    if ( empty( $excerpt ) ) {
        $excerpt = wp_strip_all_tags( $clean_content );
        $excerpt = wp_html_excerpt( $excerpt, 150, '...' );
    }

    $post_data = array(
        'post_title'   => $post->post_title,
        'post_content' => $clean_content,
        'post_excerpt' => $excerpt,
        'post_status'  => $post->post_status,
        'post_author'  => $post->post_author,
        'post_date'    => $post->post_date,
        'post_date_gmt'=> $post->post_date_gmt,
        'post_type'    => $target_type,
    );

    // 2. Perform Migration
    if ( $dry_run ) {
        echo "  [Dry-Run] Would create new CPT post:\n";
        echo "    - Title: {$post->post_title}\n";
        echo "    - Excerpt: {$excerpt}\n";
        echo "    - Content length: " . strlen( $clean_content ) . " characters (Cleaned from " . strlen( $post->post_content ) . ")\n";
        
        // Track the redirect mapping
        $source_url = get_permalink( $post->ID );
        $target_url = home_url( "/{$target_type}s/{$post->post_name}/" );
        echo "    - Redirect Map: {$source_url} -> {$target_url}\n";
        $redirects[] = array( 'from' => $source_url, 'to' => $target_url );
        $migrated_count++;
    } else {
        // Insert new post
        $new_post_id = wp_insert_post( $post_data, true );
        
        if ( is_wp_error( $new_post_id ) ) {
            echo "  [Error] Failed to insert post: " . $new_post_id->get_error_message() . "\n";
            $failed_count++;
            continue;
        }
        
        echo "  [Success] Created new {$target_type} CPT with ID: {$new_post_id}\n";
        
        // Setup ACF metadata fields
        update_field( 'ai_generated', 0, $new_post_id );
        update_field( 'public_excerpt', $excerpt, $new_post_id );
        
        // Extract first 1-2 paragraphs for analytical claim if brief CPT
        if ( $target_type === 'brief' ) {
            $paragraphs = filter_paragraphs( $clean_content );
            $claim = ! empty( $paragraphs ) ? $paragraphs[0] : $excerpt;
            update_field( 'analytical_claim', $claim, $new_post_id );
        }
        
        // Set taxonomy: Tier
        $tier_term = get_term_by( 'slug', $tier_slug, 'tier' );
        if ( $tier_term ) {
            wp_set_object_terms( $new_post_id, $tier_term->term_id, 'tier' );
            echo "    - Assigned Tier: {$tier_term->name}\n";
        } else {
            // Create tier term if it doesn't exist
            $new_term = wp_insert_term( ucfirst( $tier_slug ), 'tier', array( 'slug' => $tier_slug ) );
            if ( ! is_wp_error( $new_term ) ) {
                wp_set_object_terms( $new_post_id, $new_term['term_id'], 'tier' );
                echo "    - Created and Assigned Tier: {$tier_slug}\n";
            }
        }
        
        // Set taxonomy: Topic (if supplied)
        if ( $topic_slug ) {
            $topic_term = get_term_by( 'slug', $topic_slug, 'topic' );
            if ( $topic_term ) {
                wp_set_object_terms( $new_post_id, $topic_term->term_id, 'topic' );
                echo "    - Assigned Topic: {$topic_term->name}\n";
            }
        }
        
        // Set taxonomy: Region (if supplied)
        if ( $region_slug ) {
            $region_term = get_term_by( 'slug', $region_slug, 'region' );
            if ( $region_term ) {
                wp_set_object_terms( $new_post_id, $region_term->term_id, 'region' );
                echo "    - Assigned Region: {$region_term->name}\n";
            }
        }

        // Copy Featured Image if exists
        $thumbnail_id = get_post_thumbnail_id( $post->ID );
        if ( $thumbnail_id ) {
            set_post_thumbnail( $new_post_id, $thumbnail_id );
            echo "    - Migrated Featured Image ID: {$thumbnail_id}\n";
        }
        
        // Log redirect maps
        $source_url = wp_make_link_relative( get_permalink( $post->ID ) );
        $target_url = wp_make_link_relative( get_permalink( $new_post_id ) );
        echo "    - Redirect registered: {$source_url} -> {$target_url}\n";
        
        // Save to Rank Math redirect tables if available
        register_rank_math_redirect( $source_url, $target_url );
        $redirects[] = array( 'from' => $source_url, 'to' => $target_url );
        
        // Trash the original post (to avoid duplicate urls/search indexes)
        wp_trash_post( $post->ID );
        echo "    - Original post trashed.\n";
        
        $migrated_count++;
    }
    echo "\n";
}

echo "-------------------------------------------------------\n";
echo "Migration Summary:\n";
echo "  Total Processed: " . count( $source_posts ) . "\n";
echo "  Success:         {$migrated_count}\n";
echo "  Failed:          {$failed_count}\n";
echo "-------------------------------------------------------\n";

if ( ! empty( $redirects ) ) {
    echo "Generated Redirects Map:\n";
    foreach ( $redirects as $redir ) {
        echo "  {$redir['from']}  ===>  {$redir['to']}\n";
    }
}
echo "=======================================================\n";


/**
 * Helper function to clean Elementor styles and convert to Gutenberg blocks
 */
function clean_elementor_content( $content ) {
    if ( empty( $content ) ) {
        return '';
    }
    
    // Strip Elementor JSON meta/comment blocks
    $content = preg_replace( '/<!--\s*wp:elementor\/.*?\s*-->/i', '', $content );
    
    // Remove Elementor custom wrapper elements and styles
    // Keep internal paragraph content
    $content = preg_replace( '/<div[^>]*class="[^"]*elementor[^"]*"[^>]*>/i', '', $content );
    $content = str_replace( '</div>', '', $content );
    
    // Parse paragraphs and clean up HTML tags, then wrap with Gutenberg blocks
    // Split by paragraphs
    $paragraphs = preg_split( '/<\/p>/i', $content );
    $cleaned_blocks = '';
    
    foreach ( $paragraphs as $p ) {
        $p = trim( $p );
        if ( empty( $p ) ) {
            continue;
        }
        
        // Restore <p> tag if it was split
        if ( strpos( strtolower( $p ), '<p' ) === false ) {
            $p = '<p>' . $p;
        }
        $p .= '</p>';
        
        // Strip inline styles, classes, and ID tags from paragraph
        $p = preg_replace( '/\s*(class|style|id)="[^"]*"/i', '', $p );
        
        // Skip empty paragraphs
        if ( strip_tags( $p ) === '' ) {
            continue;
        }
        
        // Wrap as native Gutenberg block
        $cleaned_blocks .= "<!-- wp:paragraph -->\n{$p}\n<!-- /wp:paragraph -->\n\n";
    }
    
    return trim( $cleaned_blocks );
}

/**
 * Filter text paragraphs for metadata extraction
 */
function filter_paragraphs( $content ) {
    $paragraphs = array();
    if ( preg_match_all( '/<p[^>]*>(.*?)<\/p>/is', $content, $matches ) ) {
        foreach ( $matches[1] as $match ) {
            $clean = wp_strip_all_tags( $match );
            if ( ! empty( $clean ) && strlen( $clean ) > 30 ) {
                $paragraphs[] = $clean;
            }
        }
    }
    return $paragraphs;
}

/**
 * Register redirect directly in Rank Math redirection tables if Rank Math is active
 */
function register_rank_math_redirect( $from, $to ) {
    global $wpdb;
    
    // Check if Rank Math table exists
    $table_name = $wpdb->prefix . 'rank_math_redirections';
    if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
        return; // Rank Math tables not available, user will need to import the redirect map
    }
    
    $wpdb->insert(
        $table_name,
        array(
            'sources'     => serialize( array( array( 'pattern' => $from, 'regex' => 0, 'type' => 'exact' ) ) ),
            'url_to'      => $to,
            'header_code' => 301,
            'status'      => 'active',
            'created'     => current_time( 'mysql' ),
            'updated'     => current_time( 'mysql' ),
        ),
        array( '%s', '%s', '%d', '%s', '%s', '%s' )
    );
}
