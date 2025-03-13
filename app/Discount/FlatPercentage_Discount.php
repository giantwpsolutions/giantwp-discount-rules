<?php

namespace AIO_WooDiscount\Discount;

defined('ABSPATH') || exit;

use AIO_WooDiscount\Discount\Condition\Conditions;

class FlatPercentage_Discount
{
    public function __construct()
    {
        add_action('woocommerce_cart_calculate_fees', [$this, 'apply_discount']);
    }

    /**
     * Main method to apply discount if rule passes.
     */
    public function apply_discount($cart)
    {
        if (is_admin() && !defined('DOING_AJAX')) return;

        $rules = $this->get_discount_rules();
        if (empty($rules)) return;

        foreach ($rules as $rule) {
            if (
                !isset($rule['discountType']) ||
                strtolower($rule['discountType']) !== 'flat/percentage' ||
                ($rule['status'] ?? '') !== 'on'
            ) {
                continue;
            }

            // ✅ Check schedule
            if (!$this->is_schedule_active($rule)) continue;

            // ✅ Check usage limit
            if (!$this->check_usage_limit($rule)) continue;

            // ✅ Check conditions
            if (
                isset($rule['enableConditions']) && $rule['enableConditions'] &&
                !Conditions::check_all($cart, $rule['conditions'], $rule['conditionsApplies'] ?? 'all')
            ) {
                continue;
            }

            // ✅ Apply discount
            $fp_type        = $rule['fpDiscountType'] ?? 'fixed';
            $discount_value = floatval($rule['discountValue'] ?? 0);
            $max_value      = isset($rule['maxValue']) ? floatval($rule['maxValue']) : null;

            $cart_total          = $cart->get_subtotal();
            $calculated_discount = ($fp_type === 'percentage')
                ? ($cart_total * ($discount_value / 100))
                : $discount_value;

            if ($max_value !== null) {
                $calculated_discount = min($calculated_discount, $max_value);
            }

            if ($calculated_discount > 0) {
                $label = ($rule['couponName'] ?? 'Flat/Percentage Discount') . " [RuleID:{$rule['id']}]";
                $cart->add_fee($label, -$calculated_discount, false);
                aio_track_applied_rule($rule['id']);

                error_log("✅ Applied flat/percentage discount via rule #{$rule['id']} | Amount: {$calculated_discount}");
            }
        }
    }

    /**
     * Load rules from options.
     */
    private function get_discount_rules(): array
    {
        return maybe_unserialize(get_option('aio_flatpercentage_discount', [])) ?: [];
    }

    /**
     * Check if the discount is within its active schedule.
     */
    private function is_schedule_active($rule): bool
    {
        if (!isset($rule['schedule']['enableSchedule']) || !$rule['schedule']['enableSchedule']) {
            return true;
        }

        $now   = current_time('timestamp');
        $start = strtotime($rule['schedule']['startDate'] ?? '');
        $end   = strtotime($rule['schedule']['endDate'] ?? '');

        return ($now >= $start && $now <= $end);
    }

    /**
     * Check if the rule's usage limit has not been exceeded.
     */
    private function check_usage_limit($rule): bool
    {
        if (!isset($rule['usageLimits']['enableUsage']) || !$rule['usageLimits']['enableUsage']) {
            return true;
        }

        $limit = intval($rule['usageLimits']['usageLimitsCount'] ?? 0);
        $used  = intval($rule['usedCount'] ?? 0);

        return $used < $limit;
    }
}
