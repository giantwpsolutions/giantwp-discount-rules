<?php
  /**
 * BOGO Discount REST API Controller.
 *
 * @package AIO_DiscountRules
 */

namespace AIO_DiscountRules\Api\Controllers\Discounts;

defined( 'ABSPATH' ) || exit;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

use AIO_DiscountRules\Helper\Sanitization\Bogo_Sanitization_Helper;

/**
 * Bogo Data Save Controller class
 */

class Bogo_Discount_Controller extends WP_REST_Controller {

    public function __construct() {
        $this->namespace = 'aio-discountrules/v2';
        $this->rest_base = 'save-bogo-discount';
    }

    /**
     * Registers the routes for the objects of the controller.
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'save_form_data' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],
            ]
        );

        // this route for fetching all discounts
        register_rest_route(
            $this->namespace,
            '/get-bogo-discount',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_discounts' ],
                    'permission_callback' => [ $this, 'permission_callback' ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/update-bogo-discount/(?P<id>[\w-]+)',
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'update_discount' ],
                'permission_callback' => [ $this, 'permission_callback' ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/delete-bogo-discount/(?P<id>[\w-]+)',
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_discount' ],
                'permission_callback' => [ $this, 'permission_callback' ],
            ]
        );
    }


    /**
     * Checks if a given request has access.
     * 
     *@param  \WP_REST_Request $request The request object.
     *
     * @return bool True if the user has permission, false otherwise.
     * 
     */
    public function permission_callback() {
        return current_user_can( 'manage_options' );
    }



    /** 
     * Save Form Data
     *
     * @param  \WP_Rest_Request $request
     *
     * @return \WP_Rest_Response|WP_Error
     */
    public function save_form_data( WP_REST_Request $request ) {
        $params = $request->get_json_params();

        if ( empty( $params ) ) {
            return new WP_Error(
                'missing_data',
                __( 'No data received.', 'all-in-one-discount-rules' ),
                ['status' => 400]
            );
        }

        // Get existing discounts
        $existing_data = get_option( 'aio_bogo_discount', [] );

        if ( ! is_array( $existing_data ) ) {
            $existing_data = maybe_unserialize( $existing_data );

            if ( ! is_array( $existing_data ) ) {
                $existing_data = [];
            }
        }

        //Sanitize received data
        $sanitized_data = Bogo_Sanitization_Helper::Bogo_Data_Sanitization( $params );

        if ( is_wp_error( $sanitized_data ) ) {
            return $sanitized_data;
        }

        // Append new data
        $existing_data[] = $sanitized_data;

        // Save to Database
        $saved = update_option( 'aio_bogo_discount', maybe_serialize( $existing_data ) );

        if ( ! $saved ) {
            return new WP_Error(
                'save_failed',
                __( 'Failed to save data.', 'all-in-one-discount-rules' ),
                ['status' => 500]
            );
        }

        return new WP_REST_Response(
            [ 'success' => true, 'message' => __( 'Data saved successfully.', 'all-in-one-discount-rules' ) ],
            200
        );
    }


    /**
     * Update an existing discount rule.
     *
     * @param WP_REST_Request $request The request object.
     *
     * @return WP_REST_Response|WP_Error The response object.
     */
    public function update_discount( WP_REST_Request $request ) {
        // Sanitize ID from the URL
        $id = sanitize_text_field( $request->get_param('id') );

        // Get JSON Data
        $params = $request->get_json_params();
        if ( empty( $params ) ) {
            return new WP_Error( 'missing_data', __( 'No data received.', 'all-in-one-discount-rules' ), ['status' => 400] );
        }

        // Retrieve Existing BOGO Discounts
        $existing_data = get_option( 'aio_bogo_discount', [] );
        if ( ! is_array( $existing_data ) ) {
            $existing_data = maybe_unserialize( $existing_data );
        }
        if ( ! is_array( $existing_data ) ) {
            $existing_data = [];
        }

        // Find and Update the Discount by ID 
        $updated = false;

        foreach ( $existing_data as &$discount ) {
            if ( isset( $discount['id'] ) && $discount['id'] === $id ) {
                // If only status is being updated, update it separately to prevent data reset
                if ( isset( $params['status'] ) && count( $params ) === 1 ) {
                    $discount['status'] = sanitize_text_field( $params['status'] );
                } else {
                    // Sanitize the received data for a full update
                    $sanitized_data = Bogo_Sanitization_Helper::Bogo_Data_Sanitization( $params );
                    if ( is_wp_error( $sanitized_data ) ) {
                        return $sanitized_data;
                    }

                    // Merge sanitized data with existing discount (keeping original values where necessary)
                    $discount = array_merge( $discount, $sanitized_data );
                }

                $updated = true;
                break; 
            }
        }

        if ( $updated ) {
            update_option( 'aio_bogo_discount', maybe_serialize( $existing_data ) );
            return new WP_REST_Response( [ 'success' => true, 'message' => __( 'Data updated successfully.', 'all-in-one-discount-rules' ) ], 200 );
        }

        return new WP_Error( 'not_found', __( 'Discount rule not found.', 'all-in-one-discount-rules' ), ['status' => 404] );
    }


    /** 
     * Delete Bogo Discount Data
     *
     * @param  \WP_Rest_Request $request
     *
     * @return \WP_Rest_Response|WP_Error
     */
    public function delete_discount( WP_REST_Request $request ) {
        $id = $request->get_param('id');

        // Ensure existing data is an array
        $existing_data = get_option( 'aio_bogo_discount', [] );

        if ( ! is_array( $existing_data ) ) {
            $existing_data = maybe_unserialize( $existing_data );
        }

        if ( ! is_array( $existing_data ) ) {
            return new WP_Error( 'invalid_data', __( 'Stored discount data is corrupted.', 'all-in-one-discount-rules' ), ['status' => 500] );
        }

        // Find the discount with matching ID
        $existing_data = array_filter( $existing_data, function ( $discount ) use ( $id ) {
            return isset( $discount['id'] ) && $discount['id'] !== $id;
        });

        // Re-index array after filtering
        $existing_data = array_values( $existing_data );

        // Save updated data
        update_option( 'aio_bogo_discount', maybe_serialize( $existing_data ) );

        return new WP_REST_Response( [ 'success' => true, 'message' => __( 'Data deleted successfully.', 'all-in-one-discount-rules' ) ], 200);
    }


    /** 
     * Get Bogo Discount Data
     *
     * @param  \WP_Rest_Request $request
     *
     * @return \WP_Rest_Response|WP_Error
     */

    public function get_discounts( WP_REST_Request $request )  {
        $discounts = get_option( 'aio_bogo_discount', [] );

        if ( maybe_serialize( $discounts ) ) {
            $discounts = maybe_unserialize( $discounts );
        }

        wp_send_json( $discounts );
    }
}
