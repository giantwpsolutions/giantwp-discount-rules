<?php

namespace AIO_WooDiscount\Helper;

defined('ABSPATH') || exit;

/**
 * Bogo Buy Product Sanitization helper class
 */

class BogoBuyProduct_Sanitization_Helper
{

    /**
     * Sanitize individual Buy product entries.
     *
     * @param array $buyProduct The raw buyProduct data.
     * @return array The sanitized buyProduct.
     */
    public static function sanitize_bogoBuyProduct($buyProduct)
    {
        return [
            'field'    => sanitize_text_field($buyProduct['field'] ?? ''),
            'operator' => sanitize_text_field($buyProduct['operator'] ?? ''),
            'value'    => self::sanitize_buyProduct_value($buyProduct['value'] ?? null),
        ];
    }

    /**
     * Sanitize buyProduct values.
     *
     * @param mixed $value The value(s) to sanitize.
     * @return mixed The sanitized value.
     */
    private static function sanitize_buyProduct_value($value)
    {
        if (is_array($value)) {
            return array_map(fn($item) => is_numeric($item) ? (int) $item : sanitize_text_field($item), $value);
        }

        return is_numeric($value) ? (int) $value : sanitize_text_field($value);
    }
}
