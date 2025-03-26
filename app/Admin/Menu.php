<?php
  /**
 * Admin Menu Setup for GiantWP Discount Rules.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Admin;

defined( 'ABSPATH' ) || exit;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

/**
 * Admin Menu Class
 */
class Menu {

    use SingletonTrait;

    /**
     * Class Constructor
     */
    public function __construct() {

        add_action( 'admin_menu', [ $this, 'giantWP_menu' ] );
    }

    /**
     * Registers the GiantWP Discount Rules submenu under WooCommerce.
     */
    public function giantWP_menu() {

        global $submenu;
        // Check if WooCommerce is active
        if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            return;  // WooCommerce is not active, don't add the menu
        }

        // Add submenu under WooCommerce Marketing menu

        $parent_slug = 'woocommerce';
        $capability  = 'manage_woocommerce';

        add_submenu_page( $parent_slug, __( 'GiantWP Discount Rules', 'giantwp-discount-rules' ), __( 'GiantWP Discount Rules', 'giantwp-discount-rules' ), $capability, 'giantwp-discount-rules', [ $this, 'render_page' ] );


        /**
         * Tab Submenu Creation
         */
        if ( current_user_can( $capability ) ) {
            $submenu['giantwp-discount-rules'][] = [ __( 'Discount Rule', 'giantwp-discount-rules' ), $capability, 'admin.php?page=giantwp-discount-rules#/' ];
            $submenu['giantwp-discount-rules'][] = [ __( 'Settings', 'giantwp-discount-rules' ), $capability, 'admin.php?page=giantwp-discount-rules#/settings' ];
        }
    }

    /**
     * Render the submenu page content
     * 
     * @return void
     */
    public function render_page() {

        echo '<div class="wrap"><div id="giantwp-discount-rules-dashboard"></div></div>';
    }
}
