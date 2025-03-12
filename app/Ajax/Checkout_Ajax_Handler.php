<?php

namespace AIO_WooDiscount\Ajax;

use AIO_WooDiscount\Traits\SingletonTrait;

class Checkout_Ajax_Handler
{

    use SingletonTrait;

    public function __construct()
    {
        add_action('wp_ajax_aio_set_payment_method', [$this, 'aio_set_payment_method']);
        add_action('wp_ajax_nopriv_aio_set_payment_method', [$this, 'aio_set_payment_method']);
    }


    public function aio_set_payment_method()
    {
        check_ajax_referer('aio_nonce', 'security');
        error_log("📡 AIO: Received AJAX request to set payment method");

        // Ensure WC session is initialized in AJAX context
        if (null === WC()->session || !WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
        }

        $method = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : '';

        if (empty($method)) {
            wp_send_json_error(['message' => 'No method provided']);
        }

        error_log("💳 Method received: " . $method);

        WC()->session->set('aio_selected_payment_method', $method);
        wp_send_json_success(['message' => 'Payment method stored']);
    }
}
