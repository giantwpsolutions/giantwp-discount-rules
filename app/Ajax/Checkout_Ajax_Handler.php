<?php
  /**
 * Checkout AJAX Handler for GiantWP Discount Rules.
 *
 * @package GiantWP_Discount_Rules
 */

 namespace GiantWP_Discount_Rules\Ajax;

 defined( 'ABSPATH' ) || exit;
 
 use GiantWP_Discount_Rules\Traits\SingletonTrait;

/**
 * Class Checkout_Ajax_Handler
 *
 * Handles frontend AJAX actions like setting payment method at checkout.
 */
class Checkout_Ajax_Handler {

    use SingletonTrait;

    /**
     * Constructor to register AJAX handlers
     */
    public function __construct() {
        add_action( 'wp_ajax_gwp_set_payment_method', [ $this, 'gwp_set_payment_method' ] );
        add_action( 'wp_ajax_nopriv_gwp_set_payment_method', [ $this, 'gwp_set_payment_method' ] );
    }

    /**
     * AJAX callback: Sets selected payment method in WooCommerce session.
     *
     * @return void
     */
    public function gwp_set_payment_method() {
        check_ajax_referer( 'gwp_nonce', 'security' );

        // Ensure WC session is initialized in AJAX context
        if ( null === WC()->session || ! WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie( true );
        }

        $method = isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : '';


        if ( empty( $method ) ) {
            wp_send_json_error( ['message' => 'No method provided'] );
        }

        WC()->session->set( 'gwp_selected_payment_method', $method );
        wp_send_json_success( ['message' => 'Payment method stored'] );
    }
}
