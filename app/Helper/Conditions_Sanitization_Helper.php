<?php

namespace AIO_WooDiscount\Helper;

defined('ABSPATH') || exit;

/**
 * Conditions Data Sanitization Helper class
 */

class Conditions_Sanitization_Helper
{

    /**
     * Sanitize individual condition entries.
     *
     * @param array $condition The raw condition data.
     * @return array The sanitized condition.
     */
    public static function sanitize_condition($condition)  // ❌ Was private, now ✅ public
    {
        return [
            'field'    => sanitize_text_field($condition['field'] ?? ''),
            'operator' => sanitize_text_field($condition['operator'] ?? ''),
            'value'    => self::sanitize_condition_value($condition['value'] ?? null),
        ];
    }

    /**
     * Sanitize condition values.
     *
     * @param mixed $value The value(s) to sanitize.
     * @return mixed The sanitized value.
     */
    private static function sanitize_condition_value($value)
    {
        if (is_array($value)) {
            return array_map(fn($item) => is_numeric($item) ? (int) $item : sanitize_text_field($item), $value);
        }

        return is_numeric($value) ? (int) $value : sanitize_text_field($value);
    }
}
