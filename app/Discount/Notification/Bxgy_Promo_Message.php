<?php
/**
 * Displays Buy X Get Y promotional message on product pages.
 *
 * Example outputs:
 *   🎉 Buy 1 of Product A and get <a href="...">Product B</a> FREE!
 *   🎉 Buy 2 of Product A and get <a href="...">Product B</a> with 20% off ✨
 *   🎉 Buy 3 of Product A and get <a href="...">Product B</a> with ৳150.00 off 💸
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
        $type = strtolower( $rule['discountType'] ?? '' );
        if ( ! in_array( $type, array( 'buy x get y', 'buy_x_get_y', 'bxgy' ), true ) ) {
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
     * @param array $rule        Discount rule data.
     * @param int   $product_id  Product ID being viewed.
     * @return bool
     */
    private function rule_targets_buy_product( $rule, $product_id ) {
        if ( empty( $rule['buyProduct'] ) ) {
            return false;
        }

        foreach ( $rule['buyProduct'] as $cond ) {
            $field    = strtolower( $cond['field'] ?? '' );
            $operator = strtolower( $cond['operator'] ?? '' );
            $values   = array_map( 'intval', (array) ( $cond['value'] ?? array() ) );

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

        // We assume each rule has arrays `buyProduct` and `getProduct`,
        // and each of those entries may carry quantities like buyProductCount / getProductCount.
        $buy_block = $rule['buyProduct'][0] ?? array();
        $get_block = $rule['getProduct'][0] ?? array();

        $x_qty = max( 1, intval( $buy_block['buyProductCount'] ?? 1 ) );
        $y_qty = max( 1, intval( $get_block['getProductCount'] ?? 1 ) );
        $y_ids = array_map( 'intval', (array) ( $get_block['value'] ?? array() ) );

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

        $mode       = $rule['freeOrDiscount'] ?? 'free_product'; // 'free_product' or 'discount_product'
        $disc_type  = $rule['discountTypeBxgy'] ?? 'percentage'; // 'percentage' or 'fixed'
        $disc_value = floatval( $rule['discountValue'] ?? 0 );

        /**
         * Build the reward phrase (right side of "and get ...").
         *
         * Examples of $reward_phrase:
         * - "1 Product B FREE!"
         * - "1 Product B with 20% off ✨"
         * - "1 Product B with ৳150.00 off 💸"
         */
        if ( 'free_product' === $mode ) {

            /* translators: 1: number of free items (Y quantity), 2: linked product title HTML. */
            $reward_text = __( '%1$d %2$s FREE!', 'giantwp-discount-rules' );

            $reward_phrase = sprintf(
                $reward_text,
                $y_qty,
                $y_anchor
            );

        } elseif ( 'percentage' === $disc_type ) {

            /* translators: 1: number of discounted items (Y quantity), 2: linked product title HTML, 3: discount percentage (for example "20%"). */
            $reward_text = __( '%1$d %2$s with %3$s off ✨', 'giantwp-discount-rules' );

            $reward_phrase = sprintf(
                $reward_text,
                $y_qty,
                $y_anchor,
                sprintf( '%s%%', $disc_value )
            );

        } else {

            /* translators: 1: number of discounted items (Y quantity), 2: linked product title HTML, 3: discount amount formatted as price. */
            $reward_text = __( '%1$d %2$s with %3$s off 💸', 'giantwp-discount-rules' );

            $reward_phrase = sprintf(
                $reward_text,
                $y_qty,
                $y_anchor,
                wc_price( $disc_value )
            );
        }

        /**
         * Now build the full sentence prefix:
         * "🎉 Buy X and get {reward_phrase}"
         *
         * Example:
         * - "🎉 Buy 2 and get 1 Product B FREE!"
         */
        /* translators: 1: required buy quantity (X), 2: reward phrase built from the rule (already safe HTML). */
        $wrapper_text = __( '🎉 Buy %1$d and get %2$s', 'giantwp-discount-rules' );

        $final_line = sprintf(
            $wrapper_text,
            $x_qty,
            $reward_phrase
        );

        return $final_line;
    }

    /**
     * Retrieve BXGY rules from the database.
     *
     * @return array
     */
    private function get_bxgy_rules() {
        $rules = maybe_unserialize( get_option( 'giantwp_bxgy_discount', array() ) );
        return is_array( $rules ) ? $rules : array();
    }
}
