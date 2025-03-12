<?php

namespace AIO_WooDiscount\Helper;


defined('ABSPATH') || exit;

/**
 * Bulk Buy Product Sanitization helper class
 */

class Bulk_Product_Sanitization
{
    /**
     * Sanitize individual Buy product entries.
     *
     * @param array $buyProduct The raw buyProduct data.
     * @return array The sanitized buyProduct.
     */
    public static function sanitize_bulkBuyProduct($buyProduct)
    {
        return [
            'field'    => sanitize_text_field($buyProduct['field'] ?? ''),
            'operator' => sanitize_text_field($buyProduct['operator'] ?? ''),
            'value'    => self::sanitize_value($buyProduct['value'] ?? null),
        ];
    }


    /**
     * Sanitize Bulk Discount entries.
     *
     * @param array $discount The raw buyProduct data.
     * @return array The sanitized buyProduct.
     */
    public static function sanitize_bulkDiscountEntries($discount)
    {
        return [
            'fromcount'        => is_numeric($discount['fromcount']) ? intval($discount['fromcount']) : 0,
            'toCount'          => is_numeric($discount['toCount']) ? intval($discount['toCount']) : 0,
            'discountTypeBulk' => sanitize_text_field($discount['discountTypeBulk'] ?? ''),
            'discountValue'    => is_numeric($discount['discountValue']) ? intval($discount['discountValue']) : 0,
            'maxValue'    => is_numeric($discount['maxValue']) ? intval($discount['maxValue']) : 0,
        ];
    }


    /**
     * Sanitize values.
     *
     * @param mixed $value The value(s) to sanitize.
     * @return mixed The sanitized value.
     */
    private static function sanitize_value($value)
    {
        if (is_array($value)) {
            return array_map(function ($item) {
                return is_numeric($item) ? intval($item) : esc_html(sanitize_text_field($item));
            }, $value);
        }

        return is_numeric($value) ? intval($value) : esc_html(sanitize_text_field($value));
    }
}
