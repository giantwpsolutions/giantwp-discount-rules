<?php
/**
 * Flat/Percentage Discount Data Sanitization Helper.
 *
 * @package AIO_WooDiscount
 */

namespace AIO_WooDiscount\Helper\Sanitization;

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
    public static function flatPercentage_Data_Sanitization( $data )
    {
        if ( !is_array( $data ) ) {
            return [];
        }

        return [
            'id' => sanitize_text_field(  $data['id'] ?? time() ),
            'createdAt' => Conditions_Sanitization_Helper::sanitize_iso8601_datetime( $data['createdAt'] ?? current_time('c') ),
            'discountType'   => strtolower( sanitize_text_field( $data['discountType'] ?? 'flat/percentage' ) ),
            'status'         => isset( $data['status'] ) && in_array( $data['status'], ['on', 'off'] ) ? $data['status'] : 'on',
            'couponName'     => sanitize_text_field( $data['couponName'] ?? '' ),
            'fpDiscountType' => sanitize_text_field( $data['fpDiscountType'] ?? 'fixed' ),
            'discountValue'  => isset( $data['discountValue'] ) && is_numeric( $data['discountValue'] ) ? floatval( $data['discountValue'] ) : 0,
            'maxValue'       => isset( $data['maxValue'] ) && is_numeric( $data['maxValue'] ) ? floatval( $data['maxValue'] ) : null,

            'schedule'       => [
                'enableSchedule' => isset( $data['schedule']['enableSchedule'] ) ? (bool) $data['schedule']['enableSchedule'] : false,
                'startDate'      => sanitize_text_field( $data['schedule']['startDate'] ?? '' ),
                'endDate'        => sanitize_text_field( $data['schedule']['endDate'] ?? '' ),
            ],

            'usageLimits'    => [
                'enableUsage'      => isset( $data['usageLimits']['enableUsage'] ) ? (bool) $data['usageLimits']['enableUsage'] : false,
                'usageLimitsCount' => isset( $data['usageLimits']['usageLimitsCount'] ) && is_numeric( $data['usageLimits']['usageLimitsCount'] )
                    ? (int) $data['usageLimits']['usageLimitsCount']
                    :  0,
            ],

            'usedCount'       => isset( $data['usedCount'] ) && is_numeric( $data['usedCount'] ) ? intval( $data['usedCount'] ) : 0,
            'enableConditions' => isset( $data['enableConditions'] ) ? (bool) $data['enableConditions'] : false,

            // ✅ Fix: Ensure 'conditionsApplies' key exists before checking in_array()
            'conditionsApplies' => isset( $data['conditionsApplies'] ) && in_array( $data['conditionsApplies'], ['any', 'all'] )
                ? $data['conditionsApplies']
                : 'any',

            // ✅ Properly sanitize conditions using Conditions_Sanitization_Helper
            'conditions' => isset( $data['conditions'] ) && is_array( $data['conditions'] )
                ? array_map( [Conditions_Sanitization_Helper::class, 'sanitize_condition'], $data['conditions'] )
                : [],
        ];
    }
}
