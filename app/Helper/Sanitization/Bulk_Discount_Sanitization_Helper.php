<?php

namespace AIO_WooDiscount\Helper\Sanitization;

defined('ABSPATH') || exit;

/**
 * Bogo Discount Data Sanitization Helper Class
 */
class Bulk_Discount_Sanitization_Helper
{
    /**
     * Sanitize Bogo discount data.
     *
     * @param array $data The raw Bogo discount data.
     * @return array The sanitized data.
     */
    public static function Bulk_Discount_Data_Sanitization($data)
    {
        if (!is_array($data)) {
            return [];
        }

        return [
            'id'        => sanitize_text_field($data['id'] ?? time()),
            'createdAt' => Conditions_Sanitization_Helper::sanitize_iso8601_datetime($data['createdAt'] ?? current_time('c')),

            'discountType' => strtolower(sanitize_text_field($data['discountType'] ?? 'bulk discount')),
            'status'       => isset($data['status']) && in_array($data['status'], ['on', 'off']) ? $data['status'] : 'on',
            'couponName'   => sanitize_text_field($data['couponName'] ?? ''),
            'getItem'      => sanitize_text_field($data['getItem'] ?? 'alltogether'),
            'bulkDiscounts' => isset($data['bulkDiscounts']) && is_array($data['bulkDiscounts'])
                ? array_map([Bulk_Product_Sanitization::class, 'sanitize_bulkDiscountEntries'], $data['bulkDiscounts'])
                :   [],
            'getApplies'      => isset($data['getApplies']) && in_array($data['getApplies'], ['any', 'all'])
                ? $data['getApplies']
                :    'any',
            'buyProducts' => isset($data['buyProducts']) && is_array($data['buyProducts'])
                ? array_map([Bulk_Product_Sanitization::class, 'sanitize_bulkBuyProduct'], $data['buyProducts'])
                : [],
            'schedule'       => [
                'enableSchedule' => isset($data['schedule']['enableSchedule']) ? (bool) $data['schedule']['enableSchedule'] : false,
                'startDate'      => sanitize_text_field($data['schedule']['startDate'] ?? ''),
                'endDate'        => sanitize_text_field($data['schedule']['endDate'] ?? ''),
            ],

            'usageLimits'    => [
                'enableUsage'      => isset($data['usageLimits']['enableUsage']) ? (bool) $data['usageLimits']['enableUsage'] : false,
                'usageLimitsCount' => isset($data['usageLimits']['usageLimitsCount']) && is_numeric($data['usageLimits']['usageLimitsCount'])
                    ? (int) $data['usageLimits']['usageLimitsCount']
                    :  0,
            ],
            'usedCount'       => isset($data['usedCount']) && is_numeric($data['usedCount']) ? intval($data['usedCount']) : 0,
            'enableConditions' => isset($data['enableConditions']) ? (bool) $data['enableConditions'] : false,


            'conditionsApplies' => isset($data['conditionsApplies']) && in_array($data['conditionsApplies'], ['any', 'all'])
                ? $data['conditionsApplies']
                : 'any',

            // sanitize conditions using Conditions_Sanitization_Helper
            'conditions' => isset($data['conditions']) && is_array($data['conditions'])
                ? array_map([Conditions_Sanitization_Helper::class, 'sanitize_condition'], $data['conditions'])
                : [],
        ];
    }
}
