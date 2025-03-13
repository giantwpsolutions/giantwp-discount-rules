<?php

namespace AIO_WooDiscount\Helper\Sanitization;

defined('ABSPATH') || exit;

/**
 * Bogo Discount Data Sanitization Helper Class
 */
class Shipping_Sanitization_Helper
{
    /**
     * Sanitize Bogo discount data.
     *
     * @param array $data The raw Bogo discount data.
     * @return array The sanitized data.
     */
    public static function Shipping_Data_Sanitization($data)
    {
        if (!is_array($data)) {
            return [];
        }

        return [
            'id'        => sanitize_text_field($data['id'] ?? time()),
            'createdAt' => Conditions_Sanitization_Helper::sanitize_iso8601_datetime($data['createdAt'] ?? current_time('c')),

            'discountType'         => strtolower(sanitize_text_field($data['discountType'] ?? 'shipping discount')),
            'status'               => isset($data['status']) && in_array($data['status'], ['on', 'off']) ? $data['status'] : 'on',
            'couponName'           => sanitize_text_field($data['couponName'] ?? ''),
            'shippingDiscountType' => sanitize_text_field($data['shippingDiscountType'] ?? 'reduceFee'),
            'pDiscountType'        => sanitize_text_field($data['pDiscountType'] ?? 'fixed'),
            'discountValue'        => isset($data['discountValue']) && is_numeric($data['discountValue']) ? floatval($data['discountValue']) : 0,
            'maxValue'             => isset($data['maxValue']) && is_numeric($data['maxValue']) ? floatval($data['maxValue']) : null,
            'schedule'             => [
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
            'autoApply'        => isset($data['autoApply']) ? (bool) $data['autoApply'] : false,
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
