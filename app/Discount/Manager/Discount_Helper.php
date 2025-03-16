<?php

namespace AIO_WooDiscount\Discount\Manager;


class Discount_Helper
{
    /**
     * Check if a rule is within its valid schedule.
     *
     * @param array $rule
     * @return bool
     */
    public static function is_schedule_active($rule): bool
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
     * Check if usage limits are respected.
     *
     * @param array $rule
     * @return bool
     */
    public static function check_usage_limit($rule): bool
    {
        if (!isset($rule['usageLimits']['enableUsage']) || !$rule['usageLimits']['enableUsage']) {
            return true;
        }

        $limit = intval($rule['usageLimits']['usageLimitsCount'] ?? 0);
        $used  = intval($rule['usedCount'] ?? 0);

        return $used < $limit;
    }
}
