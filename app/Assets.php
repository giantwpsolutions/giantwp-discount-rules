<?php
  /**
 * Assets Class For the plugin
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules;

defined('ABSPATH') || exit; 

use GiantWP_Discount_Rules\Traits\SingletonTrait;

    /**
 * Class Assets
 *
 * Handles enqueueing of all plugin assets, including Vue frontend scripts and AJAX triggers
 * for both admin and frontend (checkout/cart) pages.
 *
 * @package GiantWP_Discount_Rules
 */
class Assets
{
    use SingletonTrait;

        /**
     * Assets constructor.
     *
     * Hooks into various WordPress actions to enqueue scripts and styles.
     */
    public function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'register_plugin_assets' ], 50);
        add_action( 'admin_enqueue_scripts', [ $this, 'register_frontend_plugin_assets' ], 50 );
        add_action('wp_enqueue_scripts', [ $this, 'register_frontend_plugin_assets' ], 50 );
        add_filter( 'script_loader_tag',     [ $this, 'add_attribute_type' ], 10, 3 );
        add_action( 'in_admin_header',       [ $this, 'disable_core_update_notifications' ] );
    }

        /**
     * Enqueue admin panel plugin assets.
     * Loads Vue build and related styles/scripts only on GiantWP Discount Rules settings page.
     */
    public function register_plugin_assets() {
        if ( ! is_admin() ) return;

        $screen = get_current_screen();
        if ( ! $screen || $screen->id !== 'woocommerce_page_giantwp-discount-rules' ) return;

        wp_enqueue_script( 'wp-i18n' );
        wp_enqueue_script( 'wp-api-fetch' );
        $is_dev = defined( 'WP_DEBUG' ) && WP_DEBUG;
        $dev_server_js = 'http://localhost:5173/src/main.js';
        $prod_js       = plugin_dir_url(__DIR__) . 'dist/assets/main.js';
        $prod_css      = plugin_dir_url(__DIR__) . 'dist/assets/main.css';

        if ( $is_dev ) {
            wp_enqueue_script( 'gwpdr-discountrule-vjs', $dev_server_js, ['wp-i18n'], '1.0', true );
        }else{
            wp_enqueue_script( 'gwpdr-discountrule-vjs', $prod_js, ['wp-i18n'], '1.0', true );
            wp_enqueue_style( 'gwpdr-discountrule-styles', $prod_css, [], '1.0' );
        }

           

        wp_localize_script( 'gwpdr-discountrule-vjs', 'pluginData', [
            'pluginUrl' => esc_url( plugin_dir_url(__DIR__) ),
            'restUrl'   => esc_url_raw( rest_url( trailingslashit('gwpdr-discountrules/v2') ) ),
            'nonce'     => wp_create_nonce( 'wp_rest' ),
            'proUrl'    => esc_url( 'https://giantwpsolutions.com/' ),
            'proActive' => defined( 'GIANTWP_DISCOUNT_RULES_PRO_ACTIVE' ) && GIANTWP_DISCOUNT_RULES_PRO_ACTIVE,
        ] );
    }

        /**
     * Enqueue frontend JavaScript files for handling AJAX triggers.
     * Localizes separate script variables for each discount type.
     */
    public function register_frontend_plugin_assets() {
        wp_enqueue_script(
            'gwpdr-checkout',
            plugin_dir_url(__DIR__) . 'assets/js/gwpdr_checkout_ajax.js',
            ['jquery'],
            time(),
            true
        );

        wp_enqueue_script(
            'gwpdr-trigger',
            plugin_dir_url(__DIR__) . 'assets/js/trigger.js',
            ['jquery'],
            time(),
            true
        );

        wp_localize_script('gwpdr-checkout', 'gwpdr_checkout_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('gwpdr_nonce'),
        ]);

        wp_localize_script('gwpdr-trigger', 'gwpdrDiscountAjax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('gwpdr_trigger_nonce'),
        ]);

        if (is_cart() || is_checkout()) {
            wp_enqueue_script(
                'gwpdr-trigger-bogo',
                plugin_dir_url(__DIR__) . 'assets/js/triggerBogo.js',
                ['jquery', 'wc-cart'],
                filemtime(plugin_dir_path(__DIR__) . 'assets/js/triggerBogo.js'),
                true
            );

            wp_localize_script('gwpdr-trigger-bogo', 'gwpdrDiscountBogo', [
                'ajax_url'    => admin_url('admin-ajax.php'),
                'nonce'       => wp_create_nonce('gwpdr_triggerBogo_nonce'),
                'is_cart'     => is_cart(),
                'is_checkout' => is_checkout(),
            ]);
        }
    }

    /**
     * Modify script tag to use type = "module" for Vue builds.
     *
     * @param string $tag    Script tag.
     * @param string $handle Script handle.
     * @param string $src    Script source URL.
     *
     * @return string
     */
    public function add_attribute_type( $tag, $handle, $src ) {
        if ( 'gwpdr-discountrule-vjs' === $handle ) {
            // Use regex to add type="module" to the existing <script> tag
            return str_replace(
                '<script ',
                '<script type="module" ',
                $tag
            );
        }
    
        return $tag;
    }
    

    /**
     * Removes all admin notices from plugin settings page.
     * 
     * Ensures a clean experience inside GiantWP Discount Rules's admin interface.
     */
    public function disable_core_update_notifications() {
        $screen = get_current_screen();
        if ( $screen && $screen->id === 'woocommerce_page_giantwp-discount-rules' ) {
            remove_all_actions( 'admin_notices' );
            remove_all_actions( 'network_admin_notices' );
        }
    }
}
