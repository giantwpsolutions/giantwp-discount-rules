<?php
/*
Plugin Name: All In One WooDiscount 
Plugin URI: https://giantwpsolutions.com
Description: All in one WooDiscount is a one-stop discount solution for WooCommerce. With this plugin, you can create any kind of discount rules.
Version: 1.0
Author: Giant WP Solutions
Author URI: https://giantwpsolutions.com
License: GPLv2 or later
Text Domain: aio-woodiscount
Domain Path: /languages/
*/


if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__  . '/app/functions.php';

/**
 * The main plugin class
 */
final class All_in_one_wooDiscount
{

    /**
     * The plugin version
     */
    const version = '1.1';

    /**
     * Class Constructor
     */
    public function __construct()
    {
        register_activation_hook(__FILE__, [$this, 'activate']);
        add_action('plugins_loaded', [$this, 'on_plugins_loaded']);
        add_action('admin_notices', [$this, 'check_woocommerce_active']);
        $this->define_constants();
    }

    /**
     * Initializes a singleton instance
     * 
     * @return All_in_one_wooDiscount
     */
    public static function init()
    {
        static $instance = false;

        if (! $instance) {
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
        define('AIOWD_VERSION', self::version);
        define('AIOWD_FILE', __FILE__);
        define('AIOWD_PATH', plugin_dir_path(__FILE__));
        define('AIOWD_LANG_DIR', plugin_basename(dirname(__FILE__)) . '/languages');
    }

    /**
     * Load plugin textdomain for translations
     * @return void
     */
    public function on_plugins_loaded()
    {
        load_plugin_textdomain('all-in-one-woodiscount', false, AIOWD_LANG_DIR);

        if (class_exists('WooCommerce')) {
            new AIO_WooDiscount\Discount\UsageTrack\Bxgy_Usage_Handler();
            new AIO_WooDiscount\Installer();
            new AIO_WooDiscount\Api\Api();
            new AIO_WooDiscount\Discount\FlatPercentage_Discount();
            new AIO_WooDiscount\Discount\UsageTrack\FlatPercentageUsage();
            new AIO_WooDiscount\Discount\Bogo_Discount();
            new AIO_WooDiscount\Discount\Bxgy_Discount();
        } else {
            add_action('admin_notices', [$this, 'woocommerce_missing_notice']);
        }
    }

    /**
     * Show alert if WooCommerce is deactivated
     */
    public function check_woocommerce_active()
    {
        if (! class_exists('WooCommerce')) {
            WoocommerceDeactivationAlert();
        }
    }

    /**
     * Do stuff upon plugin activation
     * @return void
     */
    public function activate()
    {
        if (! class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                __('All-in-One WooDiscount requires WooCommerce to be installed and active.', 'all-in-one-woodiscount'),
                __('Plugin dependency check', 'all-in-one-woodiscount'),
                ['back_link' => true]
            );
        }

        $install_time = get_option('AIOWD_installation_time');

        if (! $install_time) {
            update_option('AIOWD_installation_time', time());
        }

        update_option('AIOWD_version', self::version);
    }

    /**
     * Woocommerce Missing Notice
     *
     * @return void
     */
    public function woocommerce_missing_notice()
    {
        WoocommerceMissingAlert();
    }
}

/**
 * Initializes the main plugin
 * @return \All_in_one_wooDiscount
 */
function all_in_one_wooDiscount()
{
    return All_in_one_wooDiscount::init();
}

/**
 * Kick-off the plugin
 */
all_in_one_wooDiscount();
