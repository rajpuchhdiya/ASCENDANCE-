<?php
/**
 * GTM dataLayer & GA4 Event Tracking Class
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Analytics {

    /**
     * Singleton instance
     * @var Analytics|null
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
        add_action( 'wp_head', array( $this, 'inject_gtm_container' ), 1 );
        add_action( 'wp_footer', array( $this, 'inject_datalayer_events' ) );
        add_action( 'wp_login', array( $this, 'track_login' ), 10, 2 );
        add_action( 'user_register', array( $this, 'track_registration' ) );
        add_action( 'pmpro_after_checkout', array( $this, 'track_subscription_completed' ), 10, 2 );
    }

    /**
     * Inject Google Tag Manager main container snippet
     */
    public function inject_gtm_container() {
        $gtm_id = get_option( 'ascendance_gtm_id', 'GTM-XXXXXXX' );
        if ( 'GTM-XXXXXXX' === $gtm_id ) {
            return; // Only run if configured
        }
        ?>
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','<?php echo esc_js( $gtm_id ); ?>');</script>
        <!-- End Google Tag Manager -->
        <?php
    }

    /**
     * Track user logins by setting transient/cookie to trigger on next load
     */
    public function track_login( $user_login, $user ) {
        setcookie( 'asc_track_login', '1', time() + 120, '/' );
    }

    /**
     * Track user registrations by setting cookie
     */
    public function track_registration( $user_id ) {
        setcookie( 'asc_track_register', '1', time() + 120, '/' );
    }

    /**
     * Track PMPro subscription checkout completions
     */
    public function track_subscription_completed( $user_id, $order ) {
        if ( empty( $order ) ) {
            return;
        }

        $membership_level = $order->membership_id;
        $level_obj = pmpro_getLevel( $membership_level );
        $level_name = $level_obj ? $level_obj->name : 'Unknown';
        
        $transaction_data = array(
            'event'          => 'subscription_completed',
            'transaction_id' => $order->code,
            'affiliation'    => get_bloginfo( 'name' ),
            'value'          => (float) $order->subtotal,
            'currency'       => $order->gateway_environment === 'sandbox' ? 'USD' : $order->currency,
            'items'          => array(
                array(
                    'item_name'     => 'Membership Level: ' . $level_name,
                    'item_category' => 'Subscriptions',
                    'price'         => (float) $order->subtotal,
                    'quantity'      => 1
                )
            )
        );

        // Save transaction to cookie so it renders on GTM checkout success page redirection
        setcookie( 'asc_track_checkout', wp_json_encode( $transaction_data ), time() + 120, '/' );
    }

    /**
     * Inject custom client-side events into GTM dataLayer
     */
    public function inject_datalayer_events() {
        ?>
        <script>
            window.dataLayer = window.dataLayer || [];

            // A. Check for Login Event cookie
            if (document.cookie.split(';').some((item) => item.trim().startsWith('asc_track_login='))) {
                window.dataLayer.push({
                    'event': 'login',
                    'method': 'standard'
                });
                document.cookie = "asc_track_login=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            }

            // B. Check for Registration Event cookie
            if (document.cookie.split(';').some((item) => item.trim().startsWith('asc_track_register='))) {
                window.dataLayer.push({
                    'event': 'register',
                    'method': 'standard_subscription'
                });
                document.cookie = "asc_track_register=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            }

            // C. Check for Ecommerce Checkout Completion cookie
            const checkoutMatch = document.cookie.match('(^|;)\\s*asc_track_checkout\\s*=\\s*([^;]+)');
            if (checkoutMatch) {
                try {
                    const transaction = JSON.parse(decodeURIComponent(checkoutMatch[2]));
                    window.dataLayer.push(transaction);
                } catch(e) {
                    console.error("GTM tracking parser error: ", e);
                }
                document.cookie = "asc_track_checkout=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            }

            // D. Check for Search usage
            <?php if ( is_search() ) : ?>
                window.dataLayer.push({
                    'event': 'search_usage',
                    'search_term': '<?php echo esc_js( get_search_query() ); ?>',
                    'search_results': <?php global $wp_query; echo intval( $wp_query->found_posts ); ?>
                });
            <?php endif; ?>

            // E. Check for premium article reads
            <?php if ( is_singular( array( 'brief', 'dossier', 'update' ) ) ) : ?>
                window.dataLayer.push({
                    'event': 'article_read',
                    'post_type': '<?php echo esc_js( get_post_type() ); ?>',
                    'post_title': '<?php echo esc_js( get_the_title() ); ?>',
                    'tier_access': '<?php echo esc_js( get_field( "tier_access", get_the_ID() ) ?: "essential" ); ?>'
                });
            <?php endif; ?>
        </script>
        <?php
    }
}
