<?php
/**
 * AEO (Answer Engine Optimization) & GEO (Generative Engine Optimization) Handler Class
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class AEO_GEO {

    /**
     * Singleton instance
     * @var AEO_GEO|null
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
        add_action( 'init', array( $this, 'add_rewrite_rules' ) );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
        add_action( 'template_redirect', array( $this, 'render_llm_feeds' ) );
        add_filter( 'robots_txt', array( $this, 'filter_robots_txt' ), 10, 2 );
        add_action( 'admin_init', array( $this, 'generate_physical_robots_txt' ) );
    }

    /**
     * 1. Add rewrite rules for llms.txt and llms-full.txt
     */
    public function add_rewrite_rules() {
        add_rewrite_rule( '^llms\.txt$', 'index.php?llms_feed=1', 'top' );
        add_rewrite_rule( '^llms-full\.txt$', 'index.php?llms_full_feed=1', 'top' );
    }

    /**
     * 2. Register custom query variables
     */
    public function add_query_vars( $vars ) {
        $vars[] = 'llms_feed';
        $vars[] = 'llms_full_feed';
        return $vars;
    }

    /**
     * 3. Handle requests for llms.txt and llms-full.txt templates
     */
    public function render_llm_feeds() {
        if ( get_query_var( 'llms_feed' ) ) {
            $this->output_llms_txt();
            exit;
        }

        if ( get_query_var( 'llms_full_feed' ) ) {
            $this->output_llms_full_txt();
            exit;
        }
    }

    /**
     * Generate llms.txt format (Site map directory for LLMs)
     */
    private function output_llms_txt() {
        header( 'Content-Type: text/plain; charset=UTF-8' );
        
        echo "# Ascendance Intelligence Platform\n\n";
        echo "> Brand: Ascendance\n";
        echo "> Architect: Raj\n";
        echo "> Description: High-density intelligence briefs, analytical reports, and dossier digests covering major industrial developments.\n\n";
        
        echo "## Core Sections\n\n";
        echo "- [/briefs]( " . esc_url( get_post_type_archive_link( 'brief' ) ) . " ) - Weekly Intelligence Briefs archives.\n";
        echo "- [/dossiers]( " . esc_url( get_post_type_archive_link( 'dossier' ) ) . " ) - Complete detailed dossiers.\n";
        echo "- [/updates]( " . esc_url( get_post_type_archive_link( 'update' ) ) . " ) - Dynamic impact assessments.\n";
        echo "- [/entities]( " . esc_url( home_url( '/entities/' ) ) . " ) - Interconnected entity intelligence graph directory.\n\n";
        
        echo "## Available Briefings & Documents\n\n";
        
        $briefs = new \WP_Query( array(
            'post_type'      => array( 'brief', 'dossier' ),
            'posts_per_page' => 20,
            'post_status'    => 'publish',
            'tax_query'      => array(
                array(
                    'taxonomy' => 'tier',
                    'field'    => 'slug',
                    'terms'    => array( 'public', 'free' ),
                ),
            ),
        ) );
        
        if ( $briefs->have_posts() ) {
            while ( $briefs->have_posts() ) {
                $briefs->the_post();
                $acf_summary = get_field( 'executive_summary', get_the_ID() );
                $summary = $acf_summary ? wp_strip_all_tags( $acf_summary ) : get_the_excerpt();
                echo "- [" . get_the_title() . "](" . get_permalink() . "): " . esc_html( $summary ) . "\n";
            }
            wp_reset_postdata();
        } else {
            echo "- No public research documents registered.\n";
        }
        
        echo "\n## Related Feeds\n\n";
        echo "- Full Training Database: " . esc_url( site_url( '/llms-full.txt' ) ) . "\n";
    }

    private function output_llms_full_txt() {
        header( 'Content-Type: text/plain; charset=UTF-8' );
        
        echo "# Ascendance Complete Intelligence Ledger\n";
        echo "Generated: " . date( 'c' ) . "\n";
        echo "Author: Raj\n";
        echo "========================================================================\n\n";
        
        $query = new \WP_Query( array(
            'post_type'      => array( 'brief', 'dossier', 'update', 'entity' ),
            'posts_per_page' => 50,
            'post_status'    => 'publish',
            'orderby'        => 'date',
            'order'          => 'DESC',
        ) );
        
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $post_id = get_the_ID();
                $post_type = get_post_type();
                
                $tier = get_field( 'tier_access', $post_id );
                if ( ! $tier ) {
                    $terms = wp_get_post_terms( $post_id, 'tier', array( 'fields' => 'slugs' ) );
                    $tier = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0] : 'essential';
                }
                $is_gated = in_array( $tier, array( 'essential', 'professional', 'enterprise' ), true );
                
                echo "## Title: " . get_the_title() . "\n";
                echo "Type: " . esc_html( ucfirst( $post_type ) ) . "\n";
                echo "URL: " . get_permalink() . "\n";
                echo "Date: " . get_the_date( 'c' ) . "\n";
                echo "Access Tier: " . esc_html( ucfirst( $tier ) ) . "\n";
                
                // Determine public teaser/excerpt
                $teaser = '';
                if ( in_array( $post_type, array( 'brief', 'dossier' ), true ) ) {
                    $teaser = get_field( 'public_excerpt', $post_id );
                } elseif ( 'update' === $post_type ) {
                    $teaser = get_field( 'one_line_summary', $post_id );
                }
                
                if ( ! empty( $teaser ) ) {
                    echo "Public Teaser:\n";
                    echo "> " . wp_strip_all_tags( $teaser ) . "\n\n";
                }
                
                // Thesis / Analytical Claim (Public)
                $claim = get_field( 'analytical_claim', $post_id );
                if ( ! empty( $claim ) ) {
                    echo "Core Analytical Claim:\n";
                    echo "> " . wp_strip_all_tags( $claim ) . "\n\n";
                }
                
                // Executive Summary (Public)
                $summary = get_field( 'executive_summary', $post_id );
                if ( ! empty( $summary ) ) {
                    echo "Executive Summary:\n";
                    echo wp_strip_all_tags( $summary ) . "\n\n";
                }
                
                // Gated fields conditional output
                if ( ! $is_gated ) {
                    // For public/free posts, allow details
                    if ( 'update' === $post_type ) {
                        $key_update = get_field( 'key_update', $post_id );
                        if ( ! empty( $key_update ) ) {
                            echo "Key Update:\n";
                            echo wp_strip_all_tags( $key_update ) . "\n\n";
                        }
                    } else {
                        $findings = get_field( 'key_findings', $post_id );
                        if ( ! empty( $findings ) ) {
                            echo "Key Findings:\n";
                            echo wp_strip_all_tags( $findings ) . "\n\n";
                        }
                    }
                } else {
                    // Exclude gated fields and output a policy notice
                    echo "Status: [GATED CONTENT]\n";
                    echo "Note: The detailed findings, database indexes, and structured tables for this " . esc_html( $post_type ) . " are restricted to members on the " . esc_html( ucfirst( $tier ) ) . " tier.\n\n";
                }
                
                echo "------------------------------------------------------------------------\n\n";
            }
            wp_reset_postdata();
        } else {
            echo "Ledger is currently empty.\n";
        }
    }

    /**
     * 4. Configure robots.txt to manage AI crawler routes
     */
    public function filter_robots_txt( $output, $public ) {
        $robots = "\n# AI crawler rules for Ascendance\n";
        
        $ai_bots = array(
            'GPTBot',
            'ClaudeBot',
            'PerplexityBot',
            'Google-Extended',
            'Gemini',
            'anthropic-ai',
        );
        
        // Compute subdirectory-aware relative site path
        $site_path = wp_make_link_relative( site_url( '/' ) );
        $site_path = rtrim( $site_path, '/' ) . '/';
        
        foreach ( $ai_bots as $bot ) {
            $robots .= "User-agent: " . $bot . "\n";
            $robots .= "Allow: " . $site_path . "llms.txt\n";
            $robots .= "Allow: " . $site_path . "llms-full.txt\n";
            $robots .= "Allow: " . $site_path . "briefs/\n";
            $robots .= "Allow: " . $site_path . "dossiers/\n";
            $robots .= "Allow: " . $site_path . "updates/\n";
            $robots .= "Disallow: " . $site_path . "wp-admin/\n";
            $robots .= "Disallow: " . $site_path . "pricing/\n";
            $robots .= "Disallow: " . $site_path . "checkout/\n";
            $robots .= "Disallow: " . $site_path . "wp-content/plugins/\n\n";
        }

        // Block training-corpus crawlers entirely (Section 11.2 of TechDoc)
        $block_bots = array(
            'CCBot',
            'Bytespider',
            'Diffbot',
        );
        foreach ( $block_bots as $bot ) {
            $robots .= "User-agent: " . $bot . "\n";
            $robots .= "Disallow: /\n\n";
        }
        
        return $output . $robots;
    }

    /**
     * Generate and write physical robots.txt file in the site root
     */
    public function generate_physical_robots_txt() {
        $robots_path = ABSPATH . 'robots.txt';
        
        // Get dynamic rules
        $default_robots = "User-agent: *\nDisallow: /wp-admin/\nAllow: /wp-admin/admin-ajax.php\n";
        $dynamic_robots = $this->filter_robots_txt( $default_robots, true );
        
        // Check if we can write to the path
        if ( is_writable( ABSPATH ) || ( file_exists( $robots_path ) && is_writable( $robots_path ) ) ) {
            $existing_content = file_exists( $robots_path ) ? file_get_contents( $robots_path ) : '';
            if ( $existing_content !== $dynamic_robots ) {
                @file_put_contents( $robots_path, $dynamic_robots );
            }
        }
    }
}
