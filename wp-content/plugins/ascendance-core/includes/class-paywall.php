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
        // Only run on single CPT posts: brief, dossier, update
        if ( ! is_singular( array( 'brief', 'dossier', 'update' ) ) || ! in_the_loop() || ! is_main_query() ) {
            return $content;
        }

        // 1. Check if user has permission
        if ( $this->user_has_access() ) {
            return $content;
        }

        // 2. Truncate content: keep the first paragraph intact with formatting
        $first_para = '';
        if ( preg_match( '/<p[^>]*>.*?<\/p>/s', $content, $matches ) ) {
            $first_para = $matches[0];
        } else {
            $first_para = '<p>' . wp_trim_words( wp_strip_all_tags( $content ), 60, '...' ) . '</p>';
        }

        // 3. Append the dynamic paywall block
        $paywall_cta = $this->render_paywall_cta();

        return $first_para . $paywall_cta;
    }

    /**
     * Check if current user has access to this post based on PMPro levels
     *
     * @return bool True if user has access, false otherwise.
     */
    public function user_has_access() {
        // Admins and editors bypass paywall
        if ( current_user_can( 'edit_post', get_the_ID() ) ) {
            return true;
        }

        // SEO and AI Bot search crawlers bypass paywall for indexability (Preview Mode)
        if ( $this->is_search_crawler() ) {
            return true;
        }

        // Fetch required access tier from ACF metadata
        $required_tier = get_field( 'tier_access', get_the_ID() );
        if ( ! $required_tier ) {
            // Default fallback if ACF field isn't set yet (check CPT defaults)
            $post_type = get_post_type();
            if ( 'dossier' === $post_type ) {
                $required_tier = 'professional';
            } elseif ( 'update' === $post_type ) {
                $required_tier = 'essential';
            } else {
                $required_tier = 'essential';
            }
        }

        // Map tiers to tier hierarchy scores
        $tier_hierarchy = array(
            'free'         => 0,
            'essential'    => 1,
            'professional' => 2,
            'enterprise'   => 3,
        );

        $required_score = isset( $tier_hierarchy[ $required_tier ] ) ? $tier_hierarchy[ $required_tier ] : 1;

        // Get current user's PMPro membership level score
        $user_score = 0;
        if ( function_exists( 'pmpro_get_membership_level_for_user' ) ) {
            $user_id = get_current_user_id();
            $user_level = pmpro_get_membership_level_for_user( $user_id );
            
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
            // Logged-in subscribers default to "essential", admins default to full access
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
     * @return string HTML rendering of the paywall banner.
     */
    public function render_paywall_cta() {
        $required_tier = get_field( 'tier_access', get_the_ID() );
        if ( ! $required_tier ) {
            $required_tier = 'essential';
        }

        $tier_name = ucfirst( $required_tier );
        $pricing_url = site_url( '/pricing' );
        $login_url = wp_login_url( get_permalink() );

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
                        <a href="<?php echo esc_url( $pricing_url ); ?>" class="btn btn-primary"><?php esc_html_e( 'View Membership Plans', 'ascendance-core' ); ?></a>
                        <a href="<?php echo esc_url( $login_url ); ?>" class="btn btn-secondary"><?php esc_html_e( 'Subscriber Sign In', 'ascendance-core' ); ?></a>
                    <?php else : ?>
                        <a href="<?php echo esc_url( $pricing_url ); ?>" class="btn btn-primary"><?php esc_html_e( 'Upgrade to Unlock', 'ascendance-core' ); ?></a>
                        <span style="display: block; width: 100%; color: var(--text-muted); font-size: var(--font-size-xs); margin-top: var(--space-10);">
                            <?php 
                            $current_level = function_exists( 'pmpro_get_membership_level_for_user' ) ? pmpro_get_membership_level_for_user( get_current_user_id() ) : null;
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
}
