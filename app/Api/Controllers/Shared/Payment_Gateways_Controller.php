<?php
/**
 * Payment Gateways REST API Controller.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Api\Controllers\Shared;

defined( 'ABSPATH' ) || exit;

use WP_REST_Controller;
use WP_REST_Server;

/**
 * Payment Gateway Controller Class
 */

class Payment_Gateways_Controller extends WP_REST_Controller {

    public function __construct() {
        $this->namespace = 'gwpdr-discountrules/v2';
        $this->rest_base = 'payment-gateways';
    }

    /**
     * Registers the routes for the objects of the controller.
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_payment_gateways' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                ]
            ]
        );
    }


    /**
     * Checks if the current user has permission to access the API.
     *  @param  \WP_REST_Request $request The request object.
     *@return bool True if the user has permission, false otherwise.
     */
    public function permission_check() {
        if ( current_user_can( 'manage_woocommerce' ) ) {
            return true;
        };

        return true;
    }

    /**
     * Retrieves the list of all payment gateways, including enabled and disabled ones.
     */
    public function get_payment_gateways( $request ) {
        if ( ! class_exists( 'WC_Payment_Gateways' ) ) {
            return new \WP_Error(
                'woocommerce_inactive',
                __( 'WooCommerce is not active.', 'giantwp-discount-rules' ),
                ['status' => 500]
            );
        }

        // Retrieve all payment gateways
        $payment_gateways = WC()->payment_gateways->payment_gateways();

        if ( empty( $payment_gateways ) ) {
          return [
         'message' => __( 'No payment gateways available.', 'giantwp-discount-rules' ),
        ];
        }

        $data = [];
        foreach ( $payment_gateways as $gateway ) {
            $data[] = [
                'id' => $gateway->id,
                'title'        => $gateway->get_title(),
                'description'  => $gateway->get_description(),
                'method_title' => $gateway->get_method_title(),
                'enabled'      => $gateway->enabled === 'yes', // True if enabled
            ];
        }

        return rest_ensure_response( $data );
    }
}
