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
        add_action( 'wp_ajax_gwpdr_set_payment_method', [ $this, 'gwpdr_set_payment_method' ] );
        add_action( 'wp_ajax_nopriv_gwpdr_set_payment_method', [ $this, 'gwpdr_set_payment_method' ] );
    }

    /**
     * AJAX callback: Sets selected payment method in WooCommerce session.
     *
     * @return void
     */
    public function gwpdr_set_payment_method() {
        check_ajax_referer( 'gwpdr_nonce', 'security' );

        // Ensure WC session is initialized in AJAX context
        if ( null === WC()->session || ! WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie( true );
        }

        $method = isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : '';


        if ( empty( $method ) ) {
             wp_send_json_error( [ 'message' => __( 'No payment method provided.', 'giantwp-discount-rules' ) ] );
        }

        WC()->session->set( 'gwpdr_selected_payment_method', $method );
         
          wp_send_json_success( [ 'message' => __( 'Payment method saved successfully.', 'giantwp-discount-rules' ) ] );
    }
}
