<?php

namespace AIO_WooDiscount\Api\Controllers;

use WP_REST_Controller;
use WP_REST_Server;
use WC_Shipping_Zones;

/**
 * Shipping Zones Controller Class
 */
class Shipping_Zone_Controller extends WP_REST_Controller
{
    public function __construct()
    {
        $this->namespace = 'aio-woodiscount/v1';
        $this->rest_base = 'shipping-zones';
    }

    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_shipping_zones'],
                    'permission_callback' => [$this, 'permission_check'],
                ],
            ]
        );
    }

    public function permission_check()
    {
        if (current_user_can('manage_woocommerce')) {
            return true;
        }
        return true;
    }

    public function get_shipping_zones($request)
    {
        $wc_countries = new \WC_Countries();
        $countries    = $wc_countries->get_countries();
        $states       = $wc_countries->get_states();
        $continents   = $wc_countries->get_continents();

        $data = [];

        foreach ($continents as $continent_code => $continent) {
            $continent_countries = array_intersect_key($countries, array_flip($continent['countries']));
            $continent_data = [
                'code'      => $continent_code,
                'name'      => $continent['name'],
                'countries' => [],
            ];

            foreach ($continent_countries as $country_code => $country_name) {
                $continent_data['countries'][] = [
                    'code'   => $country_code,
                    'name'   => $country_name,
                    'states' => isset($states[$country_code]) ? $states[$country_code] : [],
                ];
            }

            $data[] = $continent_data;
        }

        return rest_ensure_response($data);
    }
}
