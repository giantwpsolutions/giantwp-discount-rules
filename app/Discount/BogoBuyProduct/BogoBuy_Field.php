<?php
  /**
 * BOGO Buy Condition Fields Handler.
 *
 * Handles condition-based checks for BOGO discount applicability.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Discount\BogoBuyProduct;

defined( 'ABSPATH' ) || exit;

  /**
 * Class BogoBuy_Field
 *
 * Validates whether cart items match BOGO conditions.
 */

class BogoBuy_Field {
    /**
     * Check if all products are allowed (always true)
     *
     * @param array $cart_items
     * @param array $condition
     * @return bool
     */
    public static function all_products( $cart_items, $condition ) {
        return true;  // All products allowed
    }

    /**
     * Checking by matching product IDs.
     *
     * @param array $cart_items
     * @param array $condition (expects 'operator' and 'value')
     * @return bool
     */
    public static function product( $cart_items, $condition ) {
        if ( ! isset( $condition['operator'], $condition['value'] ) || ! is_array( $condition['value'] ) ) {
            return false;
        }

        $product_ids = [];
        foreach ( $cart_items as $item ) {
            if ( isset( $item['product_id'] ) ) {
                $product_ids[] = (int) $item['product_id'];
            }
        }

        $condition_ids = array_map( 'intval', $condition['value'] );

        return gwp_compare_list( $product_ids, $condition['operator'], $condition_ids );
    }

    /**
     * checking by matching variation IDs.
     */
    public static function product_variation( $cart_items, $condition ) {
        if ( ! isset( $condition['operator'], $condition['value'] ) || ! is_array( $condition['value'] ) ) {
            return false;
        }

        $variation_ids = [];
        foreach ( $cart_items as $item ) {
            if ( ! empty( $item['variation_id'] ) ) {
                $variation_ids[] = (int) $item['variation_id'];
            }
        }

        $condition_ids = array_map( 'intval', $condition['value'] );
        return gwp_compare_list( $variation_ids, $condition['operator'], $condition_ids );
    }

    /**
     * Checking by product tag IDs.
     * 
     * @param array $cart_items
     * @param array $condition (expects 'operator' and 'value')
     * @return bool
     */
    public static function product_tags( $cart_items, $condition ) {
        if ( ! isset( $condition['operator'], $condition['value'] ) || ! is_array( $condition['value'] ) ) {
            return false;
        }

        $tags_in_cart = [];

        foreach ( $cart_items as $item ) {
            $product_id   = $item['product_id'] ?? 0;
            $tag_ids      = wc_get_product_term_ids( $product_id, 'product_tag' );
            $tags_in_cart = array_merge( $tags_in_cart, $tag_ids );
        }

        return gwp_compare_list( array_unique( $tags_in_cart ), $condition['operator'], $condition['value'] );
    }

    /**
     * Checking by product category IDs.
     *
     * @param array $cart_items
     * @param array $condition (expects 'operator' and 'value')
     * @return bool
     */
    public static function product_category( $cart_items, $condition ) {
        if ( ! isset( $condition['operator'], $condition['value']) || ! is_array( $condition['value'] ) ) {
            return false;
        }

        $cat_ids = [];

        foreach ( $cart_items as $item ) {
            $product_id = $item['product_id'] ?? 0;
            $cat_ids    = array_merge( $cat_ids, wc_get_product_term_ids( $product_id, 'product_cat' ) );
        }

        return gwp_compare_list( array_unique( $cat_ids ), $condition['operator'], $condition['value'] );
    }

    /**
     * Checking by product price.
     * 
     * @param array $cart_items
     * @param array $condition (expects 'operator' and 'value')
     * @return bool
     */
    public static function product_price( $cart_items, $condition ) {
        if ( ! isset( $condition['operator'], $condition['value'])) {
            return false;
        }

        $value    = is_array( $condition['value'] ) ? floatval( $condition['value'][0] ) : floatval( $condition['value'] );
        $operator = $condition['operator'];

        foreach ( $cart_items as $item ) {
            $product = $item['data'] ?? null;
            if ($product instanceof \WC_Product) {
                $price = $product->get_price();
                if ( gwp_compare_numaric_value( $price, $operator, $value ) ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checking by stock quantity.
     *
     * @param array $cart_items
     * @param array $condition (expects 'operator' and 'value')
     * @return bool
     */
    public static function product_instock( $cart_items, $condition ) {
        if ( ! isset( $condition['operator'], $condition['value'] ) ) {
            return false;
        }

        $value    = is_array( $condition['value']) ? intval( $condition['value'][0] ) : intval( $condition['value'] );
        $operator = $condition['operator'];

        foreach ( $cart_items as $item ) {
            $product = $item['data'] ?? null;
            if ( $product instanceof \WC_Product ) {
                $stock = $product->get_stock_quantity();
                if ( $stock !== null && gwp_compare_numaric_value( $stock, $operator, $value ) ) {
                    return true;
                }
            }
        }

        return false;
    }
}
