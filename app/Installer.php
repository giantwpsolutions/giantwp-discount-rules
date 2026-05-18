<?php
  /**
 * Installer class for initializing plugin components.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules;

defined( 'ABSPATH' ) || exit;

use GiantWP_Discount_Rules\Traits\SingletonTrait;
use GiantWP_Discount_Rules\Admin\Menu;
use GiantWP_Discount_Rules\Assets;
use GiantWP_Discount_Rules\Ajax\Checkout_Ajax_Handler;
use GiantWP_Discount_Rules\Ajax\TriggerCart;
use GiantWP_Discount_Rules\Ajax\TriggerBogo;
use GiantWP_Discount_Rules\Discount\Bogo_Discount;
use GiantWP_Discount_Rules\Discount\FlatPercentage_Discount;
use GiantWP_Discount_Rules\Discount\Manager\CouponDisplay;
use GiantWP_Discount_Rules\Discount\Manager\Bogo_Free_Item_Handler;
use GiantWP_Discount_Rules\Discount\Manager\DiscountLabel;
use GiantWP_Discount_Rules\Discount\Manager\FlatPercentage_Validator;
use GiantWP_Discount_Rules\Discount\Manager\Product_Badge;
use GiantWP_Discount_Rules\Discount\UsageTrack\FlatPercentageUsage;
use GiantWP_Discount_Rules\Helper\PromoMessage;
use GiantWP_Discount_Rules\Discount\Analytics\Analytics_Tracker;
use GiantWP_Discount_Rules\Pro\MarginProtection\Margin_Guard;
use GiantWP_Discount_Rules\Pro\MarginProtection\Product_Cost_Meta;


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
        Bogo_Free_Item_Handler::instance();
        Product_Badge::instance();
        DiscountLabel::instance();
        FlatPercentage_Discount::instance();
        FlatPercentageUsage::instance();
        Bogo_Discount::instance();
        PromoMessage::instance();
        Analytics_Tracker::instance();

        // Pro-only features
        if ( defined( 'GIANTWP_DISCOUNT_RULES_PRO_ACTIVE' ) && GIANTWP_DISCOUNT_RULES_PRO_ACTIVE ) {
            Margin_Guard::instance();
            Product_Cost_Meta::instance();
        }

        gwpdr_appsero_init_tracker();


        
// Make sure the namespace matches where you placed the files.


    }
}
