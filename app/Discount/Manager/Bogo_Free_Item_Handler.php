<?php

namespace AIO_WooDiscount\Discount\Manager;

defined('ABSPATH') || exit;

class Bogo_Free_Item_Handler
{

    public function __construct()
    {
        // Ensure price is zero or overridden
        add_action('woocommerce_before_calculate_totals', [$this, 'set_bogo_item_prices'], PHP_INT_MAX);

        // Display correct price in cart
        add_filter('woocommerce_cart_item_price', [$this, 'display_cart_price'], PHP_INT_MAX, 3);
        add_filter('woocommerce_cart_item_subtotal', [$this, 'display_cart_subtotal'], PHP_INT_MAX, 3);

        add_filter('woocommerce_update_cart_validation', function ($check, $cart_item_key, $values) {
            if (empty($values['aio_bogo_free_item'])) {
                return $check;
            }

            $cart_contents = WC()->cart->cart_contents;
            if (!isset($cart_contents[$cart_item_key])) {
                return false; // Block update if item mysteriously vanished
            }

            return $check;
        }, 1000, 3);
    }

    public function set_bogo_item_prices($cart)
    {
        if (is_admin() && !defined('DOING_AJAX')) return;

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (!empty($cart_item['is_bogo_extra']) && isset($cart_item['override_price'])) {
                $price = floatval($cart_item['override_price']);
                $cart_item['data']->set_price($price);
            }
        }
    }

    public function display_cart_price($price_html, $cart_item, $cart_item_key)
    {
        if (!empty($cart_item['is_bogo_extra'])) {
            if (isset($cart_item['override_price']) && floatval($cart_item['override_price']) === 0.0) {
                return '<span class="aio-free-price">' . esc_html__('Free', 'aio-woodiscount') . '</span>';
            } else {
                return wc_price($cart_item['data']->get_price());
            }
        }
        return $price_html;
    }

    public function display_cart_subtotal($subtotal_html, $cart_item, $cart_item_key)
    {
        if (!empty($cart_item['is_bogo_extra'])) {
            if (isset($cart_item['override_price']) && floatval($cart_item['override_price']) === 0.0) {
                return '<span class="aio-free-subtotal">' . esc_html__('Free', 'aio-woodiscount') . '</span>';
            } else {
                return wc_price($cart_item['data']->get_price() * $cart_item['quantity']);
            }
        }
        return $subtotal_html;
    }
}
