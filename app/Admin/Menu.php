<?php
  /**
 * Admin Menu Setup for AIO WooDiscount Plugin.
 *
 * @package AIO_DiscountRules
 */

namespace AIO_DiscountRules\Admin;

defined( 'ABSPATH' ) || exit;

use AIO_DiscountRules\Traits\SingletonTrait;

/**
 * Admin Menu Class
 */
class Menu {

    use SingletonTrait;

    /**
     * Class Constructor
     */
    public function __construct() {

        add_action( 'admin_menu', [ $this, 'AIO_Woodiscount_menu' ] );
    }

    /**
     * Registers the AIO WooDiscount submenu under WooCommerce.
     */
    public function AIO_Woodiscount_menu() {

        global $submenu;
        // Check if WooCommerce is active
        if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            return;  // WooCommerce is not active, don't add the menu
        }

        // Add submenu under WooCommerce Marketing menu

        $parent_slug = 'woocommerce';
        $capability  = 'manage_woocommerce';

        add_submenu_page( $parent_slug, __( 'All In One Discount Rules', 'all-in-one-discount-rules' ), __( 'AIO Discount Rules', 'all-in-one-discount-rules' ), $capability, 'aio-discount-rules', [ $this, 'render_page' ] );


        /**
         * Tab Submenu Creation
         */
        if ( current_user_can( $capability ) ) {
            $submenu['aio-woodiscount'][] = [ __( 'Discount Rule', 'all-in-one-discount-rules' ), $capability, 'admin.php?page=aio-discount-rulest#/' ];
            $submenu['aio-woodiscount'][] = [ __( 'Settings', 'all-in-one-discount-rules' ), $capability, 'admin.php?page=aio-discount-rules#/settings' ];
        }
    }

    /**
     * Render the submenu page content
     * 
     * @return void
     */
    public function render_page() {

        echo '<div class="wrap"><div id="aio-discount-rules-dashboard"></div></div>';
    }
}
