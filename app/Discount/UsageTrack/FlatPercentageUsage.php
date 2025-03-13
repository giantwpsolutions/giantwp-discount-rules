<?php

namespace AIO_WooDiscount\Discount\UsageTrack;



defined('ABSPATH') || exit;
class FlatPercentageUsage
{
    const META_KEY   = 'aio_flatpercentage_discount_rules';
    const OPTION_KEY = 'aio_flatpercentage_discount';

    public function __construct()
    {
        // Store applied rule IDs to meta during checkout
        add_action('woocommerce_checkout_create_order', [$this, 'store_applied_rule_ids'], 100, 2);

        // Track usage after payment completes
        add_action('woocommerce_payment_complete', [$this, 'increase_usage']);
        add_action('woocommerce_order_status_completed', [$this, 'increase_usage']);

        // Free order fallback
        add_filter('woocommerce_order_get_checkout_order_received_url', [$this, 'track_free_order'], 20, 2);
    }

    public function store_applied_rule_ids($order, $data)
    {
        if (!function_exists('aio_get_tracked_rules')) return;

        $applied = aio_get_tracked_rules();
        if (!empty($applied)) {
            update_post_meta($order->get_id(), self::META_KEY, $applied);
            error_log("📦 Stored applied rule IDs in order #{$order->get_id()}: " . implode(', ', $applied));
        } else {
            error_log("⚠️ No tracked rule IDs to store in order #{$order->get_id()}");
        }
    }

    public function increase_usage($order_id)
    {
        UsageTracker::increase_use_time($order_id, self::OPTION_KEY, self::META_KEY);
    }

    public function track_free_order($url, $order)
    {
        if ($order instanceof \WC_Order && !$order->needs_payment()) {
            $this->increase_usage($order->get_id());
        }

        return $url;
    }
}
