<?php
/**
 * Entity Intelligence Graph & Architecture Manager
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Entity_Intelligence {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
        add_action( 'save_post', array( $this, 'sync_bidirectional_relationships' ), 10, 3 );
        add_action( 'admin_notices', array( $this, 'display_duplicate_warning_notice' ) );
        add_filter( 'manage_entity_posts_columns', array( $this, 'register_admin_columns' ) );
        add_action( 'manage_entity_posts_custom_column', array( $this, 'render_admin_column' ), 10, 2 );
        add_action( 'restrict_manage_posts', array( $this, 'render_admin_filters' ) );
        add_action( 'pre_get_posts', array( $this, 'apply_admin_filters_and_search' ) );
        add_action( 'add_meta_boxes', array( $this, 'register_admin_metaboxes' ) );
    }

    /**
     * Controlled Relationship Predicates
     */
    public function get_relationship_types() {
        return array(
            'owns'         => __( 'Owns', 'ascendance-core' ),
            'operates'     => __( 'Operates', 'ascendance-core' ),
            'invests_in'   => __( 'Invests In', 'ascendance-core' ),
            'located_in'   => __( 'Located In', 'ascendance-core' ),
            'partners_with'=> __( 'Partners With', 'ascendance-core' ),
            'competes_with'=> __( 'Competes With', 'ascendance-core' ),
            'acquired'     => __( 'Acquired', 'ascendance-core' ),
            'supplies'     => __( 'Supplies', 'ascendance-core' ),
            'regulates'    => __( 'Regulates', 'ascendance-core' ),
            'leads'        => __( 'Leads', 'ascendance-core' ),
            'connected_to' => __( 'Connected To', 'ascendance-core' ),
        );
    }

    /**
     * Link Content item (Brief/Update/Dossier) to an Entity (bidirectional)
     */
    public function link_content_to_entity( $content_id, $entity_id ) {
        $content_id = (int) $content_id;
        $entity_id  = (int) $entity_id;

        if ( ! $content_id || ! $entity_id ) return false;

        // Content -> Entity
        $entities = get_post_meta( $content_id, '_related_entities', true );
        if ( ! is_array( $entities ) ) $entities = array();
        if ( ! in_array( $entity_id, $entities, true ) ) {
            $entities[] = $entity_id;
            update_post_meta( $content_id, '_related_entities', array_values( array_unique( $entities ) ) );
        }

        // Entity -> Content
        $content_items = get_post_meta( $entity_id, '_related_content', true );
        if ( ! is_array( $content_items ) ) $content_items = array();
        if ( ! in_array( $content_id, $content_items, true ) ) {
            $content_items[] = $content_id;
            update_post_meta( $entity_id, '_related_content', array_values( array_unique( $content_items ) ) );
        }

        return true;
    }

    /**
     * Unlink Content item from an Entity
     */
    public function unlink_content_from_entity( $content_id, $entity_id ) {
        $content_id = (int) $content_id;
        $entity_id  = (int) $entity_id;

        // Content -> Entity
        $entities = get_post_meta( $content_id, '_related_entities', true );
        if ( is_array( $entities ) ) {
            $entities = array_diff( $entities, array( $entity_id ) );
            update_post_meta( $content_id, '_related_entities', array_values( $entities ) );
        }

        // Entity -> Content
        $content_items = get_post_meta( $entity_id, '_related_content', true );
        if ( is_array( $content_items ) ) {
            $content_items = array_diff( $content_items, array( $content_id ) );
            update_post_meta( $entity_id, '_related_content', array_values( $content_items ) );
        }

        return true;
    }

    /**
     * Get Entities linked to a Content item
     */
    public function get_content_entities( $content_id ) {
        $entity_ids = get_post_meta( $content_id, '_related_entities', true );
        if ( empty( $entity_ids ) || ! is_array( $entity_ids ) ) {
            return array();
        }

        return get_posts( array(
            'post_type'      => 'entity',
            'post__in'       => $entity_ids,
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ) );
    }

    /**
     * Get Content items linked to an Entity
     */
    public function get_entity_content( $entity_id, $post_types = array( 'brief', 'update', 'dossier' ) ) {
        $content_ids = get_post_meta( $entity_id, '_related_content', true );
        if ( empty( $content_ids ) || ! is_array( $content_ids ) ) {
            return array();
        }

        return get_posts( array(
            'post_type'      => $post_types,
            'post__in'       => $content_ids,
            'posts_per_page' => 50,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );
    }

    /**
     * Add Entity-to-Entity Predicate Relationship
     */
    public function add_entity_relationship( $source_entity_id, $target_entity_id, $relationship_type, $notes = '' ) {
        $source_id = (int) $source_entity_id;
        $target_id = (int) $target_entity_id;

        if ( ! $source_id || ! $target_id || $source_id === $target_id ) return false;

        $types = $this->get_relationship_types();
        if ( ! isset( $types[ $relationship_type ] ) ) {
            $relationship_type = 'connected_to';
        }

        $relationships = get_post_meta( $source_id, '_entity_relationships', true );
        if ( ! is_array( $relationships ) ) $relationships = array();

        $updated = false;
        foreach ( $relationships as &$rel ) {
            if ( (int) $rel['target_id'] === $target_id && $rel['relationship_type'] === $relationship_type ) {
                $rel['notes'] = $notes;
                $updated = true;
                break;
            }
        }

        if ( ! $updated ) {
            $relationships[] = array(
                'target_id'         => $target_id,
                'relationship_type' => $relationship_type,
                'notes'             => $notes,
            );
        }

        update_post_meta( $source_id, '_entity_relationships', $relationships );

        // Invalidate object cache for both source and target entities
        wp_cache_delete( 'asc_entity_rels_' . $source_id, 'ascendance' );
        wp_cache_delete( 'asc_entity_rels_' . $target_id, 'ascendance' );

        return true;
    }

    /**
     * Get Entity-to-Entity Relationships (Direct & Inverse)
     */
    public function get_entity_relationships( $entity_id ) {
        $entity_id = (int) $entity_id;
        if ( ! $entity_id ) {
            return array( 'direct' => array(), 'inverse' => array() );
        }

        $cache_key = 'asc_entity_rels_' . $entity_id;
        $cached    = wp_cache_get( $cache_key, 'ascendance' );
        if ( false !== $cached && is_array( $cached ) ) {
            return $cached;
        }

        $direct = get_post_meta( $entity_id, '_entity_relationships', true );
        if ( ! is_array( $direct ) ) $direct = array();

        // Query inverse relationships where target_id === $entity_id
        $inverse_posts = get_posts( array(
            'post_type'      => 'entity',
            'posts_per_page' => 100,
            'post_status'    => array( 'publish', 'draft' ),
            'meta_key'       => '_entity_relationships',
        ) );

        $inverse = array();
        foreach ( $inverse_posts as $p ) {
            $rels = get_post_meta( $p->ID, '_entity_relationships', true );
            if ( is_array( $rels ) ) {
                foreach ( $rels as $r ) {
                    if ( isset( $r['target_id'] ) && (int) $r['target_id'] === $entity_id ) {
                        $inverse[] = array(
                            'source_id'         => $p->ID,
                            'source_title'      => get_the_title( $p->ID ),
                            'relationship_type' => $r['relationship_type'],
                            'notes'             => $r['notes'] ?? '',
                        );
                    }
                }
            }
        }

        $result = array(
            'direct'  => $direct,
            'inverse' => $inverse,
        );

        wp_cache_set( $cache_key, $result, 'ascendance', 300 );
        return $result;
    }

    /**
     * Duplicate Entity / Alias Check
     */
    public function check_duplicate_entity( $title, $aliases = '', $exclude_id = 0 ) {
        $title = trim( $title );
        if ( empty( $title ) ) return false;

        $alias_array = array();
        if ( ! empty( $aliases ) ) {
            $lines = explode( "\n", $aliases );
            foreach ( $lines as $l ) {
                $trimmed = trim( $l );
                if ( ! empty( $trimmed ) ) $alias_array[] = strtolower( $trimmed );
            }
        }

        // Query existing entities
        $args = array(
            'post_type'      => 'entity',
            'post_status'    => array( 'publish', 'draft' ),
            'posts_per_page' => 100,
        );
        if ( $exclude_id ) {
            $args['post__not_in'] = array( (int) $exclude_id );
        }

        $existing = get_posts( $args );

        foreach ( $existing as $p ) {
            // Check title match
            if ( 0 === strcasecmp( $p->post_title, $title ) ) {
                return array(
                    'matched_post_id' => $p->ID,
                    'matched_title'   => $p->post_title,
                    'match_type'      => 'exact_title',
                );
            }

            // Check aliases postmeta
            $existing_aliases = get_post_meta( $p->ID, 'alternate_names', true );
            if ( ! empty( $existing_aliases ) ) {
                $e_lines = explode( "\n", $existing_aliases );
                foreach ( $e_lines as $el ) {
                    $el_lower = strtolower( trim( $el ) );
                    if ( empty( $el_lower ) ) continue;

                    if ( 0 === strcasecmp( $el_lower, strtolower( $title ) ) || in_array( $el_lower, $alias_array, true ) ) {
                        return array(
                            'matched_post_id' => $p->ID,
                            'matched_title'   => $p->post_title,
                            'match_type'      => 'alias_match',
                        );
                    }
                }
            }
        }

        return false;
    }

    /**
     * Display Admin Warning Notice if Duplicate Entity Detected
     */
    public function display_duplicate_warning_notice() {
        if ( isset( $_GET['asc_dup_warning'] ) ) {
            $dup_title = sanitize_text_field( $_GET['asc_dup_warning'] );
            echo '<div class="notice notice-warning is-dismissible"><p>';
            echo '<strong>Warning:</strong> An entity with title or alias "<em>' . esc_html( $dup_title ) . '</em>" is already registered in the system. Duplicate entities should be merged or assigned as alternate names.';
            echo '</p></div>';
        }
    }

    /**
     * Sync Bidirectional Relationships on Post Save
     */
    public function sync_bidirectional_relationships( $post_id, $post, $update ) {
        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) return;

        // Duplicate check for Entity post type in admin
        if ( 'entity' === $post->post_type && is_admin() && isset( $_POST['post_title'] ) ) {
            $aliases = isset( $_POST['alternate_names'] ) ? sanitize_textarea_field( $_POST['alternate_names'] ) : get_post_meta( $post_id, 'alternate_names', true );
            $dup = $this->check_duplicate_entity( $post->post_title, $aliases, $post_id );
            if ( $dup ) {
                add_filter( 'redirect_post_location', function( $location ) use ( $dup ) {
                    return add_query_arg( 'asc_dup_warning', rawurlencode( $dup['matched_title'] ), $location );
                } );
            }
        }
    }

    /**
     * Register REST API Routes for Entity Intelligence
     */
    public function register_rest_routes() {
        register_rest_route( 'ascendance/v1', '/entities', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_entities' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( 'ascendance/v1', '/entities/(?P<id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_single_entity' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( 'ascendance/v1', '/entities/(?P<id>\d+)/content', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_entity_content' ),
            'permission_callback' => '__return_true',
        ) );
    }

    /**
     * REST Handler: Get Entity List
     */
    public function rest_get_entities( \WP_REST_Request $request ) {
        $type = $request->get_param( 'type' );
        $args = array(
            'post_type'      => 'entity',
            'post_status'    => 'publish',
            'posts_per_page' => 50,
            'orderby'        => 'title',
            'order'          => 'ASC',
        );

        if ( ! empty( $type ) ) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'entity_type',
                    'field'    => 'slug',
                    'terms'    => sanitize_text_field( $type ),
                ),
            );
        }

        $posts = get_posts( $args );
        $data  = array();

        foreach ( $posts as $p ) {
            $types = wp_get_post_terms( $p->ID, 'entity_type', array( 'fields' => 'slugs' ) );
            $data[] = array(
                'id'            => $p->ID,
                'title'         => $p->post_title,
                'slug'          => $p->post_name,
                'entity_type'   => ( ! is_wp_error( $types ) && ! empty( $types ) ) ? $types[0] : 'company',
                'official_name' => get_post_meta( $p->ID, 'official_name', true ),
                'country'       => get_post_meta( $p->ID, 'country', true ),
                'status'        => get_post_meta( $p->ID, 'entity_status', true ) ?: 'active',
                'permalink'     => get_permalink( $p->ID ),
            );
        }

        return new \WP_REST_Response( array( 'ok' => true, 'total' => count( $data ), 'data' => $data ), 200 );
    }

    /**
     * REST Handler: Get Single Entity
     */
    public function rest_get_single_entity( \WP_REST_Request $request ) {
        $id   = (int) $request->get_param( 'id' );
        $post = get_post( $id );

        if ( ! $post || 'entity' !== $post->post_type ) {
            return new \WP_REST_Response( array( 'error' => 'Entity not found.' ), 404 );
        }

        // Security check: Draft protection
        if ( 'publish' !== $post->post_status && ! current_user_can( 'edit_post', $id ) ) {
            return new \WP_REST_Response( array( 'error' => 'Forbidden: Draft entity is protected.' ), 403 );
        }

        $relationships = $this->get_entity_relationships( $id );
        $types         = wp_get_post_terms( $id, 'entity_type', array( 'fields' => 'names' ) );

        $data = array(
            'id'               => $post->ID,
            'title'            => $post->post_title,
            'entity_type'      => ( ! is_wp_error( $types ) && ! empty( $types ) ) ? $types[0] : 'Company',
            'official_name'    => get_post_meta( $id, 'official_name', true ),
            'alternate_names'  => get_post_meta( $id, 'alternate_names', true ),
            'short_description'=> get_post_meta( $id, 'short_description', true ) ?: $post->post_excerpt,
            'country'          => get_post_meta( $id, 'country', true ),
            'website'          => get_post_meta( $id, 'website', true ),
            'status'           => get_post_meta( $id, 'entity_status', true ) ?: 'active',
            'established_date' => get_post_meta( $id, 'established_date', true ),
            'relationships'    => $relationships,
            'permalink'        => get_permalink( $id ),
        );

        return new \WP_REST_Response( array( 'ok' => true, 'data' => $data ), 200 );
    }

    /**
     * REST Handler: Get Related Content for Entity
     */
    public function rest_get_entity_content( \WP_REST_Request $request ) {
        $id   = (int) $request->get_param( 'id' );
        $post = get_post( $id );

        if ( ! $post || 'entity' !== $post->post_type ) {
            return new \WP_REST_Response( array( 'error' => 'Entity not found.' ), 404 );
        }

        if ( 'publish' !== $post->post_status && ! current_user_can( 'edit_post', $id ) ) {
            return new \WP_REST_Response( array( 'error' => 'Forbidden: Draft entity content is protected.' ), 403 );
        }

        $content_posts = $this->get_entity_content( $id );
        $data          = array();
        $current_user  = get_current_user_id();

        foreach ( $content_posts as $cp ) {
            // Phase 4B: add locked flag — entity profiles are public discovery surfaces,
            // but related content items may be tier- or category-gated. The locked flag
            // lets the frontend render a lock icon; no body content is exposed here.
            $is_locked = class_exists( 'Ascendance\Core\Paywall' )
                && ! \Ascendance\Core\Paywall::get_instance()->user_has_access( $cp->ID, $current_user );

            $data[] = array(
                'id'        => $cp->ID,
                'title'     => $cp->post_title,
                'post_type' => $cp->post_type,
                'date'      => get_the_date( 'c', $cp ),
                'permalink' => get_permalink( $cp->ID ),
                'locked'    => $is_locked,
            );
        }

        return new \WP_REST_Response( array( 'ok' => true, 'total' => count( $data ), 'data' => $data ), 200 );
    }

    /**
     * Remove Entity-to-Entity Relationship
     */
    public function remove_entity_relationship( $source_entity_id, $target_entity_id, $relationship_type = '' ) {
        $source_id = (int) $source_entity_id;
        $target_id = (int) $target_entity_id;
        if ( ! $source_id || ! $target_id ) return false;

        $relationships = get_post_meta( $source_id, '_entity_relationships', true );
        if ( ! is_array( $relationships ) ) return false;

        $filtered = array();
        foreach ( $relationships as $rel ) {
            if ( (int) $rel['target_id'] === $target_id ) {
                if ( ! empty( $relationship_type ) && $rel['relationship_type'] !== $relationship_type ) {
                    $filtered[] = $rel;
                }
                continue;
            }
            $filtered[] = $rel;
        }

        update_post_meta( $source_id, '_entity_relationships', array_values( $filtered ) );
        $this->log_entity_action( $source_id, 'relationship_removed', "Removed link to Entity #$target_id" );
        return true;
    }

    /**
     * Integrity & Orphan Diagnostic Engine
     */
    public function run_orphan_diagnostic() {
        $orphan_relationships = array();
        $orphan_content_links = array();

        $entities = get_posts( array(
            'post_type'      => 'entity',
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'draft' ),
        ) );

        foreach ( $entities as $e ) {
            $rels = get_post_meta( $e->ID, '_entity_relationships', true );
            if ( is_array( $rels ) ) {
                foreach ( $rels as $r ) {
                    $target_id = isset( $r['target_id'] ) ? (int) $r['target_id'] : 0;
                    if ( $target_id ) {
                        $target_post = get_post( $target_id );
                        if ( ! $target_post || 'entity' !== $target_post->post_type ) {
                            $orphan_relationships[] = array(
                                'source_id'         => $e->ID,
                                'source_title'      => $e->post_title,
                                'target_id'         => $target_id,
                                'relationship_type' => $r['relationship_type'] ?? 'unknown',
                            );
                        }
                    }
                }
            }

            $content_links = get_post_meta( $e->ID, '_related_content', true );
            if ( is_array( $content_links ) ) {
                foreach ( $content_links as $c_id ) {
                    $c_post = get_post( (int) $c_id );
                    if ( ! $c_post || ! in_array( $c_post->post_type, array( 'brief', 'update', 'dossier' ), true ) ) {
                        $orphan_content_links[] = array(
                            'entity_id'  => $e->ID,
                            'content_id' => (int) $c_id,
                        );
                    }
                }
            }
        }

        return array(
            'total_entities_scanned' => count( $entities ),
            'orphan_relationships'   => $orphan_relationships,
            'orphan_content_links'   => $orphan_content_links,
            'is_healthy'             => ( empty( $orphan_relationships ) && empty( $orphan_content_links ) ),
        );
    }

    /**
     * Internal Entity Audit Log
     */
    public function log_entity_action( $entity_id, $action, $details = '' ) {
        $user_id = get_current_user_id();
        $log = get_post_meta( $entity_id, 'asc_entity_audit_log', true );
        if ( ! is_array( $log ) ) $log = array();

        array_unshift( $log, array(
            'action'    => $action,
            'user_id'   => $user_id,
            'user_name' => $user_id ? get_userdata( $user_id )->display_name : 'System',
            'timestamp' => time(),
            'details'   => $details,
        ) );

        $log = array_slice( $log, 0, 30 );
        update_post_meta( $entity_id, 'asc_entity_audit_log', $log );
    }

    /**
     * Admin Columns: Register
     */
    public function register_admin_columns( $columns ) {
        $new_columns = array();
        foreach ( $columns as $key => $title ) {
            $new_columns[ $key ] = $title;
            if ( 'title' === $key ) {
                $new_columns['entity_type']      = __( 'Entity Type', 'ascendance-core' );
                $new_columns['official_name']    = __( 'Official Name', 'ascendance-core' );
                $new_columns['entity_status']    = __( 'Status', 'ascendance-core' );
                $new_columns['country']          = __( 'Country', 'ascendance-core' );
                $new_columns['related_content']  = __( 'Related Content', 'ascendance-core' );
                $new_columns['related_entities'] = __( 'Related Entities', 'ascendance-core' );
            }
        }
        return $new_columns;
    }

    /**
     * Admin Columns: Render
     */
    public function render_admin_column( $column, $post_id ) {
        switch ( $column ) {
            case 'entity_type':
                $terms = wp_get_post_terms( $post_id, 'entity_type', array( 'fields' => 'names' ) );
                echo ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? esc_html( implode( ', ', $terms ) ) : '—';
                break;

            case 'official_name':
                $off = get_post_meta( $post_id, 'official_name', true );
                echo ! empty( $off ) ? esc_html( $off ) : '—';
                break;

            case 'entity_status':
                $status = get_post_meta( $post_id, 'entity_status', true ) ?: 'active';
                $colors = array(
                    'active'    => '#27AE60',
                    'inactive'  => '#7F8C8D',
                    'proposed'  => '#2980B9',
                    'suspended' => '#C0392B',
                    'completed' => '#8E44AD',
                );
                $color = $colors[ $status ] ?? '#7F8C8D';
                echo '<span style="color:' . esc_attr( $color ) . '; font-weight:bold; text-transform:uppercase; font-size:10px;">● ' . esc_html( ucfirst( $status ) ) . '</span>';
                break;

            case 'country':
                $c = get_post_meta( $post_id, 'country', true );
                echo ! empty( $c ) ? esc_html( $c ) : '—';
                break;

            case 'related_content':
                $content = get_post_meta( $post_id, '_related_content', true );
                $count = is_array( $content ) ? count( $content ) : 0;
                echo '<span class="badge" style="font-weight:bold;">' . $count . ' items</span>';
                break;

            case 'related_entities':
                $rels = get_post_meta( $post_id, '_entity_relationships', true );
                $count = is_array( $rels ) ? count( $rels ) : 0;
                echo '<span class="badge" style="font-weight:bold;">' . $count . ' links</span>';
                break;
        }
    }

    /**
     * Admin Filters: Render Dropdowns
     */
    public function render_admin_filters( $post_type ) {
        if ( 'entity' !== $post_type ) return;

        $current_status = isset( $_GET['entity_status_filter'] ) ? sanitize_text_field( $_GET['entity_status_filter'] ) : '';
        echo '<select name="entity_status_filter">';
        echo '<option value="">All Statuses</option>';
        $statuses = array( 'active' => 'Active', 'inactive' => 'Inactive', 'proposed' => 'Proposed', 'suspended' => 'Suspended', 'completed' => 'Completed' );
        foreach ( $statuses as $val => $lbl ) {
            echo '<option value="' . esc_attr( $val ) . '" ' . selected( $current_status, $val, false ) . '>' . esc_html( $lbl ) . '</option>';
        }
        echo '</select>';
    }

    /**
     * Admin Filters: Apply Query Modifications
     */
    public function apply_admin_filters_and_search( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() || 'entity' !== $query->get( 'post_type' ) ) {
            return;
        }

        if ( ! empty( $_GET['entity_status_filter'] ) ) {
            $query->set( 'meta_query', array(
                array(
                    'key'   => 'entity_status',
                    'value' => sanitize_text_field( $_GET['entity_status_filter'] ),
                ),
            ) );
        }
    }

    /**
     * Register Admin Metaboxes
     */
    public function register_admin_metaboxes() {
        add_meta_box(
            'ascendance_entity_relationships_mb',
            __( 'Entity Intelligence Relationship Graph & Audit Log', 'ascendance-core' ),
            array( $this, 'render_admin_metabox' ),
            'entity',
            'normal',
            'high'
        );
    }

    /**
     * Render Admin Metabox
     */
    public function render_admin_metabox( $post ) {
        $rels  = $this->get_entity_relationships( $post->ID );
        $links = $this->get_entity_content( $post->ID );
        $audit = get_post_meta( $post->ID, 'asc_entity_audit_log', true ) ?: array();

        echo '<div style="font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Oxygen-Sans,Ubuntu,Cantarell,Helvetica Neue,sans-serif;">';
        echo '<h4 style="margin-bottom:8px;">Direct Entity Relationships</h4>';
        if ( empty( $rels['direct'] ) ) {
            echo '<p style="color:#666; font-style:italic;">No direct entity relationships created yet.</p>';
        } else {
            echo '<ul style="margin:0 0 16px 0; padding-left:20px;">';
            foreach ( $rels['direct'] as $r ) {
                $target_title = get_the_title( $r['target_id'] );
                echo '<li><strong>' . esc_html( ucfirst( $r['relationship_type'] ) ) . '</strong> &rarr; <a href="' . get_edit_post_link( $r['target_id'] ) . '">' . esc_html( $target_title ) . '</a>';
                if ( ! empty( $r['notes'] ) ) echo ' <em>(' . esc_html( $r['notes'] ) . ')</em>';
                echo '</li>';
            }
            echo '</ul>';
        }

        echo '<h4 style="margin-bottom:8px;">Linked Intelligence Articles (' . count( $links ) . ')</h4>';
        if ( empty( $links ) ) {
            echo '<p style="color:#666; font-style:italic;">No content briefs or dossiers linked yet.</p>';
        } else {
            echo '<ul style="margin:0 0 16px 0; padding-left:20px;">';
            foreach ( $links as $l ) {
                echo '<li>[' . esc_html( strtoupper( $l->post_type ) ) . '] <a href="' . get_edit_post_link( $l->ID ) . '">' . esc_html( $l->post_title ) . '</a></li>';
            }
            echo '</ul>';
        }

        if ( ! empty( $audit ) ) {
            echo '<h4 style="margin-bottom:8px;">Recent Audit Activity</h4>';
            echo '<div style="background:#f9f9f9; border:1px solid #e5e5e5; padding:10px; max-height:120px; overflow-y:auto; font-size:11px;">';
            foreach ( array_slice( $audit, 0, 5 ) as $a ) {
                echo '<div><strong>' . esc_html( date( 'Y-m-d H:i', $a['timestamp'] ) ) . '</strong> — ' . esc_html( $a['user_name'] ) . ': <em>' . esc_html( $a['action'] ) . '</em> ' . esc_html( $a['details'] ) . '</div>';
            }
            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Calculate Entity Quality & Completeness Score (0-100)
     */
    public function calculate_entity_quality_score( $entity_id ) {
        $entity_id = (int) $entity_id;
        $post      = get_post( $entity_id );
        if ( ! $post || 'entity' !== $post->post_type ) {
            return array( 'score' => 0, 'health' => 'critical', 'issues' => array( 'invalid_post' ), 'is_critical' => true );
        }

        $score       = 100;
        $issues      = array();
        $is_critical = false;

        $terms     = wp_get_post_terms( $entity_id, 'entity_type', array( 'fields' => 'slugs' ) );
        $type_slug = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0] : 'company';

        $off_name = get_post_meta( $entity_id, 'official_name', true );
        $aliases  = get_post_meta( $entity_id, 'alternate_names', true );
        $desc     = get_post_meta( $entity_id, 'short_description', true ) ?: $post->post_excerpt;
        $country  = get_post_meta( $entity_id, 'country', true );
        $status   = get_post_meta( $entity_id, 'entity_status', true );
        $website  = get_post_meta( $entity_id, 'website', true );

        // 1. Core Field Checks (Type-Aware)
        if ( empty( $off_name ) && in_array( $type_slug, array( 'company', 'person' ), true ) ) {
            $score -= 5;
            $issues[] = 'missing_official_name';
        }

        if ( empty( $desc ) ) {
            $score -= 5;
            $issues[] = 'missing_description';
        }

        if ( empty( $country ) && in_array( $type_slug, array( 'company', 'mining-project', 'infrastructure-project' ), true ) ) {
            $score -= 5;
            $issues[] = 'missing_country';
        }

        if ( empty( $status ) ) {
            $score -= 5;
            $issues[] = 'missing_status';
        }

        // 2. Content Link Check
        $content = get_post_meta( $entity_id, '_related_content', true );
        if ( empty( $content ) || ! is_array( $content ) ) {
            $score -= 3;
            $issues[] = 'no_related_content';
        }

        // 3. Graph Integrity & Relationship Audit
        $rels = get_post_meta( $entity_id, '_entity_relationships', true );
        if ( is_array( $rels ) ) {
            $seen_targets = array();
            $allowed_preds = array_keys( $this->get_relationship_types() );

            foreach ( $rels as $r ) {
                $t_id   = isset( $r['target_id'] ) ? (int) $r['target_id'] : 0;
                $p_type = $r['relationship_type'] ?? 'connected_to';

                // Self-relationship check
                if ( $t_id === $entity_id ) {
                    $score -= 20;
                    $issues[] = 'self_relationship';
                    $is_critical = true;
                }

                // Duplicate relationship check
                if ( in_array( $t_id, $seen_targets, true ) ) {
                    $score -= 15;
                    $issues[] = 'duplicate_relationship';
                    $is_critical = true;
                }
                $seen_targets[] = $t_id;

                // Invalid predicate check
                if ( ! in_array( $p_type, $allowed_preds, true ) ) {
                    $score -= 15;
                    $issues[] = 'invalid_predicate';
                    $is_critical = true;
                }

                // Orphan relationship check
                if ( $t_id ) {
                    $t_post = get_post( $t_id );
                    if ( ! $t_post || 'entity' !== $t_post->post_type ) {
                        $score -= 20;
                        $issues[] = 'orphan_relationship';
                        $is_critical = true;
                    }
                }
            }
        }

        // 4. Duplicate Title Check
        $dup = $this->check_duplicate_entity( $post->post_title, '', $entity_id );
        if ( $dup ) {
            $score -= 15;
            $issues[] = 'duplicate_entity';
            $is_critical = true;
        }

        $score  = max( 0, $score );
        $issues = array_values( array_unique( $issues ) );

        $health = 'healthy';
        if ( $is_critical || $score < 50 ) {
            $health = 'critical';
        } elseif ( $score < 80 || ! empty( $issues ) ) {
            $health = 'review';
        }

        return array(
            'score'       => $score,
            'health'      => $health,
            'issues'      => $issues,
            'is_critical' => $is_critical,
        );
    }

    /**
     * Audit Duplicate Title and Alias Collisions Across Entities
     */
    public function get_duplicate_collisions_audit() {
        $collisions = array();
        $entities   = get_posts( array(
            'post_type'      => 'entity',
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'draft' ),
        ) );

        $alias_map = array();
        foreach ( $entities as $e ) {
            $aliases = get_post_meta( $e->ID, 'alternate_names', true );
            if ( ! empty( $aliases ) ) {
                $lines = array_filter( array_map( 'trim', explode( "\n", $aliases ) ) );
                foreach ( $lines as $line ) {
                    $key = strtolower( $line );
                    $alias_map[ $key ][] = array(
                        'entity_id' => $e->ID,
                        'title'     => $e->post_title,
                        'alias'     => $line,
                    );
                }
            }
        }

        foreach ( $alias_map as $key => $items ) {
            if ( count( $items ) > 1 ) {
                $collisions[] = array(
                    'alias'    => $items[0]['alias'],
                    'count'    => count( $items ),
                    'entities' => $items,
                );
            }
        }

        return $collisions;
    }

    /**
     * Audit Published Briefs, Updates, Dossiers Without Entity Links
     */
    public function get_unlinked_content_audit() {
        $unlinked = array();
        $posts    = get_posts( array(
            'post_type'      => array( 'brief', 'update', 'dossier' ),
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ) );

        foreach ( $posts as $p ) {
            $entities = get_post_meta( $p->ID, '_related_entities', true );
            if ( empty( $entities ) || ! is_array( $entities ) ) {
                $unlinked[] = array(
                    'id'        => $p->ID,
                    'title'     => $p->post_title,
                    'post_type' => $p->post_type,
                    'date'      => get_the_date( 'd M Y', $p ),
                );
            }
        }

        return $unlinked;
    }

    /**
     * System-Wide Full Entity Quality Audit Engine
     */
    public function run_full_quality_audit( $force = false ) {
        $transient_key = 'asc_entity_quality_report';
        if ( ! $force ) {
            $cached = get_transient( $transient_key );
            if ( false !== $cached ) {
                return $cached;
            }
        }

        $entities = get_posts( array(
            'post_type'      => 'entity',
            'posts_per_page' => -1,
            'post_status'    => array( 'publish', 'draft' ),
        ) );

        $total_entities  = count( $entities );
        $healthy_count   = 0;
        $review_count    = 0;
        $critical_count  = 0;
        $total_score_sum = 0;

        $entity_reports = array();

        foreach ( $entities as $e ) {
            $qa = $this->calculate_entity_quality_score( $e->ID );
            $total_score_sum += $qa['score'];

            if ( 'healthy' === $qa['health'] ) $healthy_count++;
            elseif ( 'review' === $qa['health'] ) $review_count++;
            elseif ( 'critical' === $qa['health'] ) $critical_count++;

            $terms = wp_get_post_terms( $e->ID, 'entity_type', array( 'fields' => 'names' ) );

            $entity_reports[] = array(
                'id'           => $e->ID,
                'title'        => $e->post_title,
                'status'       => $e->post_status,
                'type'         => ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0] : 'Entity',
                'score'        => $qa['score'],
                'health'       => $qa['health'],
                'issues'       => $qa['issues'],
                'is_critical'  => $qa['is_critical'],
                'last_updated' => get_the_modified_date( 'd M Y', $e ),
            );
        }

        $avg_health_score = $total_entities > 0 ? round( $total_score_sum / $total_entities ) : 100;
        $collisions       = $this->get_duplicate_collisions_audit();
        $unlinked_content = $this->get_unlinked_content_audit();

        $report = array(
            'total_entities'     => $total_entities,
            'healthy_count'      => $healthy_count,
            'review_count'       => $review_count,
            'critical_count'     => $critical_count,
            'overall_score'      => $avg_health_score,
            'entity_reports'     => $entity_reports,
            'collisions'         => $collisions,
            'unlinked_content'   => $unlinked_content,
            'generated_at'       => time(),
        );

        set_transient( $transient_key, $report, 3600 );
        return $report;
    }
}
