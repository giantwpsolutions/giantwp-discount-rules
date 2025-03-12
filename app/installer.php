<?php

namespace AIO_WooDiscount;

use AIO_WooDiscount\Traits\SingletonTrait;
use AIO_WooDiscount\Admin\Menu;
use AIO_WooDiscount\Assets;
use AIO_WooDiscount\Ajax\Checkout_Ajax_Handler;

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


        Checkout_Ajax_Handler::instance();
    }
}
