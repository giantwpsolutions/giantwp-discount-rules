<?php
  /**
 * BOGO Discount Usage Tracking.
 *
 * Tracks usage count of BOGO discount rules after order completion.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Discount\UsageTrack;

defined( 'ABSPATH' ) || exit;

use GiantWP_Discount_Rules\Traits\SingletonTrait;
use WC_Order;
use WC;

/**
 * Class Bogo_Usage_Handler
 *
 * Handles tracking of Buy One Get One (Bogo) discount usage count.
 *
 * @package GiantWP_Discount_Rules\Discount\UsageTrack
 */
class Bogo_Usage_Handler {

    use SingletonTrait;
    /**
     * Constructor - Hooks into WooCommerce events for Bogo tracking.
     */
    public function __construct() {



        // Save applied rules from session to order meta when order is created
        add_action( 'woocommerce_new_order', [ $this, 'store_applied_rules_to_order' ], 20, 1 );

        // Increase usage count after payment is completed
        add_action( 'woocommerce_payment_complete', [ $this, 'increase_usage_from_order' ] );

        // Handle gateways that require no payment (e.g., BACS)
        add_filter( 'woocommerce_checkout_no_payment_needed_redirect', [ $this, 'increase_usage_no_payment' ], 10, 2 );
    }

    /**
     * Save Bogo rule IDs from session to order meta.
     *
     * @param int $order_id WooCommerce order ID.
     */
    public function store_applied_rules_to_order( $order_id ) {
        $session_rules = WC()->session->get( '_gwpdr_bogo_applied_rules' );

        if (! empty( $session_rules ) && is_array( $session_rules ) ) {
            if ( function_exists( 'gwpdr_check_woocommerce_hpos' ) && gwpdr_check_woocommerce_hpos() ) {
                $order = wc_get_order( $order_id );
                if ( $order instanceof WC_Order ) {
                    $order->update_meta_data( '_gwpdr_bogo_applied_rules', $session_rules );
                    $order->save();
                }
            } else {
                update_post_meta( $order_id, '_gwpdr_bogo_applied_rules', $session_rules );
            }
        }
    }

    /**
     * Increase usage count from Bogo rules stored in the order meta.
     *
     * @param int $order_id WooCommerce order ID.
     */
    public function increase_usage_from_order( $order_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }

        $applied_rules = $order->get_meta( '_gwpdr_bogo_applied_rules' );

        if ( empty( $applied_rules ) || ! is_array( $applied_rules ) ) {
            return;
        }

        $rules   = maybe_unserialize( get_option( 'giantwp_bogo_discount', [] ) );
        $updated = false;

        foreach ( $rules as &$rule ) {
            if ( in_array( $rule['id'], $applied_rules, true ) ) {
                $rule['usedCount'] = isset( $rule['usedCount'] ) ? intval( $rule['usedCount'] ) + 1 : 1;
                $updated           = true;
            }
        }

        if ( $updated ) {
            update_option( 'giantwp_bogo_discount', maybe_serialize( $rules ) );
        }

        // Unset the session after processing
        if ( WC()->session ) {
            WC()->session->__unset( '_gwpdr_bogo_applied_rules' );
        }
    }

    /**
     * Fallback usage tracking for no-payment gateways (like Bank Transfer).
     *
     * @param string   $url   Redirect URL.
     * @param WC_Order $order WooCommerce Order object.
     *
     * @return string Redirect URL.
     */
    public function increase_usage_no_payment( $url, $order ) {
        if ( $order instanceof WC_Order ) {
            $this->increase_usage_from_order( $order->get_id() );
        }

        return $url;
    }
}
