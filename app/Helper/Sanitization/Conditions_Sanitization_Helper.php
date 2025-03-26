<?php
  /**
 * Conditions Sanitization Helper.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Helper\Sanitization;

defined('ABSPATH') || exit;

/**
 * Conditions Data Sanitization Helper class
 */

class Conditions_Sanitization_Helper {

    /**
     * Sanitize individual condition entries.
     *
     * @param array $condition The raw condition data.
     * @return array The sanitized condition.
     */
    public static function sanitize_condition( $condition ) {
        return [
            'field'    => sanitize_text_field( $condition['field'] ?? '' ),
            'operator' => sanitize_text_field( $condition['operator'] ?? '' ),
            'value'    => self::sanitize_condition_value( $condition['value'] ?? null ),
        ];
    }

    /**
     * Sanitize condition values.
     *
     * @param mixed $value The value(s) to sanitize.
     * @return mixed The sanitized value.
     */
    private static function sanitize_condition_value( $value ) {
        if ( is_array( $value ) ) {
            return array_map( function ( $item ) {
                return is_numeric( $item ) ? intval( $item ) : esc_html( sanitize_text_field( $item ) );
            }, $value );
        }

        return is_numeric( $value ) ? intval( $value ) : esc_html( sanitize_text_field( $value ) );
    }

   /**
     * Sanitize an ISO8601 datetime string.
     *
     * @since 1.0
     *
     * @param string|null $datetime Raw datetime string.
     * @return string Sanitized ISO8601 datetime or fallback.
     */
    public static function sanitize_iso8601_datetime( $datetime ) {
        if ( empty( $datetime ) || ! is_string( $datetime ) ) {
            return current_time('c');
        }

        if ( preg_match( '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\.\d+)?Z$/', $datetime ) ) {
            return sanitize_text_field( $datetime );
        }

        return current_time('c');
    }
}
    

