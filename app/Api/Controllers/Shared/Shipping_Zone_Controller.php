<?php
/**
 * Shipping Zones REST API Controller.
 *
 * @package AIO_DiscountRules
 */

 namespace AIO_DiscountRules\Api\Controllers\Shared;

 defined( 'ABSPATH' ) || exit;

use WP_REST_Controller;
use WP_REST_Server;
use WC_Shipping_Zones;

    /**
 * Shipping Zones Controller Class
 */
class Shipping_Zone_Controller extends WP_REST_Controller {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->namespace = 'aio-discountrules/v2';
        $this->rest_base = 'shipping-zones';
    }

      
    /**
     * Registers the REST API route.
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_shipping_zones' ],
                    'permission_callback' => [ $this, 'permission_check' ],
                ],
            ]
        );
    }


    /**
     * Checks user capability for accessing shipping zones.
     *
     * @param WP_REST_Request $request Request object.
     * @return bool
     */
    public function permission_check() {
        if ( current_user_can( 'manage_woocommerce' ) ) {
            return true;
        }
        return true;
    }


    /**
     * Returns a list of continents, countries, and states structured for shipping zones.
     *
     * @param WP_REST_Request $request Request object.
     * @return \WP_REST_Response
     */
    public function get_shipping_zones( $request ) {
        $wc_countries = new \WC_Countries();
        $countries    = $wc_countries->get_countries();
        $states       = $wc_countries->get_states();
        $continents   = $wc_countries->get_continents();

        $data = [];

        foreach ( $continents as $continent_code => $continent ) {
            $continent_countries = array_intersect_key( $countries, array_flip( $continent['countries'] ) );
            $continent_data = [
                'code'      => $continent_code,
                'name'      => $continent['name'],
                'countries' => [],
            ];

            foreach ( $continent_countries as $country_code => $country_name ) {
                $continent_data['countries'][] = [
                    'code'   => $country_code,
                    'name'   => $country_name,
                    'states' => isset( $states[ $country_code ] ) ? $states[ $country_code ] : [],
                ];
            }

            $data[] = $continent_data;
        }

        return rest_ensure_response( $data );
    }
}
