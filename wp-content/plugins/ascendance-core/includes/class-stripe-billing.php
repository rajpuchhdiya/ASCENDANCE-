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
        // Overwrite PMPro settings dynamically via filters using .env constants (in case PMPro is ever active)
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

        // Handle customer billing portal redirection (legacy)
        add_action( 'template_redirect', array( $this, 'handle_billing_portal_redirect' ) );

        // Calculate tax dynamically for US states & EU VAT
        add_filter( 'pmpro_tax', array( $this, 'calculate_tax' ), 10, 3 );

        // Register REST API routes for Stripe Checkout and Webhooks
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );

        // Register WP-CLI command for entitlement reconciliation
        if ( defined( 'WP_CLI' ) && WP_CLI ) {
            \WP_CLI::add_command( 'ascendance reconcile-entitlements', array( $this, 'cli_reconcile_entitlements' ) );
        }
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
     * Redirects users to Stripe Hosted Customer Billing Portal if requested (Legacy query param method)
     */
    public function handle_billing_portal_redirect() {
        if ( is_user_logged_in() && isset( $_GET['portal'] ) ) {
            // Check if we are on the membership account page
            $account_page_id = (int) get_option( 'pmpro_account_page_id' );
            if ( is_page( $account_page_id ) || ( isset( $_GET['page'] ) && 'pmpro-account' === $_GET['page'] ) ) {
                $user_id = get_current_user_id();
                $customer_id = get_user_meta( $user_id, 'pmpro_stripe_customerid', true );
                if ( empty( $customer_id ) ) {
                    $customer_id = get_user_meta( $user_id, 'ascendance_stripe_customer_id', true );
                }
                $secret_key = defined( 'STRIPE_SECRET_KEY' ) ? STRIPE_SECRET_KEY : '';

                if ( ! empty( $customer_id ) && ! empty( $secret_key ) ) {
                    $portal_params = array(
                        'customer'   => $customer_id,
                        'return_url' => home_url( '/account/' ),
                    );
                    $portal_session = $this->call_stripe_api( 'billing_portal/sessions', $portal_params );
                    if ( ! is_wp_error( $portal_session ) && isset( $portal_session['url'] ) ) {
                        wp_redirect( $portal_session['url'] );
                        exit;
                    }
                }
            }
        }
    }

    /**
     * Calculate tax for the order (EU VAT and US state taxes)
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

    /**
     * Register REST API routes for Stripe integration
     */
    public function register_rest_routes() {
        register_rest_route( 'ascendance/v1', '/stripe/webhook', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_stripe_webhook' ),
            'permission_callback' => '__return_true', // Signature verified in callback
        ) );

        register_rest_route( 'ascendance/v1', '/checkout/create', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'create_checkout_session' ),
            'permission_callback' => '__return_true', // Checkout is public
        ) );

        register_rest_route( 'ascendance/v1', '/billing/portal-session', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'create_portal_session' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( 'ascendance/v1', '/category-checkout', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'create_category_checkout_session' ),
            'permission_callback' => 'is_user_logged_in',
        ) );

        register_rest_route( 'ascendance/v1', '/stripe/category-checkout', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'create_category_checkout_session' ),
            'permission_callback' => 'is_user_logged_in',
        ) );
    }

    /**
     * Helper to make raw HTTP requests to the Stripe API
     */
    private function call_stripe_api( $endpoint, $params = array(), $method = 'POST' ) {
        $secret_key = defined( 'STRIPE_SECRET_KEY' ) ? STRIPE_SECRET_KEY : '';
        if ( empty( $secret_key ) ) {
            $secret_key = get_option( 'pmpro_stripe_secretkey' );
        }

        if ( empty( $secret_key ) ) {
            return new \WP_Error( 'stripe_error', 'Stripe secret key not configured.' );
        }

        $url = 'https://api.stripe.com/v1/' . ltrim( $endpoint, '/' );
        
        $headers = array(
            'Authorization' => 'Basic ' . base64_encode( $secret_key . ':' ),
        );

        $args = array(
            'method'  => $method,
            'headers' => $headers,
            'timeout' => 15,
        );

        if ( 'POST' === $method && ! empty( $params ) ) {
            $args['body'] = http_build_query( $params );
        }

        $response = wp_remote_request( $url, $args );
        
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( $code < 200 || $code >= 300 ) {
            $error_message = isset( $data['error']['message'] ) ? $data['error']['message'] : 'Stripe API request failed.';
            return new \WP_Error( 'stripe_api_error', $error_message, $data );
        }

        return $data;
    }

    /**
     * Create Checkout Session REST Endpoint
     */
    public function create_checkout_session( \WP_REST_Request $request ) {
        $params = $request->get_params();
        if ( empty( $params ) ) {
            // Read json payload
            $params = json_decode( $request->get_body(), true ) ?: array();
        }

        $tier = isset( $params['tier'] ) ? sanitize_text_field( strtolower( $params['tier'] ) ) : 'essential';

        $price_map = array(
            'essential'    => array( 'name' => 'Ascendance Intelligence — Essential', 'amount' => 15000 ),
            'professional' => array( 'name' => 'Ascendance Intelligence — Professional', 'amount' => 29900 ),
            'enterprise'   => array( 'name' => 'Ascendance Intelligence — Enterprise', 'amount' => 59900 ),
        );

        $selected_plan = isset( $price_map[ $tier ] ) ? $price_map[ $tier ] : $price_map['essential'];

        $user_id = get_current_user_id();
        $user_email = '';
        $customer_id = '';
        if ( $user_id ) {
            $user_data = get_userdata( $user_id );
            $user_email = $user_data->user_email;
            $customer_id = get_user_meta( $user_id, 'ascendance_stripe_customer_id', true );
        }

        $session_params = array(
            'payment_method_types' => array( 'card' ),
            'mode'                 => 'subscription',
            'line_items'           => array(
                array(
                    'price_data' => array(
                        'currency'     => 'usd',
                        'product_data' => array(
                            'name' => $selected_plan['name'],
                        ),
                        'unit_amount'  => $selected_plan['amount'],
                        'recurring'    => array(
                            'interval' => 'month',
                        ),
                    ),
                    'quantity'   => 1,
                ),
            ),
            'success_url' => home_url( '/membership-confirmation/?session_id={CHECKOUT_SESSION_ID}' ),
            'cancel_url'  => home_url( '/subscribe/' ),
            'metadata'    => array(
                'tier' => $tier,
            ),
        );

        if ( ! empty( $customer_id ) ) {
            $session_params['customer'] = $customer_id;
        } elseif ( ! empty( $user_email ) ) {
            $session_params['customer_email'] = $user_email;
        }

        $session = $this->call_stripe_api( 'checkout/sessions', $session_params );

        if ( is_wp_error( $session ) ) {
            return new \WP_REST_Response( array( 'error' => $session->get_error_message() ), 400 );
        }

        return new \WP_REST_Response( array( 'url' => $session['url'] ), 200 );
    }

    /**
     * Create Category Add-on Checkout Session REST Endpoint.
     *
     * Strict server-side eligibility checks:
     * 1. Authenticated user with valid account.
     * 2. Category topic term exists.
     * 3. Category is active paid add-on (is_paid_addon=1, addon_status=active).
     * 4. User does NOT already hold an active entitlement for this category.
     * 5. Environment-appropriate Stripe Price ID resolved.
     */
    public function create_category_checkout_session( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return new \WP_REST_Response( array( 'error' => __( 'Authentication required.', 'ascendance-core' ) ), 401 );
        }

        $params = $request->get_params();
        if ( empty( $params ) ) {
            $params = json_decode( $request->get_body(), true ) ?: array();
        }

        $category_slug = isset( $params['category_slug'] ) ? sanitize_title( $params['category_slug'] ) : '';
        if ( empty( $category_slug ) && isset( $params['category'] ) ) {
            $category_slug = sanitize_title( $params['category'] );
        }

        if ( empty( $category_slug ) ) {
            return new \WP_REST_Response( array( 'error' => __( 'Missing required category_slug parameter.', 'ascendance-core' ) ), 400 );
        }

        // 1. Verify category exists
        $term = get_term_by( 'slug', $category_slug, 'topic' );
        if ( ! $term || is_wp_error( $term ) ) {
            return new \WP_REST_Response( array( 'error' => __( 'Category topic not found.', 'ascendance-core' ) ), 404 );
        }

        // 2. Verify category is an active paid add-on
        $is_paid = get_term_meta( $term->term_id, 'is_paid_addon', true );
        $status  = get_term_meta( $term->term_id, 'addon_status', true ) ?: 'active';

        if ( ! $is_paid || 'active' !== $status ) {
            return new \WP_REST_Response( array( 'error' => __( 'Requested category is not an active paid add-on.', 'ascendance-core' ) ), 400 );
        }

        // 3. Verify user does not already hold an active entitlement
        if ( class_exists( 'Ascendance\Core\Paywall' ) ) {
            if ( Paywall::get_instance()->user_has_category_entitlement( $user_id, $term->slug ) ) {
                return new \WP_REST_Response( array( 'error' => __( 'Your account already has an active entitlement for this category.', 'ascendance-core' ) ), 400 );
            }
        }

        // 4. Resolve Stripe Price ID based on active environment (live vs test)
        $price_id = CPT_Taxonomy::get_term_stripe_price_id( $term->term_id );

        if ( ! empty( $price_id ) ) {
            $line_items = array(
                array(
                    'price'    => $price_id,
                    'quantity' => 1,
                ),
            );
        } else {
            // Dynamic price fallback if price ID not set in admin taxonomy meta
            $amount_val = (float) ( get_term_meta( $term->term_id, 'addon_price_amount', true ) ?: 49.00 );
            $unit_amount = (int) round( $amount_val * 100 );
            $line_items = array(
                array(
                    'price_data' => array(
                        'currency'     => 'usd',
                        'product_data' => array(
                            'name' => 'Ascendance Category Add-on — ' . $term->name,
                        ),
                        'unit_amount'  => $unit_amount,
                        'recurring'    => array(
                            'interval' => 'month',
                        ),
                    ),
                    'quantity'   => 1,
                ),
            );
        }

        // Customer resolution — reuse existing customer ID
        $user_data = get_userdata( $user_id );
        $user_email = $user_data ? $user_data->user_email : '';
        $customer_id = get_user_meta( $user_id, 'ascendance_stripe_customer_id', true );

        $session_params = array(
            'payment_method_types' => array( 'card' ),
            'mode'                 => 'subscription',
            'line_items'           => $line_items,
            'success_url'          => home_url( '/dashboard/?checkout=success&category=' . $term->slug . '&session_id={CHECKOUT_SESSION_ID}' ),
            'cancel_url'           => home_url( '/dashboard/?checkout=cancelled' ),
            'metadata'             => array(
                'type'          => 'category_addon',
                'category_slug' => $term->slug,
                'category_id'   => (string) $term->term_id,
                'user_id'       => (string) $user_id,
            ),
        );

        if ( ! empty( $customer_id ) ) {
            $session_params['customer'] = $customer_id;
        } elseif ( ! empty( $user_email ) ) {
            $session_params['customer_email'] = $user_email;
        }

        $session = $this->call_stripe_api( 'checkout/sessions', $session_params );

        if ( is_wp_error( $session ) ) {
            return new \WP_REST_Response( array( 'error' => $session->get_error_message() ), 400 );
        }

        return new \WP_REST_Response( array( 'ok' => true, 'url' => $session['url'], 'category' => $term->slug ), 200 );
    }

    /**
     * Create Customer Billing Portal REST Endpoint
     */
    public function create_portal_session( \WP_REST_Request $request ) {
        $user_id = get_current_user_id();
        $customer_id = get_user_meta( $user_id, 'ascendance_stripe_customer_id', true );

        if ( empty( $customer_id ) ) {
            return new \WP_REST_Response( array( 'error' => 'No active billing details found.' ), 400 );
        }

        $portal_params = array(
            'customer'   => $customer_id,
            'return_url' => home_url( '/account/' ),
        );

        $portal_session = $this->call_stripe_api( 'billing_portal/sessions', $portal_params );

        if ( is_wp_error( $portal_session ) ) {
            return new \WP_REST_Response( array( 'error' => $portal_session->get_error_message() ), 400 );
        }

        return new \WP_REST_Response( array( 'url' => $portal_session['url'] ), 200 );
    }

    /**
     * Handle Stripe Webhook callbacks
     */
    public function handle_stripe_webhook( \WP_REST_Request $request ) {
        $payload   = $request->get_body();
        $signature = $request->get_header( 'stripe_signature' );
        $secret    = defined( 'STRIPE_WEBHOOK_SECRET' ) ? STRIPE_WEBHOOK_SECRET : '';

        if ( empty( $secret ) ) {
            $secret = get_option( 'pmpro_stripe_webhook_secret' );
        }

        if ( empty( $signature ) || empty( $payload ) ) {
            return new \WP_REST_Response( array( 'error' => 'Missing signature or payload' ), 400 );
        }

        // Verify Stripe signature manually
        if ( ! $this->verify_webhook_signature( $payload, $signature, $secret ) ) {
            return new \WP_REST_Response( array( 'error' => 'Invalid signature verification' ), 400 );
        }

        $event = json_decode( $payload, true );
        if ( ! $event || ! isset( $event['type'] ) ) {
            return new \WP_REST_Response( array( 'error' => 'Invalid JSON payload' ), 400 );
        }

        // Idempotency check: prevent duplicate event processing
        $event_id = sanitize_text_field( $event['id'] );
        if ( $this->is_event_processed( $event_id ) ) {
            return new \WP_REST_Response( array( 'ok' => true, 'duplicate' => true ), 200 );
        }

        $event_type = $event['type'];
        try {
            switch ( $event_type ) {
                case 'checkout.session.completed':
                    $this->handle_checkout_completed( $event['data']['object'] );
                    break;
                case 'customer.subscription.deleted':
                    $this->handle_subscription_deleted( $event['data']['object'] );
                    break;
                case 'customer.subscription.updated':
                    $this->handle_subscription_updated( $event['data']['object'] );
                    break;
                case 'invoice.payment_failed':
                    $this->handle_invoice_payment_failed( $event['data']['object'] );
                    break;
                case 'invoice.payment_succeeded':
                    $this->handle_invoice_payment_succeeded( $event['data']['object'] );
                    break;
                default:
                    break;
            }

            $this->mark_event_processed( $event_id );
        } catch ( \Throwable $e ) {
            error_log( 'Ascendance Stripe Webhook Handler Error: ' . $e->getMessage() );
            return new \WP_REST_Response( array( 'error' => 'Handler exception: ' . $e->getMessage() ), 500 );
        }

        return new \WP_REST_Response( array( 'ok' => true ), 200 );
    }

    /**
     * Verify Stripe Webhook Signature manually
     */
    private function verify_webhook_signature( $payload, $signature_header, $secret ) {
        if ( empty( $secret ) ) {
            // Bypass verification in local debug mode if no secret configured
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                return true;
            }
            return false;
        }

        $parts = explode( ',', $signature_header );
        $timestamp = 0;
        $signatures = array();

        foreach ( $parts as $part ) {
            $kv = explode( '=', $part, 2 );
            if ( count( $kv ) === 2 ) {
                if ( trim( $kv[0] ) === 't' ) {
                    $timestamp = (int) trim( $kv[1] );
                } elseif ( trim( $kv[0] ) === 'v1' ) {
                    $signatures[] = trim( $kv[1] );
                }
            }
        }

        if ( $timestamp === 0 || empty( $signatures ) ) {
            return false;
        }

        // Allow signature timing leeway in debug/test environments
        if ( abs( time() - $timestamp ) > 600 ) {
            if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
                return false;
            }
        }

        $signed_payload = $timestamp . '.' . $payload;
        $computed_signature = hash_hmac( 'sha256', $signed_payload, $secret );

        foreach ( $signatures as $sig ) {
            if ( hash_equals( $sig, $computed_signature ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Idempotency checks
     */
    private function is_event_processed( $event_id ) {
        return (bool) get_transient( 'asc_stripe_evt_' . $event_id );
    }

    private function mark_event_processed( $event_id ) {
        set_transient( 'asc_stripe_evt_' . $event_id, '1', DAY_IN_SECONDS );
    }

    /**
     * Webhook Event: checkout.session.completed
     */
    private function handle_checkout_completed( $session ) {
        $email = isset( $session['customer_details']['email'] ) ? sanitize_email( $session['customer_details']['email'] ) : '';
        if ( empty( $email ) ) {
            $email = isset( $session['customer_email'] ) ? sanitize_email( $session['customer_email'] ) : '';
        }
        if ( empty( $email ) ) {
            throw new \Exception( 'No email address found in checkout session' );
        }

        $tier = isset( $session['metadata']['tier'] ) ? sanitize_text_field( strtolower( $session['metadata']['tier'] ) ) : 'essential';
        $customer_id = isset( $session['customer'] ) ? sanitize_text_field( $session['customer'] ) : '';
        $subscription_id = isset( $session['subscription'] ) ? sanitize_text_field( $session['subscription'] ) : '';

        $user = get_user_by( 'email', $email );
        if ( ! $user ) {
            // Create user
            $username = strstr( $email, '@', true );
            $username = $this->generate_unique_username( $username );
            $password = wp_generate_password( 16, true );
            
            $user_id = wp_create_user( $username, $password, $email );
            if ( is_wp_error( $user_id ) ) {
                throw new \Exception( 'Failed to create user on checkout: ' . $user_id->get_error_message() );
            }
            $user = get_userdata( $user_id );
        }

        $user_id = $user->ID;

        // Check if this is a category add-on checkout
        $session_type  = isset( $session['metadata']['type'] ) ? sanitize_text_field( $session['metadata']['type'] ) : '';
        $category_slug = isset( $session['metadata']['category_slug'] ) ? sanitize_title( $session['metadata']['category_slug'] ) : '';

        if ( 'category_addon' === $session_type && ! empty( $category_slug ) ) {
            // Save customer ID if not set
            if ( ! empty( $customer_id ) ) {
                update_user_meta( $user_id, 'ascendance_stripe_customer_id', $customer_id );
                update_user_meta( $user_id, 'pmpro_stripe_customerid', $customer_id );
            }

            // Grant category entitlement directly
            if ( class_exists( 'Ascendance\Core\Paywall' ) ) {
                Paywall::get_instance()->grant_user_category_entitlement( $user_id, $category_slug );
            }

            // Store subscription mapping
            if ( ! empty( $subscription_id ) ) {
                update_user_meta( $user_id, 'asc_cat_sub_' . $category_slug, $subscription_id );
                update_user_meta( $user_id, 'asc_sub_cat_' . $subscription_id, $category_slug );
            }

            // Telemetry logging
            if ( class_exists( __NAMESPACE__ . '\Mission_Control' ) ) {
                Mission_Control::get_instance()->log_activity(
                    0,
                    'system',
                    'category_addon_purchase',
                    $user_id,
                    'user',
                    sprintf( "Subscriber '%s' purchased category add-on '%s' via Stripe Checkout.", $user->display_name ?: $user->user_login, $category_slug )
                );
            }
            return;
        }

        // Standard Base Tier subscription checkout handling (existing behaviour)
        $role_name = 'ascendance_' . $tier;
        $wp_roles = wp_roles();
        if ( $wp_roles->is_role( $role_name ) ) {
            $user->set_role( $role_name );
        } else {
            $user->set_role( 'subscriber' );
        }

        // Update metadata
        update_user_meta( $user_id, 'ascendance_stripe_customer_id', $customer_id );
        update_user_meta( $user_id, 'ascendance_stripe_subscription_id', $subscription_id );
        update_user_meta( $user_id, 'ascendance_stripe_subscription_status', 'active' );
        update_user_meta( $user_id, 'pmpro_stripe_customerid', $customer_id ); // PMPro legacy compatibility
        
        // Log telemetry
        if ( class_exists( __NAMESPACE__ . '\Mission_Control' ) ) {
            Mission_Control::get_instance()->log_activity(
                0,
                'system',
                'subscription_change',
                $user_id,
                'user',
                sprintf( "Subscriber '%s' signed up for %s Tier via Stripe Checkout.", $user->display_name ?: $username, ucfirst( $tier ) )
            );
        }
    }

    /**
     * Generate unique username from a base string
     */
    private function generate_unique_username( $base_username ) {
        $username = sanitize_user( $base_username, true );
        $final_username = $username;
        $counter = 1;
        while ( username_exists( $final_username ) ) {
            $final_username = $username . $counter;
            $counter++;
        }
        return $final_username;
    }

    /**
     * Webhook Event: customer.subscription.deleted
     */
    private function handle_subscription_deleted( $subscription ) {
        $subscription_id = sanitize_text_field( $subscription['id'] );

        // 1. Check if this subscription ID belongs to a category add-on
        $cat_users = get_users( array(
            'meta_key' => 'asc_sub_cat_' . $subscription_id,
            'number'   => 1,
        ) );

        if ( ! empty( $cat_users ) ) {
            $user = $cat_users[0];
            $category_slug = get_user_meta( $user->ID, 'asc_sub_cat_' . $subscription_id, true );
            if ( $category_slug && class_exists( 'Ascendance\Core\Paywall' ) ) {
                Paywall::get_instance()->revoke_user_category_entitlement( $user->ID, $category_slug );
            }
            delete_user_meta( $user->ID, 'asc_cat_sub_' . $category_slug );
            delete_user_meta( $user->ID, 'asc_sub_cat_' . $subscription_id );

            if ( class_exists( __NAMESPACE__ . '\Mission_Control' ) ) {
                Mission_Control::get_instance()->log_activity(
                    0,
                    'system',
                    'category_addon_revocation',
                    $user->ID,
                    'user',
                    sprintf( "Subscriber '%s' category add-on '%s' canceled via Stripe webhook.", $user->display_name ?: $user->user_login, $category_slug )
                );
            }
            return;
        }

        // 2. Base Tier subscription deletion (existing behaviour)
        $users = get_users( array(
            'meta_key'   => 'ascendance_stripe_subscription_id',
            'meta_value' => $subscription_id,
            'number'     => 1,
        ) );

        if ( ! empty( $users ) ) {
            $user = $users[0];
            $user->set_role( 'subscriber' );
            update_user_meta( $user->ID, 'ascendance_stripe_subscription_status', 'canceled' );

            if ( class_exists( __NAMESPACE__ . '\Mission_Control' ) ) {
                Mission_Control::get_instance()->log_activity(
                    0,
                    'system',
                    'subscription_change',
                    $user->ID,
                    'user',
                    sprintf( "Subscriber '%s' subscription canceled / demoted to standard subscriber.", $user->display_name )
                );
            }
        }
    }

    /**
     * Webhook Event: customer.subscription.updated
     */
    private function handle_subscription_updated( $subscription ) {
        $subscription_id = sanitize_text_field( $subscription['id'] );

        // 1. Check category add-on subscription
        $cat_users = get_users( array(
            'meta_key' => 'asc_sub_cat_' . $subscription_id,
            'number'   => 1,
        ) );

        if ( ! empty( $cat_users ) ) {
            $user = $cat_users[0];
            $category_slug = get_user_meta( $user->ID, 'asc_sub_cat_' . $subscription_id, true );
            $status = isset( $subscription['status'] ) ? $subscription['status'] : '';
            $cancel_at_period_end = ! empty( $subscription['cancel_at_period_end'] );
            $current_period_end   = isset( $subscription['current_period_end'] ) ? date( 'Y-m-d H:i:s', $subscription['current_period_end'] ) : null;

            if ( in_array( $status, array( 'active', 'trialing' ), true ) ) {
                if ( class_exists( 'Ascendance\Core\Paywall' ) ) {
                    $expires = $cancel_at_period_end ? $current_period_end : null;
                    Paywall::get_instance()->grant_user_category_entitlement( $user->ID, $category_slug, $expires );
                    if ( $cancel_at_period_end ) {
                        $entitlements = (array) get_user_meta( $user->ID, 'asc_category_entitlements', true );
                        if ( isset( $entitlements[ $category_slug ] ) && is_array( $entitlements[ $category_slug ] ) ) {
                            $entitlements[ $category_slug ]['status'] = 'canceling';
                            update_user_meta( $user->ID, 'asc_category_entitlements', $entitlements );
                        }
                    }
                }
            } elseif ( 'past_due' === $status ) {
                $entitlements = (array) get_user_meta( $user->ID, 'asc_category_entitlements', true );
                if ( isset( $entitlements[ $category_slug ] ) && is_array( $entitlements[ $category_slug ] ) ) {
                    $entitlements[ $category_slug ]['status'] = 'payment_issue';
                    update_user_meta( $user->ID, 'asc_category_entitlements', $entitlements );
                }
            } else {
                if ( class_exists( 'Ascendance\Core\Paywall' ) ) {
                    Paywall::get_instance()->revoke_user_category_entitlement( $user->ID, $category_slug );
                }
            }

            if ( class_exists( 'Ascendance\Core\Paywall' ) ) {
                Paywall::get_instance()->invalidate_user_entitlements_cache( $user->ID );
            }
            return;
        }

        // 2. Base Tier subscription update (existing behaviour)
        $users = get_users( array(
            'meta_key'   => 'ascendance_stripe_subscription_id',
            'meta_value' => $subscription_id,
            'number'     => 1,
        ) );

        if ( empty( $users ) ) {
            return;
        }

        $user = $users[0];
        $status = $subscription['status'];

        if ( in_array( $status, array( 'active', 'trialing' ), true ) ) {
            update_user_meta( $user->ID, 'ascendance_stripe_subscription_status', 'active' );
            
            // Map unit amount to tier role
            $price_amount = isset( $subscription['items']['data'][0]['price']['unit_amount'] ) 
                ? (int) $subscription['items']['data'][0]['price']['unit_amount'] 
                : 0;

            $tier = 'essential';
            if ( $price_amount >= 50000 ) {
                $tier = 'enterprise';
            } elseif ( $price_amount >= 25000 ) {
                $tier = 'professional';
            }

            $role_name = 'ascendance_' . $tier;
            $wp_roles = wp_roles();
            if ( $wp_roles->is_role( $role_name ) ) {
                $user->set_role( $role_name );
            }
        } else {
            $user->set_role( 'subscriber' );
            update_user_meta( $user->ID, 'ascendance_stripe_subscription_status', $status );
        }
    }

    /**
     * Webhook Event: invoice.payment_failed
     */
    private function handle_invoice_payment_failed( $invoice ) {
        $subscription_id = isset( $invoice['subscription'] ) ? sanitize_text_field( $invoice['subscription'] ) : '';

        // 1. Check if invoice belongs to a category add-on subscription
        if ( ! empty( $subscription_id ) ) {
            $cat_users = get_users( array(
                'meta_key' => 'asc_sub_cat_' . $subscription_id,
                'number'   => 1,
            ) );
            if ( ! empty( $cat_users ) ) {
                $user = $cat_users[0];
                $category_slug = get_user_meta( $user->ID, 'asc_sub_cat_' . $subscription_id, true );

                $entitlements = (array) get_user_meta( $user->ID, 'asc_category_entitlements', true );
                if ( isset( $entitlements[ $category_slug ] ) && is_array( $entitlements[ $category_slug ] ) ) {
                    $entitlements[ $category_slug ]['status'] = 'payment_issue';
                    update_user_meta( $user->ID, 'asc_category_entitlements', $entitlements );
                }

                if ( class_exists( 'Ascendance\Core\Paywall' ) ) {
                    Paywall::get_instance()->invalidate_user_entitlements_cache( $user->ID );
                }

                if ( class_exists( __NAMESPACE__ . '\Mission_Control' ) ) {
                    Mission_Control::get_instance()->log_activity(
                        0,
                        'system',
                        'category_addon_payment_failed',
                        $user->ID,
                        'user',
                        sprintf( "Stripe payment failed for category add-on '%s'. Entitlement marked payment_issue for user #%d.", $category_slug, $user->ID )
                    );
                }
                return;
            }
        }

        // 2. Base Tier invoice payment failure (existing behaviour)
        $customer_id = sanitize_text_field( $invoice['customer'] );
        $users = get_users( array(
            'meta_key'   => 'ascendance_stripe_customer_id',
            'meta_value' => $customer_id,
            'number'     => 1,
        ) );

        if ( ! empty( $users ) ) {
            $user = $users[0];
            $user->set_role( 'subscriber' );
            update_user_meta( $user->ID, 'ascendance_stripe_subscription_status', 'past_due' );

            if ( class_exists( __NAMESPACE__ . '\Mission_Control' ) ) {
                Mission_Control::get_instance()->log_activity(
                    0,
                    'system',
                    'subscription_change',
                    $user->ID,
                    'user',
                    sprintf( "Stripe invoice payment failed. User '%s' demoted.", $user->display_name )
                );
            }
        }
    }

    /**
     * Webhook Event: invoice.payment_succeeded
     */
    private function handle_invoice_payment_succeeded( $invoice ) {
        $subscription_id = isset( $invoice['subscription'] ) ? sanitize_text_field( $invoice['subscription'] ) : '';

        if ( ! empty( $subscription_id ) ) {
            $cat_users = get_users( array(
                'meta_key' => 'asc_sub_cat_' . $subscription_id,
                'number'   => 1,
            ) );
            if ( ! empty( $cat_users ) ) {
                $user = $cat_users[0];
                $category_slug = get_user_meta( $user->ID, 'asc_sub_cat_' . $subscription_id, true );

                if ( class_exists( 'Ascendance\Core\Paywall' ) ) {
                    Paywall::get_instance()->grant_user_category_entitlement( $user->ID, $category_slug );
                    Paywall::get_instance()->invalidate_user_entitlements_cache( $user->ID );
                }

                if ( class_exists( __NAMESPACE__ . '\Mission_Control' ) ) {
                    Mission_Control::get_instance()->log_activity(
                        0,
                        'system',
                        'category_addon_payment_recovered',
                        $user->ID,
                        'user',
                        sprintf( "Stripe invoice payment recovered for category add-on '%s'. Entitlement restored for user #%d.", $category_slug, $user->ID )
                    );
                }
            }
        }
    }

    /**
     * Reconcile local category entitlements against expiration dates and system state
     *
     * @param int|null $user_id
     * @return array
     */
    public function reconcile_category_entitlements( $user_id = null ) {
        $users = array();
        if ( $user_id ) {
            $u_obj = get_userdata( $user_id );
            if ( $u_obj ) {
                $users[] = $u_obj;
            }
        } else {
            $users = get_users( array( 'number' => 1000 ) );
        }

        $scanned_count    = 0;
        $reconciled_count = 0;
        $expired_count    = 0;

        $paywall = class_exists( 'Ascendance\Core\Paywall' ) ? Paywall::get_instance() : null;

        foreach ( $users as $u ) {
            $scanned_count++;
            $raw_entitlements = (array) get_user_meta( $u->ID, 'asc_category_entitlements', true );
            if ( empty( $raw_entitlements ) ) {
                continue;
            }

            $changed = false;
            foreach ( $raw_entitlements as $slug => $item ) {
                if ( is_array( $item ) ) {
                    $expires = isset( $item['expires_at'] ) ? $item['expires_at'] : null;
                    $status  = isset( $item['status'] ) ? $item['status'] : 'active';

                    if ( $expires && strtotime( $expires ) < time() && 'expired' !== $status ) {
                        $raw_entitlements[ $slug ]['status'] = 'expired';
                        $changed = true;
                        $expired_count++;
                    }
                }
            }

            if ( $changed ) {
                update_user_meta( $u->ID, 'asc_category_entitlements', $raw_entitlements );
                if ( $paywall ) {
                    $paywall->invalidate_user_entitlements_cache( $u->ID );
                }
                $reconciled_count++;
            }
        }

        return array(
            'scanned_count'    => $scanned_count,
            'reconciled_count' => $reconciled_count,
            'expired_count'    => $expired_count,
        );
    }

    /**
     * WP-CLI Command Callback
     */
    public function cli_reconcile_entitlements( $args, $assoc_args ) {
        $user_id = isset( $assoc_args['user_id'] ) ? (int) $assoc_args['user_id'] : null;
        $res = $this->reconcile_category_entitlements( $user_id );
        if ( class_exists( 'WP_CLI' ) ) {
            \WP_CLI::success( sprintf( "Reconciled %d users (%d records updated, %d expired).", $res['scanned_count'], $res['reconciled_count'], $res['expired_count'] ) );
        }
    }
}
