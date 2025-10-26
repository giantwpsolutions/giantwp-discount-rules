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

defined('ABSPATH') || exit;

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
            return [ 'active' => false ];
        }

        $cart  = function_exists( 'WC' ) ? WC()->cart : null;
        $rules = $this->get_bxgy_rules();

        if ( empty( $rules ) ) {
            return [ 'active' => false ];
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
                return [
                    'active'        => true,
                    'message_offer' => $msg,
                ];
            }
        }

        return [ 'active' => false ];
    }

    /**
     * Check if BXGY rule is valid for display.
     */
    private function is_rule_valid( $rule, $cart ) {
        $type = strtolower( $rule['discountType'] ?? '' );
        if ( ! in_array( $type, [ 'buy x get y', 'buy_x_get_y', 'bxgy' ], true ) ) {
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

        if ( ! empty( $rule['enableConditions'] ) && ! Conditions::check_all(
            $cart,
            $rule['conditions'] ?? [],
            $rule['conditionsApplies'] ?? 'all'
        ) ) {
            return false;
        }

        return true;
    }

    /**
     * Determine if the rule targets this product as a BUY product.
     */
    private function rule_targets_buy_product( $rule, $product_id ) {
        if ( empty( $rule['buyProduct'] ) ) {
            return false;
        }

        foreach ( $rule['buyProduct'] as $cond ) {
            $field    = strtolower( $cond['field'] ?? '' );
            $operator = strtolower( $cond['operator'] ?? '' );
            $values   = array_map( 'intval', (array) ( $cond['value'] ?? [] ) );

            if (
                in_array( $field, [ 'product', 'product_id' ], true ) &&
                in_array( $operator, [ 'in', 'in_list' ], true ) &&
                in_array( $product_id, $values, true )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build the readable message line for PDP display.
     */
    private function build_bxgy_message_line( $rule ) {
        $buy_block = $rule['buyProduct'][0] ?? [];
        $get_block = $rule['getProduct'][0] ?? [];

        $x_qty = max( 1, intval( $buy_block['buyProductCount'] ?? 1 ) );
        $y_qty = max( 1, intval( $get_block['getProductCount'] ?? 1 ) );
        $y_ids = array_map( 'intval', (array) ( $get_block['value'] ?? [] ) );

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

        $mode       = $rule['freeOrDiscount'] ?? 'free_product';
        $disc_type  = $rule['discountTypeBxgy'] ?? 'percentage';
        $disc_value = floatval( $rule['discountValue'] ?? 0 );

        if ( $mode === 'free_product' ) {
            $reward_phrase = sprintf(
                __( '%1$d %2$s FREE!', 'giantwp-discount-rules' ),
                $y_qty,
                $y_anchor
            );
        } elseif ( $disc_type === 'percentage' ) {
            $reward_phrase = sprintf(
                __( '%1$d %2$s with %3$s off ✨', 'giantwp-discount-rules' ),
                $y_qty,
                $y_anchor,
                sprintf( '%s%%', $disc_value )
            );
        } else {
            $reward_phrase = sprintf(
                __( '%1$d %2$s with %3$s off 💸', 'giantwp-discount-rules' ),
                $y_qty,
                $y_anchor,
                wc_price( $disc_value )
            );
        }

        return sprintf(
            __( '🎉 Buy %1$d and get %2$s', 'giantwp-discount-rules' ),
            $x_qty,
            $reward_phrase
        );
    }

    /**
     * Retrieve BXGY rules from the database.
     */
    private function get_bxgy_rules() {
        $rules = maybe_unserialize( get_option( 'giantwp_bxgy_discount', [] ) );
        return is_array( $rules ) ? $rules : [];
    }
}
