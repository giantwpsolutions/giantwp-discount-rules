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

        $now = current_time('timestamp');

        // Use fallback if empty
        $start = !empty($rule['schedule']['startDate'])
            ? strtotime($rule['schedule']['startDate'])
            : (!empty($rule['createdAt']) ? strtotime($rule['createdAt']) : 0);

        $end = !empty($rule['schedule']['endDate'])
            ? strtotime($rule['schedule']['endDate'])
            : PHP_INT_MAX; // No end date means it's always active after start

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

        // If the field is not set or is 0 or empty, treat it as unlimited
        if (empty($rule['usageLimits']['usageLimitsCount'])) {
            return true;
        }

        $limit = intval($rule['usageLimits']['usageLimitsCount']);
        $used  = intval($rule['usedCount'] ?? 0);

        return $used < $limit;
    }
}
