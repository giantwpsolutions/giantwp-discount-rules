<?php

namespace AIO_WooDiscount\Helper;

defined('ABSPATH') || exit;

/**
 * Flat/Percentage Data Sanitization Helper Class
 */

class FlatPercentage_Sanitization_Helper
{

    /**
     * Sanitize flat/percentage discount data.
     *
     * @param array $data The raw flat/percentage discount data.
     * @return array The sanitized data.
     */
    public static function FlatPercentage_Data_Sanitization($data)
    {
        if (!is_array($data)) {
            return [];
        }

        return [
            'discountType'   => sanitize_text_field($data['discountType'] ?? 'flat'),
            'status'         => in_array($data['status'] ?? 'on', ['on', 'off']) ? $data['status'] : 'on',
            'couponName'     => sanitize_text_field($data['couponName'] ?? ''),
            'fpDiscountType' => sanitize_text_field($data['fpDiscountType'] ?? 'fixed'),
            'discountValue'  => is_numeric($data['discountValue'] ?? null) ? floatval($data['discountValue']) : 0,
            'maxValue'       => is_numeric($data['maxValue'] ?? null) ? floatval($data['maxValue']) : null,
            'schedule'       => [
                'enableSchedule' => !empty($data['schedule']['enableSchedule']),
                'startDate'      => sanitize_text_field($data['schedule']['startDate'] ?? null),
                'endDate'        => sanitize_text_field($data['schedule']['endDate'] ?? null),
            ],
            'usageLimits'    => [
                'enableUsage'      => !empty($data['usageLimits']['enableUsage']),
                'usageLimitsCount' => is_numeric($data['usageLimits']['usageLimitsCount'] ?? 0) ? (int) $data['usageLimits']['usageLimitsCount'] : 0,
            ],
            'autoApply'        => !empty($data['autoApply']),
            'enableConditions' => !empty($data['enableConditions']),
            'conditionsApplies' => in_array($data['conditionsApplies'] ?? 'any', ['any', 'all']) ? $data['conditionsApplies'] : 'any',

            // ✅ Properly sanitize conditions using Conditions_Sanitization_Helper
            'conditions'         => isset($data['conditions']) && is_array($data['conditions'])
                ? array_map([Conditions_Sanitization_Helper::class, 'sanitize_condition'], $data['conditions'])
                : [],
        ];
    }
}
