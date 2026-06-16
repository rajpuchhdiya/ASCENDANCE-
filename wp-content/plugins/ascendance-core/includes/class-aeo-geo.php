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
        echo "- [/updates]( " . esc_url( get_post_type_archive_link( 'update' ) ) . " ) - Dynamic impact assessments.\n\n";
        
        echo "## Available Briefings & Documents\n\n";
        
        $briefs = new \WP_Query( array(
            'post_type'      => array( 'brief', 'dossier' ),
            'posts_per_page' => 20,
            'post_status'    => 'publish',
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

    /**
     * Generate llms-full.txt format (Complete training ledger text)
     */
    private function output_llms_full_txt() {
        header( 'Content-Type: text/plain; charset=UTF-8' );
        
        echo "# Ascendance Complete Intelligence Ledger\n";
        echo "Generated: " . date( 'c' ) . "\n";
        echo "Author: Raj\n";
        echo "========================================================================\n\n";
        
        $briefs = new \WP_Query( array(
            'post_type'      => 'brief',
            'posts_per_page' => 50,
            'post_status'    => 'publish',
        ) );
        
        if ( $briefs->have_posts() ) {
            while ( $briefs->have_posts() ) {
                $briefs->the_post();
                
                $claim = get_field( 'analytical_claim', get_the_ID() );
                $summary = get_field( 'executive_summary', get_the_ID() );
                $findings = get_field( 'key_findings', get_the_ID() );
                
                echo "## Title: " . get_the_title() . "\n";
                echo "URL: " . get_permalink() . "\n";
                echo "Date: " . get_the_date( 'c' ) . "\n";
                
                if ( ! empty( $claim ) ) {
                    echo "Core Analytical Claim:\n";
                    echo "> " . wp_strip_all_tags( $claim ) . "\n\n";
                }
                
                if ( ! empty( $summary ) ) {
                    echo "Executive Summary:\n";
                    echo wp_strip_all_tags( $summary ) . "\n\n";
                }
                
                if ( ! empty( $findings ) ) {
                    echo "Key Findings:\n";
                    echo wp_strip_all_tags( $findings ) . "\n\n";
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
        );
        
        foreach ( $ai_bots as $bot ) {
            $robots .= "User-agent: " . $bot . "\n";
            $robots .= "Allow: /llms.txt\n";
            $robots .= "Allow: /llms-full.txt\n";
            $robots .= "Allow: /briefs/\n";
            $robots .= "Allow: /dossiers/\n";
            $robots .= "Disallow: /wp-admin/\n";
            $robots .= "Disallow: /pricing/\n";
            $robots .= "Disallow: /checkout/\n";
            $robots .= "Disallow: /wp-content/plugins/\n\n";
        }
        
        return $output . $robots;
    }
}
