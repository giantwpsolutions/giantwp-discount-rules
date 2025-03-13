<?php

namespace AIO_WooDiscount\Helper\Sanitization;

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
    public static function sanitize_condition($condition)
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
            return array_map(function ($item) {
                return is_numeric($item) ? intval($item) : esc_html(sanitize_text_field($item));
            }, $value);
        }

        return is_numeric($value) ? intval($value) : esc_html(sanitize_text_field($value));
    }


    public static function sanitize_iso8601_datetime($datetime)
    {
        // Check if the format matches ISO 8601 (e.g., 2025-03-04T17:33:16.175Z)
        if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?Z$/', $datetime)) {
            return sanitize_text_field($datetime);
        }

        // If invalid, fallback to current time in ISO format
        return current_time('c');
    }
}
