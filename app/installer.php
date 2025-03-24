<?php
  /**
 * Installer class for initializing plugin components.
 *
 * @package DealBuilder_Discount_Rules
 */

namespace DealBuilder_Discount_Rules;

defined( 'ABSPATH' ) || exit;

use DealBuilder_Discount_Rules\Traits\SingletonTrait;
use DealBuilder_Discount_Rules\Admin\Menu;
use DealBuilder_Discount_Rules\Assets;
use DealBuilder_Discount_Rules\Ajax\Checkout_Ajax_Handler;
use DealBuilder_Discount_Rules\Ajax\TriggerCart;
use DealBuilder_Discount_Rules\Ajax\TriggerBogo;
use DealBuilder_Discount_Rules\Discount\Bogo_Discount;
use DealBuilder_Discount_Rules\Discount\FlatPercentage_Discount;
use DealBuilder_Discount_Rules\Discount\Manager\CouponDisplay;
use DealBuilder_Discount_Rules\Discount\Manager\Bogo_Free_Item_Handler;
use DealBuilder_Discount_Rules\Discount\Manager\DiscountLabel;
use DealBuilder_Discount_Rules\Discount\Manager\FlatPercentage_Validator;
use DealBuilder_Discount_Rules\Discount\UsageTrack\FlatPercentageUsage;

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
