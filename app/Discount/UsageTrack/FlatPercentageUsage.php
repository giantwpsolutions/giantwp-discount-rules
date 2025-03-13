<?php

namespace AIO_WooDiscount\Discount\UsageTrack;

defined('ABSPATH') || exit;



class FlatPercentageUsage
{
    const OPTION_KEY = 'aio_flatpercentage_discount';

    public function __construct()
    {
        add_action('woocommerce_order_status_completed', [$this, 'sync_usage_from_coupon'], 20, 1);
    }

    /**
     * Sync real coupon usage to custom discount rules.
     *
     * @param int $order_id
     */
    public function sync_usage_from_coupon($order_id)
    {
        $order = wc_get_order($order_id);
        if (!$order) return;

        $applied_coupons = $order->get_coupon_codes();
        if (empty($applied_coupons)) return;

        $rules = maybe_unserialize(get_option(self::OPTION_KEY, []));
        $changed = false;

        foreach ($rules as &$rule) {
            $coupon_code = sanitize_title($rule['couponName'] ?? '');
            if (!$coupon_code) continue;

            if (in_array($coupon_code, $applied_coupons, true)) {
                $wc_coupon = new \WC_Coupon($coupon_code);
                $usage = (int) $wc_coupon->get_usage_count();

                if (!isset($rule['usedCount']) || $rule['usedCount'] != $usage) {
                    $rule['usedCount'] = $usage;
                    $changed = true;
                    error_log("🔄 Synced usedCount for rule '{$rule['id']}' (coupon: {$coupon_code}) to {$usage}");
                }
            }
        }

        if ($changed) {
            update_option(self::OPTION_KEY, maybe_serialize($rules));
        }
    }
}
