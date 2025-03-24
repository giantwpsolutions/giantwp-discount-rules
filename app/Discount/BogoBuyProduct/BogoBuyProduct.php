<?php
/**
 * BOGO/BXGY Buy Product Condition Dispatcher.
 *
 * @package DealBuilder_Discount_Rules
 */

namespace DealBuilder_Discount_Rules\Discount\BogoBuyProduct;

defined( 'ABSPATH' ) || exit;


use DealBuilder_Discount_Rules\Discount\BogoBuyProduct\BogoBuy_Field;
use WC_Cart;

/**
 * Class BogoBuyProduct
 *
 * Handles evaluation of BOGO and BXGY "buy" and "get" conditions.
 *
 * This class dispatches condition checks to the appropriate static methods
 * in the BogoBuy_Field class based on the condition type.
 */
class BogoBuyProduct {
    /**
     * Evaluate all BOGO/BXGY conditions against the cart.
     *
     * @param WC_Cart $cart        The WooCommerce cart object.
     * @param array   $conditions  The array of condition rules to evaluate.
     * @param string  $match_type  Whether to match 'all' or 'any' conditions.
     *
     * @return bool True if matched according to $match_type, false otherwise.
     */
    public static function check_all( $cart_or_items, $conditions, $match_type = 'all' ) {
        // Accept either cart object or raw cart items
        $cart_items = is_array( $cart_or_items ) ? $cart_or_items : $cart_or_items->get_cart();
        $results    = [];

        foreach ( $conditions as $condition ) {
            $field  = $condition['type'] ?? $condition['field'];
            $result = false;

            switch ( $field ) {
                case 'product':
                    $result = BogoBuy_Field::product( $cart_items, $condition );
                    break;
                case 'product_variation':
                    $result = BogoBuy_Field::product_variation( $cart_items, $condition );
                    break;
                case 'product_tags':
                    $result = BogoBuy_Field::product_tags( $cart_items, $condition );
                    break;
                case 'product_category':
                    $result = BogoBuy_Field::product_category( $cart_items, $condition );
                    break;
                case 'product_price':
                    $result = BogoBuy_Field::product_price( $cart_items, $condition );
                    break;
                case 'product_instock':
                    $result = BogoBuy_Field::product_instock( $cart_items, $condition );
                    break;
                case 'all_products':
                    $result = BogoBuy_Field::all_products( $cart_items, $condition );
                    break;
                default:
                    do_action( 'dealbuilder_unhandled_bogo_condition', $condition );
                    break;
            }

            $results[] = $result;

            if ( $match_type === 'any' && $result ) return true;
            if ( $match_type === 'all' && !$result ) return false;
        }

        return $match_type === 'all';
    }
}
