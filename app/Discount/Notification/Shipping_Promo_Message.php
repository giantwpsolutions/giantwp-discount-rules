<?php
/**
 * Build shipping promo messages for product page and cart.
 *
 * This matches how Shipping_Discount applies shipping rules.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Discount\Notification;

defined( 'ABSPATH' ) || exit;

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
     *
     * @param int $product_id Product ID.
     * @return array
     */
    public function get_offer_for_product_page( $product_id ) {
        $product_id = intval( $product_id );
        if ( ! $product_id ) {
            return array( 'active' => false );
        }

        $cart = function_exists( 'WC' ) ? WC()->cart : null;

        foreach ( $this->get_shipping_rules() as $rule ) {
            if ( ! $this->is_rule_valid_for_cart( $rule, $cart ) ) {
                continue;
            }

            // OPTIONAL: If you later add product targeting logic,
            // you can require that THIS specific product triggers the deal.

            $msg = $this->build_shipping_deal_message_for_customer( $rule );
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
     * Cart-level message:
     * "Shipping discount active: FREE shipping"
     * or "Shipping discount active: shipping will cost 10.00৳"
     *
     * We check the CURRENT cart, same way Shipping_Discount does.
     *
     * @return array
     */
    public function get_offer_for_cart() {
        if ( ! function_exists( 'WC' ) ) {
            return array( 'active' => false );
        }

        $cart = WC()->cart;
        if ( ! $cart || $cart->is_empty() ) {
            return array( 'active' => false );
        }

        foreach ( $this->get_shipping_rules() as $rule ) {
            if ( ! $this->is_rule_valid_for_cart( $rule, $cart ) ) {
                continue;
            }

            $msg = $this->build_shipping_deal_message_for_customer( $rule );
            if ( $msg ) {

                /* translators: %s: short human-readable shipping deal text (e.g. "FREE shipping 🎉", "shipping will cost $5.00"). */
                $prefix_text = __( '🚚 Shipping discount active: %s', 'giantwp-discount-rules' );

                return array(
                    'active'       => true,
                    'message_cart' => sprintf(
                        $prefix_text,
                        $msg
                    ),
                );
            }
        }

        return array( 'active' => false );
    }

    /**
     * Decide if this rule is currently valid for the given cart,
     * using the SAME checks Shipping_Discount uses.
     *
     * @param array    $rule Rule data.
     * @param \WC_Cart $cart Cart object or null.
     * @return bool
     */
    private function is_rule_valid_for_cart( $rule, $cart ) {
        // must be on.
        if ( ( $rule['status'] ?? '' ) !== 'on' ) {
            return false;
        }

        // must be shipping discount type.
        if ( ( $rule['discountType'] ?? '' ) !== 'shipping discount' ) {
            return false;
        }

        // schedule check.
        if ( ! Discount_Helper::is_schedule_active( $rule ) ) {
            return false;
        }

        // usage limit check.
        if ( ! Discount_Helper::check_usage_limit( $rule ) ) {
            return false;
        }

        // extra conditions check.
        if (
            ! empty( $rule['enableConditions'] ) &&
            ! empty( $rule['conditions'] )
        ) {
            $applies_logic = $rule['conditionsApplies'] ?? 'all';

            if ( ! Conditions::check_all( $cart, $rule['conditions'], $applies_logic ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Convert rule's shipping deal into a short string for humans.
     *
     * Examples return values:
     *  - "FREE shipping 🎉"
     *  - "shipping will cost 10.00৳"
     *  - "10.00৳ off shipping"
     *  - "20% off shipping"
     *
     * @param array $rule Rule data.
     * @return string
     */
    private function build_shipping_deal_message_for_customer( $rule ) {
        $mode          = $rule['shippingDiscountType'] ?? 'reduceFee'; // 'reduceFee' or 'customFee'.
        $discount_type = $rule['pDiscountType'] ?? 'fixed';            // 'fixed' or 'percentage'.
        $value         = floatval( $rule['discountValue'] ?? 0 );

        // customFee = we replace normal shipping with a specific fee.
        if ( 'customFee' === $mode ) {

            // output: "shipping will cost X"
            /* translators: %s: formatted custom shipping cost (e.g. "$4.99"). */
            $text = __( 'shipping will cost %s', 'giantwp-discount-rules' );

            return sprintf(
                $text,
                wc_price( max( 0, $value ) )
            );
        }

        // reduceFee = we discount existing shipping cost.
        if ( 'reduceFee' === $mode ) {

            // percentage discount
            if ( 'percentage' === $discount_type ) {

                // 100% off = FREE shipping.
                if ( $value >= 100 ) {
                    /* translators: This text is shown when shipping becomes completely free. */
                    return __( 'FREE shipping 🎉', 'giantwp-discount-rules' );
                }

                // "<X>% off shipping"
                /* translators: %s: discount percentage amount (e.g. "20%"). */
                $text = __( '%s off shipping', 'giantwp-discount-rules' );

                return sprintf(
                    $text,
                    esc_html( $value . '%' )
                );
            }

            // fixed amount off shipping, e.g. "10.00৳ off shipping".
            /* translators: %s: formatted monetary amount taken off shipping cost (e.g. "$5.00"). */
            $text = __( '%s off shipping', 'giantwp-discount-rules' );

            return sprintf(
                $text,
                wc_price( $value )
            );
        }

        return '';
    }

    /**
     * Read rules from DB.
     * This is the exact same option Shipping_Discount uses.
     *
     * @return array
     */
    private function get_shipping_rules() {
        $rules = maybe_unserialize( get_option( 'giantwp_shipping_discount', array() ) );
        return is_array( $rules ) ? $rules : array();
    }
}
