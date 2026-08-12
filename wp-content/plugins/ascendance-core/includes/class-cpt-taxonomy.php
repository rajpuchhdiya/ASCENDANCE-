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
        add_action( 'init', array( $this, 'maybe_register_roles' ) );

        // Paid Category Add-on Term Meta Hooks
        add_action( 'topic_add_form_fields', array( $this, 'render_topic_add_meta_fields' ) );
        add_action( 'topic_edit_form_fields', array( $this, 'render_topic_edit_meta_fields' ) );
        add_action( 'created_topic', array( $this, 'save_topic_meta_fields' ) );
        add_action( 'edited_topic', array( $this, 'save_topic_meta_fields' ) );

        add_filter( 'manage_edit-topic_columns', array( $this, 'register_topic_admin_columns' ) );
        add_action( 'manage_topic_custom_column', array( $this, 'render_topic_admin_column' ), 10, 3 );
        add_action( 'init', array( $this, 'seed_default_paid_categories' ), 20 );

        // Register custom post types and taxonomies with Polylang for translation
        add_filter( 'pll_get_post_types', array( $this, 'add_post_types_to_polylang' ), 10, 2 );
        add_filter( 'pll_get_taxonomies', array( $this, 'add_taxonomies_to_polylang' ), 10, 2 );
    }

    /**
     * Register custom subscriber roles
     */
    public function maybe_register_roles() {
        $wp_roles = wp_roles();
        if ( ! $wp_roles ) {
            return;
        }

        // Essential
        if ( ! $wp_roles->is_role( 'ascendance_essential' ) ) {
            add_role( 'ascendance_essential', 'Subscriber — Essential', array(
                'read'                   => true,
                'read_essential_content' => true,
            ) );
        }

        // Professional
        if ( ! $wp_roles->is_role( 'ascendance_professional' ) ) {
            add_role( 'ascendance_professional', 'Subscriber — Professional', array(
                'read'                      => true,
                'read_essential_content'    => true,
                'read_professional_content' => true,
            ) );
        }

        // Enterprise
        if ( ! $wp_roles->is_role( 'ascendance_enterprise' ) ) {
            add_role( 'ascendance_enterprise', 'Subscriber — Enterprise', array(
                'read'                      => true,
                'read_essential_content'    => true,
                'read_professional_content' => true,
                'read_enterprise_content'   => true,
            ) );
        }
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

        register_taxonomy( 'topic', array( 'brief', 'dossier', 'update' ), array(
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

        // 5. Entity Intelligence CPT (entity)
        $labels_entity = array(
            'name'               => _x( 'Entities', 'post type general name', 'ascendance-core' ),
            'singular_name'      => _x( 'Entity', 'post type singular name', 'ascendance-core' ),
            'menu_name'          => _x( 'Entities', 'admin menu', 'ascendance-core' ),
            'name_admin_bar'     => _x( 'Entity', 'add new on admin bar', 'ascendance-core' ),
            'add_new'            => _x( 'Add New', 'entity', 'ascendance-core' ),
            'add_new_item'       => __( 'Add New Entity', 'ascendance-core' ),
            'new_item'           => __( 'New Entity', 'ascendance-core' ),
            'edit_item'          => __( 'Edit Entity', 'ascendance-core' ),
            'view_item'          => __( 'View Entity', 'ascendance-core' ),
            'all_items'          => __( 'All Entities', 'ascendance-core' ),
            'search_items'       => __( 'Search Entities', 'ascendance-core' ),
            'not_found'          => __( 'No entities found.', 'ascendance-core' ),
            'not_found_in_trash' => __( 'No entities found in Trash.', 'ascendance-core' )
        );

        register_post_type( 'entity', array(
            'labels'             => $labels_entity,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'entities', 'with_front' => false ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 36,
            'menu_icon'          => 'dashicons-networking',
            'show_in_rest'       => true,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'revisions', 'custom-fields' ),
            'taxonomies'         => array( 'entity_type', 'region', 'topic' ),
        ) );
    }

    /**
     * Register custom post types with Polylang
     */
    public function add_post_types_to_polylang( $post_types, $is_settings ) {
        $post_types['brief']     = 'brief';
        $post_types['update']    = 'update';
        $post_types['dossier']   = 'dossier';
        $post_types['event']     = 'event';
        $post_types['entity']    = 'entity';
        return $post_types;
    }

    /**
     * Register custom taxonomies with Polylang
     */
    public function add_taxonomies_to_polylang( $taxonomies, $is_settings ) {
        $taxonomies['topic']            = 'topic';
        $taxonomies['region']           = 'region';
        $taxonomies['tier']             = 'tier';
        $taxonomies['intelligence_tag'] = 'intelligence_tag';
        $taxonomies['entity_type']      = 'entity_type';
        return $taxonomies;
    }

    /**
     * Seed Default Paid Categories if missing
     */
    public function seed_default_paid_categories() {
        if ( get_option( 'ascendance_paid_categories_seeded' ) ) {
            return;
        }

        $default_categories = array(
            'Critical Minerals' => 'Strategic Intelligence on Cobalt, Lithium, Copper, Nickel, and REEs.',
            'Energy'            => 'Clean energy transition, oil & gas developments, and power infrastructure.',
            'Geopolitics'       => 'Regional power dynamics, sanctions, trade policies, and security alerts.',
            'Infrastructure'    => 'Mining logistics, port corridors, rail links, and heavy infrastructure.',
            'DRC Intelligence'  => 'Deep-dive intelligence dedicated to Democratic Republic of the Congo.',
        );

        foreach ( $default_categories as $cat_name => $cat_desc ) {
            $term = get_term_by( 'name', $cat_name, 'topic' );
            if ( ! $term ) {
                $inserted = wp_insert_term( $cat_name, 'topic', array( 'description' => $cat_desc ) );
                if ( ! is_wp_error( $inserted ) && isset( $inserted['term_id'] ) ) {
                    $term_id = $inserted['term_id'];
                    update_term_meta( $term_id, 'is_paid_addon', 1 );
                    update_term_meta( $term_id, 'addon_status', 'active' );
                    update_term_meta( $term_id, 'addon_description', $cat_desc );
                    update_term_meta( $term_id, 'addon_icon', 'dashicons-category' );
                }
            } else {
                update_term_meta( $term->term_id, 'is_paid_addon', 1 );
                update_term_meta( $term->term_id, 'addon_status', 'active' );
                if ( ! get_term_meta( $term->term_id, 'addon_description', true ) ) {
                    update_term_meta( $term->term_id, 'addon_description', $cat_desc );
                }
            }
        }

        update_option( 'ascendance_paid_categories_seeded', 1 );
    }

    public function render_topic_add_meta_fields() {
        ?>
        <div class="form-field term-is-paid-addon-wrap">
            <label for="is_paid_addon">
                <input type="checkbox" name="is_paid_addon" id="is_paid_addon" value="1" />
                <strong><?php _e( 'Enable as Paid Add-on Category', 'ascendance-core' ); ?></strong>
            </label>
            <p><?php _e( 'Check this box to require a subscriber paid add-on entitlement for articles under this topic.', 'ascendance-core' ); ?></p>
        </div>
        <div class="form-field term-addon-status-wrap">
            <label for="addon_status"><?php _e( 'Add-on Status', 'ascendance-core' ); ?></label>
            <select name="addon_status" id="addon_status">
                <option value="active"><?php _e( 'Active (Purchasable & Enforced)', 'ascendance-core' ); ?></option>
                <option value="inactive"><?php _e( 'Inactive (Historical Only)', 'ascendance-core' ); ?></option>
                <option value="draft"><?php _e( 'Draft (Editorial Preview)', 'ascendance-core' ); ?></option>
            </select>
        </div>
        <div class="form-field term-addon-icon-wrap">
            <label for="addon_icon"><?php _e( 'Display Icon Class', 'ascendance-core' ); ?></label>
            <input type="text" name="addon_icon" id="addon_icon" value="dashicons-category" placeholder="dashicons-category" />
        </div>
        <div class="form-field term-addon-description-wrap">
            <label for="addon_description"><?php _e( 'Paid Add-on Description', 'ascendance-core' ); ?></label>
            <textarea name="addon_description" id="addon_description" rows="3"></textarea>
        </div>
        <div class="form-field term-addon-price-wrap">
            <label for="addon_price_amount"><?php _e( 'Add-on Price Amount ($)', 'ascendance-core' ); ?></label>
            <input type="number" step="0.01" min="0" name="addon_price_amount" id="addon_price_amount" value="49.00" placeholder="49.00" />
        </div>
        <div class="form-field term-simpay-form-wrap">
            <label for="simpay_form_id"><?php _e( 'WP Simple Pay Form ID', 'ascendance-core' ); ?></label>
            <input type="number" name="simpay_form_id" id="simpay_form_id" value="" placeholder="e.g. 123" />
        </div>
        <?php
    }

    public function render_topic_edit_meta_fields( $term ) {
        $is_paid    = get_term_meta( $term->term_id, 'is_paid_addon', true );
        $status     = get_term_meta( $term->term_id, 'addon_status', true ) ?: 'active';
        $icon       = get_term_meta( $term->term_id, 'addon_icon', true ) ?: 'dashicons-category';
        $desc       = get_term_meta( $term->term_id, 'addon_description', true );
        $amount     = get_term_meta( $term->term_id, 'addon_price_amount', true ) ?: '49.00';
        $currency   = get_term_meta( $term->term_id, 'addon_currency', true ) ?: 'USD';
        $simpay_form = get_term_meta( $term->term_id, 'simpay_form_id', true );
        ?>
        <tr class="form-field term-is-paid-addon-wrap">
            <th scope="row"><label for="is_paid_addon"><?php _e( 'Paid Add-on Category', 'ascendance-core' ); ?></label></th>
            <td>
                <label for="is_paid_addon">
                    <input type="checkbox" name="is_paid_addon" id="is_paid_addon" value="1" <?php checked( $is_paid, 1 ); ?> />
                    <strong><?php _e( 'Enable as Paid Add-on Category', 'ascendance-core' ); ?></strong>
                </label>
                <p class="description"><?php _e( 'Check this box to require a subscriber paid add-on entitlement for articles under this topic.', 'ascendance-core' ); ?></p>
            </td>
        </tr>
        <tr class="form-field term-addon-status-wrap">
            <th scope="row"><label for="addon_status"><?php _e( 'Add-on Status', 'ascendance-core' ); ?></label></th>
            <td>
                <select name="addon_status" id="addon_status">
                    <option value="active" <?php selected( $status, 'active' ); ?>><?php _e( 'Active (Purchasable & Enforced)', 'ascendance-core' ); ?></option>
                    <option value="inactive" <?php selected( $status, 'inactive' ); ?>><?php _e( 'Inactive (Historical Only)', 'ascendance-core' ); ?></option>
                    <option value="draft" <?php selected( $status, 'draft' ); ?>><?php _e( 'Draft (Editorial Preview)', 'ascendance-core' ); ?></option>
                </select>
            </td>
        </tr>
        <tr class="form-field term-addon-icon-wrap">
            <th scope="row"><label for="addon_icon"><?php _e( 'Display Icon Class', 'ascendance-core' ); ?></label></th>
            <td>
                <input type="text" name="addon_icon" id="addon_icon" value="<?php echo esc_attr( $icon ); ?>" />
            </td>
        </tr>
        <tr class="form-field term-addon-description-wrap">
            <th scope="row"><label for="addon_description"><?php _e( 'Add-on Description', 'ascendance-core' ); ?></label></th>
            <td>
                <textarea name="addon_description" id="addon_description" rows="3"><?php echo esc_textarea( $desc ); ?></textarea>
            </td>
        </tr>
        <tr class="form-field term-addon-price-wrap">
            <th scope="row"><label for="addon_price_amount"><?php _e( 'Price Amount ($)', 'ascendance-core' ); ?></label></th>
            <td>
                <input type="number" step="0.01" min="0" name="addon_price_amount" id="addon_price_amount" value="<?php echo esc_attr( $amount ); ?>" style="width:120px;" />
                <span class="description"><?php _e( 'USD monthly recurring price.', 'ascendance-core' ); ?></span>
            </td>
        </tr>
        <tr class="form-field term-simpay-form-wrap">
            <th scope="row"><label for="simpay_form_id"><?php _e( 'WP Simple Pay Form ID', 'ascendance-core' ); ?></label></th>
            <td>
                <input type="number" name="simpay_form_id" id="simpay_form_id" value="<?php echo esc_attr( $simpay_form ); ?>" placeholder="e.g. 123" />
                <p class="description"><?php _e( 'The ID of the WP Simple Pay form used for checkout.', 'ascendance-core' ); ?></p>
            </td>
        </tr>
        <?php
    }

    public function save_topic_meta_fields( $term_id ) {
        if ( isset( $_POST['is_paid_addon'] ) ) {
            update_term_meta( $term_id, 'is_paid_addon', 1 );
        } else {
            update_term_meta( $term_id, 'is_paid_addon', 0 );
        }

        if ( isset( $_POST['addon_status'] ) ) {
            update_term_meta( $term_id, 'addon_status', sanitize_text_field( $_POST['addon_status'] ) );
        }
        if ( isset( $_POST['addon_icon'] ) ) {
            update_term_meta( $term_id, 'addon_icon', sanitize_text_field( $_POST['addon_icon'] ) );
        }
        if ( isset( $_POST['addon_description'] ) ) {
            update_term_meta( $term_id, 'addon_description', sanitize_textarea_field( $_POST['addon_description'] ) );
        }
        if ( isset( $_POST['addon_price_amount'] ) ) {
            update_term_meta( $term_id, 'addon_price_amount', sanitize_text_field( $_POST['addon_price_amount'] ) );
        }
        if ( isset( $_POST['simpay_form_id'] ) ) {
            update_term_meta( $term_id, 'simpay_form_id', sanitize_text_field( $_POST['simpay_form_id'] ) );
        }
    }

    /**
     * Helper to resolve the WP Simple Pay form ID for a topic term.
     *
     * @param int $term_id Term ID
     * @return string WP Simple Pay Form ID or empty string if not configured.
     */
    public static function get_term_simpay_form_id( $term_id ) {
        $form_id = get_term_meta( $term_id, 'simpay_form_id', true );
        return (string) $form_id;
    }

    public function register_topic_admin_columns( $columns ) {
        $columns['is_paid_addon'] = __( 'Paid Add-on', 'ascendance-core' );
        $columns['addon_status']  = __( 'Add-on Status', 'ascendance-core' );
        return $columns;
    }

    public function render_topic_admin_column( $content, $column_name, $term_id ) {
        if ( 'is_paid_addon' === $column_name ) {
            $is_paid = get_term_meta( $term_id, 'is_paid_addon', true );
            return $is_paid ? '<span style="color:#16a34a; font-weight:600;">✓ Paid Add-on</span>' : '<span style="color:#94a3b8;">Standard Topic</span>';
        }
        if ( 'addon_status' === $column_name ) {
            $is_paid = get_term_meta( $term_id, 'is_paid_addon', true );
            if ( ! $is_paid ) return '—';
            $status  = get_term_meta( $term_id, 'addon_status', true ) ?: 'active';
            $colors  = array( 'active' => '#16a34a', 'inactive' => '#dc2626', 'draft' => '#d97706' );
            $color   = $colors[$status] ?? '#64748b';
            return '<span style="color:' . $color . '; font-weight:600; text-transform:capitalize;">' . esc_html( $status ) . '</span>';
        }
        return $content;
    }
}
