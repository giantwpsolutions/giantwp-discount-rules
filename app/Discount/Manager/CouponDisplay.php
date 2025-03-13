<?php

namespace AIO_WooDiscount\Discount\Manager;

use AIO_WooDiscount\Traits\SingletonTrait;

defined('ABSPATH') || exit;

class CouponDisplay
{
    use SingletonTrait;
    public function __construct()
    {
        add_action('pre_get_posts', [$this, 'hide_auto_generated_coupons']);
    }

    /**
     * Hide coupons with custom meta key from the WooCommerce Coupons admin list.
     */
    public function hide_auto_generated_coupons($query)
    {
        if (!is_admin() || !$query->is_main_query()) return;
        if ($query->get('post_type') !== 'shop_coupon') return;

        $meta_query = $query->get('meta_query') ?: [];

        $meta_query[] = [
            'key'     => 'aio_is_hidden_coupon',
            'compare' => 'NOT EXISTS',
        ];

        $query->set('meta_query', $meta_query);
    }
}
