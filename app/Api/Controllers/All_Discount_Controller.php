<?php

namespace AIO_WooDiscount\Api\Controllers;

use WP_REST_Controller;
use WP_REST_Server;

class All_Discount_Controller extends WP_REST_Controller
{

    public function __construct()
    {
        $this->namespace = 'aio-woodiscount/v2';
        $this->rest_base = 'get-all-discounts';
    }

    public function register_routes()
    {
        register_rest_route($this->namespace, $this->rest_base, array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_all_discounts'),
            'permission_callback' => '__return_true',
        ));
    }

    public function get_all_discounts()
    {
        // Fetch discount data from all three sources
        $flatpercentage_discounts = maybe_unserialize(get_option('aio_flatpercentage_discount', []));
        $bogo_discounts = maybe_unserialize(get_option('aio_bogo_discount', []));


        // Ensure they are arrays
        $flatpercentage_discounts = is_array($flatpercentage_discounts) ? $flatpercentage_discounts : [];

        $bogo_discounts = is_array($bogo_discounts) ? $bogo_discounts : [];




        // Merge all discount arrays
        $all_discounts = array_merge($flatpercentage_discounts, $bogo_discounts);

        // Sort discounts by `createdAt` (newest first)
        usort($all_discounts, function ($a, $b) {
            return strtotime($a['createdAt'] ?? 0) - strtotime($b['createdAt'] ?? 0);
        });

        return rest_ensure_response($all_discounts);
    }
}
