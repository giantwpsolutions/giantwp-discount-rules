<?php

namespace AIO_WooDiscount\Api;

use AIO_WooDiscount\Api\Controllers\General_Data;
use AIO_WooDiscount\Api\Controllers\Payment_Gateways_Controller;
use AIO_WooDiscount\Api\Controllers\Products_Category_Controller;
use AIO_WooDiscount\Api\Controllers\Products_Controller;
use AIO_WooDiscount\Api\Controllers\Products_Tag_Controller;
use AIO_WooDiscount\Api\Controllers\Shipping_Zone_Controller;
use AIO_WooDiscount\Api\Controllers\Users_Controller;
use AIO_WooDiscount\Api\Controllers\Save_Data;



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

        $ShippingZoneController = new Shipping_Zone_Controller();
        $ShippingZoneController->register_routes();

        $generalData = new General_Data();
        $generalData->register_routes();

        $save_data = new Save_Data();
        $save_data->register_routes();
    }
}
