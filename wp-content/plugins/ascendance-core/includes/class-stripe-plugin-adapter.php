<?php
/**
 * Stripe Plugin Adapter for WP Simple Pay
 *
 * @package Ascendance\Core
 */

namespace Ascendance\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Stripe_Plugin_Adapter {

    /**
     * Singleton instance
     * @var Stripe_Plugin_Adapter|null
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
        // WP Simple Pay webhook actions
        add_action( 'simpay_webhook_checkout_session_completed', array( $this, 'handle_checkout_completed' ) );
        add_action( 'simpay_webhook_customer_subscription_created', array( $this, 'handle_subscription_updated' ) );
        add_action( 'simpay_webhook_customer_subscription_updated', array( $this, 'handle_subscription_updated' ) );
        add_action( 'simpay_webhook_customer_subscription_deleted', array( $this, 'handle_subscription_deleted' ) );
        add_action( 'simpay_webhook_invoice_payment_succeeded', array( $this, 'handle_invoice_payment_succeeded' ) );
        add_action( 'simpay_webhook_invoice_payment_failed', array( $this, 'handle_invoice_payment_failed' ) );
    }

    /**
     * Helper to log activities without direct Stripe integration
     */
    private function log_billing_activity( $user_id, $action, $message ) {
        if ( class_exists( __NAMESPACE__ . '\Mission_Control' ) ) {
            $user = get_userdata( $user_id );
            Mission_Control::get_instance()->log_activity(
                0,
                'system',
                $action,
                $user_id,
                'user',
                sprintf( "[WP Simple Pay Adapter] %s", $message )
            );
        }
    }

    /**
     * Idempotency Guard — Check if webhook event ID has already been processed for user
     *
     * @param int    $user_id
     * @param string $event_id
     * @return bool
     */
    private function is_duplicate_event( $user_id, $event_id ) {
        if ( empty( $event_id ) ) {
            return false;
        }
        $processed = get_user_meta( $user_id, 'asc_processed_event_' . $event_id, true );
        return ! empty( $processed );
    }

    /**
     * Mark event ID as processed to prevent replayed webhook duplicates
     *
     * @param int    $user_id
     * @param string $event_id
     */
    private function mark_event_processed( $user_id, $event_id ) {
        if ( ! empty( $event_id ) ) {
            update_user_meta( $user_id, 'asc_processed_event_' . $event_id, time() );
        }
    }

    /**
     * Webhook Event: checkout.session.completed
     */
    public function handle_checkout_completed( $event ) {
        $session = isset( $event->data->object ) ? $event->data->object : null;
        if ( ! $session ) {
            return;
        }
        
        $email = ! empty( $session->customer_details->email ) ? sanitize_email( $session->customer_details->email ) : '';
        if ( empty( $email ) ) {
            $email = ! empty( $session->customer_email ) ? sanitize_email( $session->customer_email ) : '';
        }
        
        if ( empty( $email ) ) {
            return;
        }

        $customer_id = ! empty( $session->customer ) ? sanitize_text_field( $session->customer ) : '';
        $subscription_id = ! empty( $session->subscription ) ? sanitize_text_field( $session->subscription ) : '';
        $event_id = ! empty( $event->id ) ? sanitize_text_field( $event->id ) : '';
        
        // WP Simple Pay metadata is passed in the event
        $metadata = isset( $session->metadata ) ? $session->metadata : new \stdClass();
        $tier = ! empty( $metadata->tier ) ? sanitize_text_field( strtolower( $metadata->tier ) ) : '';
        $type = ! empty( $metadata->type ) ? sanitize_text_field( $metadata->type ) : '';
        $category_slug = ! empty( $metadata->category_slug ) ? sanitize_title( $metadata->category_slug ) : '';

        $user = get_user_by( 'email', $email );
        if ( ! $user ) {
            $username = strstr( $email, '@', true );
            $username = sanitize_user( $username, true );
            $final_username = $username;
            $counter = 1;
            while ( username_exists( $final_username ) ) {
                $final_username = $username . $counter;
                $counter++;
            }
            $password = wp_generate_password( 16, true );
            $user_id = wp_create_user( $final_username, $password, $email );
            if ( is_wp_error( $user_id ) ) {
                return;
            }
            $user = get_userdata( $user_id );
        }
        $user_id = $user->ID;

        // Idempotency check
        if ( $this->is_duplicate_event( $user_id, $event_id ) ) {
            return;
        }
        $this->mark_event_processed( $user_id, $event_id );

        // Save Customer ID securely
        if ( ! empty( $customer_id ) ) {
            update_user_meta( $user_id, 'ascendance_stripe_customer_id', $customer_id );
            update_user_meta( $user_id, 'pmpro_stripe_customerid', $customer_id );
        }

        // Handle Category Add-on
        if ( 'category_addon' === $type && ! empty( $category_slug ) ) {
            if ( class_exists( 'Ascendance\Core\Paywall' ) ) {
                Paywall::get_instance()->grant_user_category_entitlement( $user_id, $category_slug );
            }
            if ( ! empty( $subscription_id ) ) {
                update_user_meta( $user_id, 'asc_cat_sub_' . $category_slug, $subscription_id );
                update_user_meta( $user_id, 'asc_sub_cat_' . $subscription_id, $category_slug );
            }
            $this->log_billing_activity( $user_id, 'category_addon_purchase', "Purchased category add-on: {$category_slug}" );
            return;
        }

        // Handle Tier Subscription (fallback if tier is empty is essential)
        $tier = $tier ?: 'essential';
        $role_name = 'ascendance_' . $tier;
        $wp_roles = wp_roles();
        if ( $wp_roles->is_role( $role_name ) ) {
            $user->set_role( $role_name );
        } else {
            $user->set_role( 'subscriber' );
        }

        update_user_meta( $user_id, 'ascendance_stripe_subscription_id', $subscription_id );
        update_user_meta( $user_id, 'ascendance_stripe_subscription_status', 'active' );
        update_user_meta( $user_id, 'ascendance_stripe_subscription_tier', $tier );
        
        if ( class_exists( 'Ascendance\Core\Paywall' ) ) {
            Paywall::get_instance()->invalidate_user_entitlements_cache( $user_id );
        }

        $this->log_billing_activity( $user_id, 'subscription_change', "Subscribed to " . ucfirst( $tier ) );
    }

    /**
     * Webhook Event: customer.subscription.updated / created
     */
    public function handle_subscription_updated( $event ) {
        $subscription = isset( $event->data->object ) ? $event->data->object : null;
        if ( ! $subscription ) {
            return;
        }
        $subscription_id = ! empty( $subscription->id ) ? sanitize_text_field( $subscription->id ) : '';
        $event_id = ! empty( $event->id ) ? sanitize_text_field( $event->id ) : '';
        if ( empty( $subscription_id ) ) {
            return;
        }

        // 1. Check category add-on
        $cat_users = get_users( array(
            'meta_key' => 'asc_sub_cat_' . $subscription_id,
            'number'   => 1,
        ) );

        if ( ! empty( $cat_users ) ) {
            $user = $cat_users[0];
            if ( $this->is_duplicate_event( $user->ID, $event_id ) ) {
                return;
            }
            $this->mark_event_processed( $user->ID, $event_id );

            $category_slug = get_user_meta( $user->ID, 'asc_sub_cat_' . $subscription_id, true );
            $status = ! empty( $subscription->status ) ? $subscription->status : '';
            $cancel_at_period_end = ! empty( $subscription->cancel_at_period_end );
            $current_period_end = ! empty( $subscription->current_period_end ) ? date( 'Y-m-d H:i:s', $subscription->current_period_end ) : null;

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

        // 2. Base Tier subscription
        $users = get_users( array(
            'meta_key'   => 'ascendance_stripe_subscription_id',
            'meta_value' => $subscription_id,
            'number'     => 1,
        ) );

        if ( ! empty( $users ) ) {
            $user = $users[0];
            if ( $this->is_duplicate_event( $user->ID, $event_id ) ) {
                return;
            }
            $this->mark_event_processed( $user->ID, $event_id );

            $status = ! empty( $subscription->status ) ? $subscription->status : '';
            $cancel_at_period_end = ! empty( $subscription->cancel_at_period_end );
            $period_end = ! empty( $subscription->current_period_end ) ? date( 'Y-m-d H:i:s', $subscription->current_period_end ) : '';

            if ( ! empty( $period_end ) ) {
                update_user_meta( $user->ID, 'ascendance_stripe_period_end', $period_end );
            }
            
            if ( in_array( $status, array( 'active', 'trialing' ), true ) ) {
                $local_status = $cancel_at_period_end ? 'canceling' : 'active';
                update_user_meta( $user->ID, 'ascendance_stripe_subscription_status', $local_status );
            } elseif ( 'past_due' === $status ) {
                update_user_meta( $user->ID, 'ascendance_stripe_subscription_status', 'payment_issue' );
            } else {
                $roles = (array) $user->roles;
                if ( in_array( 'administrator', $roles, true ) || in_array( 'ascendance_enterprise', $roles, true ) ) {
                    $user->remove_role( 'ascendance_essential' );
                    $user->remove_role( 'ascendance_professional' );
                } else {
                    $user->set_role( 'subscriber' );
                }
                update_user_meta( $user->ID, 'ascendance_stripe_subscription_status', 'canceled' );
            }

            if ( class_exists( 'Ascendance\Core\Paywall' ) ) {
                Paywall::get_instance()->invalidate_user_entitlements_cache( $user->ID );
            }
        }
    }

    /**
     * Webhook Event: customer.subscription.deleted
     */
    public function handle_subscription_deleted( $event ) {
        $subscription = isset( $event->data->object ) ? $event->data->object : null;
        if ( ! $subscription ) {
            return;
        }
        $subscription_id = ! empty( $subscription->id ) ? sanitize_text_field( $subscription->id ) : '';
        $event_id = ! empty( $event->id ) ? sanitize_text_field( $event->id ) : '';
        if ( empty( $subscription_id ) ) {
            return;
        }

        // 1. Category add-on check
        $cat_users = get_users( array(
            'meta_key' => 'asc_sub_cat_' . $subscription_id,
            'number'   => 1,
        ) );

        if ( ! empty( $cat_users ) ) {
            $user = $cat_users[0];
            if ( $this->is_duplicate_event( $user->ID, $event_id ) ) {
                return;
            }
            $this->mark_event_processed( $user->ID, $event_id );

            $category_slug = get_user_meta( $user->ID, 'asc_sub_cat_' . $subscription_id, true );
            if ( $category_slug && class_exists( 'Ascendance\Core\Paywall' ) ) {
                Paywall::get_instance()->revoke_user_category_entitlement( $user->ID, $category_slug );
            }
            delete_user_meta( $user->ID, 'asc_cat_sub_' . $category_slug );
            delete_user_meta( $user->ID, 'asc_sub_cat_' . $subscription_id );
            $this->log_billing_activity( $user->ID, 'category_addon_revocation', "Category add-on '{$category_slug}' canceled." );
            return;
        }

        // 2. Base Tier check
        $users = get_users( array(
            'meta_key'   => 'ascendance_stripe_subscription_id',
            'meta_value' => $subscription_id,
            'number'     => 1,
        ) );

        if ( ! empty( $users ) ) {
            $user = $users[0];
            if ( $this->is_duplicate_event( $user->ID, $event_id ) ) {
                return;
            }
            $this->mark_event_processed( $user->ID, $event_id );

            $roles = (array) $user->roles;
            if ( in_array( 'administrator', $roles, true ) || in_array( 'ascendance_enterprise', $roles, true ) ) {
                $user->remove_role( 'ascendance_essential' );
                $user->remove_role( 'ascendance_professional' );
            } else {
                $user->set_role( 'subscriber' );
            }
            update_user_meta( $user->ID, 'ascendance_stripe_subscription_status', 'canceled' );
            if ( class_exists( 'Ascendance\Core\Paywall' ) ) {
                Paywall::get_instance()->invalidate_user_entitlements_cache( $user->ID );
            }
            $this->log_billing_activity( $user->ID, 'subscription_change', "Subscription canceled / demoted." );
        }
    }

    /**
     * Webhook Event: invoice.payment_succeeded
     */
    public function handle_invoice_payment_succeeded( $event ) {
        $invoice = isset( $event->data->object ) ? $event->data->object : null;
        if ( ! $invoice || empty( $invoice->subscription ) ) {
            return;
        }
        $subscription_id = sanitize_text_field( $invoice->subscription );
        $event_id = ! empty( $event->id ) ? sanitize_text_field( $event->id ) : '';

        // Category add-on
        $cat_users = get_users( array(
            'meta_key' => 'asc_sub_cat_' . $subscription_id,
            'number'   => 1,
        ) );
        if ( ! empty( $cat_users ) ) {
            $user = $cat_users[0];
            if ( $this->is_duplicate_event( $user->ID, $event_id ) ) {
                return;
            }
            $this->mark_event_processed( $user->ID, $event_id );

            $category_slug = get_user_meta( $user->ID, 'asc_sub_cat_' . $subscription_id, true );
            if ( class_exists( 'Ascendance\Core\Paywall' ) ) {
                Paywall::get_instance()->grant_user_category_entitlement( $user->ID, $category_slug );
            }
            return;
        }

        // Base tier
        $users = get_users( array(
            'meta_key'   => 'ascendance_stripe_subscription_id',
            'meta_value' => $subscription_id,
            'number'     => 1,
        ) );
        if ( ! empty( $users ) ) {
            $user = $users[0];
            if ( $this->is_duplicate_event( $user->ID, $event_id ) ) {
                return;
            }
            $this->mark_event_processed( $user->ID, $event_id );

            update_user_meta( $user->ID, 'ascendance_stripe_subscription_status', 'active' );
            if ( class_exists( 'Ascendance\Core\Paywall' ) ) {
                Paywall::get_instance()->invalidate_user_entitlements_cache( $user->ID );
            }
        }
    }

    /**
     * Webhook Event: invoice.payment_failed
     */
    public function handle_invoice_payment_failed( $event ) {
        $invoice = isset( $event->data->object ) ? $event->data->object : null;
        if ( ! $invoice || empty( $invoice->subscription ) ) {
            return;
        }
        $subscription_id = sanitize_text_field( $invoice->subscription );
        $event_id = ! empty( $event->id ) ? sanitize_text_field( $event->id ) : '';

        $cat_users = get_users( array(
            'meta_key' => 'asc_sub_cat_' . $subscription_id,
            'number'   => 1,
        ) );
        if ( ! empty( $cat_users ) ) {
            $user = $cat_users[0];
            if ( $this->is_duplicate_event( $user->ID, $event_id ) ) {
                return;
            }
            $this->mark_event_processed( $user->ID, $event_id );

            $category_slug = get_user_meta( $user->ID, 'asc_sub_cat_' . $subscription_id, true );
            $entitlements = (array) get_user_meta( $user->ID, 'asc_category_entitlements', true );
            if ( isset( $entitlements[ $category_slug ] ) && is_array( $entitlements[ $category_slug ] ) ) {
                $entitlements[ $category_slug ]['status'] = 'payment_issue';
                update_user_meta( $user->ID, 'asc_category_entitlements', $entitlements );
            }
            if ( class_exists( 'Ascendance\Core\Paywall' ) ) {
                Paywall::get_instance()->invalidate_user_entitlements_cache( $user->ID );
            }
            return;
        }

        $users = get_users( array(
            'meta_key'   => 'ascendance_stripe_subscription_id',
            'meta_value' => $subscription_id,
            'number'     => 1,
        ) );
        if ( ! empty( $users ) ) {
            $user = $users[0];
            if ( $this->is_duplicate_event( $user->ID, $event_id ) ) {
                return;
            }
            $this->mark_event_processed( $user->ID, $event_id );

            update_user_meta( $user->ID, 'ascendance_stripe_subscription_status', 'payment_issue' );
            if ( class_exists( 'Ascendance\Core\Paywall' ) ) {
                Paywall::get_instance()->invalidate_user_entitlements_cache( $user->ID );
            }
        }
    }

    /**
     * Administrative Subscription Reconciliation Tool
     *
     * @param bool $fix Whether to auto-repair detected anomalies safely.
     * @return array Reconciliation report
     */
    public function reconcile_subscriptions( $fix = false ) {
        $report = array(
            'total_users'     => 0,
            'anomalies_count' => 0,
            'details'         => array(),
        );

        $users = get_users( array( 'number' => -1 ) );
        $report['total_users'] = count( $users );
        $sub_map = array();

        foreach ( $users as $user ) {
            $roles = (array) $user->roles;
            $tier_role = false;
            foreach ( $roles as $r ) {
                if ( 0 === strpos( $r, 'ascendance_' ) && 'ascendance_subscriber' !== $r ) {
                    $tier_role = str_replace( 'ascendance_', '', $r );
                    break;
                }
            }

            $sub_id = get_user_meta( $user->ID, 'ascendance_stripe_subscription_id', true );
            $status = get_user_meta( $user->ID, 'ascendance_stripe_subscription_status', true );

            // Track duplicate subscription IDs
            if ( ! empty( $sub_id ) ) {
                if ( isset( $sub_map[ $sub_id ] ) ) {
                    $report['anomalies_count']++;
                    $report['details'][] = sprintf( 'Duplicate subscription ID %s found on User ID %d and User ID %d', $sub_id, $sub_map[ $sub_id ], $user->ID );
                } else {
                    $sub_map[ $sub_id ] = $user->ID;
                }
            }

            // Anomaly 1: User has active tier role but status is canceled or sub_id missing
            if ( $tier_role && ( 'canceled' === $status || 'revoked' === $status || empty( $sub_id ) ) ) {
                $report['anomalies_count']++;
                $msg = sprintf( 'User ID %d (%s) has role %s but status is "%s" (Sub ID: %s)', $user->ID, $user->user_email, $tier_role, $status ?: 'none', $sub_id ?: 'none' );
                $report['details'][] = $msg;
                if ( $fix ) {
                    $user->set_role( 'subscriber' );
                    update_user_meta( $user->ID, 'ascendance_stripe_subscription_status', 'canceled' );
                }
            }

            // Anomaly 2: User has status active but role is subscriber
            if ( 'active' === $status && ! $tier_role ) {
                $report['anomalies_count']++;
                $msg = sprintf( 'User ID %d (%s) has active status but role is subscriber', $user->ID, $user->user_email );
                $report['details'][] = $msg;
                if ( $fix ) {
                    $stored_tier = get_user_meta( $user->ID, 'ascendance_stripe_subscription_tier', true ) ?: 'essential';
                    $role_name = 'ascendance_' . $stored_tier;
                    if ( wp_roles()->is_role( $role_name ) ) {
                        $user->set_role( $role_name );
                    }
                }
            }
        }

        return $report;
    }
}

Stripe_Plugin_Adapter::get_instance();
