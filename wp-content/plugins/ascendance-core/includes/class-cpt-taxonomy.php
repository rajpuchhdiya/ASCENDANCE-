<?php
/**
 * Custom Post Types and Taxonomies Registration Class
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CPT_Taxonomy {

    /**
     * Singleton instance
     * @var CPT_Taxonomy|null
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Class constructor
     */
    private function __construct() {
        add_action( 'init', array( $this, 'register_content_types' ) );
    }

    /**
     * Register CPTs and Taxonomies
     */
    public function register_content_types() {
        $this->register_taxonomies();
        $this->register_post_types();
    }

    /**
     * Register Taxonomies
     */
    private function register_taxonomies() {
        
        // 1. Topic Taxonomy (brief, dossier)
        $labels_topic = array(
            'name'              => _x( 'Topics', 'taxonomy general name', 'ascendance-core' ),
            'singular_name'     => _x( 'Topic', 'taxonomy singular name', 'ascendance-core' ),
            'search_items'      => __( 'Search Topics', 'ascendance-core' ),
            'all_items'         => __( 'All Topics', 'ascendance-core' ),
            'parent_item'       => __( 'Parent Topic', 'ascendance-core' ),
            'parent_item_colon' => __( 'Parent Topic:', 'ascendance-core' ),
            'edit_item'         => __( 'Edit Topic', 'ascendance-core' ),
            'update_item'       => __( 'Update Topic', 'ascendance-core' ),
            'add_new_item'      => __( 'Add New Topic', 'ascendance-core' ),
            'new_item_name'     => __( 'New Topic Name', 'ascendance-core' ),
            'menu_name'         => __( 'Topics', 'ascendance-core' ),
        );

        register_taxonomy( 'topic', array( 'brief', 'dossier' ), array(
            'hierarchical'      => true,
            'labels'            => $labels_topic,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'show_in_rest'      => true, // Required for Gutenberg
            'rewrite'           => array( 'slug' => 'topic' ),
        ) );

        // 2. Region Taxonomy (brief, dossier, update)
        $labels_region = array(
            'name'              => _x( 'Regions', 'taxonomy general name', 'ascendance-core' ),
            'singular_name'     => _x( 'Region', 'taxonomy singular name', 'ascendance-core' ),
            'search_items'      => __( 'Search Regions', 'ascendance-core' ),
            'all_items'         => __( 'All Regions', 'ascendance-core' ),
            'parent_item'       => __( 'Parent Region', 'ascendance-core' ),
            'parent_item_colon' => __( 'Parent Region:', 'ascendance-core' ),
            'edit_item'         => __( 'Edit Region', 'ascendance-core' ),
            'update_item'       => __( 'Update Region', 'ascendance-core' ),
            'add_new_item'      => __( 'Add New Region', 'ascendance-core' ),
            'new_item_name'     => __( 'New Region Name', 'ascendance-core' ),
            'menu_name'         => __( 'Regions', 'ascendance-core' ),
        );

        register_taxonomy( 'region', array( 'brief', 'dossier', 'update' ), array(
            'hierarchical'      => false, // Non-hierarchical per spec
            'labels'            => $labels_region,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'show_in_rest'      => true, // Required for Gutenberg
            'rewrite'           => array( 'slug' => 'region' ),
        ) );

        // 3. Tier Taxonomy (brief, update, dossier)
        $labels_tier = array(
            'name'              => _x( 'Tiers', 'taxonomy general name', 'ascendance-core' ),
            'singular_name'     => _x( 'Tier', 'taxonomy singular name', 'ascendance-core' ),
            'search_items'      => __( 'Search Tiers', 'ascendance-core' ),
            'all_items'         => __( 'All Tiers', 'ascendance-core' ),
            'edit_item'         => __( 'Edit Tier', 'ascendance-core' ),
            'update_item'       => __( 'Update Tier', 'ascendance-core' ),
            'add_new_item'      => __( 'Add New Tier', 'ascendance-core' ),
            'new_item_name'     => __( 'New Tier Name', 'ascendance-core' ),
            'menu_name'         => __( 'Tiers', 'ascendance-core' ),
        );

        register_taxonomy( 'tier', array( 'brief', 'update', 'dossier' ), array(
            'hierarchical'      => false,
            'labels'            => $labels_tier,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'tier' ),
        ) );

        // 4. Intelligence Tag Taxonomy (brief, update, dossier)
        $labels_tag = array(
            'name'              => _x( 'Intelligence Tags', 'taxonomy general name', 'ascendance-core' ),
            'singular_name'     => _x( 'Intelligence Tag', 'taxonomy singular name', 'ascendance-core' ),
            'search_items'      => __( 'Search Tags', 'ascendance-core' ),
            'all_items'         => __( 'All Tags', 'ascendance-core' ),
            'edit_item'         => __( 'Edit Tag', 'ascendance-core' ),
            'update_item'       => __( 'Update Tag', 'ascendance-core' ),
            'add_new_item'      => __( 'Add New Tag', 'ascendance-core' ),
            'new_item_name'     => __( 'New Tag Name', 'ascendance-core' ),
            'menu_name'         => __( 'Intelligence Tags', 'ascendance-core' ),
        );

        register_taxonomy( 'intelligence_tag', array( 'brief', 'update', 'dossier' ), array(
            'hierarchical'      => false,
            'labels'            => $labels_tag,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => array( 'slug' => 'intelligence-tag' ),
        ) );
    }

    /**
     * Register Custom Post Types
     */
    private function register_post_types() {
        
        // 1. Intelligence Brief (brief)
        $labels_brief = array(
            'name'               => _x( 'Intelligence Briefs', 'post type general name', 'ascendance-core' ),
            'singular_name'      => _x( 'Brief', 'post type singular name', 'ascendance-core' ),
            'menu_name'          => _x( 'Briefs', 'admin menu', 'ascendance-core' ),
            'name_admin_bar'     => _x( 'Brief', 'add new on admin bar', 'ascendance-core' ),
            'add_new'            => _x( 'Add New', 'brief', 'ascendance-core' ),
            'add_new_item'       => __( 'Add New Intelligence Brief', 'ascendance-core' ),
            'new_item'           => __( 'New Brief', 'ascendance-core' ),
            'edit_item'          => __( 'Edit Brief', 'ascendance-core' ),
            'view_item'          => __( 'View Brief', 'ascendance-core' ),
            'all_items'          => __( 'All Briefs', 'ascendance-core' ),
            'search_items'       => __( 'Search Intelligence Briefs', 'ascendance-core' ),
            'parent_item_colon'  => __( 'Parent Briefs:', 'ascendance-core' ),
            'not_found'          => __( 'No briefs found.', 'ascendance-core' ),
            'not_found_in_trash' => __( 'No briefs found in Trash.', 'ascendance-core' )
        );

        register_post_type( 'brief', array(
            'labels'             => $labels_brief,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'briefs', 'with_front' => false ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 32,
            'menu_icon'          => 'dashicons-media-document',
            'show_in_rest'       => true, // Required for Gutenberg editor & REST API
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields' ),
            'taxonomies'         => array( 'topic', 'region', 'tier', 'intelligence_tag' ),
        ) );

        // 2. Intelligence Update (update)
        $labels_update = array(
            'name'               => _x( 'Intelligence Updates', 'post type general name', 'ascendance-core' ),
            'singular_name'      => _x( 'Update', 'post type singular name', 'ascendance-core' ),
            'menu_name'          => _x( 'Updates', 'admin menu', 'ascendance-core' ),
            'add_new_item'       => __( 'Add New Intelligence Update', 'ascendance-core' ),
            'edit_item'          => __( 'Edit Update', 'ascendance-core' ),
            'view_item'          => __( 'View Update', 'ascendance-core' ),
            'all_items'          => __( 'All Updates', 'ascendance-core' ),
            'search_items'       => __( 'Search Intelligence Updates', 'ascendance-core' ),
            'not_found'          => __( 'No updates found.', 'ascendance-core' ),
        );

        register_post_type( 'update', array(
            'labels'             => $labels_update,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'updates', 'with_front' => false ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 33,
            'menu_icon'          => 'dashicons-update',
            'show_in_rest'       => true,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
            'taxonomies'         => array( 'region', 'tier', 'intelligence_tag' ),
        ) );

        // 3. Intelligence Dossier (dossier)
        $labels_dossier = array(
            'name'               => _x( 'Intelligence Dossiers', 'post type general name', 'ascendance-core' ),
            'singular_name'      => _x( 'Dossier', 'post type singular name', 'ascendance-core' ),
            'menu_name'          => _x( 'Dossiers', 'admin menu', 'ascendance-core' ),
            'add_new_item'       => __( 'Add New Intelligence Dossier', 'ascendance-core' ),
            'edit_item'          => __( 'Edit Dossier', 'ascendance-core' ),
            'view_item'          => __( 'View Dossier', 'ascendance-core' ),
            'all_items'          => __( 'All Dossiers', 'ascendance-core' ),
            'search_items'       => __( 'Search Intelligence Dossiers', 'ascendance-core' ),
            'not_found'          => __( 'No dossiers found.', 'ascendance-core' ),
        );

        register_post_type( 'dossier', array(
            'labels'             => $labels_dossier,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'dossiers', 'with_front' => false ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 34,
            'menu_icon'          => 'dashicons-portfolio',
            'show_in_rest'       => true,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
            'taxonomies'         => array( 'topic', 'region', 'tier', 'intelligence_tag' ),
        ) );

        // 4. Intelligence Event (event)
        $labels_event = array(
            'name'               => _x( 'Intelligence Events', 'post type general name', 'ascendance-core' ),
            'singular_name'      => _x( 'Event', 'post type singular name', 'ascendance-core' ),
            'menu_name'          => _x( 'Events', 'admin menu', 'ascendance-core' ),
            'add_new'            => _x( 'Add New', 'event', 'ascendance-core' ),
            'add_new_item'       => __( 'Add New Intelligence Event', 'ascendance-core' ),
            'edit_item'          => __( 'Edit Event', 'ascendance-core' ),
            'view_item'          => __( 'View Event', 'ascendance-core' ),
            'all_items'          => __( 'All Events', 'ascendance-core' ),
            'search_items'       => __( 'Search Intelligence Events', 'ascendance-core' ),
            'not_found'          => __( 'No events found.', 'ascendance-core' ),
        );

        register_post_type( 'event', array(
            'labels'             => $labels_event,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'events', 'with_front' => false ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 35,
            'menu_icon'          => 'dashicons-calendar-alt',
            'show_in_rest'       => true,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt' ),
            'taxonomies'         => array( 'topic', 'region' ),
        ) );
    }
}
