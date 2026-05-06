<?php
/**
 * Analytics REST API Controller.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Api\Controllers\Discounts;

defined( 'ABSPATH' ) || exit;

use WP_REST_Server;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use GiantWP_Discount_Rules\Discount\Analytics\Analytics_Tracker;

class Analytics_Controller extends WP_REST_Controller {

    public function register_routes(): void {

        register_rest_route( 'gwpdr-discountrules/v2', '/analytics', [
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => [ $this, 'get_analytics' ],
            'permission_callback' => [ $this, 'check_permission' ],
        ] );

        register_rest_route( 'gwpdr-discountrules/v2', '/analytics/reset', [
            'methods'             => WP_REST_Server::DELETABLE,
            'callback'            => [ $this, 'reset_analytics' ],
            'permission_callback' => [ $this, 'check_permission' ],
        ] );
    }

    public function check_permission(): bool {
        return current_user_can( 'manage_woocommerce' );
    }

    public function get_analytics( WP_REST_Request $request ): WP_REST_Response {
        $raw      = get_option( Analytics_Tracker::OPTION_KEY, [] );
        $currency = get_woocommerce_currency_symbol();
        $rows     = [];

        foreach ( $raw as $entry ) {
            $rows[] = [
                'id'             => $entry['id']             ?? '',
                'name'           => $entry['name']           ?? '',
                'type'           => $entry['type']           ?? '',
                'applied'        => (int)   ( $entry['applied']        ?? 0 ),
                'revenue_total'  => (float) ( $entry['revenue_total']  ?? 0 ),
                'discount_total' => (float) ( $entry['discount_total'] ?? 0 ),
                'last_applied'   => $entry['last_applied']   ?? '',
            ];
        }

        // Sort by revenue descending
        usort( $rows, fn( $a, $b ) => $b['revenue_total'] <=> $a['revenue_total'] );

        $totals = array_reduce( $rows, function ( $carry, $row ) {
            $carry['total_revenue']  += $row['revenue_total'];
            $carry['total_discount'] += $row['discount_total'];
            $carry['total_applied']  += $row['applied'];
            return $carry;
        }, [ 'total_revenue' => 0.0, 'total_discount' => 0.0, 'total_applied' => 0 ] );

        return rest_ensure_response( [
            'rows'     => $rows,
            'totals'   => $totals,
            'currency' => $currency,
        ] );
    }

    public function reset_analytics( WP_REST_Request $request ): WP_REST_Response {
        Analytics_Tracker::reset();
        return rest_ensure_response( [ 'success' => true ] );
    }
}
