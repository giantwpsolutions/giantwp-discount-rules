<?php

namespace AIO_WooDiscount\Discount;

use AIO_WooDiscount\Discount\Condition\Conditions;
use AIO_WooDiscount\Discount\Manager\Discount_Helper;

class Shipping_Discount
{
    public function __construct()
    {
        add_action('woocommerce_cart_calculate_fees', [$this, 'maybe_apply_shipping_discount'], 20);
    }

    /**
     * Apply shipping discount or custom fee based on rules.
     *
     * @param \WC_Cart $cart
     * @return void
     */
    public function maybe_apply_shipping_discount($cart)
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        if (!$cart || $cart->is_empty()) {
            return;
        }

        $rules = maybe_unserialize(get_option('aio_shipping_discount', []));
        if (empty($rules)) {
            return;
        }

        foreach ($rules as $rule) {
            if (($rule['status'] ?? '') !== 'on') continue;
            if (($rule['discountType'] ?? '') !== 'shipping discount') continue;

            // ✅ Schedule check
            if (!Discount_Helper::is_schedule_active($rule)) continue;

            // ✅ Usage limit check
            if (!Discount_Helper::check_usage_limit($rule)) continue;

            // ✅ Conditions check
            if (
                isset($rule['enableConditions']) && $rule['enableConditions'] &&
                !Conditions::check_all($cart, $rule['conditions'], $rule['conditionsApplies'] ?? 'all')
            ) continue;

            $shipping_total = $cart->get_shipping_total();
            if ($shipping_total <= 0) continue;

            $mode          = $rule['shippingDiscountType'] ?? 'reduceFee';                // reduceFee or customFee
            $discount_type = $rule['pDiscountType'] ?? 'fixed';
            $value         = floatval($rule['discountValue'] ?? 0);
            $max_value     = isset($rule['maxValue']) ? floatval($rule['maxValue']) : 0;

            if ($mode === 'reduceFee') {
                // 🧮 Calculate reduction
                $discount = $this->calculate_discount($shipping_total, $discount_type, $value, $max_value);

                if ($discount > 0) {
                    $cart->add_fee(__('Shipping Discount', 'aio-woodiscount'), -$discount);
                }
            } elseif ($mode === 'customFee') {
                // 🧮 Calculate custom fee
                $fee_amount = $this->calculate_discount($shipping_total, $discount_type, $value, $max_value);

                if ($fee_amount > 0) {
                    $cart->add_fee(__('Custom Shipping Fee', 'aio-woodiscount'), $fee_amount);
                }
            }

            WC()->session->set('_aio_shipping_applied_rules', [$rule['id']]);

            break; // Only apply first matching rule
        }
    }

    /**
     * Calculate discount or fee based on type.
     *
     * @param float $base
     * @param string $type fixed|percentage
     * @param float $value
     * @param float $max
     * @return float
     */
    private function calculate_discount($base, $type, $value, $max)
    {
        $amount = 0;

        if ($type === 'percentage') {
            $amount = ($base * $value) / 100;
        } else {
            $amount = $value;
        }

        if ($max > 0) {
            $amount = min($amount, $max);
        }

        return max(0, $amount);
    }
}
