<?php
  /**
 * Plugin Settings REST API Controller.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Api\Controllers\Discounts;

defined( 'ABSPATH' ) || exit;


use WP_REST_Server;
use WP_REST_Controller;

/**
 * Class Settings_Controller
 *
 * Handles the API endpoints for plugin-wide settings such as: 
 * - discountBasedOn: whether to calculate discounts from regular or sale price.
 * - orderPageLabel: whether to show applied discount labels in the admin orders page.
 *
 * Route: /db-discountrules/v2/settings
 *
 * @package GiantWP_Discount_Rules\Api\Controllers\Discounts
 */
class Settings_Controller extends WP_REST_Controller
{
    /**
     * Registers the REST API routes for settings.
     *
     * @return void
     */
    public function register_routes() {
        // Save settings (POST/PUT)
        register_rest_route( 'gwp-discountrules/v2', '/settings', [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [ $this, 'save_settings' ],
            'permission_callback' => function () {
                return current_user_can( 'manage_woocommerce' );
            },
        ] );

        // Get settings (GET)
        register_rest_route( 'gwp-discountrules/v2', '/settings', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_settings' ],
            'permission_callback' => function () {
                return current_user_can( 'manage_woocommerce' );
            },
        ]);
    }

    /**
     * Handles saving of plugin settings.
     *
     * @param \WP_REST_Request $request The REST request object.
     * @return \WP_REST_Response The response containing success status and saved settings.
     */
    public function save_settings( $request ) {
        $data = $request->get_json_params();

        $sanitized = [
            'discountBasedOn' => in_array( $data['discountBasedOn'] ?? '', ['regular_price', 'sale_price'] ) ? sanitize_text_field( $data['discountBasedOn'] ) : 'regular_price', 'orderPageLabel'  => ! empty( $data['orderPageLabel']) ? true : false,
        ];

        update_option( 'giantwp_discountrules_settings', $sanitized );

        return rest_ensure_response([
            'success' => true,
            'data'    => $sanitized,
        ]);
    }

    /**
     * Retrieves the current plugin settings, with fallback defaults.
     *
     * @return \WP_REST_Response The current settings.
     */
    public function get_settings() {
        $defaults = [
            'discountBasedOn'  => 'sale_price',
            'orderPageLabel'   => true,
        ];

        $settings = get_option('giantwp_discountrules_settings', []);
        $settings = wp_parse_args( $settings, $defaults );

        return rest_ensure_response( $settings );
    }
}
