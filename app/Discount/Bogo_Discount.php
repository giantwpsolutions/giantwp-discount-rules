<?php

namespace AIO_WooDiscount\Discount;

use AIO_WooDiscount\Discount\Condition\Conditions;
use AIO_WooDiscount\Discount\BogoBuyProduct\BogoBuy_Field;
use AIO_WooDiscount\Discount\BogoBuyProduct\BogoBuyProduct;

/**
 * Class Bogo_Discount
 * Handles Buy One Get One discount logic, including free items and discounted items.
 */
class Bogo_Discount
{
    /**
     * Register WooCommerce hooks.
     */
    public function __construct()
    {
        add_action('woocommerce_cart_loaded_from_session', [$this, 'maybe_apply_discount'], 20);
        add_action('woocommerce_before_calculate_totals', [$this, 'adjust_discounted_items'], PHP_INT_MAX);
    }

    /**
     * Evaluate BOGO rules and apply free or discounted items to the cart.
     *
     * @param \WC_Cart|null $cart
     * @return void
     */
    public function maybe_apply_discount($cart = null)
    {
        if (is_null($cart)) {
            $cart = WC()->cart;
        }

        if (is_admin() && !defined('DOING_AJAX')) return;
        if (!$cart || $cart->is_empty()) return;

        // Clear previous BOGO data
        foreach ($cart->get_cart() as $key => $item) {
            if (!empty($item['aio_bogo_discount'])) {
                unset($cart->cart_contents[$key]['aio_bogo_discount']);
            }

            if (!empty($item['aio_bogo_free_item'])) {
                $cart->remove_cart_item($key);
            }
        }

        $rules = $this->get_discount_rules();
        if (empty($rules)) return;

        foreach ($rules as $rule) {
            if (!isset($rule['discountType']) || strtolower($rule['discountType']) !== 'bogo') continue;
            if (($rule['status'] ?? '') !== 'on') continue;
            if (!$this->is_schedule_active($rule)) continue;
            if (!$this->check_usage_limit($rule)) continue;

            if (
                isset($rule['enableConditions']) && $rule['enableConditions'] &&
                !Conditions::check_all($cart, $rule['conditions'], $rule['conditionsApplies'] ?? 'all')
            ) continue;

            if (!BogoBuyProduct::check_all($cart, $rule['buyProduct'] ?? [], $rule['bogoApplies'] ?? 'all')) continue;

            $free_or_discount = $rule['freeOrDiscount'] ?? 'freeproduct';

            if ($free_or_discount === 'freeproduct') {
                $this->apply_free_item($rule);
            } else {
                $this->mark_discounted_items($rule);
            }

            break;
        }
    }

    /**
     * Apply free items to the cart based on matched rule.
     *
     * @param array $rule
     * @return void
     */
    private function apply_free_item($rule)
    {
        $cart_items = WC()->cart->get_cart();
        $eligible   = $this->get_eligible_products($cart_items, $rule['buyProduct']);
        $buy_count  = intval($rule['buyProductCount'] ?? 1);
        $get_count  = intval($rule['getProductCount'] ?? 1);
        $repeat     = $rule['isRepeat'] ?? false;

        $total_quantity = array_sum(array_column($eligible, 'quantity'));
        $repeat_times   = $repeat ? floor($total_quantity / $buy_count) : ($total_quantity >= $buy_count ? 1 : 0);
        $add_count      = $repeat_times * $get_count;

        $added = 0;
        foreach ($eligible as $item) {
            if ($added >= $add_count) break;

            WC()->cart->add_to_cart(
                $item['product_id'],
                1,
                $item['variation_id'] ?? 0,
                [],
                [
                    'aio_bogo_free_item' => true,
                    'aio_bogo_rule_id'   => $rule['id']
                ]
            );

            $added++;
        }
    }

    /**
     * Mark cart items with discount metadata for BOGO rules.
     *
     * @param array $rule
     * @return void
     */
    private function mark_discounted_items($rule)
    {
        $cart       = WC()->cart;
        $cart_items = $cart->get_cart();
        $eligible   = [];

        foreach ($cart_items as $key => $item) {
            if (!empty($item['aio_bogo_free_item'])) continue;

            foreach ($rule['buyProduct'] as $condition) {
                $field = $condition['field'] ?? '';
                if (method_exists(BogoBuy_Field::class, $field) && BogoBuy_Field::$field([$item], $condition)) {
                    $eligible[] = ['key' => $key, 'item' => $item];
                    break;
                }
            }
        }

        $buy_count = intval($rule['buyProductCount'] ?? 1);
        $get_count = intval($rule['getProductCount'] ?? 1);
        $repeat    = $rule['isRepeat'] ?? false;

        $total_quantity = array_sum(array_map(fn($i) => $i['item']['quantity'] ?? 0, $eligible));
        $needed_qty     = $buy_count + $get_count;
        $repeat_times   = $repeat ? floor($total_quantity / $needed_qty) : ($total_quantity >= $needed_qty ? 1 : 0);
        $discount_qty   = $repeat_times * $get_count;

        if ($discount_qty <= 0) return;

        usort($eligible, function ($a, $b) {
            return $a['item']['data']->get_price() <=> $b['item']['data']->get_price();
        });

        $discounted     = 0;
        $discount_type  = $rule['discounttypeBogo'] ?? 'fixed';
        $discount_value = floatval($rule['discountValue'] ?? 0);
        $max            = floatval($rule['maxValue'] ?? 0);
        $rule_id        = $rule['id'];

        foreach ($eligible as $entry) {
            $key  = $entry['key'];
            $item = &$cart->cart_contents[$key];

            $qty_to_discount = min($item['quantity'], $discount_qty - $discounted);
            if ($qty_to_discount <= 0) continue;

            $item['aio_bogo_discount'] = [
                'rule_id' => $rule_id,
                'qty'     => $qty_to_discount,
                'type'    => $discount_type,
                'value'   => $discount_value,
                'max'     => $max
            ];

            $discounted += $qty_to_discount;
            if ($discounted >= $discount_qty) break;
        }
    }

    /**
     * Apply price adjustments for discounted items or set free item price to zero.
     *
     * @param \WC_Cart $cart
     * @return void
     */
    public function adjust_discounted_items($cart)
    {
        foreach ($cart->get_cart() as $key => $item) {
            if (!empty($item['aio_bogo_free_item'])) {
                $item['data']->set_price(0);
                continue;
            }

            if (!empty($item['aio_bogo_discount'])) {
                $info            = $item['aio_bogo_discount'];
                $original_price  = $item['data']->get_price();
                $qty_to_discount = $info['qty'] ?? 0;

                $discount = $info['type'] === 'percentage'
                    ? ($original_price * $info['value'] / 100)
                    :  $info['value'];

                if ($info['max'] > 0) {
                    $discount = min($discount, $info['max']);
                }

                $new_price = $original_price;
                if ($item['quantity'] > $qty_to_discount) {
                    $full_price_qty   = $item['quantity'] - $qty_to_discount;
                    $discounted_total = $qty_to_discount * ($original_price - $discount);
                    $normal_total     = $full_price_qty * $original_price;
                    $blended_price    = ($discounted_total + $normal_total) / $item['quantity'];
                    $new_price        = $blended_price;
                } else {
                    $new_price = $original_price - $discount;
                }

                $item['data']->set_price($new_price);
            }
        }
    }

    /**
     * Get products from cart matching the rule's buy conditions.
     *
     * @param array $cart_items
     * @param array $buy_conditions
     * @return array
     */
    private function get_eligible_products($cart_items, $buy_conditions)
    {
        $eligible = [];
        foreach ($cart_items as $item) {
            if (!empty($item['aio_bogo_free_item'])) continue;
            foreach ($buy_conditions as $condition) {
                $field = $condition['field'] ?? '';
                if (method_exists(BogoBuy_Field::class, $field) && BogoBuy_Field::$field([$item], $condition)) {
                    $eligible[] = $item;
                    break;
                }
            }
        }
        return $eligible;
    }

    /**
     * Remove free items from cart by rule ID.
     *
     * @param string|int $rule_id
     * @return void
     */
    private function remove_bogo_items($rule_id)
    {
        foreach (WC()->cart->get_cart() as $key => $item) {
            if (!empty($item['aio_bogo_free_item']) && ($item['aio_bogo_rule_id'] ?? '') === $rule_id) {
                WC()->cart->remove_cart_item($key);
            }
        }
    }

    /**
     * Get BOGO discount rules from DB.
     *
     * @return array
     */
    private function get_discount_rules(): array
    {
        return maybe_unserialize(get_option('aio_bogo_discount', [])) ?: [];
    }

    /**
     * Check if a rule is within its valid schedule.
     *
     * @param array $rule
     * @return bool
     */
    private function is_schedule_active($rule): bool
    {
        if (!isset($rule['schedule']['enableSchedule']) || !$rule['schedule']['enableSchedule']) {
            return true;
        }

        $now = current_time('timestamp');
        $start = strtotime($rule['schedule']['startDate'] ?? '');
        $end = strtotime($rule['schedule']['endDate'] ?? '');

        return ($now >= $start && $now <= $end);
    }

    /**
     * Check if usage limits are respected.
     *
     * @param array $rule
     * @return bool
     */
    private function check_usage_limit($rule): bool
    {
        if (!isset($rule['usageLimits']['enableUsage']) || !$rule['usageLimits']['enableUsage']) {
            return true;
        }

        $limit = intval($rule['usageLimits']['usageLimitsCount'] ?? 0);
        $used = intval($rule['usedCount'] ?? 0);

        return $used < $limit;
    }
}
