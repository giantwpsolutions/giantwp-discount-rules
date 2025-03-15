<?php

namespace AIO_WooDiscount\Ajax;

class TriggerBogo
{
    public function __construct()
    {
        add_action('wp_ajax_aio_check_bogo_discounts', [$this, 'run_bogo_logic']);
        add_action('wp_ajax_nopriv_aio_check_bogo_discounts', [$this, 'run_bogo_logic']);
    }

    public function run_bogo_logic()
    {
        check_ajax_referer('aio_triggerBogo_nonce', 'nonce');

        if (!WC()->cart || WC()->cart->is_empty()) {
            wp_send_json_error(['message' => 'Cart is empty']);
        }

        $discount = new \AIO_WooDiscount\Discount\Bogo_Discount();
        $discount->maybe_apply_discount(WC()->cart);
        WC()->cart->calculate_totals();
        WC()->cart->set_session();

        wp_send_json_success([
            'message' => 'BOGO discount applied',
            'fragments' => \WC_AJAX::get_refreshed_fragments()
        ]);
    }
}
