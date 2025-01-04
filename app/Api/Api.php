<?php 

namespace AIO_WooDiscount\Api;
use AIO_WooDiscount\Api\Controllers\Products_Controller;
/**
 * Rest API Class
 */
class Api{

    public function __construct ()
    {
        add_action( 'rest_api_init', [ $this, 'register_api'] );
    }

    public function register_api(){

        $product_controller = new Products_Controller();
        $product_controller->register_routes();
    }
}