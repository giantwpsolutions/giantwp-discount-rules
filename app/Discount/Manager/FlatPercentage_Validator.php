<?php
  /**
 * Flat/Percentage Discount Validator.
 *
 * Validates auto-generated coupons for flat/percentage discounts based on
 * schedule, usage limits, and conditional logic.
 *
 * @package GiantWP_Discount_Rules\Discount\Manager
 */

namespace GiantWP_Discount_Rules\Discount\Manager;

defined('ABSPATH') || exit;

use GiantWP_Discount_Rules\Discount\Condition\Conditions;
use GiantWP_Discount_Rules\Traits\SingletonTrait;

/**
 * Class FlatPercentage_Validator
 */
class FlatPercentage_Validator {

    use SingletonTrait;

    /**
     * Constructor.
     */
    public function __construct() {
        add_filter( 'woocommerce_coupon_is_valid', [ $this, 'validate_coupon_conditions' ], 10, 2 );
    }

     /**
     * Validates coupon against rule conditions, usage limits and schedule.
     *
     * @param bool      $valid  Whether the coupon is valid.
     * @param \WC_Coupon $coupon Coupon object.
     *
     * @return bool
     */
    public function validate_coupon_conditions( $valid, $coupon ) {
        if ( ! $coupon instanceof \WC_Coupon ) return $valid;

        // Only process hidden plugin-generated coupons
        if ( ! $coupon->get_meta( 'gwp_is_hidden_coupon' )) return $valid;

        $rule_id = $coupon->get_meta( 'gwp_rule_id' );
        if ( ! $rule_id ) return false;

        $rules = maybe_unserialize( get_option( 'giantwp_flatpercentage_discount', [] ) ) ?: [];

        foreach ( $rules as $rule ) {
            if ($rule['id'] !== $rule_id) continue;

            //Check schedule
            if ( ! Discount_Helper::is_schedule_active( $rule ) ) return false;

            // Check usage limit
            if ( ! Discount_Helper::check_usage_limit( $rule ) ) return false;

            // Check conditions
            if (
                isset( $rule['enableConditions'] ) && $rule['enableConditions'] &&
                !Conditions::check_all( WC()->cart, $rule['conditions'], $rule['conditionsApplies'] ?? 'all' )
            ) {
                return false;
            }

            return true;
        }

        return false;
    }

}
