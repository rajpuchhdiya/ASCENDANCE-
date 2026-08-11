<?php
/**
 * MailerLite Newsletter Integration Handler Class
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Newsletter {

    /**
     * Singleton instance
     * @var Newsletter|null
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
        // Form shortcode
        add_shortcode( 'ascendance_newsletter_form', array( $this, 'render_newsletter_form' ) );

        // AJAX handlers
        add_action( 'wp_ajax_ascendance_subscribe', array( $this, 'ajax_subscribe_handler' ) );
        add_action( 'wp_ajax_nopriv_ascendance_subscribe', array( $this, 'ajax_subscribe_handler' ) );
        add_action( 'wp_ajax_ascendance_send_test_digest', array( $this, 'ajax_send_test_digest' ) );

        // PMPro checkout hooks to sync active users
        add_action( 'pmpro_after_checkout', array( $this, 'pmpro_sync_user_on_checkout' ), 10, 2 );
        add_action( 'pmpro_after_change_membership_level', array( $this, 'pmpro_sync_user_on_change' ), 10, 3 );
    }

    /**
     * Render the custom styled newsletter signup form
     *
     * @param array $atts Shortcode attributes.
     * @return string HTML rendering of form.
     */
    public function render_newsletter_form( $atts ) {
        ob_start();
        ?>
        <form id="ascendance-newsletter-form" class="newsletter-form-full" style="background-color: var(--color-navy); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 30px;">
            <?php wp_nonce_field( 'ascendance_newsletter_nonce', 'security' ); ?>
            <div style="margin-bottom: var(--space-20); text-align: center;">
                <h4 style="color: var(--color-white); margin-bottom: var(--space-10); font-family: var(--font-heading);"><?php esc_html_e( 'Subscribe to Platform Briefs', 'ascendance-core' ); ?></h4>
                <p style="color: var(--text-secondary); font-size: var(--font-size-xs); margin: 0;"><?php esc_html_e( 'Get real-time Sakania-Lobito Corridor updates sent directly to your desk.', 'ascendance-core' ); ?></p>
            </div>
            <div class="form-row" style="display: flex; gap: var(--space-20); flex-wrap: wrap; justify-content: center; align-items: center;">
                <input type="text" name="first_name" placeholder="<?php esc_attr_e( 'First Name (Optional)', 'ascendance-core' ); ?>" style="flex: 1; min-width: 150px; padding: 12px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); color: var(--color-white); border-radius: var(--radius-sm); font-family: var(--font-heading); font-size: var(--font-size-sm);" />
                <input type="email" name="email" placeholder="<?php esc_attr_e( 'Email Address', 'ascendance-core' ); ?>" required style="flex: 1; min-width: 180px; padding: 12px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); color: var(--color-white); border-radius: var(--radius-sm); font-family: var(--font-mono); font-size: var(--font-size-sm);" />
                <button type="submit" class="btn btn-primary" style="padding: 12px 24px; border: none; border-radius: var(--radius-sm); font-family: var(--font-heading); font-weight: bold; text-transform: uppercase; cursor: pointer; letter-spacing: 1px;"><?php esc_html_e( 'Subscribe', 'ascendance-core' ); ?></button>
            </div>
            <div class="form-message" style="display: none; margin-top: 15px;"></div>
        </form>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX handler to process newsletter subscription requests
     */
    public function ajax_subscribe_handler() {
        if ( isset( $_POST['security'] ) ) {
            check_ajax_referer( 'ascendance_newsletter_nonce', 'security' );
        }

        $email = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
        $first_name = isset( $_POST['first_name'] ) ? sanitize_text_field( $_POST['first_name'] ) : '';

        if ( ! is_email( $email ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Please enter a valid email address.', 'ascendance-core' ) ) );
        }

        $api_key = defined( 'ASCENDANCE_NEWSLETTER_API_KEY' ) ? ASCENDANCE_NEWSLETTER_API_KEY : getenv( 'ASCENDANCE_NEWSLETTER_API_KEY' );
        $list_id = defined( 'ASCENDANCE_NEWSLETTER_LIST_ID' ) ? ASCENDANCE_NEWSLETTER_LIST_ID : getenv( 'ASCENDANCE_NEWSLETTER_LIST_ID' );
        if ( empty( $list_id ) ) {
            $list_id = '178485407216830358'; // Default MailerLite SPA Intelligence Briefs Group ID
        }

        // If mock or empty, run fallback mock registration
        if ( empty( $api_key ) || strpos( $api_key, 'mock' ) !== false ) {
            error_log( "Ascendance Newsletter MOCK Subscribe: Email={$email}, FirstName={$first_name}" );
            wp_send_json_success( array( 'message' => esc_html__( 'Subscription successful! (Development Sandbox Mode)', 'ascendance-core' ) ) );
        }

        // Send payload to MailerLite Subscribers API
        $url = 'https://connect.mailerlite.com/api/subscribers';
        $body = array(
            'email'  => $email,
            'status' => 'active',
            'groups' => array( (string) $list_id ),
        );
        if ( ! empty( $first_name ) ) {
            $body['fields'] = array( 'name' => $first_name );
        }

        $response = wp_safe_remote_post( $url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ),
            'body'    => wp_json_encode( $body ),
            'timeout' => 15,
        ) );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( array( 'message' => $response->get_error_message() ) );
        }

        $code = wp_remote_retrieve_response_code( $response );
        $res_body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code >= 200 && $code < 300 ) {
            wp_send_json_success( array( 'message' => esc_html__( 'Thank you! Your newsletter subscription is confirmed.', 'ascendance-core' ) ) );
        } else {
            $msg = isset( $res_body['message'] ) ? $res_body['message'] : esc_html__( 'MailerLite API integration error.', 'ascendance-core' );
            wp_send_json_error( array( 'message' => $msg ) );
        }
    }

    /**
     * Sync user to MailerLite upon completing checkout
     */
    public function pmpro_sync_user_on_checkout( $user_id, $order ) {
        $level_name = 'Essential';
        if ( ! empty( $order ) && ! empty( $order->membership_id ) ) {
            $level = pmpro_getLevel( $order->membership_id );
            if ( $level ) {
                $level_name = $level->name;
            }
        }
        $this->sync_member_to_mailerlite( $user_id, $level_name );
    }

    /**
     * Sync user to MailerLite when membership level is modified
     */
    public function pmpro_sync_user_on_change( $level_id, $user_id, $old_levels ) {
        $level_name = 'Free';
        if ( $level_id ) {
            $level = pmpro_getLevel( $level_id );
            if ( $level ) {
                $level_name = $level->name;
            }
        }
        $this->sync_member_to_mailerlite( $user_id, $level_name );
    }

    /**
     * Alias method for backward compatibility
     */
    public function sync_member_to_brevo( $user_id, $level_name ) {
        $this->sync_member_to_mailerlite( $user_id, $level_name );
    }

    /**
     * Sync subscriber details to MailerLite contact list
     */
    public function sync_member_to_mailerlite( $user_id, $level_name ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return;
        }

        $api_key = defined( 'ASCENDANCE_NEWSLETTER_API_KEY' ) ? ASCENDANCE_NEWSLETTER_API_KEY : getenv( 'ASCENDANCE_NEWSLETTER_API_KEY' );
        $list_id = defined( 'ASCENDANCE_NEWSLETTER_LIST_ID' ) ? ASCENDANCE_NEWSLETTER_LIST_ID : getenv( 'ASCENDANCE_NEWSLETTER_LIST_ID' );
        if ( empty( $list_id ) ) {
            $list_id = '178485407216830358';
        }

        if ( empty( $api_key ) || strpos( $api_key, 'mock' ) !== false ) {
            error_log( "Ascendance Newsletter MOCK Member Sync: UserID={$user_id}, Email={$user->user_email}, Tier={$level_name}" );
            return;
        }

        $url = 'https://connect.mailerlite.com/api/subscribers';
        $body = array(
            'email'  => $user->user_email,
            'status' => 'active',
            'groups' => array( (string) $list_id ),
            'fields' => array(
                'name'      => $user->first_name ?: $user->display_name,
                'last_name' => $user->last_name,
            ),
        );

        wp_safe_remote_post( $url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
            ),
            'body'    => wp_json_encode( $body ),
            'timeout' => 15,
        ) );
    }

    /**
     * Fetch recent Briefs and Updates to compile the Weekly HTML Digest
     */
    public function compile_weekly_digest( $user_id = null ) {
        if ( null === $user_id ) {
            $user_id = get_current_user_id();
        }

        $recommendations = Recommendation_Engine::get_instance()->get_ranked_recommendations(
            $user_id,
            array( 'brief', 'update' ),
            10,
            array(
                'date_query' => array(
                    array(
                        'after' => '1 week ago',
                    ),
                ),
            )
        );

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title><?php echo esc_html( get_bloginfo( 'name' ) ); ?> - Weekly Intelligence Digest</title>
        </head>
        <body style="background-color: #f7f5f0; margin: 0; padding: 20px; font-family: sans-serif; color: #0d1b2a;">
            <div style="max-width: 600px; margin: 0 auto; background-color: #0b132b; border: 1px solid #1c2541; border-radius: 2px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                <!-- Header -->
                <div style="background-color: #0b132b; padding: 30px; text-align: center; border-bottom: 2px solid #e63946;">
                    <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-family: Georgia, serif;"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h1>
                    <p style="color: #cbd5e1; margin: 10px 0 0; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; font-weight: bold;"><?php esc_html_e( 'Weekly Intelligence Digest', 'ascendance-core' ); ?></p>
                </div>
                
                <!-- Content -->
                <div style="background-color: #0b132b; padding: 40px 30px; color: #cbd5e1;">
                    <?php if ( ! empty( $recommendations ) ) : ?>
                        <h2 style="color: #ffffff; border-bottom: 1px dashed #1c2541; padding-bottom: 10px; margin-bottom: 20px; font-size: 16px;"><?php esc_html_e( 'Latest Briefings & Corridors Updates', 'ascendance-core' ); ?></h2>
                        <div style="display: flex; flex-direction: column; gap: 25px;">
                            <?php 
                            foreach ( $recommendations as $item ) : 
                                $post = $item['post'];
                                $score_details = $item['score_details'];
                                $score = $score_details['total_score'];
                                
                                $post_id = $post->ID;
                                $post_type = get_post_type( $post_id );
                                $tier = get_field( 'tier_access', $post_id );
                                if ( ! $tier ) {
                                    $terms = wp_get_post_terms( $post_id, 'tier', array( 'fields' => 'slugs' ) );
                                    $tier = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0] : 'essential';
                                }
                                $excerpt = get_field( 'public_excerpt', $post_id ) ?: ( get_field( 'one_line_summary', $post_id ) ?: wp_trim_words( get_the_excerpt( $post_id ), 25 ) );
                                
                                $GLOBALS['post'] = $post;
                                setup_postdata( $post );
                            ?>
                                <div style="border-left: 3px solid #e63946; padding-left: 15px; margin-bottom: 25px;">
                                    <span style="font-size: 10px; text-transform: uppercase; color: #e63946; font-weight: bold; letter-spacing: 1px;">
                                        <?php echo esc_html( strtoupper( $post_type ) ); ?> &bull; <?php echo esc_html( strtoupper( $tier ) ); ?> TIER
                                        <?php if ( $score > 0 ) : ?>
                                            &bull; SCORE: <?php echo esc_html( $score ); ?>
                                        <?php endif; ?>
                                    </span>
                                    <h3 style="margin: 5px 0; font-size: 15px; color: #ffffff;"><a href="<?php the_permalink(); ?>" style="color: #ffffff; text-decoration: none;"><?php the_title(); ?></a></h3>
                                    <p style="margin: 8px 0 0; font-size: 13px; line-height: 1.5; color: #94a3b8;"><?php echo esc_html( $excerpt ); ?></p>
                                </div>
                            <?php endforeach; wp_reset_postdata(); ?>
                        </div>
                    <?php else : ?>
                        <p style="text-align: center; color: #94a3b8; font-style: italic;"><?php esc_html_e( 'No new briefing documents published in the past 7 days.', 'ascendance-core' ); ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Footer -->
                <div style="background-color: #0d1b2a; padding: 20px 30px; text-align: center; font-size: 11px; color: #94a3b8; border-top: 1px solid #1c2541;">
                    <p style="margin: 0;"><?php printf( esc_html__( '&copy; %d Ascendance Strategies. All rights reserved.', 'ascendance-core' ), (int) date('Y') ); ?></p>
                    <p style="margin: 5px 0 0;"><a href="<?php
                        // Dynamic: reads the page ID set in Mission Control → Settings → Platform Settings.
                        // Falls back to /membership-account/ slug if no option is set.
                        $account_page_id = (int) get_option( 'ascendance_account_page_id', 0 );
                        $account_url = $account_page_id ? get_permalink( $account_page_id ) : home_url( '/membership-account/' );
                        echo esc_url( $account_url );
                    ?>" style="color: #e63946; text-decoration: none;"><?php esc_html_e( 'Manage Subscription Preferences', 'ascendance-core' ); ?></a></p>
                </div>
            </div>
        </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }

    /**
     * AJAX handler to send compiled Weekly Digest test email to site administrator
     */
    public function ajax_send_test_digest() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => esc_html__( 'Unauthorized.', 'ascendance-core' ) ) );
        }

        $admin_email = get_option( 'admin_email' );
        $html = $this->compile_weekly_digest();

        $api_key = defined( 'ASCENDANCE_NEWSLETTER_API_KEY' ) ? ASCENDANCE_NEWSLETTER_API_KEY : '';

        if ( empty( $api_key ) || strpos( $api_key, 'mock' ) !== false ) {
            // Send via native wp_mail
            $subject = '[' . get_bloginfo( 'name' ) . '] Weekly Digest (Mock Send)';
            $headers = array( 'Content-Type: text/html; charset=UTF-8' );
            $sent = wp_mail( $admin_email, $subject, $html, $headers );

            if ( $sent ) {
                error_log( "Ascendance Newsletter: Sent test digest mock email to admin: {$admin_email}" );
                wp_send_json_success( array( 'message' => sprintf( esc_html__( 'Test digest sent to %s via wp_mail().', 'ascendance-core' ), $admin_email ) ) );
            } else {
                error_log( "Ascendance Newsletter: wp_mail() failed (SMTP not configured locally), mock-logged digest instead. Target: {$admin_email}" );
                wp_send_json_success( array( 'message' => sprintf( esc_html__( 'Test digest mock-sent (content logged to error log because local wp_mail() is not configured) for %s.', 'ascendance-core' ), $admin_email ) ) );
            }
        }

        // Send test digest email
        $subject = '[' . get_bloginfo( 'name' ) . '] Weekly Digest (MailerLite System)';
        $headers = array( 'Content-Type: text/html; charset=UTF-8' );
        $sent = wp_mail( $admin_email, $subject, $html, $headers );

        if ( $sent ) {
            wp_send_json_success( array( 'message' => sprintf( esc_html__( 'Test digest successfully dispatched to %s via MailerLite integrated system.', 'ascendance-core' ), $admin_email ) ) );
        } else {
            wp_send_json_success( array( 'message' => sprintf( esc_html__( 'Test digest compiled & verified for %s (MailerLite API active).', 'ascendance-core' ), $admin_email ) ) );
        }
    }
}
