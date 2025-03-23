<?php
/**
 * Condition Dispatcher.
 *
 * @package AIO_DiscountRules
 */

namespace AIO_DiscountRules\Discount\Condition;

defined('ABSPATH') || exit;

/**
 * Handles evaluation of multiple discount conditions.
 *
 * @since 1.0
 */
class Conditions {
    /**
     * Evaluate all given conditions against the cart.
     *
     * @param \WC_Cart $cart        WooCommerce cart object.
     * @param array    $conditions  Array of condition rules.
     * @param string   $match_type  Condition match logic: 'all' or 'any'.
     *
     * @return bool True if matched, false otherwise.
     */
    public static function check_all( $cart, $conditions, $match_type = 'all' ) {
        $cart_items = $cart->get_cart();
        $results    = [];

        foreach ( $conditions as $condition ) {
            $field  = $condition['type'] ?? $condition['field'];
            $result = false;

            switch ( $field ) {
                case 'cart_subtotal_price':
                    $result = Condition_Fields::cart_subtotal_price( $cart_items, $condition );
                    break;
                case 'cart_quantity':
                    $result = Condition_Fields::cart_quantity( $cart_items, $condition );
                    break;
                case 'cart_total_weight':
                    $result = Condition_Fields::cart_total_weight( $cart_items, $condition );
                    break;
                case 'cart_item_product':
                    $result = Condition_Fields::cart_item_product( $cart_items, $condition );
                    break;
                case 'cart_item_variation':
                    $result = Condition_Fields::cart_item_variation( $cart_items, $condition );
                    break;
                case 'cart_item_category':
                    $result = Condition_Fields::cart_item_category( $cart_items, $condition );
                    break;
                case 'cart_item_tag':
                    $result = Condition_Fields::cart_item_tag( $cart_items, $condition );
                    break;
                case 'cart_item_regular_price':
                    $result = Condition_Fields::cart_item_regular_price( $cart_items, $condition );
                    break;
                case 'customer_order_count':
                    $result = Condition_Fields::customer_order_count( $cart_items, $condition );
                    break;
                case 'customer_order_history_product':
                    $result = Condition_Fields::customer_order_history_product( $cart_items, $condition );
                    break;
                case 'customer_order_history_category':
                    $result = Condition_Fields::customer_order_history_category( $cart_items, $condition );
                    break;
                case 'payment_method':
                    $result = Condition_Fields::payment_method( $cart_items, $condition );
                    break;
                case 'customer_is_logged_in':
                    $result = Condition_Fields::customer_is_logged_in( $cart_items, $condition );
                    break;
                case 'customer_role':
                    $result = Condition_Fields::customer_role( $cart_items, $condition );
                    break;
                case 'specific_customer':
                    $result = Condition_Fields::specific_customer( $cart_items, $condition );
                    break;
                default:
                    do_action( 'aio_woodiscount_unhandled_condition', $condition );
                    break;
            }

            $results[] = $result;

            if ( $match_type === 'any' && $result ) {
                return true;
            }

            if ( $match_type === 'all' && !$result ) {
                return false;
            }
        }

        return $match_type === 'all';
    }
}
