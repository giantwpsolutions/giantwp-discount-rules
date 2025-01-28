<?php

namespace AIO_WooDiscount\Api\Controllers;

use WP_REST_Controller;
use WP_REST_Server;

class Save_Data extends WP_REST_Controller
{

    public function __construct()
    {
        $this->namespace = 'aio-woodiscount/v1';

        $this->rest_base = 'save-data';
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
                    'permission_callback' => [$this, 'save_form_data_permission'],
                    'args' => []

                ]
            ]
        );
    }


    /**
     * Checks if a given request has access to Create .
     * 
     *@param  \WP_REST_Request $request The request object.
     *
     * @return bool True if the user has permission, false otherwise.
     * 
     */
    public function save_form_data_permission()
    {
        if (current_user_can('manage_woocommerce')) {

            return true;
        }

        return true;
    }


    /** 
     * Save page data
     *
     * @param  \WP_Rest_Request $request
     *
     * @return \WP_Rest_Response|WP_Error
     */

    // public function save_form_data_permission() {


    // }
}
