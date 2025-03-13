<?php

namespace AIO_WooDiscount\Api\Controllers\Shared;

use WP_REST_Controller;
use WP_REST_Server;

/**
 * Product Category Controller Class
 */

class Products_Category_Controller extends WP_REST_Controller
{

    public function __construct()
    {
        $this->namespace = 'aio-woodiscount/v2';

        $this->rest_base = 'categories';
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
                    'callback'            => [$this, 'get_product_categories'],
                    'permission_callback' => [$this, 'get_category_permission'],

                ]
            ]
        );
    }


    /**
     * Checks if a given request has access to read Categories.
     * 
     *@param  \WP_REST_Request $request The request object.
     *
     * @return bool True if the user has permission, false otherwise.
     * 
     */

    public function get_category_permission()
    {
        if (current_user_can('manage_woocommerce')) {
            return true;
        }

        return true;
    }


    /** 
     * Retrieves list of categories
     *
     * @param  \WP_Rest_Request $request
     *
     * @return \WP_Rest_Response|WP_Error
     */
    public function get_product_categories($request)
    {
        $args = [
            'taxonomy'     => 'product_cat',
            'hide_empty'   => false,
            'hierarchical' => true,
        ];

        $categories = get_terms($args);

        if (is_wp_error($categories)) {
            return rest_ensure_response($categories);
        }

        $data = $this->format_categories($categories);

        return rest_ensure_response($data);
    }

    /**
     * Format categories with parent-child structure.
     */
    private function format_categories($categories, $parent_id = 0, $prefix = '')
    {
        $output = [];

        foreach ($categories as $category) {
            if ($category->parent == $parent_id) {
                $formatted_category = [
                    'id'   => $category->term_id,
                    'name' => $prefix . $category->name,
                    'slug' => $category->slug,
                ];

                $children = $this->format_categories($categories, $category->term_id, $prefix . $category->name . ' ⇒ ');
                $output[] = $formatted_category;

                if (! empty($children)) {
                    $output = array_merge($output, $children);
                }
            }
        }

        return $output;
    }
}
