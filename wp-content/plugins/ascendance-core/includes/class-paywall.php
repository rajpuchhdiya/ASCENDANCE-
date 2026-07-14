<?php
/**
 * Custom Subscription Paywall Engine Class
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Paywall {

    /**
     * Singleton instance
     * @var Paywall|null
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
        add_filter( 'the_content', array( $this, 'enforce_paywall' ), 99 );
        add_action( 'init', array( $this, 'register_paywall_cta_block' ) );

        // Gate REST API responses for briefs, dossiers, and updates
        add_filter( 'rest_prepare_brief', array( $this, 'gate_rest_response' ), 10, 3 );
        add_filter( 'rest_prepare_update', array( $this, 'gate_rest_response' ), 10, 3 );
        add_filter( 'rest_prepare_dossier', array( $this, 'gate_rest_response' ), 10, 3 );
    }

    /**
     * Register Gutenberg block mockup or shortcode for the Paywall CTA
     */
    public function register_paywall_cta_block() {
        // Register shortcode fallback
        add_shortcode( 'ascendance_paywall_cta', array( $this, 'render_paywall_cta' ) );
    }

    /**
     * Enforce paywall restriction logic on post content
     *
     * @param string $content The post content.
     * @return string Truncated content with paywall CTA if restricted, otherwise full content.
     */
    public function enforce_paywall( $content ) {
        // Check if singular post page or RSS feed
        $is_feed = is_feed();
        $is_singular = is_singular( array( 'brief', 'dossier', 'update' ) ) && in_the_loop() && is_main_query();

        if ( ! $is_singular && ! $is_feed ) {
            return $content;
        }

        // For feed, ensure it's one of our post types
        if ( $is_feed ) {
            $post_type = get_post_type();
            if ( ! in_array( $post_type, array( 'brief', 'dossier', 'update' ), true ) ) {
                return $content;
            }
        }

        $post_id = get_the_ID();
        $post_type = get_post_type( $post_id );

        // 1. Determine curated teaser content: try author-curated ACF teaser fields first
        $teaser = '';
        $has_curated = false;

        if ( in_array( $post_type, array( 'brief', 'dossier' ), true ) ) {
            $acf_excerpt = get_field( 'public_excerpt', $post_id );
            if ( ! empty( $acf_excerpt ) ) {
                $teaser = '<div class="aeo-lead-citation-block" itemprop="description"><p class="aeo-lead-citation">' . esc_html( $acf_excerpt ) . '</p></div>';
                $has_curated = true;
            }
        } elseif ( 'update' === $post_type ) {
            $acf_summary = get_field( 'one_line_summary', $post_id );
            if ( ! empty( $acf_summary ) ) {
                $teaser = '<div class="aeo-lead-citation-block update-teaser" itemprop="description"><p class="aeo-lead-citation">' . esc_html( $acf_summary ) . '</p></div>';
                $has_curated = true;
            }
        }

        // Fallback to first paragraph of content if no curated teaser was found (only for guests/non-access)
        if ( empty( $teaser ) ) {
            if ( preg_match( '/<p[^>]*>.*?<\/p>/s', $content, $matches ) ) {
                $teaser = '<div class="aeo-lead-citation-block" itemprop="description"><div class="aeo-lead-citation">' . $matches[0] . '</div></div>';
            } else {
                $teaser = '<div class="aeo-lead-citation-block" itemprop="description"><p class="aeo-lead-citation">' . wp_trim_words( wp_strip_all_tags( $content ), 60, '...' ) . '</p></div>';
            }
        }

        // 2. Check if user has permission
        if ( $this->user_has_access( $post_id ) ) {
            // For authorized users, prepend the curated teaser/citable lead if it exists
            if ( $has_curated ) {
                return $teaser . $content;
            }
            return $content;
        }

        // 3. For feeds, return only the teaser with a short plain text paywall note
        if ( $is_feed ) {
            $pricing_url = site_url( '/pricing' );
            $feed_paywall_note = sprintf(
                "\n\n<p><strong>[%s]</strong> %s <a href='%s'>%s</a></p>",
                esc_html__( 'Gated Content', 'ascendance-core' ),
                esc_html__( 'This analytical report requires an active subscription to read.', 'ascendance-core' ),
                esc_url( $pricing_url ),
                esc_html__( 'View Membership Plans', 'ascendance-core' )
            );
            return $teaser . $feed_paywall_note;
        }

        // 4. Append the dynamic paywall block
        $paywall_cta = $this->render_paywall_cta( $post_id );

        return $teaser . $paywall_cta;
    }

    /**
     * Check if current user has access to this post based on PMPro levels
     *
     * @param int|null $post_id Optional post ID to check.
     * @return bool True if user has access, false otherwise.
     */
    public function user_has_access( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        if ( ! $post_id ) {
            return false;
        }

        // Admins and editors bypass paywall
        if ( current_user_can( 'edit_post', $post_id ) ) {
            return true;
        }

        // SEO and AI Bot search crawlers bypass paywall for indexability (Preview Mode)
        if ( $this->is_search_crawler() ) {
            return true;
        }

        // Fetch required access tier from ACF metadata
        $required_tier = get_field( 'tier_access', $post_id );
        if ( ! $required_tier ) {
            // Fallback to check taxonomy 'tier'
            $terms = wp_get_post_terms( $post_id, 'tier', array( 'fields' => 'slugs' ) );
            if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                $required_tier = $terms[0];
            } else {
                // Default fallback if ACF field and taxonomy aren't set yet (check CPT defaults)
                $post_type = get_post_type( $post_id );
                if ( 'dossier' === $post_type ) {
                    $required_tier = 'professional';
                } elseif ( 'update' === $post_type ) {
                    $required_tier = 'essential';
                } else {
                    $required_tier = 'essential';
                }
            }
        }

        // Map tiers to tier hierarchy scores
        $tier_hierarchy = array(
            'public'       => 0,
            'free'         => 0,
            'essential'    => 1,
            'professional' => 2,
            'enterprise'   => 3,
        );

        $required_score = isset( $tier_hierarchy[ $required_tier ] ) ? $tier_hierarchy[ $required_tier ] : 1;

        // Get current user's PMPro membership level score
        $user_score = 0;
        if ( function_exists( 'pmpro_getMembershipLevelForUser' ) ) {
            $user_id = get_current_user_id();
            $user_level = pmpro_getMembershipLevelForUser( $user_id );
            
            if ( ! empty( $user_level ) ) {
                $level_slug = strtolower( $user_level->name ); // e.g., "Essential", "Professional", "Enterprise"
                
                if ( strpos( $level_slug, 'enterprise' ) !== false ) {
                    $user_score = 3;
                } elseif ( strpos( $level_slug, 'professional' ) !== false ) {
                    $user_score = 2;
                } elseif ( strpos( $level_slug, 'essential' ) !== false ) {
                    $user_score = 1;
                }
            }
        } else {
            // Fallback if Paid Memberships Pro is not active
            if ( is_user_logged_in() ) {
                if ( current_user_can( 'administrator' ) ) {
                    $user_score = 3;
                } else {
                    $user_score = 1; // Default subscribers to Essential tier
                }
            } else {
                $user_score = 0; // Public guests
            }
        }

        return $user_score >= $required_score;
    }

    /**
     * Check if current user-agent is a verified/known search crawler
     *
     * @return bool
     */
    private function is_search_crawler() {
        if ( empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
            return false;
        }

        $user_agent = strtolower( $_SERVER['HTTP_USER_AGENT'] );
        $crawlers = array(
            'googlebot',
            'bingbot',
            'slurp',
            'duckduckbot',
            'baiduspider',
            'yandexbot',
            // AI Search engine crawlers
            'gptbot',
            'claudebot',
            'perplexitybot',
            'gemini',
        );

        foreach ( $crawlers as $crawler ) {
            if ( strpos( $user_agent, $crawler ) !== false ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render HTML block for Paywall CTA
     *
     * @param int|null $post_id Optional post ID to check.
     * @return string HTML rendering of the paywall banner.
     */
    public function render_paywall_cta( $post_id = null ) {
        if ( ! $post_id ) {
            $post_id = get_the_ID();
        }
        $required_tier = get_field( 'tier_access', $post_id );
        if ( ! $required_tier ) {
            $terms = wp_get_post_terms( $post_id, 'tier', array( 'fields' => 'slugs' ) );
            $required_tier = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0] : 'essential';
        }

        $tier_name = ucfirst( $required_tier );
        $pricing_url = function_exists( 'pmpro_url' ) ? pmpro_url( 'levels' ) : home_url( '/membership-levels/' );
        $login_url = wp_login_url( get_permalink( $post_id ) );

        ob_start();
        ?>
        <div class="paywall-container">
            <div class="paywall-fade"></div>
            <div class="paywall-cta-box">
                <span class="paywall-badge"><?php echo esc_html( $tier_name ); ?> <?php esc_html_e( 'Membership Required', 'ascendance-core' ); ?></span>
                <h3 style="color: var(--color-white); margin-bottom: var(--space-10); font-family: var(--font-heading);"><?php esc_html_e( 'Intelligence Protected', 'ascendance-core' ); ?></h3>
                <p style="color: var(--text-secondary); margin-bottom: var(--space-30); font-size: var(--font-size-sm); max-width: 500px; margin-left: auto; margin-right: auto;">
                    <?php 
                    printf( 
                        esc_html__( 'This analytical report, compiled by our leading intelligence desk, is restricted to members subscribed to the %s tier. Upgrade your account to unlock full document analysis.', 'ascendance-core' ),
                        '<strong>' . esc_html( $tier_name ) . '</strong>'
                    ); 
                    ?>
                </p>
                
                <div style="display: flex; justify-content: center; gap: var(--space-20); flex-wrap: wrap;">
                    <?php if ( ! is_user_logged_in() ) : ?>
                        <a href="<?php echo esc_url( $pricing_url ); ?>" class="btn btn-primary paywall-subscribe-cta"><?php esc_html_e( 'View Membership Plans', 'ascendance-core' ); ?></a>
                        <a href="<?php echo esc_url( $login_url ); ?>" class="btn btn-secondary"><?php esc_html_e( 'Subscriber Sign In', 'ascendance-core' ); ?></a>
                    <?php else : ?>
                        <a href="<?php echo esc_url( $pricing_url ); ?>" class="btn btn-primary paywall-subscribe-cta"><?php esc_html_e( 'Upgrade to Unlock', 'ascendance-core' ); ?></a>
                        <span style="display: block; width: 100%; color: var(--text-muted); font-size: var(--font-size-xs); margin-top: var(--space-10);">
                            <?php 
                            $current_level = function_exists( 'pmpro_getMembershipLevelForUser' ) ? pmpro_getMembershipLevelForUser( get_current_user_id() ) : null;
                            if ( $current_level ) {
                                printf( esc_html__( 'Current membership level: %s', 'ascendance-core' ), '<strong>' . esc_html( $current_level->name ) . '</strong>' );
                            } else {
                                esc_html_e( 'You do not have an active membership tier.', 'ascendance-core' );
                            }
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Gate REST API response content and fields for unauthorized users
     *
     * @param WP_REST_Response $response The response object.
     * @param WP_Post $post The post object.
     * @param WP_REST_Request $request The request object.
     * @return WP_REST_Response The modified response object.
     */
    public function gate_rest_response( $response, $post, $request ) {
        // Bypass gating in the block editor / context=edit requests
        if ( isset( $request['context'] ) && 'edit' === $request['context'] ) {
            return $response;
        }

        // Bypass gating if the request originates from the WordPress admin dashboard (e.g. Gutenberg editor iframe or metaboxes)
        if ( ! empty( $_SERVER['HTTP_REFERER'] ) && strpos( $_SERVER['HTTP_REFERER'], '/wp-admin/' ) !== false ) {
            return $response;
        }

        // Bypass if user has access to this post
        if ( $this->user_has_access( $post->ID ) ) {
            return $response;
        }

        // User does not have access. Truncate/sanitize response data.
        $data = $response->get_data();

        // 1. Teaser calculation
        $teaser = '';
        $post_type = $post->post_type;

        if ( in_array( $post_type, array( 'brief', 'dossier' ), true ) ) {
            $acf_excerpt = get_field( 'public_excerpt', $post->ID );
            if ( ! empty( $acf_excerpt ) ) {
                $teaser = '<p class="teaser-text">' . esc_html( $acf_excerpt ) . '</p>';
            }
        } elseif ( 'update' === $post_type ) {
            $acf_summary = get_field( 'one_line_summary', $post->ID );
            if ( ! empty( $acf_summary ) ) {
                $teaser = '<p class="teaser-text update-teaser">' . esc_html( $acf_summary ) . '</p>';
            }
        }

        if ( empty( $teaser ) ) {
            $raw_content = isset( $data['content']['raw'] ) ? $data['content']['raw'] : ( isset( $data['content']['rendered'] ) ? $data['content']['rendered'] : '' );
            if ( preg_match( '/<p[^>]*>.*?<\/p>/s', $raw_content, $matches ) ) {
                $teaser = $matches[0];
            } else {
                $teaser = '<p>' . wp_trim_words( wp_strip_all_tags( $raw_content ), 60, '...' ) . '</p>';
            }
        }

        $paywall_cta = $this->render_paywall_cta( $post->ID );

        // Truncate the rendered content field
        if ( isset( $data['content']['rendered'] ) ) {
            $data['content']['rendered'] = $teaser . $paywall_cta;
        }
        if ( isset( $data['content']['raw'] ) ) {
            $data['content']['raw'] = wp_strip_all_tags( $teaser ) . "\n\n[Gated Content]";
        }

        // Clear out any sensitive CPT metadata or ACF fields that shouldn't leak
        if ( isset( $data['acf'] ) ) {
            // Keep public fields, clear gated ones
            $public_acf = array( 'public_excerpt', 'one_line_summary', 'tier_access' );
            foreach ( $data['acf'] as $key => $val ) {
                if ( ! in_array( $key, $public_acf, true ) ) {
                    unset( $data['acf'][ $key ] );
                }
            }
        }

        $response->set_data( $data );
        return $response;
    }
}
