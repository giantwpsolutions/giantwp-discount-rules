<?php
  /**
 * Admin Menu Setup for DealBuilder Discount Rules.
 *
 * @package DealBuilder_Discount_Rules
 */

namespace DealBuilder_Discount_Rules\Admin;

defined( 'ABSPATH' ) || exit;

use DealBuilder_Discount_Rules\Traits\SingletonTrait;

/**
 * Admin Menu Class
 */
class Menu {

    use SingletonTrait;

    /**
     * Class Constructor
     */
    public function __construct() {

        add_action( 'admin_menu', [ $this, 'dealbuilder_menu' ] );
    }

    /**
     * Registers the DealBuilder Discount Rules submenu under WooCommerce.
     */
    public function dealbuilder_menu() {

        global $submenu;
        // Check if WooCommerce is active
        if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            return;  // WooCommerce is not active, don't add the menu
        }

        // Add submenu under WooCommerce Marketing menu

        $parent_slug = 'woocommerce';
        $capability  = 'manage_woocommerce';

        add_submenu_page( $parent_slug, __( 'DealBuilder Discount Rules', 'dealbuilder-discount-rules' ), __( 'DealBuilder Discount Rules', 'dealbuilder-discount-rules' ), $capability, 'dealbuilder-discount-rules', [ $this, 'render_page' ] );


        /**
         * Tab Submenu Creation
         */
        if ( current_user_can( $capability ) ) {
            $submenu['dealbuilder-discount-rules'][] = [ __( 'Discount Rule', 'dealbuilder-discount-rules' ), $capability, 'admin.php?page=dealbuilder-discount-rules#/' ];
            $submenu['dealbuilder-discount-rules'][] = [ __( 'Settings', 'dealbuilder-discount-rules' ), $capability, 'admin.php?page=dealbuilder-discount-rules#/settings' ];
        }
    }

    /**
     * Render the submenu page content
     * 
     * @return void
     */
    public function render_page() {

        echo '<div class="wrap"><div id="dealbuilder-discount-rules-dashboard"></div></div>';
    }
}
