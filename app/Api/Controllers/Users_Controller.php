<?php

namespace AIO_WooDiscount\Api\Controllers;

use WP_REST_Controller;
use WP_REST_Server;

/**
 * Users Controller Class
 */
class Users_Controller extends WP_REST_Controller
{

    public function __construct()
    {
        $this->namespace = 'aio-woodiscount/v1';
        $this->rest_base = 'users';
    }


    /**
     * Registers the routes for the object
     */

    public function register_routes()
    {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_users'],
                    'permission_callback' => [$this, 'get_users_permission'],
                ]
            ]
        );

        register_rest_route(
            $this->namespace,
            '/roles',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_roles'],
                    'permission_callback' => [$this, 'get_users_permission'],
                ]
            ]
        );
    }


    /**
     * Checks if a given request has access to read users.
     * @param  \WP_REST_Request $request The request object.
     *@return bool True if the user has permission, false otherwise.
     */

    public function get_users_permission()
    {
        if (current_user_can('manage_woocommerce')) {
            return true;
        };

        return true;
    }


    /**
     * Retrieves a list of users with optional role filtering
     * @param  \WP_Rest_Request $request
     * @return \WP_Rest_Response|WP_Error
     */

    public function get_users($request)
    {
        $role = $request->get_param('role');
        $args = [
            'orderby' => 'display_name',
            'order'   => 'ASC',
            'number'  => 100,
        ];

        if (!empty($role)) {
            $args['role'] = $role;
        }

        $user_query = new \WP_User_Query($args);
        $users      = $user_query->get_results();

        $data = [];
        foreach ($users as $user) {
            $data[] = [
                'id'           => $user->ID,
                'display_name' => $user->display_name,
                'email'        => $user->user_email,
                'roles'        => $user->roles,
            ];
        }

        return rest_ensure_response($data);
    }


    /**
     * Checks if a given request has access to read roles.
     */

    public function get_roles_permission()
    {
        return current_user_can('manage_options');
    }


    /**
     * Retrieves a list of all roles on the site
     */

    public function get_roles($request)
    {
        global $wp_roles;

        if (!isset($wp_roles)) {
            $wp_roles = new \WP_Roles();
        }

        $roles = $wp_roles->roles;

        $data = [];
        foreach ($roles as $role_key => $role) {
            $data[] = [
                'key'         => $role_key,
                'name'        => $role['name'],
                'capabilities' => $role['capabilities'],
            ];
        }

        return rest_ensure_response($data);
    }
}
