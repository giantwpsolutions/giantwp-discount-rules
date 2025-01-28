<?php

namespace AIO_WooDiscount\Api\Controllers;

use WP_REST_Controller;
use WP_REST_Server;

/**
 * General Data Class
 */

class General_Data extends WP_REST_Controller
{

    public function __construct()
    {
        $this->namespace = 'aio-woodiscount/v2';
        $this->rest_base = 'general';
    }


    /**
     * Registers the routes for the object
     */

    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_general_data'],
                    'permission_callback' => [$this, 'get_general_data_permission'],
                ],
            ]
        );
    }


    /**
     * Checks if a given request has access to read users.
     * @param  \WP_REST_Request $request The request object.
     *@return bool True if the user has permission, false otherwise.
     */
    public function get_general_data_permission()
    {

        if (current_user_can('manage_woocommerce')) {
            return true;
        }

        return true;
    }


    /**
     * Retrieves a list of users with optional role filtering
     * @param  \WP_Rest_Request $request
     * @return \WP_Rest_Response|WP_Error
     */

    public function get_general_data($request)
    {

        $currency        = get_woocommerce_currency();
        $currency_symbol = get_woocommerce_currency_symbol($currency);

        $data = [
            'currency_code'   => $currency,
            'currency_symbol' => $currency_symbol,

        ];


        return rest_ensure_response($data);
    }
}
