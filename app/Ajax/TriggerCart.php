<?php
/**
 * Cart Discount Trigger AJAX Handler.
 *
 * @package AIO_DiscountRules
 */

namespace AIO_DiscountRules\Ajax;

use AIO_DiscountRules\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

/**
 * Class TriggerCart
 *
 * Handles AJAX request to re-check and apply cart-level discounts.
 */
class TriggerCart{

    use SingletonTrait;

    /**
     * Constructor to register AJAX actions.
     */
    public function __construct() {
        add_action( 'wp_ajax_aio_check_cart_discounts', [ $this, 'aio_check_cart_discounts' ] );
        add_action( 'wp_ajax_nopriv_aio_check_cart_discounts', [ $this, 'aio_check_cart_discounts' ] );
    }

    /**
     * AJAX callback to re-trigger flat/percentage discount evaluation.
     *
     * @return void
     */
    public function aio_check_cart_discounts() {

        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

        if ( ! $nonce || ! wp_verify_nonce( $nonce, 'aio_trigger_nonce' ) ) {
            wp_send_json_error( [ 'message' => 'Invalid nonce' ] );
        }
        

        if ( ! WC()->cart ) {
            wp_send_json_error( ['message' => 'Cart not found'] );
        }

        // Apply discount logic
        (new \AIO_WooDiscount\Discount\FlatPercentage_Discount())->maybe_apply_discount(true);

        wp_send_json_success( ['message' => 'Discount checked'] );
    }
}
