<?php
  /**
 * Condition Field Dispatcher.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Discount\Condition;

defined('ABSPATH') || exit;

class Condition_Fields {

    /**
     * Checking 'cart_subtotal_price' condition.
     *
     * @since 1.0
     *
     * @param array $cart_items Array of WooCommerce cart items.
     * @param array $condition  Condition array with 'operator' and 'value'.
     * Example: ['operator' => 'greater_than', 'value' => [100]]
     *
     * @return bool True if the subtotal matches the condition, false otherwise.
     */
    public static function cart_subtotal_price( $cart_items, $condition ) {
        if ( ! is_array( $condition ) || ! isset( $condition['field'] ) && !isset( $condition['operator'] ) ) {

            return false;
        }

        $comparation = $condition['operator'] ?? $condition['operator'];
        $raw_value   = $condition['value'] ?? [];

        // Handle value as scalar or array
        $value = is_array( $raw_value ) ? $raw_value[0] : $raw_value;

        $subtotal = 0;
        foreach ( $cart_items as $item ) {
            $subtotal += $item['line_subtotal'] ?? 0;
        }

        return gwpdr_compare_numaric_value( $subtotal, $comparation, $value );
    }


    /**
     * Evaluate 'cart_quantity' condition.
     *
     * @since 1.0
     *
     * @param array $cart_items Array of WooCommerce cart items.
     * @param array $condition  Condition array with 'operator' and 'value'.
     * Example: ['operator' => 'less_than', 'value' => [5]]
     *
     * @return bool True if quantity matches the condition, false otherwise.
     */
    public static function cart_quantity( $cart_items, $condition ) {
        if ( ! is_array( $condition ) || ! isset( $condition['field']) && ! isset( $condition['operator'] ) ) {
            return false;
        }

        $quantity = 0;
        foreach ( $cart_items as $item ) {
            $quantity += $item['quantity'];
        }

        return gwpdr_compare_numaric_value( $quantity, $condition['operator'], $condition['value'] );
    }



    /**
     * Checking 'cart_total_weight' condition.
     *
     * Calculates the total weight of all products in the cart
     * and checks it against the given numeric condition.
     *
     * @since 1.0
     *
     * @param array $cart_items Array of WooCommerce cart items.
     * @param array $condition  Condition array with 'operator' and 'value'.
     * Example: ['operator' => 'equal_less_than', 'value' => [10]]
     *
     * @return bool True if total weight matches the condition, false otherwise.
     */
    public static function cart_total_weight( $cart_items, $condition ) {
        if ( ! is_array( $condition ) || ! isset( $condition['operator'], $condition['value'] ) ) {

            return false;
        }

        $comparation = $condition['operator'];
        $raw_value   = $condition['value'];
        $value       = is_array( $raw_value ) ? $raw_value[0] : $raw_value;

        $total_weight = 0;

        foreach ($cart_items as $item) {
            $product = $item['data'];      // WC_Product object
            $qty     = $item['quantity'];

            if ( $product instanceof \WC_Product ) {
                $weight        = floatval( $product->get_weight() );
                $total_weight += $weight * $qty;
            }
        }

        return gwpdr_compare_numaric_value( $total_weight, $comparation, $value );
    }


    /**
     * Checking 'cart_item_regular_price' condition.
     *
     * Passes if **any item** in the cart has a regular price that matches
     * the condition's numeric comparison.
     *
     * @since 1.0
     *
     * @param array $cart_items Cart contents from WC_Cart.
     * @param array $condition  Condition with 'operator' and 'value'.
     *
     * @return bool True if condition passes, false otherwise.
     */
    public static function cart_item_regular_price( $cart_items, $condition ) {
        if ( ! is_array( $condition ) || ! isset( $condition['operator'], $condition['value'] ) ) {
            return false;
        }

        $operator = $condition['operator'];
        $value    = floatval( $condition['value'] );

        foreach ( $cart_items as $item ) {
            if ( ! isset( $item['data']) || ! ( $item['data'] instanceof \WC_Product ) ) {
                continue;
            }

            $product       = $item['data'];
            $regular_price = floatval( $product->get_regular_price() );


            if ( gwpdr_compare_numaric_value( $regular_price, $operator, $value ) ) {
                return true;
            }
        }
        return false;
    }


    /**
     * This checks if the cart contains, does not contain, or fully matches
     * a list of product IDs provided in the condition value.
     *
     * @since 1.0
     *
     * @param array $cart_items Cart contents from WC_Cart.
     * @param array $condition  Condition array with 'operator' and 'value'.
     *
     * @return bool True if condition passes, false otherwise.
     */
    public static function cart_item_product( $cart_items, $condition ) {
        if (
            ! is_array( $condition ) || ! isset( $condition['operator'], $condition['value'] ) || ! is_array( $condition['value'] )
        ) {
            return false;
        }

        $product_ids_in_cart = [];

        foreach ( $cart_items as $item ) {
            if ( ! isset( $item['data'] ) || ! ( $item['data'] instanceof \WC_Product ) ) {
                continue;
            }

            $product    = $item['data'];
            $product_id = $product->get_id();
            $parent_id  = $product->get_parent_id();

            // Add product ID and parent ID to cart list
            $product_ids_in_cart[] = $product_id;
            if ( ! empty( $parent_id ) ) {
                $product_ids_in_cart[] = $parent_id;
            }
        }

        // Ensure cart IDs are unique and condition value is array of integers
        $product_ids_in_cart = array_unique( array_map( 'intval', $product_ids_in_cart ) );
        $condition_ids       = array_map( 'intval', $condition['value'] );
        $operator            = $condition['operator'];


        return gwpdr_compare_cart_items( $product_ids_in_cart, $operator, $condition_ids );
    }


    /**
     * Checking 'cart_item_variation' condition based on variation IDs in cart.
     *
     * @since 1.0
     *
     * @param array $cart_items Cart contents from WC_Cart.
     * @param array $condition  Condition with 'operator' and 'value'.
     *
     * @return bool True if condition passes, false otherwise.
     */
    public static function cart_item_variation( $cart_items, $condition ) {
        if ( ! is_array( $condition ) || ! isset( $condition['operator'], $condition['value'] ) || ! is_array( $condition['value'] ) ) {
            return false;
        }

        $variation_ids_in_cart = [];

        foreach ( $cart_items as $item ) {
            if ( ! isset( $item['data']) || ! ( $item['data'] instanceof \WC_Product ) ) {
                continue;
            }

            $product = $item['data'];

            // Only consider variations
            if ( $product->is_type( 'variation' ) ) {
                $variation_ids_in_cart[] = $product->get_id();
            }
        }

        $variation_ids_in_cart = array_unique( array_map( 'intval', $variation_ids_in_cart ) );
        $condition_ids         = array_map( 'intval', $condition['value'] );
        $operator              = $condition['operator'];

        return gwpdr_compare_cart_items( $variation_ids_in_cart, $operator, $condition_ids );
    }

    /**
     * Check 'cart_item_category' condition based on product categories in cart.
     *
     * Supports subcategories as well. Categories are compared by their term IDs.
     *
     * @since 1.0
     *
     * @param array $cart_items Cart contents from WC_Cart.
     * @param array $condition  Condition with 'operator' and 'value' (array of category term IDs).
     *
     * @return bool True if condition passes, false otherwise.
     */
    public static function cart_item_category( $cart_items, $condition ) {
        if ( ! is_array($condition) || ! isset( $condition['operator'], $condition['value'] ) || ! is_array( $condition['value']) ) {
            return false;
        }

        $cart_category_ids = [];

        foreach ( $cart_items as $item ) {
            if ( ! isset($item['data']) || ! ( $item['data'] instanceof \WC_Product ) ) {
                continue;
            }

            $product = $item['data'];

            // Get category IDs (include parent + subcategories)
            $term_ids          = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
            $cart_category_ids = array_merge( $cart_category_ids, $term_ids );
        }

        $cart_category_ids = array_unique( array_map( 'intval', $cart_category_ids ) );
        $condition_ids     = array_map( 'intval', $condition['value'] );
        $operator          = $condition['operator'];


        return gwpdr_compare_cart_items( $cart_category_ids, $operator, $condition_ids );
    }

    /**
     * Check 'cart_item_tag' condition based on product tags in cart.
     *
     * Tags are matched by their term IDs.
     *
     * @since 1.0
     *
     * @param array $cart_items Cart contents from WC_Cart.
     * @param array $condition  Condition with 'operator' and 'value' (array of tag term IDs).
     *
     * @return bool True if condition passes, false otherwise.
     */
    public static function cart_item_tag( $cart_items, $condition ) {
        if ( ! is_array( $condition ) || ! isset( $condition['operator'], $condition['value'] ) || ! is_array( $condition['value'] ) ) {
            return false;
        }

        $tag_ids_in_cart = [];

        foreach ( $cart_items as $item ) {
            if ( ! isset( $item['data']) || ! ( $item['data'] instanceof \WC_Product ) ) {
                continue;
            }

            $product = $item['data'];

            // Get tag IDs (term IDs) for this product
            $term_ids        = wc_get_product_term_ids( $product->get_id(), 'product_tag' );
            $tag_ids_in_cart = array_merge( $tag_ids_in_cart, $term_ids );
        }

        $tag_ids_in_cart = array_unique( array_map( 'intval', $tag_ids_in_cart ) );
        $condition_ids   = array_map( 'intval', $condition['value'] );
        $operator        = $condition['operator'];

        return gwpdr_compare_cart_items( $tag_ids_in_cart, $operator, $condition_ids );
    }


    /**
     * Checking 'customer_order_count' condition for Order history.
     *
     * @since 1.0
     *
     * @param array $cart_items (Not used here, passed for consistency.)
     * @param array $condition  Condition array with 'operator' and 'value'.
     * Example: ['operator' => 'greater_than', 'value' => [3]]
     *
     * @return bool True if the customer's order count matches the condition.
     */
    public static function customer_order_count( $cart_items, $condition ) {
        if ( ! is_user_logged_in() || ! is_array( $condition ) || ! isset( $condition['operator'], $condition['value'] ) ) {
            return false;
        }

        $user_id  = get_current_user_id();
        $operator = $condition['operator'];
        $value    = is_array( $condition['value'] ) ? intval( $condition['value'][0] ) : intval( $condition['value'] );

        $args = [
            'customer_id' => $user_id,
            'status'      => [ 'wc-completed', 'wc-processing', 'wc-on-hold' ],   // filter valid order statuses
            'return'      => 'ids',
        ];

        $orders      = wc_get_orders( $args );
        $order_count = count( $orders );

        return gwpdr_compare_numaric_value( $order_count, $operator, $value );
    }


    /**
     * Evaluate 'customer_order_history_product' condition.
     *
     * Checks if the customer has purchased certain products in their order history,
     * using list-based comparison (contain, not_contain, contain_all).
     *
     * @since 1.0
     *
     * @param array $cart_items (Unused, passed for consistency).
     * @param array $condition  Condition with 'operator' and 'value' (product IDs).
     *
     * @return bool True if condition passes, false otherwise.
     */
    public static function customer_order_history_product( $cart_items, $condition ) {
        if ( ! is_user_logged_in() || ! is_array( $condition ) || ! isset( $condition['operator'], $condition['value'] ) || ! is_array( $condition['value'] )){
            return false;
        }

        $user_id       = get_current_user_id();
        $operator      = $condition['operator'];
        $condition_ids = array_map( 'intval', $condition['value'] );  // Expected product IDs

        $args = [
            'customer_id' => $user_id,
            'status'      => [ 'wc-completed', 'wc-processing' ],   // Limit to valid orders
            'return'      => 'ids',
            'limit'       => -1,
        ];

        $orders                = wc_get_orders( $args );
        $purchased_product_ids = [];

        foreach ( $orders as $order_id ) {
            $order = wc_get_order( $order_id );
            if ( ! $order) {
                continue;
            }

            foreach ( $order->get_items() as $item ) {
                if ( ! ( $item instanceof \WC_Order_Item_Product )) {
                    continue;
                }

                $product = $item->get_product();

                if ( ! $product || ! ( $product instanceof \WC_Product ) ) {
                    continue;
                }

                $product_id              = $product->get_id();
                $parent_id               = $product->get_parent_id();
                $purchased_product_ids[] = $product_id;

                if ( $parent_id ) {
                    $purchased_product_ids[] = $parent_id;
                }
            }
        }

        $purchased_product_ids = array_unique( $purchased_product_ids );

        return gwpdr_compare_cart_items( $purchased_product_ids, $operator, $condition_ids );
    }


    /**
     * Checking 'customer_order_history_category' condition.
     *
     * @since 1.0
     *
     * @param array $cart_items (Unused, passed for consistency).
     * @param array $condition  Condition with 'operator' and 'value' (category IDs).
     *
     * @return bool True if condition passes, false otherwise.
     */
    public static function customer_order_history_category( $cart_items, $condition ) {
        if ( ! is_user_logged_in() || ! is_array( $condition ) || ! isset( $condition['operator'], $condition['value']) || ! is_array( $condition['value'] ) ) {
            return false;
        }

        $user_id         = get_current_user_id();
        $operator        = $condition['operator'];
        $condition_ids   = array_map( 'intval', $condition['value'] );
        $ordered_cat_ids = [];

        $args = [
            'customer_id' => $user_id,
            'status'      => [ 'wc-completed', 'wc-processing' ],
            'return'      => 'ids',
            'limit'       => -1,
        ];

        $orders = wc_get_orders( $args );

        foreach ( $orders as $order_id ) {
            $order = wc_get_order( $order_id );
            if ( ! $order ) continue;

            foreach ( $order->get_items() as $item ) {
                if ( ! ( $item instanceof \WC_Order_Item_Product ) ) continue;

                $product = $item->get_product();
                if ( ! $product || ! ( $product instanceof \WC_Product ) ) continue;

                // Get product category IDs
                $term_ids        = wc_get_product_term_ids( $product->get_id(), 'product_cat' );
                $ordered_cat_ids = array_merge( $ordered_cat_ids, $term_ids );
            }
        }

        $ordered_cat_ids = array_unique( $ordered_cat_ids );

        return gwpdr_compare_cart_items( $ordered_cat_ids, $operator, $condition_ids );
    }


    /**
     * Check if selected payment method matches the condition.
     *
     * @since 1.0
     *
     * @param array $cart_items (Unused).
     * @param array $condition  Must contain 'operator' and 'value'.
     *
     * @return bool
     */
    public static function payment_method( $cart_items, $condition ) {
        if ( ! is_array( $condition ) || ! isset( $condition['operator'], $condition['value'] ) || ! is_array( $condition['value'] ) ) {
            return false;
        }

        $operator         = $condition['operator'];
        $expected_methods = array_map( 'sanitize_text_field', $condition['value'] );

        // Try both Woo default and plugin-specific session
        $chosen_method = WC()->session->get( 'chosen_payment_method' ) ?: WC()->session->get( 'gwpdr_selected_payment_method' );

        if ( empty( $chosen_method ) ) {
            return false;
        }

        return gwpdr_compare_list( $chosen_method, $operator, $expected_methods );
    }

    /**
     * Check if customer is logged in or not.
     *
     * @since 1.0
     *
     * @param array $cart_items (Unused here, but consistent with other methods).
     * @param array $condition  Must contain 'operator' => 'logged_in' or 'not_logged_in'.
     *
     * @return bool True if condition passes.
     */
    public static function customer_is_logged_in( $cart_items, $condition ) {
        if ( ! is_array( $condition ) || ! isset( $condition['operator'] ) || ! in_array( $condition['operator'], ['logged_in', 'not_logged_in'], true ) ) {
            return false;
        }

        $is_logged_in = is_user_logged_in();

        return $condition['operator'] === 'logged_in' ? $is_logged_in : ! $is_logged_in;
    }

    /**
     * checking customer_role condition.
     *
     * @since 1.0
     *
     * @param array $cart_items Unused (required for consistency).
     * @param array $condition  Condition with 'operator' and 'value' (list of role
     *
     * @return bool True if condition passes, false otherwise.
     */
    public static function customer_role( $cart_items, $condition ) {
        if ( ! is_array( $condition ) || ! isset( $condition['operator'], $condition['value'] ) || ! is_array( $condition['value'] ) ) {
            return false;
        }

        if ( ! is_user_logged_in() ) {
            return false;
        }

        $current_user = wp_get_current_user();
        $user_roles   = (array) $current_user->roles;

        return gwpdr_compare_list( $user_roles, $condition['operator'], $condition['value'] );
    }

    /**
     * Checking specific_customer condition.
     *
     * @since 1.0
     *
     * @param array $cart_items Unused (included for consistency).
     * @param array $condition  Condition with 'operator' and 'value' (array of user IDs).
     *
     * @return bool True if condition passes, false otherwise.
     */
    public static function specific_customer( $cart_items, $condition ) {
        if ( ! is_user_logged_in() || ! is_array( $condition ) || ! isset( $condition['operator'], $condition['value'] ) || ! is_array( $condition['value'] ) ) {
            return false;
        }

        $current_user_id = get_current_user_id();

        return gwpdr_compare_list( $current_user_id, $condition['operator'], $condition['value'] );
    }
}
