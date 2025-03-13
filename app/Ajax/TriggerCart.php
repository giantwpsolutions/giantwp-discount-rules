<?php

namespace AIO_WooDiscount\Ajax;

class TriggerCart
{

    public function __construct()
    {
        add_action('wp_ajax_aio_check_cart_discounts', [$this, 'aio_check_cart_discounts']);
        add_action('wp_ajax_nopriv_aio_check_cart_discounts', [$this, 'aio_check_cart_discounts']);
    }

    public function aio_check_cart_discounts()
    {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'aio_discount_nonce')) {
            wp_send_json_error(['message' => 'Invalid nonce']);
        }

        if (!WC()->cart) {
            wp_send_json_error(['message' => 'Cart not found']);
        }

        // Apply discount logic
        (new \AIO_WooDiscount\Discount\FlatPercentage_Discount())->maybe_apply_discount(true);

        wp_send_json_success(['message' => 'Discount checked']);
    }
}
