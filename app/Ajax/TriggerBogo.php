<?php

namespace AIO_WooDiscount\Ajax;

class TriggerBogo
{
    public function __construct()
    {
        add_action('wp_ajax_aio_check_bogo_discounts', [$this, 'aio_check_bogo_discounts']);
        add_action('wp_ajax_nopriv_aio_check_bogo_discounts', [$this, 'aio_check_bogo_discounts']);
    }

    public function aio_check_bogo_discounts()
    {
        check_ajax_referer('aio_triggerBogo_nonce', 'nonce');

        // 🔥 Safe trigger
        do_action('aio_run_bogo_discount', WC()->cart);
        WC()->cart->calculate_totals();

        wp_send_json_success(['message' => '[AIO BOGO] Applied!']);
    }
}
