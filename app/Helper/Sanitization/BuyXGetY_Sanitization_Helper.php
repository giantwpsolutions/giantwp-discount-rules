<?php

namespace AIO_WooDiscount\Helper\Sanitization;

defined('ABSPATH') || exit;

/**
 * Buy X Get Y Discount Data Sanitization Helper Class
 */

class BuyXGetY_Sanitization_Helper
{


    /**
     * Sanitize Buy x Get y discount data.
     *
     * @param array $data The raw Buy x Get y discount data.
     * @return array The sanitized data.
     */
    public static function BuyXGetY_Data_Sanitization($data)
    {
        if (!is_array($data)) {
            return [];
        }

        return [
            'id'        => sanitize_text_field($data['id'] ?? time()),
            'createdAt' => Conditions_Sanitization_Helper::sanitize_iso8601_datetime($data['createdAt'] ?? current_time('c')),

            'discountType' => strtolower(sanitize_text_field($data['discountType'] ?? 'buy x get y')),

            'status'       => isset($data['status']) && in_array($data['status'], ['on', 'off']) ? $data['status'] : 'on',
            'couponName'   => sanitize_text_field($data['couponName'] ?? ''),
            'buyXApplies' => isset($data['buyXApplies']) && in_array($data['buyXApplies'], ['any', 'all'])
                ? $data['buyXApplies']
                :   'any',
            'buyProduct' => isset($data['buyProduct']) && is_array($data['buyProduct'])
                ? array_map([BogoProduct_Sanitization_Helper::class, 'sanitize_buyXProduct'], $data['buyProduct'])
                :  [],
            'freeOrDiscount'   => sanitize_text_field($data['freeOrDiscount'] ?? 'freeproduct'),
            'isRepeat'         => isset($data['isRepeat']) ? (bool) $data['isRepeat'] : true,
            'discountTypeBxgy' => sanitize_text_field($data['discountTypeBxgy'] ?? 'fixed'),
            'discountValue'    => isset($data['discountValue']) && is_numeric($data['discountValue']) ? floatval($data['discountValue']) : 0,
            'maxValue'         => isset($data['maxValue']) && is_numeric($data['maxValue']) ? floatval($data['maxValue']) : null,
            'getYApplies' => isset($data['getYApplies']) && in_array($data['getYApplies'], ['any', 'all'])
                ? $data['getYApplies']
                :    'any',
            'getProduct' => isset($data['getProduct']) && is_array($data['getProduct'])
                ? array_map([BogoProduct_Sanitization_Helper::class, 'sanitize_getYProduct'], $data['getProduct'])
                :  [],

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
