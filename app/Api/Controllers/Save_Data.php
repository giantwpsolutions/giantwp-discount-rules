<?php

namespace AIO_WooDiscount\Api\Controllers;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class Save_Data extends WP_REST_Controller
{
    public function __construct()
    {
        $this->namespace = 'aio-woodiscount/v2';
        $this->rest_base = 'save-data';
    }

    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [$this, 'save_form_data'],
                    'permission_callback' => [$this, 'save_form_data_permission'],
                ],
            ]
        );
    }

    public function save_form_data_permission()
    {
        return current_user_can('manage_options'); // ✅ Ensure only admins can save
    }

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

        // ✅ Sanitize received data
        $sanitized_data = $this->sanitize_data($params);

        if (is_wp_error($sanitized_data)) {
            return $sanitized_data;
        }

        // ✅ Save to WordPress options
        $saved = update_option('aio_woodiscount_data', maybe_serialize($sanitized_data));

        if (!$saved) {
            return new WP_Error(
                'save_failed',
                __('Failed to save data.', 'aio-woodiscount'),
                ['status' => 500]
            );
        }

        error_log("🟠 SANITIZED DATA TO SAVE: " . print_r($sanitized_data, true));



        return new WP_REST_Response(
            ['success' => true, 'message' => __('Data saved successfully.', 'aio-woodiscount')],
            200
        );
    }

    private function sanitize_data($data)
    {
        if (!is_array($data)) {
            return new WP_Error('invalid_data', __('Invalid data format.'), ['status' => 400]);
        }

        // Process conditions array
        if (isset($data['conditions']) && is_array($data['conditions'])) {
            $data['conditions'] = array_map(function ($condition) {
                return [
                    'field'    => sanitize_text_field($condition['field'] ?? ''),
                    'operator' => sanitize_text_field($condition['operator'] ?? ''),
                    'value'    => $this->sanitize_condition_value($condition['value'] ?? null)
                ];
            }, $data['conditions']);
        }

        error_log("🟢 FIXED SANITIZED DATA: " . print_r($data, true));  // ✅ DEBUG LOG

        return $data;
    }


    private function sanitize_condition_value($value)
    {
        // If it's an array, process each item
        if (is_array($value)) {
            return array_map(fn($item) => is_numeric($item) ? (int) $item : sanitize_text_field($item), $value);
        }

        // If it's a string or number, sanitize directly
        return is_numeric($value) ? (int) $value : sanitize_text_field($value);
    }
}
