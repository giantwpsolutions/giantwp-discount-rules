<?php

namespace AIO_WooDiscount\Api;

use AIO_WooDiscount\Api\Controllers\Payment_Gateways_Controller;
use AIO_WooDiscount\Api\Controllers\Products_Category_Controller;
use AIO_WooDiscount\Api\Controllers\Products_Controller;
use AIO_WooDiscount\Api\Controllers\Products_Tag_Controller;
use AIO_WooDiscount\Api\Controllers\Users_Controller;

/**
 * Rest API Class
 */
class Api
{

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_api']);
    }

    public function register_api()
    {

        $product_controller = new Products_Controller();
        $product_controller->register_routes();

        $users_controller = new Users_Controller();
        $users_controller->register_routes();

        $product_category = new Products_Category_Controller();
        $product_category->register_routes();


        $product_tag = new Products_Tag_Controller();
        $product_tag->register_routes();

        $paymentGatewayController = new Payment_Gateways_Controller();
        $paymentGatewayController->register_routes();
    }
}
