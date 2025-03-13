<?php

namespace AIO_WooDiscount\Discount\Manager;

use AIO_WooDiscount\Discount\Condition\Conditions;
use AIO_WooDiscount\Traits\SingletonTrait;
use Engramium\Optimator\Traits\Singleton;

defined('ABSPATH') || exit;

class FlatPercentage_Validator
{
    use SingletonTrait;

    public function __construct()
    {
        add_filter('woocommerce_coupon_is_valid', [$this, 'validate_coupon_conditions'], 10, 2);
    }

    /**
     * Prevent usage if conditions are not met (manual usage).
     */
    public function validate_coupon_conditions($valid, $coupon)
    {
        if (!$coupon instanceof \WC_Coupon) return $valid;

        // Only process hidden plugin-generated coupons
        if (!$coupon->get_meta('aio_is_hidden_coupon')) return $valid;

        $rule_id = $coupon->get_meta('aio_rule_id');
        if (!$rule_id) return false;

        $rules = maybe_unserialize(get_option('aio_flatpercentage_discount', [])) ?: [];

        foreach ($rules as $rule) {
            if ($rule['id'] !== $rule_id) continue;

            // ✅ Check schedule
            if (!self::is_schedule_active($rule)) return false;

            // ✅ Check usage limit
            if (!self::check_usage_limit($rule)) return false;

            // ✅ Check conditions
            if (
                isset($rule['enableConditions']) && $rule['enableConditions'] &&
                !Conditions::check_all(WC()->cart, $rule['conditions'], $rule['conditionsApplies'] ?? 'all')
            ) {
                return false;
            }

            return true;
        }

        return false;
    }

    private static function is_schedule_active($rule): bool
    {
        if (!isset($rule['schedule']['enableSchedule']) || !$rule['schedule']['enableSchedule']) {
            return true;
        }

        $now   = current_time('timestamp');
        $start = strtotime($rule['schedule']['startDate'] ?? '');
        $end   = strtotime($rule['schedule']['endDate'] ?? '');

        return ($now >= $start && $now <= $end);
    }

    private static function check_usage_limit($rule): bool
    {
        if (!isset($rule['usageLimits']['enableUsage']) || !$rule['usageLimits']['enableUsage']) {
            return true;
        }

        $limit = intval($rule['usageLimits']['usageLimitsCount'] ?? 0);
        $used  = intval($rule['usedCount'] ?? 0);

        return $used < $limit;
    }
}
