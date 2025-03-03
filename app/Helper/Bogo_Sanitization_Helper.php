<?php

namespace AIO_WooDiscount\Helper;

defined('ABSPATH') || exit;

/**
 * Bogo Discount Data Sanitization Helper Class
 */
class Bogo_Sanitization_Helper
{
    /**
     * Sanitize Bogo discount data.
     *
     * @param array $data The raw Bogo discount data.
     * @return array The sanitized data.
     */
    public static function Bogo_Data_Sanitization($data)
    {
        if (!is_array($data)) {
            return [];
        }

        return [
            'id'               => sanitize_text_field($data['id'] ?? time()),
            'discountType'     => sanitize_text_field($data['discountType'] ?? 'bogo'),
            'status'           => isset($data['status']) && in_array($data['status'], ['on', 'off']) ? $data['status'] : 'on',
            'couponName'       => sanitize_text_field($data['couponName'] ?? ''),
            'buyProductCount'  => isset($data['buyProductCount']) && is_numeric($data['buyProductCount']) ? floatval($data['buyProductCount']) : 1,
            'getProductCount'  => isset($data['getProductCount']) && is_numeric($data['getProductCount']) ? floatval($data['getProductCount']) : 1,
            'freeOrDiscount'   => sanitize_text_field($data['freeOrDiscount'] ?? 'freeproduct'),
            'isRepeat'         => isset($data['isRepeat']) ? (bool) $data['isRepeat'] : true,
            'discounttypeBogo' => sanitize_text_field($data['discounttypeBogo'] ?? 'fixed'),
            'discountValue'    => isset($data['discountValue']) && is_numeric($data['discountValue']) ? floatval($data['discountValue']) : 0,
            'maxValue'         => isset($data['maxValue']) && is_numeric($data['maxValue']) ? floatval($data['maxValue']) : null,
            'bogoApplies'      => isset($data['bogoApplies']) && in_array($data['bogoApplies'], ['any', 'all'])
                ? $data['bogoApplies']
                :    'any',

            'buyProduct' => isset($data['buyProduct']) && is_array($data['buyProduct'])
                ? array_map([BogoBuyProduct_Sanitization_Helper::class, 'sanitize_bogoBuyProduct'], $data['buyProduct'])
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
