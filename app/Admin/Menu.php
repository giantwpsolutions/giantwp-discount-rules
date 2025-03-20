<?php
  /**
 * Admin Menu Setup for AIO WooDiscount Plugin.
 *
 * @package AIO_WooDiscount
 */

namespace AIO_WooDiscount\Admin;

defined( 'ABSPATH' ) || exit;

use AIO_WooDiscount\Traits\SingletonTrait;

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

        add_submenu_page( $parent_slug, __( 'All In One WooDiscount', 'all-in-one-woodiscount' ), __( 'AIO WooDiscount', 'all-in-one-woodiscount' ), $capability, 'aio-woodiscount', [ $this, 'render_page' ] );


        /**
         * Tab Submenu Creation
         */
        if ( current_user_can( $capability ) ) {
            $submenu['aio-woodiscount'][] = [ __( 'Discount Rule', 'all-in-one-woodiscount' ), $capability, 'admin.php?page=aio-woodiscount#/' ];
            $submenu['aio-woodiscount'][] = [ __( 'Settings', 'all-in-one-woodiscount' ), $capability, 'admin.php?page=aio-woodiscount#/settings' ];
        }
    }

    /**
     * Render the submenu page content
     * 
     * @return void
     */
    public function render_page() {

        echo '<div class="wrap"><div id="aio-woodiscount-dashboard"></div></div>';
    }
}
