<?php
  /**
 * Checkout AJAX Handler for DealBuilder Discount Rules.
 *
 * @package DealBuilder_Discount_Rules
 */

 namespace DealBuilder_Discount_Rules\Ajax;

 defined( 'ABSPATH' ) || exit;
 
 use DealBuilder_Discount_Rules\Traits\SingletonTrait;

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
        add_action( 'wp_ajax_db_set_payment_method', [ $this, 'db_set_payment_method' ] );
        add_action( 'wp_ajax_nopriv_db_set_payment_method', [ $this, 'db_set_payment_method' ] );
    }

    /**
     * AJAX callback: Sets selected payment method in WooCommerce session.
     *
     * @return void
     */
    public function db_set_payment_method() {
        check_ajax_referer( 'db_nonce', 'security' );

        // Ensure WC session is initialized in AJAX context
        if ( null === WC()->session || ! WC()->session->has_session() ) {
            WC()->session->set_customer_session_cookie( true );
        }

        $method = isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : '';


        if ( empty( $method ) ) {
            wp_send_json_error( ['message' => 'No method provided'] );
        }

        WC()->session->set( 'db_selected_payment_method', $method );
        wp_send_json_success( ['message' => 'Payment method stored'] );
    }
}
