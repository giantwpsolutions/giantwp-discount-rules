<?php
  /**
 * Flat/Percentage Discount Usage Tracker.
 *
 * Syncs WooCommerce coupon usage with internal rule usage count.
 *
 * @package DealBuilder_Discount_Rules
 */

namespace DealBuilder_Discount_Rules\Discount\UsageTrack;

use DealBuilder_Discount_Rules\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

  /**
 * Class FlatPercentageUsage
 */
class FlatPercentageUsage {

    use SingletonTrait;
    
    const OPTION_KEY = 'dealbuilder_flatpercentage_discount';

    public function __construct() {
        add_action( 'woocommerce_order_status_completed', [ $this, 'sync_usage_from_coupon' ], 20, 1 );
    }

    /**
     * Sync real coupon usage to custom discount rules.
     *
     * @param int $order_id
     */
    public function sync_usage_from_coupon( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) return;

        $applied_coupons = $order->get_coupon_codes();
        if ( empty( $applied_coupons ) ) return;

        $rules = maybe_unserialize( get_option( self::OPTION_KEY, [] ) );
        $changed = false;

        foreach ( $rules as &$rule ) {
            $coupon_code = sanitize_title( $rule['couponName'] ?? '' );
            if ( ! $coupon_code ) continue;

            if ( in_array( $coupon_code, $applied_coupons, true ) ) {
                $wc_coupon = new \WC_Coupon( $coupon_code );
                $usage = (int) $wc_coupon->get_usage_count();

                if ( ! isset( $rule['usedCount'] ) || $rule['usedCount'] != $usage ) {
                    $rule['usedCount'] = $usage;
                    $changed = true;
                }
            }
        }

        if ( $changed ) {
            update_option( self::OPTION_KEY, maybe_serialize( $rules ) );
        }
    }
}
