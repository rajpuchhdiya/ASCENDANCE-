<?php
/**
 * Stripe & Billing Integration Handler Class
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Stripe_Billing {

    /**
     * Singleton instance
     * @var Stripe_Billing|null
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
        // Overwrite PMPro settings dynamically via filters using .env constants
        add_filter( 'option_pmpro_gateway', array( $this, 'override_gateway' ) );
        add_filter( 'option_pmpro_gateway_environment', array( $this, 'override_environment' ) );
        add_filter( 'option_pmpro_stripe_publishablekey', array( $this, 'override_publishable_key' ) );
        add_filter( 'option_pmpro_stripe_secretkey', array( $this, 'override_secret_key' ) );
        add_filter( 'option_pmpro_stripe_webhook_secret', array( $this, 'override_webhook_secret' ) );
        add_filter( 'option_pmpro_stripe_billingaddress', '__return_zero' );
        
        // Dynamic overrides for sandbox/live connect keys just in case
        add_filter( 'option_pmpro_sandbox_stripe_connect_publishablekey', array( $this, 'override_publishable_key' ) );
        add_filter( 'option_pmpro_sandbox_stripe_connect_secretkey', array( $this, 'override_secret_key' ) );
        add_filter( 'option_pmpro_live_stripe_connect_publishablekey', array( $this, 'override_publishable_key' ) );
        add_filter( 'option_pmpro_live_stripe_connect_secretkey', array( $this, 'override_secret_key' ) );

        // Handle customer billing portal redirection
        add_action( 'template_redirect', array( $this, 'handle_billing_portal_redirect' ) );

        // Calculate tax dynamically for US states & EU VAT
        add_filter( 'pmpro_tax', array( $this, 'calculate_tax' ), 10, 3 );
    }

    /**
     * Dynamic overrides
     */
    public function override_gateway( $val ) {
        return 'stripe';
    }

    public function override_environment( $val ) {
        if ( defined( 'WP_ENVIRONMENT_TYPE' ) && 'production' === WP_ENVIRONMENT_TYPE ) {
            return 'live';
        }
        return 'sandbox';
    }

    public function override_publishable_key( $val ) {
        if ( defined( 'STRIPE_PUBLISHABLE_KEY' ) && ! empty( STRIPE_PUBLISHABLE_KEY ) ) {
            return STRIPE_PUBLISHABLE_KEY;
        }
        return $val;
    }

    public function override_secret_key( $val ) {
        if ( defined( 'STRIPE_SECRET_KEY' ) && ! empty( STRIPE_SECRET_KEY ) ) {
            return STRIPE_SECRET_KEY;
        }
        return $val;
    }

    public function override_webhook_secret( $val ) {
        if ( defined( 'STRIPE_WEBHOOK_SECRET' ) && ! empty( STRIPE_WEBHOOK_SECRET ) ) {
            return STRIPE_WEBHOOK_SECRET;
        }
        return $val;
    }

    /**
     * Redirects users to Stripe Hosted Customer Billing Portal if requested
     */
    public function handle_billing_portal_redirect() {
        if ( is_user_logged_in() && isset( $_GET['portal'] ) ) {
            // Check if we are on the membership account page
            $account_page_id = (int) get_option( 'pmpro_account_page_id' );
            if ( is_page( $account_page_id ) || ( isset( $_GET['page'] ) && 'pmpro-account' === $_GET['page'] ) ) {
                $user_id = get_current_user_id();
                $customer_id = get_user_meta( $user_id, 'pmpro_stripe_customerid', true );
                $secret_key = defined( 'STRIPE_SECRET_KEY' ) ? STRIPE_SECRET_KEY : '';

                if ( ! empty( $customer_id ) && ! empty( $secret_key ) ) {
                    // Check if Stripe client functions exist via PMPro
                    if ( class_exists( '\Stripe\Stripe' ) ) {
                        \Stripe\Stripe::setApiKey( $secret_key );
                        try {
                            $session = \Stripe\BillingPortal\Session::create( array(
                                'customer'   => $customer_id,
                                'return_url' => home_url( '/membership-account/' ),
                            ) );
                            wp_redirect( $session->url );
                            exit;
                        } catch ( \Exception $e ) {
                            // Log error and fallback to standard edit billing template
                            error_log( 'Ascendance Stripe Billing Portal Error: ' . $e->getMessage() );
                        }
                    }
                }
            }
        }
    }

    /**
     * Calculate tax for the order (EU VAT and US state taxes)
     *
     * @param float $tax The current calculated tax.
     * @param array $values Billing and price values from PMPro.
     * @param object $order The PMPro order object.
     * @return float The modified tax amount.
     */
    public function calculate_tax( $tax, $values, $order ) {
        $price = isset( $values['price'] ) ? (float) $values['price'] : 0.0;
        $country = isset( $values['billing_country'] ) ? strtoupper( trim( $values['billing_country'] ) ) : '';
        $state = isset( $values['billing_state'] ) ? strtoupper( trim( $values['billing_state'] ) ) : '';

        // List of European Union countries (ISO 2-letter codes)
        $eu_countries = array(
            'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 
            'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 
            'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'
        );

        if ( in_array( $country, $eu_countries, true ) ) {
            // Flat 20% EU VAT
            $tax = round( $price * 0.20, 2 );
        } elseif ( 'US' === $country ) {
            // State-specific US sales tax
            if ( 'NY' === $state ) {
                $tax = round( $price * 0.08875, 2 ); // New York: 8.875%
            } elseif ( 'WA' === $state ) {
                $tax = round( $price * 0.065, 2 );   // Washington: 6.5%
            }
        }

        return $tax;
    }
}
