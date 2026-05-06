<?php
/**
 * Analytics Tracker — records per-rule revenue and usage on order completion.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Discount\Analytics;

defined( 'ABSPATH' ) || exit;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

class Analytics_Tracker {

    use SingletonTrait;

    const OPTION_KEY = 'gwpdr_analytics';

    /** Option keys → discount type label */
    const RULE_SOURCES = [
        'giantwp_bogo_discount'           => 'bogo',
        'giantwp_bxgy_discount'           => 'buy x get y',
        'giantwp_bulk_discount'           => 'bulk discount',
        'giantwp_flatpercentage_discount' => 'flat/percentage',
        'giantwp_shipping_discount'       => 'shipping',
    ];

    /** Order meta keys that store applied rule IDs (non-flat/percentage rules) */
    const META_RULE_SOURCES = [
        '_gwpdr_bogo_applied_rules'     => 'giantwp_bogo_discount',
        '_gwpdr_bxgy_applied_rules'     => 'giantwp_bxgy_discount',
        '_gwpdr_bulk_applied_rules'     => 'giantwp_bulk_discount',
        '_gwpdr_shipping_applied_rules' => 'giantwp_shipping_discount',
    ];

    public function __construct() {
        add_action( 'woocommerce_order_status_completed',  [ $this, 'track_order' ], 30 );
        add_action( 'woocommerce_payment_complete',        [ $this, 'track_order' ], 30 );
    }

    public function track_order( int $order_id ): void {
        $order = wc_get_order( $order_id );
        if ( ! $order ) return;

        // Prevent double-tracking
        if ( $order->get_meta( '_gwpdr_analytics_tracked' ) ) return;

        $order_total    = (float) $order->get_total();
        $discount_total = (float) $order->get_discount_total();
        $date           = current_time( 'Y-m-d' );
        $analytics      = get_option( self::OPTION_KEY, [] );
        $found_any      = false;

        // --- Rules stored as IDs in order meta (BOGO, BXGY, Bulk) ---
        foreach ( self::META_RULE_SOURCES as $meta_key => $option_key ) {
            $applied = $order->get_meta( $meta_key );
            if ( empty( $applied ) ) continue;

            $rule_ids = is_array( $applied ) ? $applied : [ $applied ];
            $rules    = maybe_unserialize( get_option( $option_key, [] ) );
            if ( ! is_array( $rules ) ) continue;

            foreach ( $rules as $rule ) {
                $rule_id = $rule['id'] ?? '';
                if ( ! $rule_id || ! in_array( $rule_id, $rule_ids, true ) ) continue;

                $type      = self::RULE_SOURCES[ $option_key ] ?? 'unknown';
                $analytics = $this->update_entry( $analytics, $rule_id, $rule['couponName'] ?? '', $type, $order_total, $discount_total, $date );
                $found_any = true;
            }
        }

        // --- Flat/Percentage rules tracked via WC coupon codes ---
        $coupons  = $order->get_coupon_codes();
        $fp_rules = maybe_unserialize( get_option( 'giantwp_flatpercentage_discount', [] ) );
        if ( is_array( $fp_rules ) && ! empty( $coupons ) ) {
            foreach ( $fp_rules as $rule ) {
                $code = sanitize_title( $rule['couponName'] ?? '' );
                if ( ! $code || ! in_array( $code, $coupons, true ) ) continue;

                $analytics = $this->update_entry( $analytics, $rule['id'], $rule['couponName'] ?? '', 'flat/percentage', $order_total, $discount_total, $date );
                $found_any = true;
            }
        }

        if ( $found_any ) {
            update_option( self::OPTION_KEY, $analytics );
            $order->update_meta_data( '_gwpdr_analytics_tracked', '1' );
            $order->save();
        }
    }

    private function update_entry( array $analytics, string $id, string $name, string $type, float $revenue, float $discount, string $date ): array {
        if ( ! isset( $analytics[ $id ] ) ) {
            $analytics[ $id ] = [
                'id'             => $id,
                'name'           => $name,
                'type'           => $type,
                'applied'        => 0,
                'revenue_total'  => 0.0,
                'discount_total' => 0.0,
                'last_applied'   => '',
            ];
        }

        $analytics[ $id ]['applied']        += 1;
        $analytics[ $id ]['revenue_total']  += $revenue;
        $analytics[ $id ]['discount_total'] += $discount;
        $analytics[ $id ]['last_applied']    = $date;
        $analytics[ $id ]['name']            = $name; // keep in sync if renamed

        return $analytics;
    }

    public static function reset(): void {
        delete_option( self::OPTION_KEY );
    }
}
