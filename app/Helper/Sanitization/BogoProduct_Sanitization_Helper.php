<?php
  /**
 * BOGO Product Condition Sanitization Helper.
 *
 * @package AIO_WooDiscount
 */

namespace AIO_WooDiscount\Helper\Sanitization;

defined( 'ABSPATH' ) || exit;

  /**
 * Class BogoProduct_Sanitization_Helper
 *
 * Provides sanitization for BOGO and BxGy product conditions.
 */
class BogoProduct_Sanitization_Helper {

    /**
     * Sanitize individual Buy product entries.
     *
     * @param array $buyProduct The raw buyProduct data.
     * @return array The sanitized buyProduct.
     */
    public static function sanitize_bogoBuyProduct( $buyProduct ) {
        return [
            'field'    => sanitize_text_field( $buyProduct['field'] ?? '' ),
            'operator' => sanitize_text_field( $buyProduct['operator'] ?? '' ),
            'value'    => self::sanitize_value( $buyProduct['value'] ?? null ),
        ];
    }


    /**
     * Sanitize individual Buy X product entries.
     *
     * @param array $buyXProduct The raw buyProduct data.
     * @return array The sanitized buyXProduct.
     */
    public static function sanitize_buyXProduct( $buyXProduct ) {
        return [
            'buyProductCount' => is_numeric( $buyXProduct['buyProductCount'] ) ? intval( $buyXProduct['buyProductCount'] ) : 0,
            'field'    => sanitize_text_field( $buyXProduct['field'] ?? '' ),
            'operator' => sanitize_text_field( $buyXProduct['operator'] ?? '' ),
            'value'    => self::sanitize_value( $buyXProduct['value'] ?? null ),
        ];
    }


    /**
     * Sanitize individual Get Y product entries.
     *
     * @param array $getYProduct The raw buyProduct data.
     * @return array The sanitized getYProduct.
     */
    public static function sanitize_getYProduct( $getYProduct ) {
        return [
            'getProductCount' => is_numeric( $getYProduct['getProductCount']) ? intval( $getYProduct['getProductCount'] ) : 0,
            'field'    => sanitize_text_field( $getYProduct['field'] ?? '' ),
            'operator' => sanitize_text_field( $getYProduct['operator'] ?? '' ),
            'value'    => self::sanitize_value( $getYProduct['value'] ?? null ),
        ];
    }

    /**
     * Sanitize values.
     *
     * @param mixed $value The value(s) to sanitize.
     * @return mixed The sanitized value.
     */
    private static function sanitize_value( $value ) {
        if ( is_array( $value ) ) {
            return array_map( function ( $item ) {
                return is_numeric( $item ) ? intval( $item ) : esc_html( sanitize_text_field( $item ) );
            }, $value );
        }

        return is_numeric( $value ) ? intval( $value ) : esc_html( sanitize_text_field( $value ) );
    }
}
