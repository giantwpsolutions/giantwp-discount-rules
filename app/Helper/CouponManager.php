<?php
  /**
 * Coupon Validation Helper.
 *
 * @package DealBuilder_Discount_Rules
 */

namespace DealBuilder_Discount_Rules\Helper;

defined( 'ABSPATH' ) || exit;

  /**
 * Class CouponManager
 *
 * Provides utility functions to validate WooCommerce coupons.
 */
class CouponManager {

      /**
     * Check if a given coupon code is valid and usable.
     *
     * @param string $code The coupon code to validate.
     * @return bool True if valid and usable, false otherwise.
     */
    public static function is_valid_coupon( $code ) {
        try {
            $coupon = new \WC_Coupon( $code );

            return (
                $coupon->get_id() > 0 &&
                $coupon->is_valid() &&
                ! $coupon->get_virtual() // Ensure it's not a programmatic/internal coupon
            );
        } catch ( \Exception $e ) {
            return false;
        }
    }
}
