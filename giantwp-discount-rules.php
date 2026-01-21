<?php
/**
 * Plugin Name: GiantWP Discount Rules – Dynamic Pricing & BOGO Deals for WooCommerce
 * Plugin URI: https://giantwpsolutions.com/plugins/giantwp-discount-rules
 * Description: Create dynamic discounts, bulk pricing, and BOGO offers for WooCommerce with an easy rule builder. A powerful one-stop discount solution by GiantWP.
 * Version: 1.2.12
 * Author: Giant WP Solutions
 * Author URI: https://giantwpsolutions.com
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: giantwp-discount-rules
 * Domain Path: /languages
 * Requires at least: 5.8
 * Tested up to: 6.9
 * Requires PHP: 7.4
 * WC requires at least: 3.0
 * WC tested up to: 10.4
 * WooCommerce HPOS support: yes
 * @package GiantWP_Discount_Rules
 */


if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__  . '/app/functions.php';

/**
 * The main plugin class
 */
final class GiantWP_Discount_Rules
{

    /**
     * The plugin version
     */
    const version = '1.2.12';

    /**
     * Class Constructor
     */
    public function __construct()
    {
        register_activation_hook( __FILE__ , [ $this, 'activate' ] );
        add_action( 'plugins_loaded', [ $this, 'on_plugins_loaded'] );
        add_action( 'admin_notices', [ $this, 'check_woocommerce_active' ] );
        add_action( 'admin_init', [ $this, 'activation_redirect' ] );
        add_filter( 'plugin_action_links_giantwp-discount-rules/giantwp-discount-rules.php', [ $this, 'gwpdr_discount_settings_link' ] );
        add_filter( 'plugin_row_meta', [ $this, 'gwpdr_plugin_row_meta' ], 10, 2 );
        $this->declare_hpos_compatibility();
        $this->define_constants();
        $this->declare_hpos_block_compatibility();

    }

    /**
     * Initializes a singleton instance
     * 
     * @return GiantWP_Discount_Rules
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
        define( 'GWPDR_VERSION', self::version );
        define( 'GWPDR_FILE', __FILE__ );
        define( 'GWPDR_PATH', plugin_dir_path(__FILE__) );
        define( 'GWPDR_LANG_DIR', plugin_basename( dirname(__FILE__) ) . '/languages' );
    }

    /**
     * Load plugin textdomain for translations
     * @return void
     */
    public function on_plugins_loaded()
    {

        if ( class_exists( 'WooCommerce' ) ) {

            \GiantWP_Discount_Rules\Api\Api::instance();
            \GiantWP_Discount_Rules\Installer::instance();
             \GiantWP_Discount_Rules\Integration\IntegrationInit::instance();

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
            gwpdr_WoocommerceDeactivationAlert();
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
                esc_html__( 'GiantWP Discount Rules requires WooCommerce to be installed and active.', 'giantwp-discount-rules' ),
                esc_html__( 'Plugin dependency check', 'giantwp-discount-rules' ),
                [ 'back_link' => true ]
            );
        }

        $install_time = get_option( 'GWPDR_installation_time' );

        if ( ! $install_time ) {
            update_option( 'GWPDR_installation_time', time() );
        }

        update_option( 'GWPDR_version', self::version );

        // Set transient to trigger redirect to settings page
        set_transient( 'gwpdr_activation_redirect', true, 30 );
    }

    /*
     * Woocommerce Missing Notice
     *
     * @return void
     */
    public function woocommerce_missing_notice()
    {
        gwpdr_WoocommerceMissingAlert();
    }

     /**
     * Adds action links to the plugin on the Plugins screen.
     *
     * This adds "Settings" and "Get Pro" links to the plugin action links.
     *
     * @param array $links An array of existing action links.
     * @return array Modified array of action links with the added links.
     */
    public function gwpdr_discount_settings_link( $links ) {
        $settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=giantwp-discount-rules#' ) ) . '">' . esc_html__( 'Settings', 'giantwp-discount-rules' ) . '</a>';
        $pro_link = '<a href="https://giantwpsolutions.com/plugins/giantwp-discount-rules" target="_blank" style="color: #00a32a; font-weight: bold;">' . esc_html__( 'Get Pro', 'giantwp-discount-rules' ) . '</a>';

        array_unshift( $links, $settings_link );
        $links[] = $pro_link;

        return $links;
    }

    /**
     * Adds row meta links to the plugin on the Plugins screen.
     *
     * This adds "Docs", "Support", and "Rate Us" links below the plugin description.
     *
     * @param array $links An array of existing row meta links.
     * @param string $file The plugin file path.
     * @return array Modified array of row meta links with the added links.
     */
    public function gwpdr_plugin_row_meta( $links, $file ) {
        if ( plugin_basename( __FILE__ ) === $file ) {
            $row_meta = [
                'docs'    => '<a href="' . esc_url( 'https://docs.giantwpsolutions.com' ) . '" target="_blank">' . esc_html__( 'Docs', 'giantwp-discount-rules' ) . '</a>',
                'support' => '<a href="' . esc_url( 'https://www.giantwpsolutions.com/support' ) . '" target="_blank">' . esc_html__( 'Support', 'giantwp-discount-rules' ) . '</a>',
                'rate'    => '<a href="' . esc_url( 'https://wordpress.org/support/plugin/giantwp-discount-rules/reviews/#new-post' ) . '" target="_blank">' . esc_html__( 'Rate Us ★★★★★', 'giantwp-discount-rules' ) . '</a>',
            ];

            return array_merge( $links, $row_meta );
        }

        return $links;
    }

    /**
     * Redirect to plugin main page after plugin activation.
     *
     * This method checks for the activation redirect transient and redirects the user
     * to the plugin main page. It only redirects on single plugin activation,
     * not during bulk activations or AJAX requests.
     *
     * @return void
     */
    public function activation_redirect() {
        // Check if the transient is set
        if ( ! get_transient( 'gwpdr_activation_redirect' ) ) {
            return;
        }

        // Delete the transient immediately to prevent multiple redirects
        delete_transient( 'gwpdr_activation_redirect' );

        // Don't redirect if activating multiple plugins at once
        if ( isset( $_GET['activate-multi'] ) ) {
            return;
        }

        // Don't redirect on AJAX requests
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }

        // Redirect to plugin main page (discount rules listing)
        wp_safe_redirect( admin_url( 'admin.php?page=giantwp-discount-rules' ) );
        exit;
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


    /**
     * Declare HPOS Block compatibility for WooCommerce.
     *
     * @return void
     */
    public function declare_hpos_block_compatibility() {
        add_action( 'before_woocommerce_init', function() {
            if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
                \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                    'cart_checkout_blocks',
                    __FILE__,
                    true
                );
            }
        });
    }

}

/**
 * Initializes the main plugin
 * @return \GiantWP_Discount_Rules
 */
function giantWP_discount_rules()
{
    return GiantWP_Discount_Rules::init();
}

/**
 * Kick-off the plugin
 */
giantWP_discount_rules();
