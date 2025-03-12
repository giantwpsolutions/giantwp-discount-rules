<?php

namespace AIO_WooDiscount\Discount;

defined('ABSPATH') || exit;
/**
 * Flat Percentage Discount Adjustment
 */

use AIO_WooDiscount\Discount\Condition\Conditions;

defined('ABSPATH') || exit;

class FlatPercentage_Discount
{
    public function __construct()
    {
        add_action('woocommerce_cart_calculate_fees', [$this, 'apply_discount']);
    }

    public function apply_discount($cart)
    {
        if (is_admin() && !defined('DOING_AJAX')) return;

        $ruless = get_option('aio_flatpercentage_discount', []);
        $rules  = maybe_unserialize($ruless);

        if (empty($rules)) return;

        foreach ($rules as $rule) {
            if (
                !isset($rule['discountType']) || strtolower($rule['discountType']) !== 'flat/percentage' ||
                ($rule['status'] ?? '')                                     !== 'on'
            ) {
                continue;
            }

            // Check if schedule is active (optional)
            if (isset($rule['schedule']['enableSchedule']) && $rule['schedule']['enableSchedule']) {
                $now   = current_time('timestamp');
                $start = strtotime($rule['schedule']['startDate']);
                $end   = strtotime($rule['schedule']['endDate']);
                if ($now < $start || $now > $end) continue;
            }

            // Check usage limit (optional)
            if (!self::check_usage_limit($rule)) continue;

            // Check all conditions
            if (
                isset($rule['enableConditions']) && $rule['enableConditions'] &&
                !Conditions::check_all($cart, $rule['conditions'], $rule['conditionsApplies'] ?? 'all')
            ) {
                continue;
            }

            // Apply discount
            $fp_type        = $rule['fpDiscountType'] ?? 'fixed';
            $discount_value = floatval($rule['discountValue'] ?? 0);
            $max_value      = isset($rule['maxValue']) ? floatval($rule['maxValue']) : null;

            $cart_total          = $cart->get_subtotal();
            $calculated_discount = ($fp_type === 'percentage') ? ($cart_total * ($discount_value / 100)) : $discount_value;

            if ($max_value !== null) {
                $calculated_discount = min($calculated_discount, $max_value);
            }

            if ($calculated_discount > 0) {
                $label = $rule['couponName'] ?? __('Flat/Percentage Discount', 'aio-woodiscount');
                $cart->add_fee($label, -$calculated_discount, false);
                error_log("✅ Discount applied: $calculated_discount via $label");
            }
        }
    }

    private static function check_usage_limit($rule)
    {
        if (!isset($rule['usageLimits']['enableUsage']) || !$rule['usageLimits']['enableUsage']) {
            return true;
        }

        $limit = intval($rule['usageLimits']['usageLimitsCount'] ?? 0);
        if (!$limit) return true;

        $used = get_option('aio_woodiscount_usage_' . $rule['id'], 0);
        return $used < $limit;
    }
}
