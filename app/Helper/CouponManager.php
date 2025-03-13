<?php

namespace AIO_WooDiscount\Helper;

defined('ABSPATH') || exit;

class CouponManager
{
    public static function is_valid_coupon($code)
    {
        try {
            $coupon = new \WC_Coupon($code);
            return $coupon->get_id() > 0 &&
                $coupon->is_valid() &&
                !$coupon->get_virtual(); // Ensure it's not a virtual coupon
        } catch (\Exception $e) {
            return false;
        }
    }
}
