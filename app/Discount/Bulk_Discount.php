<?php

namespace AIO_WooDiscount\Discount;

use AIO_WooDiscount\Discount\BogoBuyProduct\BogoBuyProduct;
use AIO_WooDiscount\Discount\Condition\Conditions;
use AIO_WooDiscount\Discount\Manager\Discount_Helper;

class Bulk_Discount
{
    public function __construct()
    {
        add_action('woocommerce_before_calculate_totals', [$this, 'maybe_apply_bulk_discount'], 99);
    }

    public function maybe_apply_bulk_discount($cart)
    {
        if (is_admin() && !defined('DOING_AJAX')) return;
        if (!$cart || $cart->is_empty()) return;

        $rules = maybe_unserialize(get_option('aio_bulk_discount', []));
        if (empty($rules)) return;

        foreach ($rules as $rule) {
            if (($rule['status'] ?? '') !== 'on') continue;
            if (($rule['discountType'] ?? '') !== 'bulk discount') continue;

            // ✅ Schedule check
            if (!Discount_Helper::is_schedule_active($rule)) continue;

            // ✅ Usage limit check
            if (!Discount_Helper::check_usage_limit($rule)) continue;

            // ✅ Conditions check
            if (
                isset($rule['enableConditions']) && $rule['enableConditions'] &&
                !Conditions::check_all($cart, $rule['conditions'], $rule['conditionsApplies'] ?? 'all')
            ) continue;

            $mode           = $rule['getItem'] ?? 'alltogether';
            $buy_conditions = $rule['buyProducts'] ?? [];
            $ranges         = $rule['bulkDiscounts'] ?? [];

            $matched_items = [];
            foreach ($cart->get_cart() as $key => $item) {
                if (BogoBuyProduct::check_all($cart, $buy_conditions, $rule['getApplies'] ?? 'any')) {
                    $matched_items[$key] = $item;
                }
            }

            if (empty($matched_items)) continue;

            if ($mode === 'alltogether') {
                $total_qty = array_sum(array_column($matched_items, 'quantity'));
                $range     = $this->get_matching_range($ranges, $total_qty);
                if (!$range) continue;

                foreach ($matched_items as $key => $item) {
                    $original_price = $item['data']->get_price();
                    $discount       = $this->calculate_discount($original_price, $range);

                    if ($range['discountTypeBulk'] === 'flat_price') {
                        $item['data']->set_price(floatval($range['discountValue']));
                    } else {
                        $item['data']->set_price($original_price - $discount);
                    }
                }

                WC()->session->set('_aio_bulk_applied_rules', [$rule['id']]);
            } elseif ($mode === 'iq_each') {
                foreach ($matched_items as $key => $item) {
                    $qty   = $item['quantity'];
                    $range = $this->get_matching_range($ranges, $qty);
                    if (!$range) continue;

                    $original_price = $item['data']->get_price();
                    $discount       = $this->calculate_discount($original_price, $range);

                    if ($range['discountTypeBulk'] === 'flat_price') {
                        $item['data']->set_price(floatval($range['discountValue']));
                    } else {
                        $item['data']->set_price($original_price - $discount);
                    }
                }

                WC()->session->set('_aio_bulk_applied_rules', [$rule['id']]);
            }
        }
    }

    private function get_matching_range($ranges, $qty)
    {
        foreach ($ranges as $range) {
            $from = intval($range['fromcount'] ?? 0);
            $to   = intval($range['toCount'] ?? 0);
            if ($qty >= $from && $qty <= $to) {
                return $range;
            }
        }
        return null;
    }

    private function calculate_discount($price, $range)
    {
        $type  = $range['discountTypeBulk'] ?? 'fixed';
        $value = floatval($range['discountValue'] ?? 0);
        $max   = floatval($range['maxValue'] ?? 0);

        switch ($type) {
            case 'percentage':
                $discount = ($price * $value) / 100;
                if ($max > 0) {
                    $discount = min($discount, $max);
                }
                return max(0, $discount);

            case 'fixed':
                $discount = $value;
                if ($max > 0) {
                    $discount = min($discount, $max);
                }
                return max(0, $discount);

            case 'flat_price':
                // Flat is the target price, not a discount amount
                return null;

            default:
                return 0;
        }
    }
}
