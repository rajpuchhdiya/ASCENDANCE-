<?php
/**
 * Custom Search Weighting, Native JSON-LD Schema & Newsletter Sync Class
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Search_SEO {

    /**
     * Singleton instance
     * @var Search_SEO|null
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
        // Custom Weighted Search logic
        add_filter( 'posts_clauses', array( $this, 'custom_weighted_search' ), 10, 2 );
        add_filter( 'posts_search', array( $this, 'custom_search_where' ), 10, 2 );

        // Restrict frontend search to intelligence post types
        add_action( 'pre_get_posts', array( $this, 'restrict_search_post_types' ) );

        // Native Head JSON-LD Schema injection (Yoast Free compatible)
        add_action( 'wp_head', array( $this, 'inject_custom_schema' ) );

        // PMPro Level Change -> Newsletter Sync (Brevo)
        add_action( 'pmpro_after_change_membership_level', array( $this, 'sync_subscriber_to_newsletter' ), 10, 3 );
    }

    /**
     * Expand search SQL WHERE clause to match Entity official_name and alternate_names postmeta
     */
    public function custom_search_where( $search, $query ) {
        if ( is_admin() || ! $query->is_search() ) {
            return $search;
        }
        global $wpdb;
        $search_term = $query->get( 's' );
        if ( empty( $search_term ) ) {
            return $search;
        }
        $term = esc_sql( $wpdb->esc_like( $search_term ) );

        if ( ! empty( $search ) ) {
            $search .= " OR EXISTS (
                SELECT 1 FROM {$wpdb->postmeta} pm_s
                WHERE pm_s.post_id = {$wpdb->posts}.ID
                AND pm_s.meta_key IN ('official_name', 'alternate_names')
                AND pm_s.meta_value LIKE '%{$term}%'
            )";
        }

        return $search;
    }

    /**
     * Restrict search results to Briefs, Dossiers, Updates, and Entities on the frontend
     */
    public function restrict_search_post_types( $query ) {
        if ( is_admin() || ! $query->is_search() || ! $query->is_main_query() ) {
            return $query;
        }
        $query->set( 'post_type', array( 'brief', 'dossier', 'update', 'entity' ) );
        return $query;
    }

    /**
     * 1. Custom database-level search weighting query filter
     */
    public function custom_weighted_search( $clauses, $query ) {
        if ( is_admin() || ! $query->is_search() ) {
            return $clauses;
        }

        global $wpdb;
        $search_term = $query->get( 's' );
        if ( empty( $search_term ) ) {
            return $clauses;
        }

        $term = esc_sql( $wpdb->esc_like( $search_term ) );
        
        // Weighting: Title = 10, Excerpt (Dek) = 7, Content = 3, Entity Meta (Official Name / Aliases) = 12
        $relevance = "
            (CASE
                WHEN {$wpdb->posts}.post_title LIKE '%{$term}%' THEN 10
                ELSE 0
            END) +
            (CASE
                WHEN {$wpdb->posts}.post_excerpt LIKE '%{$term}%' THEN 7
                ELSE 0
            END) +
            (CASE
                WHEN {$wpdb->posts}.post_content LIKE '%{$term}%' THEN 3
                ELSE 0
            END) +
            (CASE WHEN EXISTS (
                SELECT 1 FROM {$wpdb->postmeta} pm_meta
                WHERE pm_meta.post_id = {$wpdb->posts}.ID
                AND pm_meta.meta_key IN ('official_name', 'alternate_names')
                AND pm_meta.meta_value LIKE '%{$term}%'
            ) THEN 12 ELSE 0 END)
        ";

        if ( ! isset( $clauses['fields'] ) ) {
            $clauses['fields'] = '';
        }
        $clauses['fields'] .= ", ($relevance) AS search_relevance";
        $clauses['orderby'] = "search_relevance DESC, {$wpdb->posts}.post_date DESC";

        return $clauses;
    }

    /**
     * 2. Inject structured JSON-LD data for Google Schema / E-E-A-T credentials
     */
    public function inject_custom_schema( $target_post_id = 0 ) {
        $curr_post = $target_post_id ? get_post( $target_post_id ) : get_post();
        if ( ! $curr_post ) {
            return;
        }

        if ( 'entity' === $curr_post->post_type || is_singular( 'entity' ) ) {
            $post_id   = $curr_post->ID;
            $terms     = wp_get_post_terms( $post_id, 'entity_type', array( 'fields' => 'slugs' ) );
            $type_slug = ( ! is_wp_error( $terms ) && ! empty( $terms ) ) ? $terms[0] : 'company';

            $schema_type = 'Organization';
            if ( 'person' === $type_slug ) {
                $schema_type = 'Person';
            } elseif ( in_array( $type_slug, array( 'mining-project', 'infrastructure-project', 'strategic-initiative' ), true ) ) {
                $schema_type = 'Project';
            }

            $aliases_str = get_post_meta( $post_id, 'alternate_names', true );
            $aliases_arr = ! empty( $aliases_str ) ? array_filter( array_map( 'trim', explode( "\n", $aliases_str ) ) ) : array();

            $entity_schema = array(
                '@context'      => 'https://schema.org',
                '@type'         => $schema_type,
                'name'          => get_the_title( $post_id ),
                'legalName'     => get_post_meta( $post_id, 'official_name', true ) ?: get_the_title( $post_id ),
                'description'   => get_post_meta( $post_id, 'short_description', true ) ?: wp_strip_all_tags( get_the_excerpt( $post_id ) ),
                'url'           => get_post_meta( $post_id, 'website', true ) ?: get_permalink( $post_id ),
            );
            if ( ! empty( $aliases_arr ) ) {
                $entity_schema['alternateName'] = array_values( $aliases_arr );
            }

            echo "\n" . '<!-- Ascendance Custom Entity JSON-LD Schema -->' . "\n";
            echo '<script type="application/ld+json">' . wp_json_encode( $entity_schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
            return;
        }

        if ( ! in_array( $curr_post->post_type, array( 'brief', 'dossier' ), true ) && ! is_singular( array( 'brief', 'dossier' ) ) ) {
            return;
        }

        $post_id = $curr_post->ID;
        $author_name = get_the_author_meta( 'display_name' );
        $pub_date = get_the_date( 'c' );
        $mod_date = get_the_modified_date( 'c' );
        
        $excerpt = get_field( 'public_excerpt', $post_id );
        if ( empty( $excerpt ) ) {
            $excerpt = wp_strip_all_tags( get_the_excerpt() );
        }

        $post_type = get_post_type();
        $schema_type = 'brief' === $post_type ? 'NewsArticle' : 'Report';

        $schema = array(
            '@context'         => 'https://schema.org',
            '@type'            => $schema_type,
            'headline'         => get_the_title(),
            'datePublished'    => $pub_date,
            'dateModified'     => $mod_date,
            'author'           => array(
                '@type'    => 'Person',
                'name'     => $author_name,
                // Dynamic: reads WP Biographical Info for each author, then falls back to the
                // global default set in Mission Control → Settings → Platform Settings.
                'jobTitle' => $this->get_author_job_title( get_the_author_meta( 'ID' ) ),
            ),
            'publisher'        => array(
                '@type' => 'Organization',
                'name'  => get_bloginfo( 'name' ),
                'logo'  => array(
                    '@type' => 'ImageObject',
                    // Dynamic: uses WP Custom Logo first, then the URL set in Platform Settings.
                    'url'   => $this->get_seo_logo_url(),
                )
            ),
            'description'         => $excerpt,
            'isAccessibleForFree' => 'False',
            'hasPart'             => array(
                '@type'               => 'WebPageElement',
                'isAccessibleForFree' => 'False',
                'cssSelector'         => '.paywall-gated-content'
            )
        );

        echo "\n" . '<!-- Ascendance Custom JSON-LD Paywall Schema -->' . "\n";
        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";

        // Inject FAQ Schema if questions are found in the content
        $faq_items = $this->get_faq_items( $post_id );
        if ( ! empty( $faq_items ) ) {
            $faq_schema = array(
                '@context'   => 'https://schema.org',
                '@type'      => 'FAQPage',
                'mainEntity' => array(),
            );
            
            foreach ( $faq_items as $item ) {
                $faq_schema['mainEntity'][] = array(
                    '@type'          => 'Question',
                    'name'           => $item['question'],
                    'acceptedAnswer' => array(
                        '@type' => 'Answer',
                        'text'  => $item['answer'],
                    ),
                );
            }
            
            echo "\n" . '<!-- Ascendance Dynamic FAQ Schema -->' . "\n";
            echo '<script type="application/ld+json">' . wp_json_encode( $faq_schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
        }
    }

    /**
     * Parse post content for H2 headings ending with "?" and extract the following paragraph as the answer.
     *
     * @param int $post_id The post ID.
     * @return array Array of FAQ items with question and answer.
     */
    private function get_faq_items( $post_id ) {
        $post = get_post( $post_id );
        if ( ! $post ) {
            return array();
        }

        $content = $post->post_content;
        $faq_items = array();

        // Match h2 questions and their following paragraph answers (allowing Gutenberg comments in-between)
        if ( preg_match_all( '/<h2[^>]*>(.*?\?)<\/h2>(?:\s*|<!--.*?-->)*?(<p[^>]*>.*?<\/p>)/is', $content, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                $question = wp_strip_all_tags( $match[1] );
                $answer = wp_strip_all_tags( $match[2] );

                if ( ! empty( $question ) && ! empty( $answer ) ) {
                    $faq_items[] = array(
                        'question' => trim( $question ),
                        'answer'   => trim( $answer ),
                    );
                }
            }
        }

        return $faq_items;
    }

    /**
     * 3. Sync PMPro subscriber status to Brevo Lists
     */
    public function sync_subscriber_to_newsletter( $level_id, $user_id, $old_levels ) {
        $user_data = get_userdata( $user_id );
        if ( ! $user_data ) {
            return;
        }

        $email = $user_data->user_email;
        $first_name = $user_data->first_name;
        $last_name = $user_data->last_name;

        // Fetch new level name
        $level_name = 'Inactive';
        if ( $level_id ) {
            $level = pmpro_getLevel( $level_id );
            if ( $level ) {
                $level_name = $level->name;
            }
        }

        $api_key = getenv( 'ASCENDANCE_NEWSLETTER_API_KEY' );
        $list_id = getenv( 'ASCENDANCE_NEWSLETTER_LIST_ID' );

        if ( empty( $api_key ) ) {
            error_log( sprintf( "Subscriber sync requested: User ID %d ($email) registered level %s", $user_id, $level_name ) );
            return;
        }

        // Setup call parameters
        $body = array(
            'email'      => $email,
            'attributes' => array(
                'FIRSTNAME'   => $first_name,
                'LASTNAME'    => $last_name,
                'MEMBER_TIER' => $level_name,
                'STATUS'      => $level_id ? 'Active' : 'Cancelled',
            ),
            'updateEnabled' => true,
        );

        // Execute background remote post request
        wp_remote_post( 'https://api.brevo.com/v3/contacts', array(
            'headers' => array(
                'api-key'      => $api_key,
                'content-type' => 'application/json',
            ),
            'body'    => wp_json_encode( $body ),
            'timeout' => 5,
        ) );
    }

    /**
     * Returns the SEO logo URL.
     * Priority: WP Custom Logo → ascendance_seo_logo_url option → empty string.
     *
     * @return string
     */
    private function get_seo_logo_url(): string {
        // 1. Try WordPress native Custom Logo (set via Appearance → Customize → Site Identity)
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        if ( $custom_logo_id ) {
            $logo_data = wp_get_attachment_image_src( $custom_logo_id, 'full' );
            if ( ! empty( $logo_data[0] ) ) {
                return esc_url( $logo_data[0] );
            }
        }

        // 2. Fall back to URL saved in Mission Control → Settings → Platform Settings
        $saved_url = get_option( 'ascendance_seo_logo_url', '' );
        if ( ! empty( $saved_url ) ) {
            return esc_url( $saved_url );
        }

        return '';
    }

    /**
     * Returns the job title for a given author.
     * Priority: WP user "description" (Biographical Info) → ascendance_author_job_title option → 'Intelligence Analyst'.
     *
     * @param int $author_id WP user ID.
     * @return string
     */
    private function get_author_job_title( $author_id ): string {
        $author_id = (int) $author_id;
        if ( ! empty( $author_id ) ) {
            // 1. Per-author: WP Admin → Users → Edit → Biographical Info (first line used as title)
            $bio = get_user_meta( $author_id, 'description', true );
            if ( ! empty( $bio ) ) {
                $first_line = strtok( strip_tags( $bio ), "\n" );
                if ( strlen( $first_line ) <= 80 ) {
                    return sanitize_text_field( $first_line );
                }
            }
        }

        // 2. Global fallback: Mission Control → Settings → Platform Settings
        return sanitize_text_field(
            get_option( 'ascendance_author_job_title', 'Intelligence Analyst' )
        );
    }
}
