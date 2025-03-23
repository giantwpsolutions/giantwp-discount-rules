<?php
  /**
 * Installer class for initializing plugin components.
 *
 * @package AIO_DiscountRules
 */

namespace AIO_DiscountRules;

defined( 'ABSPATH' ) || exit;

use AIO_DiscountRules\Traits\SingletonTrait;
use AIO_DiscountRules\Admin\Menu;
use AIO_DiscountRules\Assets;
use AIO_DiscountRules\Ajax\Checkout_Ajax_Handler;
use AIO_DiscountRules\Ajax\TriggerCart;
use AIO_DiscountRules\Ajax\TriggerBogo;
use AIO_DiscountRules\Discount\Bogo_Discount;
use AIO_DiscountRules\Discount\FlatPercentage_Discount;
use AIO_DiscountRules\Discount\Manager\CouponDisplay;
use AIO_DiscountRules\Discount\Manager\Bogo_Free_Item_Handler;
use AIO_DiscountRules\Discount\Manager\DiscountLabel;
use AIO_DiscountRules\Discount\Manager\FlatPercentage_Validator;
use AIO_DiscountRules\Discount\UsageTrack\FlatPercentageUsage;

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
