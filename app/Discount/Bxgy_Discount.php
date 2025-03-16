<?php

namespace AIO_WooDiscount\Discount;

use AIO_WooDiscount\Discount\Condition\Conditions;
use AIO_WooDiscount\Discount\BogoBuyProduct\BogoBuyProduct;
use AIO_WooDiscount\Discount\Manager\Discount_Helper;

class Bxgy_Discount
{
    public function __construct()
    {
        add_action('woocommerce_cart_loaded_from_session', [$this, 'maybe_apply_discount'], 20);
        add_action('woocommerce_before_calculate_totals', [$this, 'adjust_discounted_items'], PHP_INT_MAX);
        // add_action('woocommerce_checkout_create_order', [$this, 'maybe_store_applied_bxgy_rule'], 20, 2);
    }

    public function maybe_apply_discount($cart = null)
    {
        if (is_null($cart)) {
            $cart = WC()->cart;
        }

        if (is_admin() && !defined('DOING_AJAX')) return;
        if (!$cart || $cart->is_empty()) return;

        $rules = maybe_unserialize(get_option('aio_bxgy_discount', []));
        if (empty($rules)) return;

        foreach ($rules as $rule) {
            if (($rule['status'] ?? '') !== 'on') continue;
            if (($rule['discountType'] ?? '') !== 'buy x get y') continue;

            // ✅ Schedule check
            if (!Discount_Helper::is_schedule_active($rule)) {
                continue;
            }

            // ✅ Usage limit
            if (!Discount_Helper::check_usage_limit($rule)) {
                continue;
            }

            // ✅ Conditions
            if (
                isset($rule['enableConditions']) && $rule['enableConditions'] &&
                !Conditions::check_all($cart, $rule['conditions'], $rule['conditionsApplies'] ?? 'all')
            ) {
                continue;
            }

            // 🧹 Remove previously added Y items
            foreach ($cart->get_cart() as $key => $item) {
                if (!empty($item['aio_bxgy_free_item']) && ($item['aio_bxgy_rule_id'] ?? '') === $rule['id']) {
                    $cart->remove_cart_item($key);
                }
            }

            // 🧮 Gather matching items
            $buy_items = [];
            $get_items = [];

            foreach ($cart->get_cart() as $key => $item) {
                if (!empty($item['aio_bxgy_free_item'])) continue;

                if (BogoBuyProduct::check_all($cart, $rule['buyProduct'], $rule['buyXApplies'] ?? 'any')) {
                    $buy_items[] = ['key' => $key, 'item' => $item];
                }

                if (BogoBuyProduct::check_all($cart, $rule['getProduct'], $rule['getYApplies'] ?? 'any')) {
                    $get_items[] = ['key' => $key, 'item' => $item];
                }
            }

            $buy_count = intval($rule['buyProduct'][0]['buyProductCount'] ?? 1);
            $get_count = intval($rule['getProduct'][0]['getProductCount'] ?? 1);
            $repeat    = $rule['isRepeat'] ?? false;

            $total_buy_qty  = array_sum(array_map(fn($i) => $i['item']['quantity'], $buy_items));
            $times_to_apply = $repeat ? floor($total_buy_qty / $buy_count) : ($total_buy_qty >= $buy_count ? 1 : 0);
            $total_free_qty = $times_to_apply * $get_count;

            // error_log("[BXGY] Matched Rule: {$rule['id']} | Times: {$times_to_apply} | Free Items: {$total_free_qty}");

            if ($total_free_qty <= 0) return;

            if (($rule['freeOrDiscount'] ?? 'free_product') === 'free_product') {
                $this->apply_free_y_items($get_items, $rule, $total_free_qty);
            } else {
                $this->mark_discount_y_items($get_items, $rule, $total_free_qty);
            }

            WC()->session->set('_aio_bxgy_applied_rules', [$rule['id']]);



            break;  // only first matching rule
        }
    }


    private function apply_free_y_items($eligible, $rule, $count)
    {
        $added = 0;
        $cart  = WC()->cart;

        $get_conditions = $rule['getProduct'] ?? [];

        foreach ($get_conditions as $condition) {
            $product_ids = $condition['value'] ?? [];

            foreach ($product_ids as $product_id) {
                while ($added < $count) {
                    $cart->add_to_cart(
                        $product_id,
                        1,
                        0,
                        [],
                        [
                            'aio_bxgy_free_item' => true,
                            'aio_bxgy_rule_id'   => $rule['id']
                        ]
                    );
                    $added++;
                }
            }
        }

        // error_log("[BXGY] Free items added: {$added}");
    }




    private function mark_discount_y_items($eligible, $rule, $count)
    {
        $cart           = WC()->cart;
        $discount_type  = $rule['discountTypeBxgy'] ?? 'fixed';
        $discount_value = floatval($rule['discountValue'] ?? 0);
        $max_value      = floatval($rule['maxValue'] ?? 0);
        $rule_id        = $rule['id'];

        $marked = 0;
        foreach ($eligible as $entry) {
            $key  = $entry['key'];
            $item = &$cart->cart_contents[$key];

            $qty = min($item['quantity'], $count - $marked);
            if ($qty <= 0) continue;

            $item['aio_bxgy_discount'] = [
                'rule_id' => $rule_id,
                'qty'     => $qty,
                'type'    => $discount_type,
                'value'   => $discount_value,
                'max'     => $max_value
            ];

            $marked += $qty;
            if ($marked >= $count) break;
        }

        // error_log("[BXGY] Discounted items marked: {$marked}");
    }

    public function adjust_discounted_items($cart)
    {
        foreach ($cart->get_cart() as $key => $item) {
            if (!empty($item['aio_bxgy_free_item'])) {
                $item['data']->set_price(0);
                continue;
            }

            if (!empty($item['aio_bxgy_discount'])) {
                $info           = $item['aio_bxgy_discount'];
                $original_price = $item['data']->get_price();

                $discount = $info['type'] === 'percentage'
                    ? ($original_price * $info['value'] / 100)
                    :        $info['value'];

                if ($info['max'] > 0) {
                    $discount = min($discount, $info['max']);
                }

                if ($item['quantity'] > $info['qty']) {
                    $full_price_qty   = $item['quantity'] - $info['qty'];
                    $discounted_total = $info['qty'] * ($original_price - $discount);
                    $normal_total     = $full_price_qty * $original_price;
                    $blended_price    = ($discounted_total + $normal_total) / $item['quantity'];
                    $item['data']->set_price($blended_price);
                } else {
                    $item['data']->set_price($original_price - $discount);
                }
            }
        }
    }
}
