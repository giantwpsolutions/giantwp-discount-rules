<?php
  /**
 * All Discount REST API Controller
 *
 * @package DealBuilder_Discount_Rules
 */

namespace DealBuilder_Discount_Rules\Api\Controllers\Discounts;

defined( 'ABSPATH' ) || exit;

use WP_REST_Controller;
use WP_REST_Server;

/**
 * REST API Controller to fetch all discount types (Flat, BOGO, Shipping, BXGY, Bulk).
 */
class All_Discount_Controller extends WP_REST_Controller {

    /**
     * Constructor to set namespace and rest base.
     */
    public function __construct() {
        $this->namespace = 'db-discountrules/v2';
        $this->rest_base = 'get-all-discounts';
    }

    /**
     * Register the REST route for retrieving all discounts.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, $this->rest_base, array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => array($this, 'get_all_discounts'),
            'permission_callback' => '__return_true', 
        ) );
    }

    /**
     * Callback for fetching all discount types from plugin options.
     *
     * @return \WP_REST_Response
     */
    public function get_all_discounts() {
        // Fetch discount data from all three sources
        $flatpercentage_discounts = maybe_unserialize( get_option( 'dealbuilder_flatpercentage_discount', [] ) );
        $bogo_discounts           = maybe_unserialize( get_option( 'dealbuilder_bogo_discount', [] ) );
        $shipping_discounts       = maybe_unserialize( get_option( 'dealbuilder_shipping_discount', [] ) );
        $bxgy_discounts           = maybe_unserialize( get_option( 'dealbuilder_bxgy_discount', [] ) );
        $bulk_discounts           = maybe_unserialize( get_option( 'dealbuilder_bulk_discount', [] ) );

        // Ensure they are arrays
        $flatpercentage_discounts = is_array( $flatpercentage_discounts ) ? $flatpercentage_discounts : [];

        $bogo_discounts     = is_array( $bogo_discounts ) ? $bogo_discounts : [];
        $shipping_discounts = is_array( $shipping_discounts ) ? $shipping_discounts : [];
        $bxgy_discounts     = is_array( $bxgy_discounts ) ? $bxgy_discounts : [];
        $bulk_discounts     = is_array( $bulk_discounts ) ? $bulk_discounts : [];

        // Merge all discount arrays
        $all_discounts = array_merge( $flatpercentage_discounts, $bogo_discounts, $shipping_discounts, $bxgy_discounts, $bulk_discounts );

        // Sort discounts by `createdAt` (newest first)
        usort( $all_discounts, function ( $a, $b ) {
            return strtotime( $a['createdAt'] ?? 0) - strtotime( $b['createdAt'] ?? 0 );
        });

        return rest_ensure_response( $all_discounts );
    }
}
