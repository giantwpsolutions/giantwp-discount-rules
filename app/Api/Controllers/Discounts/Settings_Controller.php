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
 * - upsellNotificationWidget: whether to show upsell / promo widget to customer.
 *
 * Route: /gwpdr-discountrules/v2/settings
 */
class Settings_Controller extends WP_REST_Controller {

    /**
     * Registers the REST API routes for settings.
     *
     * @return void
     */
    public function register_routes() {

        // Save settings (POST/PUT)
        register_rest_route(
            'gwpdr-discountrules/v2',
            '/settings',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'save_settings' ],
                'permission_callback' => function () {
                    return current_user_can( 'manage_woocommerce' );
                },
            ]
        );

        // Get settings (GET)
        register_rest_route(
            'gwpdr-discountrules/v2',
            '/settings',
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_settings' ],
                'permission_callback' => function () {
                    return current_user_can( 'manage_woocommerce' );
                },
            ]
        );
    }

    /**
     * Handles saving of plugin settings.
     *
     * @param \WP_REST_Request $request The REST request object.
     * @return \WP_REST_Response The response containing success status and saved settings.
     */
    public function save_settings( $request ) {
        $data = $request->get_json_params();

        // sanitize / normalize
        $discount_based_on = isset( $data['discountBasedOn'] ) && in_array(
            $data['discountBasedOn'],
            [ 'regular_price', 'sale_price' ],
            true
        )
            ? sanitize_text_field( $data['discountBasedOn'] )
            : 'regular_price';

        $order_page_label = ! empty( $data['orderPageLabel'] ) ? true : false;

        $upsell_widget = ! empty( $data['upsellNotificationWidget'] ) ? true : false;

        $sanitized = [
            'discountBasedOn'          => $discount_based_on,
            'orderPageLabel'           => $order_page_label,
            'upsellNotificationWidget' => $upsell_widget,
        ];

        update_option( 'giantwp_discountrules_settings', $sanitized );

        return rest_ensure_response(
            [
                'success' => true,
                'data'    => $sanitized,
            ]
        );
    }

    /**
     * Retrieves the current plugin settings, with fallback defaults.
     *
     * @return \WP_REST_Response The current settings.
     */
    public function get_settings() {

        // These should match the defaults you're expecting in Vue
        $defaults = [
            'discountBasedOn'          => 'regular_price', // or 'sale_price' if that's your true default
            'orderPageLabel'           => true,
            'upsellNotificationWidget' => false,
        ];

        $settings = get_option( 'giantwp_discountrules_settings', [] );

        // Merge any saved values over the defaults
        $settings = wp_parse_args( $settings, $defaults );

        return rest_ensure_response( $settings );
    }
}
