<?php
namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Recommendation Engine for priority-based personalization feed
 *
 * Phase 2B — Personalized "For You" Intelligence Engine
 */
class Recommendation_Engine {
    /**
     * Singleton instance
     * @var Recommendation_Engine|null
     */
    private static $instance = null;

    // Configurable Default Scoring Weights
    const TOPIC_WEIGHT              = 15;
    const REGION_WEIGHT             = 15;
    const EXACT_MATCH_BONUS         = 20;
    const SAVED_SIMILARITY_WEIGHT   = 15;
    const READING_SIMILARITY_WEIGHT = 12;
    const CONTINUE_READING_BOOST    = 25;
    const FRESHNESS_BOOST           = 10;
    const CONTENT_TYPE_WEIGHT       = 5;
    const CATEGORY_ENTITLEMENT_WEIGHT = 20;
    const CATEGORY_AFFINITY_WEIGHT    = 15;

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
     * Constructor
     */
    private function __construct() {}

    /**
     * Get user membership level slug ('essential', 'professional', 'enterprise', or 'guest')
     *
     * @param int $user_id
     * @return string
     */
    public function get_user_tier_slug( $user_id ) {
        if ( ! $user_id ) {
            return 'guest';
        }

        if ( function_exists( 'pmpro_getMembershipLevelForUser' ) ) {
            $user_level = pmpro_getMembershipLevelForUser( $user_id );
            if ( ! empty( $user_level ) && isset( $user_level->name ) ) {
                $name = strtolower( $user_level->name );
                if ( strpos( $name, 'enterprise' ) !== false ) {
                    return 'enterprise';
                }
                if ( strpos( $name, 'professional' ) !== false ) {
                    return 'professional';
                }
                if ( strpos( $name, 'essential' ) !== false ) {
                    return 'essential';
                }
            }
        }

        // Check user meta override or default
        $meta_tier = get_user_meta( $user_id, 'ascendance_membership_tier', true );
        if ( ! empty( $meta_tier ) ) {
            return strtolower( $meta_tier );
        }

        // Administrators get enterprise clearance
        if ( user_can( $user_id, 'manage_options' ) ) {
            return 'enterprise';
        }

        return 'essential'; // default logged-in tier
    }

    /**
     * Check if user can access post based on tier
     *
     * @param int $user_id
     * @param int $post_id
     * @return bool
     */
    public function user_can_access_post( $user_id, $post_id ) {
        if ( ! $user_id || ! $post_id ) {
            return false;
        }

        if ( user_can( $user_id, 'edit_post', $post_id ) ) {
            return true;
        }

        $user_tier = $this->get_user_tier_slug( $user_id );
        if ( 'enterprise' === $user_tier ) {
            return true;
        }

        $required_tier = get_field( 'tier_access', $post_id );
        if ( ! $required_tier ) {
            $terms = wp_get_post_terms( $post_id, 'tier', array( 'fields' => 'slugs' ) );
            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                $required_tier = $terms[0];
            } else {
                $post_type = get_post_type( $post_id );
                $required_tier = ( 'dossier' === $post_type ) ? 'professional' : 'essential';
            }
        }
        $required_tier = strtolower( $required_tier );

        if ( 'essential' === $user_tier ) {
            return 'essential' === $required_tier;
        }
        if ( 'professional' === $user_tier ) {
            return in_array( $required_tier, array( 'essential', 'professional' ), true );
        }

        return true;
    }

    /**
     * Extract topic and region term IDs from a user's saved posts
     *
     * @param int $user_id
     * @return array Array with 'topics' and 'regions' keys
     */
    public function get_user_saved_affinities( $user_id ) {
        $saved_ids = (array) get_user_meta( $user_id, 'as_saved_posts', true );
        $saved_ids = array_values( array_filter( array_map( 'intval', $saved_ids ) ) );

        if ( empty( $saved_ids ) ) {
            return array( 'topics' => array(), 'regions' => array() );
        }

        $topics = array();
        $regions = array();

        foreach ( $saved_ids as $p_id ) {
            $t_terms = wp_get_post_terms( $p_id, 'topic', array( 'fields' => 'ids' ) );
            if ( ! is_wp_error( $t_terms ) && ! empty( $t_terms ) ) {
                $topics = array_merge( $topics, array_map( 'intval', $t_terms ) );
            }
            $r_terms = wp_get_post_terms( $p_id, 'region', array( 'fields' => 'ids' ) );
            if ( ! is_wp_error( $r_terms ) && ! empty( $r_terms ) ) {
                $regions = array_merge( $regions, array_map( 'intval', $r_terms ) );
            }
        }

        return array(
            'topics'  => array_values( array_unique( $topics ) ),
            'regions' => array_values( array_unique( $regions ) ),
        );
    }

    /**
     * Extract topic/region term IDs and progress map from a user's reading history
     *
     * @param int $user_id
     * @return array Array with 'topics', 'regions', 'in_progress', 'completed'
     */
    public function get_user_reading_affinities( $user_id ) {
        $history = (array) get_user_meta( $user_id, 'asc_reading_history', true );
        if ( empty( $history ) || ! is_array( $history ) ) {
            return array( 'topics' => array(), 'regions' => array(), 'in_progress' => array(), 'completed' => array() );
        }

        $topics = array();
        $regions = array();
        $in_progress = array(); // post_id => progress float
        $completed = array();   // post_id array

        foreach ( $history as $item ) {
            if ( empty( $item['post_id'] ) ) continue;
            $p_id = (int) $item['post_id'];
            $prog = isset( $item['progress'] ) ? (float) $item['progress'] : 0;

            if ( $prog >= 95 ) {
                $completed[] = $p_id;
            } elseif ( $prog >= 5 ) {
                $in_progress[$p_id] = $prog;
            }

            $t_terms = wp_get_post_terms( $p_id, 'topic', array( 'fields' => 'ids' ) );
            if ( ! is_wp_error( $t_terms ) && ! empty( $t_terms ) ) {
                $topics = array_merge( $topics, array_map( 'intval', $t_terms ) );
            }
            $r_terms = wp_get_post_terms( $p_id, 'region', array( 'fields' => 'ids' ) );
            if ( ! is_wp_error( $r_terms ) && ! empty( $r_terms ) ) {
                $regions = array_merge( $regions, array_map( 'intval', $r_terms ) );
            }
        }

        return array(
            'topics'      => array_values( array_unique( $topics ) ),
            'regions'     => array_values( array_unique( $regions ) ),
            'in_progress' => $in_progress,
            'completed'   => array_values( array_unique( $completed ) ),
        );
    }

    /**
     * Extract topic term IDs for paid category add-ons aligned with user's reading/saved history and preferences
     *
     * @param int $user_id
     * @return array Array of topic term IDs
     */
    public function get_user_category_affinities( $user_id ) {
        if ( ! $user_id ) {
            return array();
        }

        $cache_key = 'asc_user_cat_affinities_' . $user_id;
        $cached    = wp_cache_get( $cache_key, 'ascendance' );
        if ( false !== $cached ) {
            return $cached;
        }

        $saved_affinities   = $this->get_user_saved_affinities( $user_id );
        $reading_affinities = $this->get_user_reading_affinities( $user_id );
        $pref_topics        = (array) get_user_meta( $user_id, 'preferred_topics', true );

        $all_topic_ids = array_unique( array_merge(
            $saved_affinities['topics'],
            $reading_affinities['topics'],
            array_map( 'intval', $pref_topics )
        ) );

        $category_affinities = array();
        if ( ! empty( $all_topic_ids ) ) {
            foreach ( $all_topic_ids as $t_id ) {
                $is_paid = get_term_meta( $t_id, 'is_paid_addon', true );
                if ( $is_paid ) {
                    $category_affinities[] = (int) $t_id;
                }
            }
        }

        wp_cache_set( $cache_key, $category_affinities, 'ascendance', 300 );
        return $category_affinities;
    }

    /**
     * Get subscriber entity affinities (Direct Read, Saved, 1-Hop Related)
     *
     * @param int $user_id
     * @return array
     */
    public function get_user_entity_affinities( $user_id ) {
        if ( ! $user_id ) {
            return array(
                'direct_entities'  => array(),
                'saved_entities'   => array(),
                'related_entities' => array(),
            );
        }

        $cache_key = 'asc_user_entity_affinities_' . $user_id;
        $cached    = wp_cache_get( $cache_key, 'ascendance' );
        if ( false !== $cached ) {
            return $cached;
        }

        $direct_entities  = array();
        $saved_entities   = array();
        $related_entities = array();

        $entity_mgr = class_exists( 'Ascendance\Core\Entity_Intelligence' ) ? Entity_Intelligence::get_instance() : null;

        // 1. Saved Content Entities
        $saved_ids = (array) get_user_meta( $user_id, 'as_saved_posts', true );
        $saved_ids = array_values( array_filter( array_map( 'intval', $saved_ids ) ) );
        foreach ( $saved_ids as $s_id ) {
            $e_list = get_post_meta( $s_id, '_related_entities', true );
            if ( is_array( $e_list ) ) {
                foreach ( $e_list as $e_id ) {
                    $e_id = (int) $e_id;
                    if ( $e_id && ! in_array( $e_id, $saved_entities, true ) ) {
                        $saved_entities[] = $e_id;
                    }
                }
            }
        }

        // 2. Reading History Entities (From DB Table & Usermeta)
        global $wpdb;
        $read_post_ids = array();

        $meta_history = (array) get_user_meta( $user_id, 'asc_reading_history', true );
        foreach ( $meta_history as $m_item ) {
            if ( ! empty( $m_item['post_id'] ) ) {
                $read_post_ids[] = (int) $m_item['post_id'];
            }
        }

        $table_name = $wpdb->prefix . 'asc_reading_history';
        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) === $table_name ) {
            $db_read = $wpdb->get_col( $wpdb->prepare( "SELECT DISTINCT post_id FROM $table_name WHERE user_id = %d", $user_id ) );
            if ( is_array( $db_read ) ) {
                $read_post_ids = array_merge( $read_post_ids, array_map( 'intval', $db_read ) );
            }
        }

        $read_post_ids = array_values( array_unique( $read_post_ids ) );
        foreach ( $read_post_ids as $r_id ) {
            $e_list = get_post_meta( $r_id, '_related_entities', true );
            if ( is_array( $e_list ) ) {
                foreach ( $e_list as $e_id ) {
                    $e_id = (int) $e_id;
                    if ( $e_id && ! in_array( $e_id, $direct_entities, true ) ) {
                        $direct_entities[] = $e_id;
                    }
                }
            }
        }

        // 3. 1-Hop Related Entities Graph Traversal
        if ( $entity_mgr ) {
            $all_user_entities = array_unique( array_merge( $saved_entities, $direct_entities ) );
            foreach ( $all_user_entities as $base_entity_id ) {
                $rels = $entity_mgr->get_entity_relationships( $base_entity_id );
                if ( ! empty( $rels['direct'] ) ) {
                    foreach ( $rels['direct'] as $rel ) {
                        $t_id   = (int) $rel['target_id'];
                        $p_type = $rel['relationship_type'] ?? 'connected_to';
                        if ( $t_id && ! in_array( $t_id, $all_user_entities, true ) ) {
                            $related_entities[ $t_id ] = array(
                                'source_entity'     => $base_entity_id,
                                'relationship_type' => $p_type,
                            );
                        }
                    }
                }
            }
        }

        $affinities = array(
            'direct_entities'  => $direct_entities,
            'saved_entities'   => $saved_entities,
            'related_entities' => $related_entities,
        );

        wp_cache_set( $cache_key, $affinities, 'ascendance', 300 );
        return $affinities;
    }

    /**
     * Generate human-readable recommendation explanation and reason code
     *
     * @param int $post_id
     * @param array $score_details
     * @param int $user_id
     * @return array Array with 'reason' and 'reason_type'
     */
    public function generate_recommendation_reason( $post_id, $score_details, $user_id ) {
        // Priority 1: Unfinished Continue Reading item
        if ( ! empty( $score_details['continue_reading_boost'] ) && $score_details['continue_reading_boost'] > 0 ) {
            $prog = isset( $score_details['progress'] ) ? round( $score_details['progress'] ) : 0;
            return array(
                'reason'      => sprintf( __( 'Continue reading: %d%% completed', 'ascendance-core' ), $prog ),
                'reason_type' => 'continue_reading',
            );
        }

        // Priority 1B: Locked Category / Locked Tier Notification
        if ( ! empty( $score_details['is_locked'] ) ) {
            if ( ! empty( $score_details['is_category_locked'] ) && ! empty( $score_details['required_category_name'] ) ) {
                return array(
                    'reason'      => sprintf( __( 'Category Locked: Requires %s Add-on Desk', 'ascendance-core' ), $score_details['required_category_name'] ),
                    'reason_type' => 'category_locked',
                );
            }
            if ( ! empty( $score_details['is_tier_locked'] ) ) {
                return array(
                    'reason'      => __( 'Clearance Locked: Tier upgrade required', 'ascendance-core' ),
                    'reason_type' => 'tier_locked',
                );
            }
        }

        // Priority 2: Category Entitlement Match (Owned Add-on Desk)
        if ( ! empty( $score_details['category_entitlement_score'] ) && $score_details['category_entitlement_score'] > 0 && ! empty( $score_details['matching_category_name'] ) ) {
            return array(
                'reason'      => sprintf( __( 'Recommended because you own the %s Desk', 'ascendance-core' ), $score_details['matching_category_name'] ),
                'reason_type' => 'category_entitlement',
            );
        }

        // Priority 3: Category Affinity Match (Demonstrated interest in specialist desk)
        if ( ! empty( $score_details['category_affinity_score'] ) && $score_details['category_affinity_score'] > 0 && ! empty( $score_details['matching_category_name'] ) ) {
            return array(
                'reason'      => sprintf( __( 'Matches your %s specialist interest', 'ascendance-core' ), $score_details['matching_category_name'] ),
                'reason_type' => 'category_affinity',
            );
        }

        // Priority 2: Saved Entity Match
        if ( ! empty( $score_details['entity_match_type'] ) && 'saved' === $score_details['entity_match_type'] && ! empty( $score_details['matched_entity_name'] ) ) {
            return array(
                'reason'      => sprintf( __( 'Recommended because you saved %s-related intelligence', 'ascendance-core' ), $score_details['matched_entity_name'] ),
                'reason_type' => 'saved_entity',
            );
        }

        // Priority 3: Direct Entity Read Match
        if ( ! empty( $score_details['entity_match_type'] ) && 'direct' === $score_details['entity_match_type'] && ! empty( $score_details['matched_entity_name'] ) ) {
            return array(
                'reason'      => sprintf( __( 'Recommended because you recently read %s intelligence', 'ascendance-core' ), $score_details['matched_entity_name'] ),
                'reason_type' => 'direct_entity',
            );
        }

        // Priority 4: Related Entity Graph Match
        if ( ! empty( $score_details['entity_match_type'] ) && 'related' === $score_details['entity_match_type'] && ! empty( $score_details['matched_entity_name'] ) ) {
            return array(
                'reason'      => sprintf( __( 'Recommended because it relates to an Entity you recently researched (%s)', 'ascendance-core' ), $score_details['matched_entity_name'] ),
                'reason_type' => 'related_entity',
            );
        }

        // Get term labels for human readability
        $topic_label = '';
        if ( ! empty( $score_details['matching_topics'] ) ) {
            $first_topic_id = reset( $score_details['matching_topics'] );
            $t_obj = get_term( $first_topic_id, 'topic' );
            if ( $t_obj && ! is_wp_error( $t_obj ) ) {
                $topic_label = $t_obj->name;
            }
        }

        $region_label = '';
        if ( ! empty( $score_details['matching_regions'] ) ) {
            $first_region_id = reset( $score_details['matching_regions'] );
            $r_obj = get_term( $first_region_id, 'region' );
            if ( $r_obj && ! is_wp_error( $r_obj ) ) {
                $region_label = $r_obj->name;
            }
        }

        // Priority 5: Exact Topic + Region Match
        if ( ! empty( $score_details['exact_bonus'] ) && $score_details['exact_bonus'] > 0 && $topic_label && $region_label ) {
            return array(
                'reason'      => sprintf( __( 'Matches your %s & %s focus', 'ascendance-core' ), $topic_label, $region_label ),
                'reason_type' => 'exact_match',
            );
        }

        // Priority 6: Saved Content Similarity
        if ( ! empty( $score_details['saved_similarity'] ) && $score_details['saved_similarity'] > 0 ) {
            if ( $topic_label ) {
                return array(
                    'reason'      => sprintf( __( 'Recommended because you saved similar %s intelligence', 'ascendance-core' ), $topic_label ),
                    'reason_type' => 'saved_similarity',
                );
            }
            return array(
                'reason'      => __( 'Recommended because you saved similar intelligence', 'ascendance-core' ),
                'reason_type' => 'saved_similarity',
            );
        }

        // Priority 7: Reading History Similarity
        if ( ! empty( $score_details['reading_similarity'] ) && $score_details['reading_similarity'] > 0 ) {
            if ( $region_label ) {
                return array(
                    'reason'      => sprintf( __( 'Recommended based on your recent %s reading', 'ascendance-core' ), $region_label ),
                    'reason_type' => 'reading_similarity',
                );
            }
            if ( $topic_label ) {
                return array(
                    'reason'      => sprintf( __( 'Recommended based on your recent %s reading', 'ascendance-core' ), $topic_label ),
                    'reason_type' => 'reading_similarity',
                );
            }
        }

        // Priority 8: Topic Match
        if ( ! empty( $score_details['topic_score'] ) && $score_details['topic_score'] > 0 && $topic_label ) {
            return array(
                'reason'      => sprintf( __( 'Recommended because you follow %s', 'ascendance-core' ), $topic_label ),
                'reason_type' => 'topic_match',
            );
        }

        // Priority 9: Region Match
        if ( ! empty( $score_details['region_score'] ) && $score_details['region_score'] > 0 && $region_label ) {
            return array(
                'reason'      => sprintf( __( 'Recommended because you watch %s', 'ascendance-core' ), $region_label ),
                'reason_type' => 'region_match',
            );
        }

        // Priority 10: Content Freshness
        if ( ! empty( $score_details['freshness_bonus'] ) && $score_details['freshness_bonus'] > 0 ) {
            if ( $topic_label ) {
                return array(
                    'reason'      => sprintf( __( 'Fresh intelligence on %s', 'ascendance-core' ), $topic_label ),
                    'reason_type' => 'freshness',
                );
            }
            return array(
                'reason'      => __( 'Fresh intelligence published this week', 'ascendance-core' ),
                'reason_type' => 'freshness',
            );
        }

        // Fallback: Trending / Clearance Tier match
        return array(
            'reason'      => __( 'Top briefing for your clearance tier', 'ascendance-core' ),
            'reason_type' => 'trending',
        );
    }

    /**
     * Calculate scoring details for a single post relative to a subscriber's preferences
     *
     * @param int $post_id
     * @param int $user_id
     * @param array|null $preferred_topics
     * @param array|null $preferred_regions
     * @return array
     */
    public function calculate_post_score( $post_id, $user_id, $preferred_topics = null, $preferred_regions = null ) {
        if ( null === $preferred_topics ) {
            $preferred_topics = get_user_meta( $user_id, 'preferred_topics', true );
            if ( empty( $preferred_topics ) ) {
                $preferred_topics = get_user_meta( $user_id, 'preferred_industries', true );
            }
        }
        if ( null === $preferred_regions ) {
            $preferred_regions = get_user_meta( $user_id, 'preferred_regions', true );
        }

        $preferred_topics = is_array( $preferred_topics ) ? $preferred_topics : ( ! empty( $preferred_topics ) ? (array) $preferred_topics : array() );
        $preferred_regions = is_array( $preferred_regions ) ? $preferred_regions : ( ! empty( $preferred_regions ) ? (array) $preferred_regions : array() );

        $preferred_topics = array_map( 'intval', $preferred_topics );
        $preferred_regions = array_map( 'intval', $preferred_regions );

        // Fetch scoring weights from options with defaults
        $topic_score_val      = intval( get_option( 'ascendance_rec_topic_score', self::TOPIC_WEIGHT ) );
        $region_score_val     = intval( get_option( 'ascendance_rec_region_score', self::REGION_WEIGHT ) );
        $exact_bonus_val      = intval( get_option( 'ascendance_rec_exact_bonus', self::EXACT_MATCH_BONUS ) );
        $saved_weight_val     = intval( get_option( 'ascendance_rec_saved_weight', self::SAVED_SIMILARITY_WEIGHT ) );
        $reading_weight_val   = intval( get_option( 'ascendance_rec_reading_weight', self::READING_SIMILARITY_WEIGHT ) );
        $continue_boost_val   = intval( get_option( 'ascendance_rec_continue_boost', self::CONTINUE_READING_BOOST ) );
        $freshness_bonus_val  = intval( get_option( 'ascendance_rec_freshness_bonus', self::FRESHNESS_BOOST ) );
        $freshness_days       = intval( get_option( 'ascendance_rec_freshness_days', 7 ) );

        // Fetch post taxonomy terms
        $post_topics = wp_get_post_terms( $post_id, 'topic', array( 'fields' => 'ids' ) );
        $post_topics = ( ! is_wp_error( $post_topics ) && ! empty( $post_topics ) ) ? array_map( 'intval', $post_topics ) : array();

        $post_regions = wp_get_post_terms( $post_id, 'region', array( 'fields' => 'ids' ) );
        $post_regions = ( ! is_wp_error( $post_regions ) && ! empty( $post_regions ) ) ? array_map( 'intval', $post_regions ) : array();

        $matching_topics = array_intersect( $post_topics, $preferred_topics );
        $matching_regions = array_intersect( $post_regions, $preferred_regions );

        $topic_score = ! empty( $matching_topics ) ? $topic_score_val : 0;
        $region_score = ! empty( $matching_regions ) ? $region_score_val : 0;
        $exact_bonus = ( ! empty( $matching_topics ) && ! empty( $matching_regions ) ) ? $exact_bonus_val : 0;

        // Saved Content Similarity
        $saved_affinities = $this->get_user_saved_affinities( $user_id );
        $saved_topic_match = array_intersect( $post_topics, $saved_affinities['topics'] );
        $saved_region_match = array_intersect( $post_regions, $saved_affinities['regions'] );
        $saved_similarity = ( ! empty( $saved_topic_match ) || ! empty( $saved_region_match ) ) ? $saved_weight_val : 0;

        // Reading History Similarity
        $reading_affinities = $this->get_user_reading_affinities( $user_id );
        $read_topic_match = array_intersect( $post_topics, $reading_affinities['topics'] );
        $read_region_match = array_intersect( $post_regions, $reading_affinities['regions'] );
        $reading_similarity = ( ! empty( $read_topic_match ) || ! empty( $read_region_match ) ) ? $reading_weight_val : 0;

        // Continue Reading Boost
        $continue_reading_boost = 0;
        $current_progress = 0;
        if ( isset( $reading_affinities['in_progress'][$post_id] ) ) {
            $current_progress = $reading_affinities['in_progress'][$post_id];
            $continue_reading_boost = $continue_boost_val;
        }

        // Decaying Freshness Bonus
        $freshness_bonus = 0;
        $post_date = get_post_field( 'post_date', $post_id );
        if ( $post_date ) {
            $diff_days = ( time() - strtotime( $post_date ) ) / DAY_IN_SECONDS;
            if ( $diff_days >= 0 && $diff_days <= $freshness_days ) {
                $decay_factor = max( 0, 1 - ( $diff_days / $freshness_days ) );
                $freshness_bonus = round( $freshness_bonus_val * $decay_factor );
            }
        }

        // Advanced Entity-Aware Recommendation Scoring
        $entity_bonus           = 0;
        $matched_entity_id      = 0;
        $matched_entity_name    = '';
        $entity_match_type      = ''; // 'saved', 'direct', 'related'

        $post_entities = get_post_meta( $post_id, '_related_entities', true );
        if ( ! empty( $post_entities ) && is_array( $post_entities ) && $user_id ) {
            $entity_affinities = $this->get_user_entity_affinities( $user_id );

            $predicate_multipliers = array(
                'operates'     => 1.2,
                'owns'         => 1.2,
                'invests_in'   => 1.2,
                'acquired'     => 1.2,
                'leads'        => 1.2,
                'partners_with'=> 1.0,
                'located_in'   => 1.0,
                'supplies'     => 1.0,
                'regulates'    => 1.0,
                'competes_with'=> 0.8,
                'connected_to' => 0.8,
            );

            foreach ( $post_entities as $cand_e_id ) {
                $cand_e_id = (int) $cand_e_id;
                if ( ! $cand_e_id ) continue;

                // Priority 1: Saved Entity Match (+25 pts)
                if ( in_array( $cand_e_id, $entity_affinities['saved_entities'], true ) ) {
                    $entity_bonus       += 25;
                    $matched_entity_id   = $cand_e_id;
                    $entity_match_type   = 'saved';
                    break;
                }

                // Priority 2: Direct Read Entity Match (+20 pts)
                if ( in_array( $cand_e_id, $entity_affinities['direct_entities'], true ) ) {
                    $entity_bonus       += 20;
                    $matched_entity_id   = $cand_e_id;
                    $entity_match_type   = 'direct';
                    break;
                }

                // Priority 3: 1-Hop Related Entity Match (+15 pts * predicate multiplier)
                if ( isset( $entity_affinities['related_entities'][ $cand_e_id ] ) ) {
                    $rel_info   = $entity_affinities['related_entities'][ $cand_e_id ];
                    $p_type     = $rel_info['relationship_type'];
                    $mult       = $predicate_multipliers[ $p_type ] ?? 1.0;
                    $entity_bonus       += round( 15 * $mult );
                    $matched_entity_id   = $cand_e_id;
                    $entity_match_type   = 'related';
                    break;
                }
            }

            // Combined Entity + Topic Bonus (+10 pts)
            if ( $entity_bonus > 0 && ! empty( $matching_topics ) ) {
                $entity_bonus += 10;
            }

            // Combined Entity + Region Bonus (+10 pts)
            if ( $entity_bonus > 0 && ! empty( $matching_regions ) ) {
                $entity_bonus += 10;
            }

            if ( $matched_entity_id ) {
                $matched_entity_name = get_the_title( $matched_entity_id );
            }
        }

        // Category-Aware Personalization Scoring
        $category_entitlement_score = 0;
        $category_affinity_score    = 0;
        $matching_category_name     = '';
        $matching_category_slug     = '';

        $paywall = class_exists( 'Ascendance\Core\Paywall' ) ? Paywall::get_instance() : null;
        if ( $paywall && ! empty( $post_topics ) ) {
            $cat_affinities = $this->get_user_category_affinities( $user_id );
            foreach ( $post_topics as $t_id ) {
                $is_paid = get_term_meta( $t_id, 'is_paid_addon', true );
                if ( $is_paid ) {
                    $t_obj = get_term( $t_id, 'topic' );
                    if ( $t_obj && ! is_wp_error( $t_obj ) ) {
                        $matching_category_slug = $t_obj->slug;
                        $matching_category_name = $t_obj->name;

                        // Check if user holds active entitlement for this paid category (+20 pts)
                        if ( $user_id && $paywall->user_has_category_entitlement( $user_id, $t_obj->slug ) ) {
                            $category_entitlement_score = intval( get_option( 'ascendance_rec_cat_entitle_weight', self::CATEGORY_ENTITLEMENT_WEIGHT ) );
                        }

                        // Check if user has high category reading/saved affinity (+15 pts)
                        if ( in_array( $t_id, $cat_affinities, true ) ) {
                            $category_affinity_score = intval( get_option( 'ascendance_rec_cat_affinity_weight', self::CATEGORY_AFFINITY_WEIGHT ) );
                        }

                        if ( $category_entitlement_score > 0 || $category_affinity_score > 0 ) {
                            break; // Stop after first matching category
                        }
                    }
                }
            }
        }

        $category_score = $category_entitlement_score + $category_affinity_score;
        $total_score = $topic_score + $region_score + $exact_bonus + $saved_similarity + $reading_similarity + $continue_reading_boost + $freshness_bonus + $entity_bonus + $category_score;

        $score_details = array(
            'total_score'                => (int) $total_score,
            'category_score'             => (int) $category_score,
            'category_entitlement_score' => (int) $category_entitlement_score,
            'category_affinity_score'    => (int) $category_affinity_score,
            'matching_category_slug'     => $matching_category_slug,
            'matching_category_name'     => $matching_category_name,
            'entity_bonus'               => (int) $entity_bonus,
            'entity_match_type'          => $entity_match_type,
            'matched_entity_name'        => $matched_entity_name,
            'topic_score'                => (int) $topic_score,
            'region_score'               => (int) $region_score,
            'exact_bonus'                => (int) $exact_bonus,
            'saved_similarity'           => (int) $saved_similarity,
            'reading_similarity'         => (int) $reading_similarity,
            'continue_reading_boost'     => (int) $continue_reading_boost,
            'freshness_bonus'            => (int) $freshness_bonus,
            'progress'                   => $current_progress,
            'matching_topics'            => array_values( $matching_topics ),
            'matching_regions'           => array_values( $matching_regions ),
        );

        // Allow future filters to adjust scoring details
        $score_details = apply_filters( 'ascendance_recommendation_score_details', $score_details, $post_id, $user_id );

        // Recalculate total score if filters updated components
        $score_details['total_score'] = (int) $score_details['topic_score'] +
                                        (int) $score_details['region_score'] +
                                        (int) $score_details['exact_bonus'] +
                                        (int) $score_details['saved_similarity'] +
                                        (int) $score_details['reading_similarity'] +
                                        (int) $score_details['continue_reading_boost'] +
                                        (int) $score_details['freshness_bonus'] +
                                        (int) $score_details['entity_bonus'] +
                                        (int) $score_details['category_score'];

        // Generate explainable recommendation reason
        $reason_data = $this->generate_recommendation_reason( $post_id, $score_details, $user_id );
        $score_details['reason'] = $reason_data['reason'];
        $score_details['reason_type'] = $reason_data['reason_type'];

        return $score_details;
    }

    /**
     * Apply diversity filter to ensure feed balanced across topics and post types
     *
     * @param array $ranked_list
     * @param int $posts_per_page
     * @return array
     */
    public function apply_diversity_filter( $ranked_list, $posts_per_page ) {
        if ( count( $ranked_list ) <= 2 ) {
            return $ranked_list;
        }

        $filtered = array();
        $topic_counts = array();
        $type_counts = array();

        foreach ( $ranked_list as $item ) {
            $post = $item['post'];
            $post_type = get_post_type( $post );

            // Always keep Continue Reading item in top slots
            if ( ! empty( $item['score_details']['continue_reading_boost'] ) ) {
                $filtered[] = $item;
                continue;
            }

            $primary_topic = 'general';
            if ( ! empty( $item['score_details']['matching_topics'] ) ) {
                $primary_topic = reset( $item['score_details']['matching_topics'] );
            }

            $t_count = isset( $topic_counts[$primary_topic] ) ? $topic_counts[$primary_topic] : 0;
            $type_count = isset( $type_counts[$post_type] ) ? $type_counts[$post_type] : 0;

            // Cap consecutive same-topic or same-type items
            if ( $t_count >= 3 && count( $filtered ) < $posts_per_page ) {
                continue; // skip over-saturated topic for diversity
            }

            $filtered[] = $item;
            $topic_counts[$primary_topic] = $t_count + 1;
            $type_counts[$post_type] = $type_count + 1;

            if ( count( $filtered ) >= $posts_per_page * 2 ) {
                break;
            }
        }

        // If filtering removed too many items, backfill with remaining candidates
        if ( count( $filtered ) < $posts_per_page ) {
            foreach ( $ranked_list as $item ) {
                $p_id = $item['post']->ID;
                $already_in = false;
                foreach ( $filtered as $f ) {
                    if ( $f['post']->ID === $p_id ) {
                        $already_in = true;
                        break;
                    }
                }
                if ( ! $already_in ) {
                    $filtered[] = $item;
                    if ( count( $filtered ) >= $posts_per_page ) {
                        break;
                    }
                }
            }
        }

        return $filtered;
    }

    /**
     * Invalidate transient caches for a user
     *
     * @param int $user_id
     */
    public function invalidate_user_cache( $user_id ) {
        if ( ! $user_id ) return;
        delete_transient( 'asc_rec_u' . $user_id . '_default' );
        delete_transient( 'asc_rec_u' . $user_id . '_locked' );
    }

    /**
     * Get scored and ranked recommendations for a user
     *
     * @param int $user_id
     * @param array|string $post_types
     * @param int $posts_per_page
     * @param array $extra_args e.g. date_query, bypass_cache, include_locked
     * @return array Array of formatted recommendation objects
     */
    public function get_ranked_recommendations( $user_id, $post_types = array( 'brief', 'dossier', 'update' ), $posts_per_page = 6, $extra_args = array() ) {
        $user_id = (int) $user_id;
        $bypass_cache = ! empty( $extra_args['bypass_cache'] );
        $include_locked = ! empty( $extra_args['include_locked'] );

        $cache_key = 'asc_rec_u' . $user_id . '_' . ( $include_locked ? 'locked' : 'default' );

        if ( ! $bypass_cache ) {
            $cached = get_transient( $cache_key );
            if ( false !== $cached && is_array( $cached ) && count( $cached ) >= $posts_per_page ) {
                return array_slice( $cached, 0, $posts_per_page );
            }
        }

        $preferred_topics = (array) get_user_meta( $user_id, 'preferred_topics', true );
        $preferred_regions = (array) get_user_meta( $user_id, 'preferred_regions', true );

        $post_types = array_values( (array) $post_types );

        // Fetch candidate posts from database
        $pool_size = max( 40, $posts_per_page * 4 );
        $args = array(
            'post_type'      => $post_types,
            'post_status'    => 'publish',
            'posts_per_page' => $pool_size,
            'orderby'        => 'date',
            'order'          => 'DESC',
        );

        if ( isset( $extra_args['date_query'] ) ) {
            $args['date_query'] = $extra_args['date_query'];
        }

        $query = new \WP_Query( $args );
        $candidates = $query->posts;

        // Fallback: Ensure candidate pool is non-empty
        if ( empty( $candidates ) ) {
            $fallback_args = array(
                'post_type'      => array( 'brief', 'update', 'dossier' ),
                'post_status'    => 'publish',
                'posts_per_page' => $posts_per_page * 2,
                'orderby'        => 'date',
                'order'          => 'DESC',
            );
            $fallback_query = new \WP_Query( $fallback_args );
            $candidates = $fallback_query->posts;
        }

        // Bulk Pre-warm Postmeta & Term Caches to Eliminate N+1 Database Queries
        if ( ! empty( $candidates ) ) {
            $candidate_ids = wp_list_pluck( $candidates, 'ID' );
            update_postmeta_cache( $candidate_ids );
            update_object_term_cache( $candidate_ids, array( 'topic', 'region', 'entity_type' ) );
        }

        $reading_affinities = $this->get_user_reading_affinities( $user_id );
        $completed_ids = $reading_affinities['completed'];
        $saved_ids = (array) get_user_meta( $user_id, 'as_saved_posts', true );
        $saved_ids = array_values( array_filter( array_map( 'intval', $saved_ids ) ) );

        // Score and filter candidates
        $paywall = class_exists( 'Ascendance\Core\Paywall' ) ? Paywall::get_instance() : null;
        $ranked_list = array();
        foreach ( $candidates as $post ) {
            // Dual Gate Evaluation (Tier Authorization + Category Entitlement Authorization)
            $access_check = $paywall ? $paywall->check_access( $post->ID, $user_id ) : array( 'allowed' => true, 'reason' => 'allowed_tier' );
            $can_access   = ! empty( $access_check['allowed'] );
            $reason_code  = isset( $access_check['reason'] ) ? $access_check['reason'] : 'allowed_tier';

            $is_tier_locked     = ( 'denied_tier' === $reason_code || 'denied_not_logged_in' === $reason_code );
            $is_category_locked = ( 'denied_category' === $reason_code || 'denied_revoked_entitlement' === $reason_code || 'denied_expired_entitlement' === $reason_code );
            $is_locked          = ! $can_access;

            // Exclude inaccessible posts unless include_locked is explicitly requested
            if ( $is_locked && ! $include_locked ) {
                continue;
            }

            // Exclude completed items (unless in progress)
            if ( in_array( $post->ID, $completed_ids, true ) && ! isset( $reading_affinities['in_progress'][$post->ID] ) ) {
                continue;
            }

            $score_details = $this->calculate_post_score( $post->ID, $user_id, $preferred_topics, $preferred_regions );
            $score_details['is_locked']          = $is_locked;
            $score_details['is_tier_locked']     = $is_tier_locked;
            $score_details['is_category_locked'] = $is_category_locked;

            $req_cat_slug = '';
            $req_cat_name = '';
            if ( $is_category_locked ) {
                $paid_cats = $paywall ? $paywall->get_post_paid_categories( $post->ID ) : array();
                if ( ! empty( $paid_cats ) ) {
                    $req_cat_slug = $paid_cats[0]->slug;
                    $req_cat_name = $paid_cats[0]->name;
                }
            }

            $score_details['required_category_slug'] = $req_cat_slug;
            $score_details['required_category_name'] = $req_cat_name;

            // Regenerate reason with lock flags populated
            $reason_data = $this->generate_recommendation_reason( $post->ID, $score_details, $user_id );
            $score_details['reason']      = $reason_data['reason'];
            $score_details['reason_type'] = $reason_data['reason_type'];

            // Fetch primary terms for display
            $t_terms = wp_get_post_terms( $post->ID, 'topic', array( 'fields' => 'names' ) );
            $topic_name = ( ! is_wp_error( $t_terms ) && ! empty( $t_terms ) ) ? $t_terms[0] : ucfirst( get_post_type( $post ) );

            $r_terms = wp_get_post_terms( $post->ID, 'region', array( 'fields' => 'names' ) );
            $region_name = ( ! is_wp_error( $r_terms ) && ! empty( $r_terms ) ) ? $r_terms[0] : 'Central Africa';

            $required_tier = get_field( 'tier_access', $post->ID ) ?: 'essential';

            $ranked_list[] = array(
                'post'                   => $post,
                'post_id'                => $post->ID,
                'title'                  => get_the_title( $post ),
                'permalink'              => get_permalink( $post ),
                'post_type'              => get_post_type( $post ),
                'date_label'             => get_the_date( 'j M Y', $post ),
                'topic_name'             => $topic_name,
                'region_name'            => $region_name,
                'score_details'          => $score_details,
                'reason'                 => $score_details['reason'],
                'reason_type'            => $score_details['reason_type'],
                'is_locked'              => $is_locked,
                'is_tier_locked'         => $is_tier_locked,
                'is_category_locked'     => $is_category_locked,
                'access_reason'          => $reason_code,
                'required_category_slug' => $req_cat_slug,
                'required_category_name' => $req_cat_name,
                'required_tier'          => $required_tier,
                'is_saved'               => in_array( $post->ID, $saved_ids, true ),
                'progress'               => isset( $score_details['progress'] ) ? $score_details['progress'] : 0,
            );
        }

        // Sort candidates by total_score DESC, then post_date DESC
        usort( $ranked_list, function( $a, $b ) {
            if ( $a['score_details']['total_score'] === $b['score_details']['total_score'] ) {
                $time_a = strtotime( $a['post']->post_date );
                $time_b = strtotime( $b['post']->post_date );
                return $time_b - $time_a; // newer first
            }
            return $b['score_details']['total_score'] - $a['score_details']['total_score'];
        } );

        // Apply recommendation diversity filter
        $ranked_list = $this->apply_diversity_filter( $ranked_list, $posts_per_page );

        // Slice to requested limit
        $result = array_slice( $ranked_list, 0, $posts_per_page );

        // Cache results in transient (15 minute TTL)
        set_transient( $cache_key, $result, 15 * MINUTE_IN_SECONDS );

        return $result;
    }
}
