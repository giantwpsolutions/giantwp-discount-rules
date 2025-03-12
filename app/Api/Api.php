<?php

namespace AIO_WooDiscount\Api;

use AIO_WooDiscount\Api\Controllers\All_Discount_Controller;
use AIO_WooDiscount\Api\Controllers\Bogo_Discount_Controller;
use AIO_WooDiscount\Api\Controllers\Bulk_Discount_Controller;
use AIO_WooDiscount\Api\Controllers\BuyXGetY_Discount_Controller;
use AIO_WooDiscount\Api\Controllers\General_Data;
use AIO_WooDiscount\Api\Controllers\Payment_Gateways_Controller;
use AIO_WooDiscount\Api\Controllers\Products_Category_Controller;
use AIO_WooDiscount\Api\Controllers\Products_Controller;
use AIO_WooDiscount\Api\Controllers\Products_Tag_Controller;
use AIO_WooDiscount\Api\Controllers\Shipping_Zone_Controller;
use AIO_WooDiscount\Api\Controllers\Users_Controller;
use AIO_WooDiscount\Api\Controllers\FlatPercentage_Discount_Controller;
use AIO_WooDiscount\Api\Controllers\Shipping_Discount_Controller;

/**
 * Rest API Class
 */
class Api
{

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'aio_register_api']);
    }

    public function aio_register_api()
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

        $FlatPercentage_Discount_Controller = new FlatPercentage_Discount_Controller();
        $FlatPercentage_Discount_Controller->register_routes();

        $Bogo_Discount_Controller = new Bogo_Discount_Controller();
        $Bogo_Discount_Controller->register_routes();

        $all_discount = new All_Discount_Controller();
        $all_discount->register_routes();

        $shipping_discount = new Shipping_Discount_Controller();
        $shipping_discount->register_routes();

        $buyxgety_discount = new BuyXGetY_Discount_Controller();
        $buyxgety_discount->register_routes();

        $bulk_discount = new Bulk_Discount_Controller();
        $bulk_discount->register_routes();
    }
}
