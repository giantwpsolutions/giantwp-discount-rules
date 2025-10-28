<?php
/**
 * Cart Discount Trigger AJAX Handler.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Ajax;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

class TriggerCart {

    use SingletonTrait;

    public function __construct() {
        add_action( 'wp_ajax_gwpdr_check_cart_discounts', [ $this, 'gwpdr_check_cart_discounts' ] );
        add_action( 'wp_ajax_nopriv_gwpdr_check_cart_discounts', [ $this, 'gwpdr_check_cart_discounts' ] );
    }

    public function gwpdr_check_cart_discounts() {

        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'gwpdr_trigger_nonce' ) ) {
            wp_send_json_error( [
                'message' => __( 'Invalid request. Please refresh and try again.', 'giantwp-discount-rules' ),
            ] );
        }

        if ( ! WC()->cart ) {
            wp_send_json_error( [
                'message' => __( 'Cart not found.', 'giantwp-discount-rules' ),
            ] );
        }

        // Apply discount logic
        ( new \GiantWP_Discount_Rules\Discount\FlatPercentage_Discount() )->maybe_apply_discount( true );

        wp_send_json_success( [
            'message' => __( 'Discounts recalculated successfully.', 'giantwp-discount-rules' ),
        ] );
    }
}

