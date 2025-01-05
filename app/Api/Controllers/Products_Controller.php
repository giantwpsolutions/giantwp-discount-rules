<?php

namespace AIO_WooDiscount\Api\Controllers;

use WP_REST_Controller;
use WP_REST_Server;

/**
 * Product Controller Class
 */

class Products_Controller extends WP_REST_Controller
{

    public function __construct()
    {
        $this->namespace = 'aio-woodiscount/v1';
        $this->rest_base = 'products';
    }

    /**
     * Registers the routes for the objects of the controller.
     *
     * @return void
     */
    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_products_item'],
                    'permission_callback' => [$this, 'get_products_item_permission'],
                ]
            ]
        );
    }


    /**
     * Checks if a given request has access to read products.
     *
     * @param  \WP_REST_Request $request The request object.
     * @return bool True if the user has permission, false otherwise.
     */
    public function get_products_item_permission()
    {
        // if (current_user_can('manage_woocommerce')) {
        //     return true;
        // }

        return true;
    }


    /** 
     * Retrieves list of Products
     *
     * @param  \WP_Rest_Request $request
     *
     * @return \WP_Rest_Response|WP_Error
     */

    public function get_products_item($request)
    {

        $args = [
            'status' => 'publish',
            'limit' => -1,
        ];

        $products = wc_get_products($args);

        $data = [];
        foreach ($products as $product) {
            $data[] = [
                'id'    => $product->get_id(),
                'name'  => $product->get_name(),
                'price' => $product->get_price(),
            ];
        }

        return rest_ensure_response($data);
    }
}
