<?php
/**
 * Flatpercentage  Class.
 *
 * Handles Flat/Percentage Discount
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Discount;

defined('ABSPATH') || exit;

use GiantWP_Discount_Rules\Discount\Condition\Conditions;
use GiantWP_Discount_Rules\Discount\Manager\Discount_Helper;
use GiantWP_Discount_Rules\Traits\SingletonTrait;

/**
 * Class FlatPercentage_Discount
 *
 * Handles flat or percentage-based cart-wide discounts using real WooCommerce coupons.
 */
class FlatPercentage_Discount {

    use SingletonTrait;
    /**
     * Constructor - attaches hooks for cart fee calculation and suppressing messages.
     */
    public function __construct() {
        add_action( 'woocommerce_cart_calculate_fees', [ $this, 'maybe_apply_discount' ], 20 );
        add_filter( 'woocommerce_coupon_message', [ $this, 'suppress_coupon_message' ], 100, 3 );
        add_filter( 'woocommerce_coupon_error', [ $this, 'suppress_coupon_message' ], 100, 3 );
    }

    /**
     * Conditionally applies a flat or percentage discount coupon to the cart.
     *
     * @param \WC_Cart $cart WooCommerce cart object
     * @return void
     */
    public function maybe_apply_discount( $cart ) {
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }
    
        if ( ! $cart || $cart->is_empty() ) {
            return;
        }
    
        $rules      = $this->get_discount_rules();
        $settings   = maybe_unserialize( get_option( 'giantwp_discountrules_settings', [] ) );
        $use_regular = isset( $settings['discountBasedOn'] ) && $settings['discountBasedOn'] === 'regular_price';
    
        if ( empty( $rules ) ) {
            return;
        }
    
        $matched = false;
    
        foreach ( $rules as $rule ) {
            if (
                ! isset( $rule['discountType'] ) ||
                strtolower( $rule['discountType'] ) !== 'flat/percentage' ||
                ( $rule['status'] ?? '' ) !== 'on'
            ) {
                continue;
            }
    
            if ( ! Discount_Helper::is_schedule_active( $rule ) ) {
                continue;
            }
    
            if ( ! Discount_Helper::check_usage_limit( $rule ) ) {
                continue;
            }
    
            if (
                isset( $rule['enableConditions'] ) && $rule['enableConditions'] &&
                ! Conditions::check_all( $cart, $rule['conditions'], $rule['conditionsApplies'] ?? 'all' )
            ) {
                continue;
            }
    
            $fp_type        = $rule['fpDiscountType'] ?? 'fixed';
            $discount_value = floatval( $rule['discountValue'] ?? 0 );
            $max_value      = floatval( $rule['maxValue'] ?? 0 );
    
            $cart_total = $use_regular
                ? array_sum(
                    array_map(
                        fn( $item ) => $item['quantity'] * $item['data']->get_regular_price(),
                        $cart->get_cart()
                    )
                )
                : $cart->get_subtotal();
    
            // Calculate discount
            if ( $fp_type === 'percentage' ) {
                $calculated_discount = ( $cart_total * $discount_value / 100 );
              
            } else {
                $calculated_discount = $discount_value;
            }
            
            $calculated_discounts = $calculated_discount;

            // Always treat max_value as amount (not percent)
            if ( $max_value > 0 ) {
                $calculated_discounts = min( $calculated_discount, $max_value );
            }
    
            if ( $calculated_discounts > 0 ) {
                $this->create_or_update_coupon( $rule, $calculated_discounts );
                $cart->apply_coupon( $rule['couponName'] );
                $matched = true;
                break;
            }
        }
    
        // Remove hidden coupon if not matched
        if ( ! $matched ) {
            foreach ( $cart->get_applied_coupons() as $code ) {
                $coupon = new \WC_Coupon( $code );
                if ( $coupon->get_meta( 'gwpdr_is_hidden_coupon' ) ) {
                    $cart->remove_coupon( $code );
                }
            }
        }
    }
    
    


    /**
     * Creates or updates a real WooCommerce coupon with calculated discount.
     *
     * @param array $rule The discount rule
     * @param float $amount The discount amount to apply
     * @return void
     */
    private function create_or_update_coupon( $rule, $amount ) {
        $coupon_code   = $rule['couponName'];
        $fp_type       = $rule['fpDiscountType'] ?? 'fixed';
        $discount_type = 'fixed_cart'; // default fallback
    
        // Determine if the discount is capped
        $original_discount_value = floatval( $rule['discountValue'] ?? 0 );
        $cart_total = WC()->cart->get_subtotal();
        $calculated_discount = $fp_type === 'percentage'
            ? ( $cart_total * $original_discount_value / 100 )
            : $original_discount_value;
    
        $is_capped = $amount < $calculated_discount;
    
        // Final discount type logic
        if ( $fp_type === 'percentage' && ! $is_capped ) {
            $discount_type = 'percent';
        } else {
            $discount_type = 'fixed_cart';
        }
    
        // Try to find existing coupon by rule ID
        $existing = get_posts([
            'post_type'   => 'shop_coupon',
            'post_status' => 'publish',
            'numberposts' => 1,
            'meta_query'  => [
                [
                    'key'     => 'gwpdr_rule_id',
                    'value'   => $rule['id'],
                    'compare' => '=',
                ]
            ]
        ]);
    
        if ( ! empty( $existing ) ) {
            // Existing coupon found by rule ID
            $coupon = new \WC_Coupon( $existing[0]->ID );
    
            // Update name if changed
            if ( $coupon->get_code() !== $coupon_code ) {
                wp_update_post([
                    'ID'         => $coupon->get_id(),
                    'post_title' => $coupon_code,
                    'post_name'  => $coupon_code,
                ]);
                $coupon = new \WC_Coupon( $coupon_code ); // Reload with updated code
            }
        } else {
            // Create new coupon
            $coupon = new \WC_Coupon();
            $coupon->set_code( $coupon_code );
            $coupon->set_discount_type( $discount_type );
            $coupon->set_individual_use( false );
            $coupon->set_usage_limit( 999999 );
            $coupon->set_description( __('Auto-generated by GiantWP Discount Rules', 'giantwp-discount-rules') );
            $coupon->update_meta_data( 'gwpdr_rule_id', $rule['id'] );
            $coupon->update_meta_data( 'gwpdr_is_hidden_coupon', true );
        }
    
        // Always update the amount and discount type
        $coupon->set_discount_type( $discount_type );
        $coupon->set_amount( $amount );
    
        // Optional debug info for admin trace
        if ( $is_capped ) {
            $coupon->update_meta_data( 'gwpdr_debug_note', "Discount capped from {$calculated_discount} to {$amount}" );
        }
    
        $coupon->save();
    }
    

    /**
     * Fetch discount rules from database.
     *
     * @return array
     */
    private function get_discount_rules(): array {
        return maybe_unserialize( get_option( 'giantwp_flatpercentage_discount', [] ) ) ?: [];
    }


    /**
     * Suppress frontend coupon messages for hidden/internal coupons.
     *
     * @param string $message
     * @param string $message_code
     * @param \WC_Coupon $coupon
     * @return string
     */
    public function suppress_coupon_message( $message, $message_code, $coupon ) {
        if ( $coupon instanceof \WC_Coupon && $coupon->get_meta( 'gwpdr_is_hidden_coupon' ) ) {
            return ''; // Hide all coupon messages
        }

        return $message;
    }
}
