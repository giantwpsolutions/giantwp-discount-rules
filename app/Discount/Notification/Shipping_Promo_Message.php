<?php
/**
 * Build shipping promo messages for product page and cart.
 *
 * This matches how Shipping_Discount applies shipping rules.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Discount\Notification;

defined('ABSPATH') || exit;

use GiantWP_Discount_Rules\Traits\SingletonTrait;
use GiantWP_Discount_Rules\Discount\Manager\Discount_Helper;
use GiantWP_Discount_Rules\Discount\Condition\Conditions;

class Shipping_Promo_Message {
    use SingletonTrait;

    /**
     * Product page message:
     * "Buy this and shipping will be FREE" or "Buy this and shipping will be 10.00৳".
     *
     * We consider a rule "relevant to this product" if its conditions would pass
     * assuming customer buys this product (or it's already in cart).
     */
    public function get_offer_for_product_page( $product_id ) {
        $product_id = intval($product_id);
        if ( ! $product_id ) {
            return [ 'active' => false ];
        }

        $cart = function_exists('WC') ? WC()->cart : null;

        foreach ( $this->get_shipping_rules() as $rule ) {
            if ( ! $this->is_rule_valid_for_cart($rule, $cart) ) {
                continue;
            }

            // OPTIONAL: if you eventually add "applyToProducts" targeting,
            // you can filter here. For now we assume rule is global.
            // If you only want to show product message when THIS product
            // actually triggers cheaper shipping, you can add logic like:
            //
            // if ( ! $this->product_triggers_rule($rule, $product_id) ) continue;

            $msg = $this->build_shipping_deal_message_for_customer( $rule );
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
     * Cart-level message:
     * "Shipping discount active: FREE shipping" or
     * "Shipping discount active: shipping will cost 10.00৳"
     *
     * We check the CURRENT cart, same way Shipping_Discount does.
     */
    public function get_offer_for_cart() {
        if ( ! function_exists('WC') ) {
            return [ 'active' => false ];
        }

        $cart = WC()->cart;
        if ( ! $cart || $cart->is_empty() ) {
            return [ 'active' => false ];
        }

        foreach ( $this->get_shipping_rules() as $rule ) {
            if ( ! $this->is_rule_valid_for_cart($rule, $cart) ) {
                continue;
            }

            $msg = $this->build_shipping_deal_message_for_customer( $rule );

            if ( $msg ) {
                return [
                    'active'       => true,
                    'message_cart' => sprintf(
                        // ex: "🚚 Shipping discount active: FREE shipping"
                        __( '🚚 Shipping discount active: %s', 'giantwp-discount-rules' ),
                        $msg
                    ),
                ];
            }
        }

        return [ 'active' => false ];
    }

    /**
     * Decide if this rule is currently valid for the given cart,
     * using the SAME checks Shipping_Discount uses.
     */
    private function is_rule_valid_for_cart( $rule, $cart ) {
        // must be on
        if ( ($rule['status'] ?? '') !== 'on' ) {
            return false;
        }

        // must be shipping discount type
        if ( ($rule['discountType'] ?? '') !== 'shipping discount' ) {
            return false;
        }

        // schedule check
        if ( ! Discount_Helper::is_schedule_active($rule) ) {
            return false;
        }

        // usage limit check
        if ( ! Discount_Helper::check_usage_limit($rule) ) {
            return false;
        }

        // extra conditions check
        if (
            ! empty($rule['enableConditions']) &&
            ! empty($rule['conditions'])
        ) {
            $applies_logic = $rule['conditionsApplies'] ?? 'all';
            if ( ! Conditions::check_all($cart, $rule['conditions'], $applies_logic) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Convert rule's shipping deal into a short string for humans.
     *
     * Examples:
     *  - "FREE shipping"
     *  - "shipping will cost 10.00৳"
     *  - "10.00৳ off shipping"
     *  - "20% off shipping"
     */
    private function build_shipping_deal_message_for_customer( $rule ) {
        $mode          = $rule['shippingDiscountType'] ?? 'reduceFee'; // 'reduceFee' or 'customFee'
        $discount_type = $rule['pDiscountType'] ?? 'fixed';            // 'fixed' or 'percentage'
        $value         = floatval($rule['discountValue'] ?? 0);

        // customFee = we replace normal shipping with a specific fee.
        if ( $mode === 'customFee' ) {
            // "shipping will cost X"
            return sprintf(
                __( 'shipping will cost %s', 'giantwp-discount-rules' ),
                wc_price( max(0, $value) )
            );
        }

        // reduceFee = we discount existing shipping cost.
        if ( $mode === 'reduceFee' ) {

            // % discount
            if ( $discount_type === 'percentage' ) {

                // 100% off = FREE shipping
                if ( $value >= 100 ) {
                    return __( 'FREE shipping 🎉', 'giantwp-discount-rules' );
                }

                return sprintf(
                    __( '%s off shipping', 'giantwp-discount-rules' ),
                    esc_html( $value . '%' )
                );
            }

            // fixed amount off shipping
            // ex: "10.00৳ off shipping"
            return sprintf(
                __( '%s off shipping', 'giantwp-discount-rules' ),
                wc_price( $value )
            );
        }

        return '';
    }

    /**
     * Read rules from DB.
     * This is the exact same option Shipping_Discount uses.
     */
    private function get_shipping_rules() {
        $rules = maybe_unserialize( get_option( 'giantwp_shipping_discount', [] ) );
        return is_array($rules) ? $rules : [];
    }
}
