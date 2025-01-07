<?php

namespace AIO_WooDiscount\Api\Controllers;

use WP_REST_Controller;
use WP_REST_Server;

/**
 * Product Category Controller Class
 */

class Products_Tag_Controller extends WP_REST_Controller
{

    public function __construct()
    {
        $this->namespace = 'aio-woodiscount/v1';

        $this->rest_base = 'tags';
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
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_product_tags'],
                    'permission_callback' => [$this, 'get_tag_permission'],

                ]
            ]
        );
    }


    /**
     * Checks if a given request has access to read Tags.
     * 
     *@param  \WP_REST_Request $request The request object.
     *
     * @return bool True if the user has permission, false otherwise.
     * 
     */
    public function get_tag_permission()
    {
        if (current_user_can('manage_woocommerce')) {

            return true;
        }

        return true;
    }



    /** 
     * Retrieves list of tags
     *
     * @param  \WP_Rest_Request $request
     *
     * @return \WP_Rest_Response|WP_Error
     */
    public function get_product_tags($request)
    {
        $args = [
            'taxonomy'   => 'product_tag',
            'hide_empty' => false,
        ];

        $tags = get_terms($args);

        if (is_wp_error($tags)) {
            return rest_ensure_response($tags);
        }

        $data = array_map(function ($tag) {
            return [
                'id'   => $tag->term_id,
                'name' => $tag->name,
                'slug' => $tag->slug,
            ];
        }, $tags);

        return rest_ensure_response($data);
    }
}
