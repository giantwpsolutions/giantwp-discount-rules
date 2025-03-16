<?php

namespace AIO_WooDiscount;

use AIO_WooDiscount\Traits\SingletonTrait;
use AIO_WooDiscount\Admin\Menu;
use AIO_WooDiscount\Assets;
use AIO_WooDiscount\Ajax\Checkout_Ajax_Handler;
use AIO_WooDiscount\Ajax\TriggerCart;
use AIO_WooDiscount\Ajax\TriggerBogo;
use AIO_WooDiscount\Discount\Manager\CouponDisplay;
use AIO_WooDiscount\Discount\Manager\Bogo_Free_Item_Handler;
use AIO_WooDiscount\Discount\Manager\FlatPercentage_Validator;
use AIO_WooDiscount\Discount\Bxgy_Discount;
use AIO_WooDiscount\Discount\UsageTrack\Bxgy_Usage_Handler;
use AIO_WooDiscount\Discount\UsageTrack\Bogo_Usage_Handler;


/**
 * Plugin Functions Installer Class
 */
class Installer
{


    use SingletonTrait;

    /**
     * Class Constructor 
     */
    public function __construct()
    {
        Assets::instance();

        if (is_admin()) {
            Menu::instance();
        }
        FlatPercentage_Validator::instance();
        CouponDisplay::instance();
        Checkout_Ajax_Handler::instance();
        new TriggerCart();
        new TriggerBogo();
        new Bogo_Free_Item_Handler();
        new Bogo_Usage_Handler();
    }
}
