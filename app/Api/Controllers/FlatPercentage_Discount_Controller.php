<?php

namespace AIO_WooDiscount\Api\Controllers;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use AIO_WooDiscount\Helper\FlatPercentage_Sanitization_Helper;

class FlatPercentage_Discount_Controller extends WP_REST_Controller
{
    public function __construct()
    {
        $this->namespace = 'aio-woodiscount/v2';
        $this->rest_base = 'save-flatpercentage-discount';
    }


    /**
     * Registers the routes for the objects of the controller.
     *
     * @return void
     */
    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [$this, 'save_form_data'],
                    'permission_callback' => [$this, 'permission_callback'],
                ],
            ]
        );

        // this route for fetching all discounts
        register_rest_route(
            $this->namespace,
            '/get-flatpercentage-discount',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_discounts'],
                    'permission_callback' => [$this, 'permission_callback'],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/update-flatpercentage-discount/(?P<id>[\w-]+)',
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'update_discount'],
                'permission_callback' => [$this, 'permission_callback'],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/delete-flatpercentage-discount/(?P<id>[\w-]+)',
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_discount'],
                'permission_callback' => [$this, 'permission_callback'],
            ]
        );
    }

    public function get_discounts(WP_REST_Request $request)
    {
        $discounts = get_option('aio_flatpercentage_discount', []);

        if (maybe_serialize($discounts)) {
            $discounts = maybe_unserialize($discounts);
        }

        // if (!is_array($discounts)) {
        //     $discounts = [];
        // }

        wp_send_json($discounts);
    }


    /**
     * Checks if a given request has access to create.
     * 
     *@param  \WP_REST_Request $request The request object.
     *
     * @return bool True if the user has permission, false otherwise.
     * 
     */
    public function permission_callback()
    {
        return current_user_can('manage_options');
    }


    /** 
     * Save Form Data
     *
     * @param  \WP_Rest_Request $request
     *
     * @return \WP_Rest_Response|WP_Error
     */
    public function save_form_data(WP_REST_Request $request)
    {
        $params = $request->get_json_params();

        if (empty($params)) {
            return new WP_Error(
                'missing_data',
                __('No data received.', 'aio-woodiscount'),
                ['status' => 400]
            );
        }

        error_log("🔴 RAW DATA RECEIVED: " . print_r($params, true));

        // Get existing discounts
        $existing_data = get_option('aio_flatpercentage_discount', []);

        if (!is_array($existing_data)) {
            $existing_data = maybe_unserialize($existing_data);

            if (!is_array($existing_data)) {
                $existing_data = [];
            }
        }

        //Sanitize received data
        $sanitized_data = FlatPercentage_Sanitization_Helper::FlatPercentage_Data_Sanitization($params);

        if (is_wp_error($sanitized_data)) {
            return $sanitized_data;
        }

        // Append new data
        $existing_data[] = $sanitized_data;

        // Save to Database
        $saved = update_option('aio_flatpercentage_discount', maybe_serialize($existing_data));

        if (!$saved) {
            return new WP_Error(
                'save_failed',
                __('Failed to save data.', 'aio-woodiscount'),
                ['status' => 500]
            );
        }

        error_log("🟠 SANITIZED DATA TO SAVE: " . print_r($existing_data, true));

        return new WP_REST_Response(
            ['success' => true, 'message' => __('Data saved successfully.', 'aio-woodiscount')],
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
    public function update_discount(WP_REST_Request $request)
    {
        $id     = $request->get_param('id');
        $params = $request->get_json_params();
        if (empty($params)) {
            return new WP_Error('missing_data', __('No data received.', 'aio-woodiscount'), ['status' => 400]);
        }

        $existing_data = get_option('aio_flatpercentage_discount', []);
        $existing_data = maybe_unserialize($existing_data);
        if (!is_array($existing_data)) {
            $existing_data = [];
        }

        $updated = false;
        foreach ($existing_data as &$discount) {
            if (isset($discount['id']) && $discount['id'] === $id) {
                $discount = $params;
                $updated  = true;
                break;
            }
        }

        if ($updated) {

            $saved = update_option('aio_flatpercentage_discount', maybe_serialize($existing_data));
            if ($saved) {
                return new WP_REST_Response(['success' => true, 'message' => __('Data updated successfully.', 'aio-woodiscount')], 200);
            } else {
                return new WP_Error('save_failed', __('Failed to save data.', 'aio-woodiscount'), ['status' => 500]);
            }
        }

        return new WP_Error('not_found', __('Discount rule not found.', 'aio-woodiscount'), ['status' => 404]);
    }


    public function delete_discount(WP_REST_Request $request)
    {
        $id = $request->get_param('id');

        // ✅ Ensure existing data is an array
        $existing_data = get_option('aio_flatpercentage_discount', []);

        if (!is_array($existing_data)) {
            $existing_data = maybe_unserialize($existing_data);
        }

        if (!is_array($existing_data)) {
            return new WP_Error('invalid_data', __('Stored discount data is corrupted.', 'aio-woodiscount'), ['status' => 500]);
        }

        // ✅ Find the discount with matching ID
        $existing_data = array_filter($existing_data, function ($discount) use ($id) {
            return isset($discount['id']) && $discount['id'] !== $id;
        });

        // ✅ Re-index array after filtering
        $existing_data = array_values($existing_data);

        // ✅ Save updated data
        update_option('aio_flatpercentage_discount', maybe_serialize($existing_data));

        return new WP_REST_Response(['success' => true, 'message' => __('Data deleted successfully.', 'aio-woodiscount')], 200);
    }
}
