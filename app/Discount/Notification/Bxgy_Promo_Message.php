<?php
/**
 * Displays Buy X Get Y promotional message on product pages.
 *
 * Example outputs:
 *   🎉 Buy 1 and get 1 <a href="...">Product B</a> FREE!
 *   🎉 Buy 2 and get 1 <a href="...">Product B</a> with 20% off ✨
 *   🎉 Buy 3 and get 1 <a href="...">Product B</a> with ৳150.00 off 💸
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Discount\Notification;

use GiantWP_Discount_Rules\Traits\SingletonTrait;
use GiantWP_Discount_Rules\Discount\Manager\Discount_Helper;
use GiantWP_Discount_Rules\Discount\Condition\Conditions;

defined( 'ABSPATH' ) || exit;

class Bxgy_Promo_Message {
    use SingletonTrait;

    /**
     * Return message array for a specific product page.
     *
     * @param int $product_id Product ID.
     * @return array
     */
    public function get_offer_for_product_page( $product_id ) {
        $product_id = intval( $product_id );
        if ( ! $product_id ) {
            return array( 'active' => false );
        }

        $cart  = function_exists( 'WC' ) ? WC()->cart : null;
        $rules = $this->get_bxgy_rules();

        if ( empty( $rules ) ) {
            return array( 'active' => false );
        }

        foreach ( $rules as $rule ) {
            if ( ! $this->is_rule_valid( $rule, $cart ) ) {
                continue;
            }

            if ( ! $this->rule_targets_buy_product( $rule, $product_id ) ) {
                continue;
            }

            $msg = $this->build_bxgy_message_line( $rule );
            if ( $msg ) {
                return array(
                    'active'        => true,
                    'message_offer' => $msg,
                );
            }
        }

        return array( 'active' => false );
    }

    /**
     * Check if BXGY rule is valid for display.
     *
     * @param array     $rule Discount rule data.
     * @param \WC_Cart  $cart Cart object or null.
     * @return bool
     */
    private function is_rule_valid( $rule, $cart ) {
        $type_raw = strtolower( $rule['discountType'] ?? '' );
        $is_bxgy  = in_array( $type_raw, array( 'bxgy', 'buy_x_get_y', 'buy x get y' ), true );
        if ( ! $is_bxgy ) {
            return false;
        }

        if ( ( $rule['status'] ?? '' ) !== 'on' ) {
            return false;
        }

        if ( ! empty( $rule['schedule']['enableSchedule'] ) && ! Discount_Helper::is_schedule_active( $rule ) ) {
            return false;
        }

        if ( ! empty( $rule['usageLimits']['enableUsage'] ) && ! Discount_Helper::check_usage_limit( $rule ) ) {
            return false;
        }

        if (
            ! empty( $rule['enableConditions'] )
            && ! Conditions::check_all(
                $cart,
                $rule['conditions'] ?? array(),
                $rule['conditionsApplies'] ?? 'all'
            )
        ) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the rule targets this product as a BUY product.
     *
     * Supports:
     *  - Empty buyProduct  => all products
     *  - field=all_products (operator may be empty)
     *  - field=product|product_id with operator all|any|''
     *  - Explicit IN list (product/product_id + in|in_list)
     *
     * @param array $rule        Discount rule data.
     * @param int   $product_id  Product ID being viewed.
     * @return bool
     */
    private function rule_targets_buy_product( $rule, $product_id ) {
        $product_id = (int) $product_id;
        $buy        = $rule['buyProduct'] ?? array();

        // No constraints => all products
        if ( empty( $buy ) ) {
            return true;
        }

        foreach ( (array) $buy as $cond ) {
            $field    = strtolower( $cond['field'] ?? '' );
            $operator = strtolower( $cond['operator'] ?? '' );
            $values   = isset( $cond['value'] ) ? (array) $cond['value'] : array();
            $values   = array_map( 'intval', $values );

            // Your serialized shape: field = all_products, operator = "".
            if ( $field === 'all_products' ) {
                return true;
            }

            // product/product_id with "all"/"any" (or empty) operator => all products
            if (
                in_array( $field, array( 'product', 'product_id' ), true ) &&
                ( $operator === '' || in_array( $operator, array( 'all', 'any' ), true ) )
            ) {
                return true;
            }

            // Explicit product IN list
            if (
                in_array( $field, array( 'product', 'product_id' ), true ) &&
                in_array( $operator, array( 'in', 'in_list' ), true ) &&
                in_array( $product_id, $values, true )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build the readable message line for PDP display.
     *
     * Returns something like:
     * - "🎉 Buy 2 and get 1 <a>Product B</a> FREE!"
     * - "🎉 Buy 2 and get 1 <a>Product B</a> with 20% off ✨"
     *
     * @param array $rule Discount rule data.
     * @return string
     */
    private function build_bxgy_message_line( $rule ) {
        // Normalize counts (prefer top-level keys; fallback to nested blocks if present).
        $buy_block = $rule['buyProduct'][0] ?? array();
        $get_block = $rule['getProduct'][0] ?? array();

        $x_qty = max(
            1,
            intval( $rule['buyProductCount'] ?? $buy_block['buyProductCount'] ?? $buy_block['count'] ?? 1 )
        );
        $y_qty = max(
            1,
            intval( $rule['getProductCount'] ?? $get_block['getProductCount'] ?? $get_block['count'] ?? 1 )
        );

        // Determine GET (Y) target(s).
        $get_is_all = $this->block_targets_all_products( $get_block );
        $y_ids      = array();
        if ( isset( $get_block['value'] ) ) {
            $y_ids = array_map( 'intval', (array) $get_block['value'] );
        }

        // Mode & discount type normalization.
        // freeOrDiscount can be 'freeproduct' or 'free_product'
        $mode_raw = strtolower( $rule['freeOrDiscount'] ?? 'freeproduct' );
        $mode     = ( $mode_raw === 'free_product' || $mode_raw === 'freeproduct' ) ? 'freeproduct' : 'discount_product';

        // Discount type may come as discountTypeBxgy or discounttypeBogo (we normalize both)
        $disc_type_raw  = strtolower( $rule['discountTypeBxgy'] ?? $rule['discounttypeBogo'] ?? 'percentage' );
        $disc_type      = in_array( $disc_type_raw, array( 'percentage', 'fixed' ), true ) ? $disc_type_raw : 'percentage';
        $disc_value     = floatval( $rule['discountValue'] ?? 0 );

        // Build anchor (or generic label) for the GET product.
        $y_label_html = '';
        if ( $get_is_all ) {
            // Generic wording when Y is "any item".
            $y_label_html = sprintf(
                /* translators: generic label when GET product is any item */
                __( '%s item(s)', 'giantwp-discount-rules' ),
                $y_qty
            );
        } else {
            // Use the first Y product ID for the link; if none, bail gracefully.
            if ( empty( $y_ids ) ) {
                return '';
            }
            $y_product = wc_get_product( $y_ids[0] );
            if ( ! $y_product ) {
                return '';
            }

            $y_anchor = sprintf(
                '<a href="%s">%s</a>',
                esc_url( get_permalink( $y_product->get_id() ) ),
                esc_html( $y_product->get_name() )
            );

            /* translators: 1: quantity of Y, 2: linked product title HTML */
            $y_label_html = sprintf( __( '%1$d %2$s', 'giantwp-discount-rules' ), $y_qty, $y_anchor );
        }

        // Build the reward phrase (right side of "and get ...").
        if ( 'freeproduct' === $mode ) {
            /* translators: 1: Y label (already contains qty/title/link). */
            $reward_text  = __( '%1$s FREE!', 'giantwp-discount-rules' );
            $reward_phrase = sprintf( $reward_text, $y_label_html );

        } elseif ( 'percentage' === $disc_type ) {
            /* translators: 1: Y label, 2: discount percentage (e.g. "20%"). */
            $reward_text  = __( '%1$s with %2$s off ✨', 'giantwp-discount-rules' );
            $reward_phrase = sprintf( $reward_text, $y_label_html, sprintf( '%s%%', $disc_value ) );

        } else {
            /* translators: 1: Y label, 2: discount amount formatted as price. */
            $reward_text  = __( '%1$s with %2$s off 💸', 'giantwp-discount-rules' );
            $reward_phrase = sprintf( $reward_text, $y_label_html, wc_price( $disc_value ) );
        }

        /**
         * Full sentence prefix: "🎉 Buy X and get {reward_phrase}"
         */
        /* translators: 1: required buy quantity (X), 2: reward phrase (already-safe HTML). */
        $wrapper_text = __( '🎉 Buy %1$d and get %2$s', 'giantwp-discount-rules' );

        $final_line = sprintf(
            $wrapper_text,
            $x_qty,
            $reward_phrase
        );

        return $final_line;
    }

    /**
     * Helper: does a condition block target "all products"?
     * Recognizes:
     *  - field=all_products
     *  - field=product|product_id with operator all|any|''
     *
     * @param array $block A single condition block (e.g., buyProduct[0] or getProduct[0]).
     * @return bool
     */
    private function block_targets_all_products( $block ) {
        $field    = strtolower( $block['field'] ?? '' );
        $operator = strtolower( $block['operator'] ?? '' );

        if ( $field === 'all_products' ) {
            return true;
        }

        if ( in_array( $field, array( 'product', 'product_id' ), true ) ) {
            if ( $operator === '' || in_array( $operator, array( 'all', 'any' ), true ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve BXGY rules from the database.
     *
     * @return array
     */
    private function get_bxgy_rules() {
        // Support both new and legacy option keys.
        $rules = maybe_unserialize( get_option( 'giantwp_bxgy_discount', get_option( 'aio_bxgy_discount', array() ) ) );
        $rules = is_array( $rules ) ? $rules : array();

        // Normalize discountType if omitted.
        foreach ( $rules as &$r ) {
            if ( empty( $r['discountType'] ) ) {
                $r['discountType'] = 'bxgy';
            }
        }

        return $rules;
    }
}
