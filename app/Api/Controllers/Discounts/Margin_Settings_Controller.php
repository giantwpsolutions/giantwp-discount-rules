<?php
/**
 * REST API controller for Margin Protection Guard settings.
 *
 * GET  /gwpdr-discountrules/v2/margin-settings  → return saved settings
 * POST /gwpdr-discountrules/v2/margin-settings  → save settings
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Api\Controllers\Discounts;

defined( 'ABSPATH' ) || exit;

use GiantWP_Discount_Rules\Pro\MarginProtection\Margin_Guard;

class Margin_Settings_Controller {

    public function register_routes(): void {
        register_rest_route(
            'gwpdr-discountrules/v2',
            '/margin-settings',
            [
                [
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_settings' ],
                    'permission_callback' => [ $this, 'check_permission' ],
                ],
                [
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'save_settings' ],
                    'permission_callback' => [ $this, 'check_permission' ],
                ],
            ]
        );
    }

    public function check_permission(): bool {
        return current_user_can( 'manage_woocommerce' );
    }

    public function get_settings( \WP_REST_Request $request ): \WP_REST_Response {
        return new \WP_REST_Response( Margin_Guard::get_settings(), 200 );
    }

    public function save_settings( \WP_REST_Request $request ): \WP_REST_Response {
        $body = $request->get_json_params() ?? [];

        Margin_Guard::save_settings( $body );

        return new \WP_REST_Response( Margin_Guard::get_settings(), 200 );
    }
}
