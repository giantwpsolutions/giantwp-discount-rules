<?php
  /**
 * Installer class for initializing plugin components.
 *
 * @package AIO_WooDiscount
 */

namespace AIO_WooDiscount;

defined( 'ABSPATH' ) || exit;

use AIO_WooDiscount\Traits\SingletonTrait;
use AIO_WooDiscount\Admin\Menu;
use AIO_WooDiscount\Assets;
use AIO_WooDiscount\Ajax\Checkout_Ajax_Handler;
use AIO_WooDiscount\Ajax\TriggerCart;
use AIO_WooDiscount\Ajax\TriggerBogo;
use AIO_WooDiscount\Discount\Bogo_Discount;
use AIO_WooDiscount\Discount\FlatPercentage_Discount;
use AIO_WooDiscount\Discount\Manager\CouponDisplay;
use AIO_WooDiscount\Discount\Manager\Bogo_Free_Item_Handler;
use AIO_WooDiscount\Discount\Manager\DiscountLabel;
use AIO_WooDiscount\Discount\Manager\FlatPercentage_Validator;
use AIO_WooDiscount\Discount\UsageTrack\FlatPercentageUsage;

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
        TriggerCart::instance();
        TriggerBogo::instance();
        Bogo_Free_Item_Handler::instance();
        DiscountLabel::instance();
        FlatPercentage_Discount::instance();
        FlatPercentageUsage::instance();
        Bogo_Discount::instance();

        

    }
}
