<?php
/**
* Plugin Name: DealBuilder Discount Rules
* Plugin URI: https://giantwpsolutions.com/plugins/dealbuilder-discount-rules
* Description: DealBuilder Discount Rules is a powerful one-stop discount solution for WooCommerce. With this plugin, you can create any kind of discount rules.
* Version: 1.0.1
* Author: Giant WP Solutions
* Author URI: https://giantwpsolutions.com
* License: GPLv2 or later
* Text Domain: dealbuilder-discount-rules
* WC requires at least: 3.0.0
* WC tested up to: 9.7.1
* Requires PHP: 7.4
* WooCommerce HPOS support: yes
* Domain Path: /languages
*@package DealBuilder Discount Rules
*/


if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__  . '/app/functions.php';

/**
 * The main plugin class
 */
final class DealBuilder_Discount_Rules
{

    /**
     * The plugin version
     */
    const version = '1.0.1';

    /**
     * Class Constructor
     */
    public function __construct()
    {
        register_activation_hook( __FILE__ , [ $this, 'activate' ] );
        add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded'] );
        add_action( 'admin_notices', [ $this, 'check_woocommerce_active' ] );
        add_filter( 'plugin_action_links_dealbuilder-discount-rules/dealbuilder-discount-rules.php', [ $this, 'db_discount_settings_link' ] );
        $this->declare_hpos_compatibility();
        $this->define_constants();
    }

    /**
     * Initializes a singleton instance
     * 
     * @return DealBuilder_Discount_Rules
     */
    public static function init()
    {
        static $instance = false;

        if ( ! $instance) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Necessary plugin constants
     * @return void
     */
    public function define_constants()
    {
        define( 'DBDR_VERSION', self::version );
        define( 'DBDR_FILE', __FILE__ );
        define( 'DBDR_PATH', plugin_dir_path(__FILE__) );
        define( 'DBDR_LANG_DIR', plugin_basename( dirname(__FILE__) ) . '/languages' );
    }

    /**
     * Load plugin textdomain for translations
     * @return void
     */
    public function on_plugins_loaded()
    {
        load_plugin_textdomain( 'dealbuilder-discount-rules', false, DBDR_LANG_DIR );

        if ( class_exists( 'WooCommerce' ) ) {

            new DealBuilder_Discount_Rules\Api\Api();
            new DealBuilder_Discount_Rules\Installer();

        } else {
            add_action( 'admin_notices', [ $this, 'woocommerce_missing_notice' ] );
        }
    }

    /**
     * Show alert if WooCommerce is deactivated
     */
    public function check_woocommerce_active()
    {
        if ( ! class_exists( 'WooCommerce' ) ) {
            db_WoocommerceDeactivationAlert();
        }
    }

    /**
     * Do stuff upon plugin activation
     * @return void
     */
    public function activate()
    {
        if ( ! class_exists( 'WooCommerce' ) ) {
            deactivate_plugins( plugin_basename( __FILE__ ) );
        
            wp_die(
                esc_html__( 'All-in-One Discount Rules requires WooCommerce to be installed and active.', 'dealbuilder-discount-rules' ),
                esc_html__( 'Plugin dependency check', 'dealbuilder-discount-rules' ),
                [ 'back_link' => true ]
            );
        }

        $install_time = get_option( 'DBDR_installation_time' );

        if ( ! $install_time ) {
            update_option( 'DBDR_installation_time', time() );
        }

        update_option( 'dealbuilder_version', self::version );
    }

    /*
     * Woocommerce Missing Notice
     *
     * @return void
     */
    public function woocommerce_missing_notice()
    {
        db_WoocommerceMissingAlert();
    }

     /**
     * Adds a "Settings" link to the plugin action links on the Plugins screen.
     *
     * This link will redirect the user to the plugin's settings page under the WooCommerce menu.
     *
     * @param array $links An array of existing action links.
     * @return array Modified array of action links with the added "Settings" link.
     */
    public function db_discount_settings_link( $links ) {
        $settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=dealbuilder-discount-rules#' ) ) . '">' . esc_html__( 'Settings', 'dealbuilder-discount-rules' ) . '</a>';
        array_unshift( $links, $settings_link );
        return $links;
    }

     /**
     * Declare HPOS compatibility for WooCommerce.
     *
     * @return void
     */
    public function declare_hpos_compatibility() {
        add_action( 'before_woocommerce_init', function () {
            if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                    'custom_order_tables',
                    __FILE__,
                    true
                );
            }
        } );
    }

}

/**
 * Initializes the main plugin
 * @return \DealBuilder_Discount_Rules
 */
function dealbuilder_discount_rules()
{
    return DealBuilder_Discount_Rules::init();
}

/**
 * Kick-off the plugin
 */
dealbuilder_discount_rules();
