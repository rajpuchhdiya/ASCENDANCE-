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
        add_action( 'wp_head', array( $this, 'inject_analytics_scripts' ), 1 );
        add_action( 'wp_footer', array( $this, 'inject_datalayer_events' ) );
        add_action( 'wp_login', array( $this, 'track_login' ), 10, 2 );
        add_action( 'user_register', array( $this, 'track_registration' ) );
        add_action( 'pmpro_after_checkout', array( $this, 'track_subscription_completed' ), 10, 2 );
    }

    /**
     * Inject analytics tracking snippets (GTM, GA4, Clarity, Facebook Pixel, Hotjar)
     */
    public function inject_analytics_scripts() {
        // A. Google Tag Manager
        $gtm_id = get_option( 'ascendance_gtm_id' );
        if ( ! empty( $gtm_id ) && 'GTM-XXXXXXX' !== $gtm_id ) {
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

        // B. Google Analytics (GA4)
        $ga4_id = get_option( 'ascendance_ga4_id' );
        if ( ! empty( $ga4_id ) && 'G-XXXXXXXXXX' !== $ga4_id ) {
            ?>
            <!-- Google Analytics (GA4) -->
            <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $ga4_id ); ?>"></script>
            <script>
              window.dataLayer = window.dataLayer || [];
              function gtag(){dataLayer.push(arguments);}
              gtag('js', new Date());
              gtag('config', '<?php echo esc_js( $ga4_id ); ?>');
            </script>
            <!-- End Google Analytics (GA4) -->
            <?php
        }

        // C. Microsoft Clarity
        $clarity_id = get_option( 'ascendance_clarity_id' );
        if ( ! empty( $clarity_id ) ) {
            ?>
            <!-- Microsoft Clarity -->
            <script type="text/javascript">
                (function(c,l,a,r,i,t,y){
                    c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
                    t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
                    y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
                })(window,document,"clarity","script","<?php echo esc_js( $clarity_id ); ?>");
            </script>
            <!-- End Microsoft Clarity -->
            <?php
        }

        // D. Facebook Pixel
        $fb_pixel_id = get_option( 'ascendance_facebook_pixel_id' );
        if ( ! empty( $fb_pixel_id ) ) {
            ?>
            <!-- Facebook Pixel -->
            <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '<?php echo esc_js( $fb_pixel_id ); ?>');
            fbq('track', 'PageView');
            </script>
            <!-- End Facebook Pixel -->
            <?php
        }

        // E. Hotjar
        $hotjar_id = get_option( 'ascendance_hotjar_id' );
        if ( ! empty( $hotjar_id ) ) {
            ?>
            <!-- Hotjar Tracking Code -->
            <script>
                (function(h,o,t,j,a,r){
                    h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
                    h._hjSettings={hjid:<?php echo intval( $hotjar_id ); ?>,hjsv:6};
                    a=o.getElementsByTagName('head')[0];
                    r=o.createElement('script');r.async=1;
                    r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
                    a.appendChild(r);
                })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
            </script>
            <!-- End Hotjar -->
            <?php
        }
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

                    // Also push flat checkout_complete event
                    const item = transaction.items && transaction.items[0];
                    window.dataLayer.push({
                        'event': 'checkout_complete',
                        'transaction_id': transaction.transaction_id,
                        'membership_level': item ? item.item_name.replace('Membership Level: ', '') : 'Unknown',
                        'price': transaction.value,
                        'currency': transaction.currency
                    });
                } catch(e) {
                    console.error("GTM tracking parser error: ", e);
                }
                document.cookie = "asc_track_checkout=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            }

            // D. Check for Search usage
            <?php if ( is_search() ) : ?>
                window.dataLayer.push({
                    'event': 'search_used',
                    'search_term': '<?php echo esc_js( get_search_query() ); ?>',
                    'search_results': <?php global $wp_query; echo intval( $wp_query->found_posts ); ?>
                });
                window.dataLayer.push({
                    'event': 'search_usage',
                    'search_term': '<?php echo esc_js( get_search_query() ); ?>',
                    'search_results': <?php global $wp_query; echo intval( $wp_query->found_posts ); ?>
                });
            <?php endif; ?>

            // E. Check for premium article reads and paywall views
            <?php if ( is_singular( array( 'brief', 'dossier', 'update' ) ) ) : 
                $post_id = get_the_ID();
                $has_access = class_exists( 'Ascendance\Core\Paywall' ) && \Ascendance\Core\Paywall::get_instance()->user_has_access( $post_id );
                $tier_access = get_field( "tier_access", $post_id );
                if ( ! $tier_access ) {
                    $terms = wp_get_post_terms( $post_id, 'tier', array( 'fields' => 'slugs' ) );
                    $tier_access = ( ! empty( $terms ) && ! is_wp_error( $terms ) ) ? $terms[0] : 'essential';
                }
                ?>
                window.dataLayer.push({
                    'event': 'article_read',
                    'post_type': '<?php echo esc_js( get_post_type() ); ?>',
                    'post_title': '<?php echo esc_js( get_the_title() ); ?>',
                    'tier_access': '<?php echo esc_js( $tier_access ); ?>'
                });
                <?php if ( ! $has_access ) : ?>
                    window.dataLayer.push({
                        'event': 'paywall_view',
                        'post_type': '<?php echo esc_js( get_post_type() ); ?>',
                        'post_title': '<?php echo esc_js( get_the_title() ); ?>',
                        'tier_access': '<?php echo esc_js( $tier_access ); ?>'
                    });
                <?php endif; ?>
            <?php endif; ?>

            // F. Check for Checkout Start
            <?php 
            $checkout_page_id = function_exists( 'pmpro_get_page_id' ) ? pmpro_get_page_id( 'checkout' ) : 0;
            if ( ! $checkout_page_id ) {
                $checkout_page_id = (int) get_option( 'pmpro_checkout_page_id' );
            }
            if ( $checkout_page_id && is_page( $checkout_page_id ) ) :
                $level_id = isset( $_GET['level'] ) ? intval( $_GET['level'] ) : 0;
                $level_name = 'Unknown';
                $price = 0.0;
                if ( $level_id && function_exists( 'pmpro_getLevel' ) ) {
                    $level_obj = pmpro_getLevel( $level_id );
                    if ( $level_obj ) {
                        $level_name = $level_obj->name;
                        $price = (float) $level_obj->initial_payment;
                    }
                }
                ?>
                window.dataLayer.push({
                    'event': 'checkout_start',
                    'membership_level_id': <?php echo intval( $level_id ); ?>,
                    'membership_level': '<?php echo esc_js( $level_name ); ?>',
                    'price': <?php echo floatval( $price ); ?>
                });
            <?php endif; ?>

            // G. Scroll depth tracking for Brief reads (scroll depth >= 90%)
            <?php if ( is_singular( 'brief' ) && class_exists( 'Ascendance\Core\Paywall' ) && \Ascendance\Core\Paywall::get_instance()->user_has_access( get_the_ID() ) ) : ?>
                (function() {
                    let sent = false;
                    window.addEventListener('scroll', function() {
                        if (sent) return;
                        const docHeight = document.documentElement.scrollHeight - window.innerHeight;
                        if (docHeight <= 0) return;
                        const scrollPercent = (window.scrollY / docHeight) * 100;
                        if (scrollPercent >= 90) {
                            sent = true;
                            window.dataLayer.push({
                                'event': 'brief_read',
                                'post_type': 'brief',
                                'post_title': '<?php echo esc_js( get_the_title() ); ?>'
                            });
                        }
                    });
                })();
            <?php endif; ?>

            // H. Subscribe button click event listeners with source mapping
            document.addEventListener('click', function(e) {
                let target = e.target.closest('a, button');
                if (!target) return;
                
                let isSubscribeClick = false;
                let sourceLocation = '';
                
                // 1. Header Subscribe CTA
                if (target.id === 'header-subscribe-cta' || target.closest('#header-subscribe-cta')) {
                    isSubscribeClick = true;
                    sourceLocation = 'header';
                }
                // 2. Footer Subscribe
                else if (target.closest('.site-footer') && ( (target.href && target.href.includes('/newsletter/')) || target.classList.contains('footer-subscribe-cta') || target.textContent.trim().toLowerCase() === 'subscribe' )) {
                    isSubscribeClick = true;
                    sourceLocation = 'footer';
                }
                // 3. Paywall CTA buttons
                else if (target.closest('.paywall-cta-box') || target.classList.contains('paywall-subscribe-cta')) {
                    isSubscribeClick = true;
                    sourceLocation = 'paywall';
                }
                // 4. Pricing table buttons
                else if (target.closest('.pricing-matrix')) {
                    isSubscribeClick = true;
                    sourceLocation = 'pricing';
                }
                // 5. Landing / Hero page forms
                else if (target.closest('.newsletter-hero-section') && target.type === 'submit') {
                    isSubscribeClick = true;
                    sourceLocation = 'landing_page';
                }
                
                if (isSubscribeClick) {
                    window.dataLayer.push({
                        'event': 'subscribe_click',
                        'source_location': sourceLocation,
                        'button_text': target.textContent.trim(),
                        'destination_url': target.href || ''
                    });
                }
            });
        </script>
        <?php
    }
}
