<?php

namespace AIO_WooDiscount\Api\Controllers;

use WP_REST_Controller;
use WP_REST_Server;
use WP_Error;

/**
 * Users Controller Class
 */
class Users_Controller extends WP_REST_Controller
{

    public function __construct()
    {
        $this->namespace = 'aio-woodiscount/v1';
        $this->rest_base = 'customers';
    }


    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'  => WP_REST_Server::READABLE,
                    'callback'  => [$this, 'get_customers'],
                    'permission_callback' => [$this, 'get_customer_permission'],
                ]
            ]
        );
    }
}
