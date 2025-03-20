<?php
    /**
 * Checkout AJAX Handler for AIO WooDiscount.
 *
 * @package AIO_WooDiscount
 */

namespace AIO_WooDiscount\Api;

defined( 'ABSPATH' ) || exit;

use AIO_WooDiscount\Api\Controllers\Discounts\All_Discount_Controller;
use AIO_WooDiscount\Api\Controllers\Discounts\Bogo_Discount_Controller;
use AIO_WooDiscount\Api\Controllers\Shared\General_Data;
use AIO_WooDiscount\Api\Controllers\Shared\Payment_Gateways_Controller;
use AIO_WooDiscount\Api\Controllers\Shared\Products_Category_Controller;
use AIO_WooDiscount\Api\Controllers\Shared\Products_Controller;
use AIO_WooDiscount\Api\Controllers\Shared\Products_Tag_Controller;
use AIO_WooDiscount\Api\Controllers\Shared\Shipping_Zone_Controller;
use AIO_WooDiscount\Api\Controllers\Shared\Users_Controller;
use AIO_WooDiscount\Api\Controllers\Discounts\FlatPercentage_Discount_Controller;
use AIO_WooDiscount\Api\Controllers\Discounts\Settings_Controller;


/**
 * Registers all REST API routes.
 */
class Api {


    /**
     * Class constructor.
     */
    public function __construct() {
        add_action( 'rest_api_init', [ $this, 'aio_register_api' ] );
    }

    /**
     * Registers REST API routes for all controllers.
     *
     * @return void
     */
    public function aio_register_api() {

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

        $settings = new Settings_Controller();
        $settings->register_routes();

        $all_discount = new All_Discount_Controller();
        $all_discount->register_routes();
    }
}
