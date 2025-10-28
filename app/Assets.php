<?php
/**
 * Assets Class For the plugin
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules;

defined('ABSPATH') || exit;

use GiantWP_Discount_Rules\Traits\SingletonTrait;


class Assets {
    use SingletonTrait;

    public function __construct() {
        add_action( 'admin_enqueue_scripts', [ $this, 'register_plugin_assets' ], 50 );
        add_action( 'wp_enqueue_scripts',    [ $this, 'register_frontend_plugin_assets' ], 50 );

        add_filter( 'script_loader_tag', [ $this, 'add_attribute_type' ], 10, 3 );
        add_action( 'in_admin_header',   [ $this, 'disable_core_update_notifications' ] );


    }

    /**
     * Enqueue assets for admin (Vue app etc).
     */
    public function register_plugin_assets() {
        if ( ! is_admin() ) return;

        $screen = get_current_screen();
        if ( ! $screen || $screen->id !== 'woocommerce_page_giantwp-discount-rules' ) return;

        wp_enqueue_script( 'wp-i18n' );
        wp_enqueue_script( 'wp-api-fetch' );

        $is_dev        = defined( 'WP_DEBUG' ) && WP_DEBUG;
        $dev_server_js = 'http://localhost:5173/src/main.js';
        $prod_js       = plugin_dir_url(__DIR__) . 'dist/assets/main.js';
        $prod_css      = plugin_dir_url(__DIR__) . 'dist/assets/main.css';

        if ( $is_dev ) {
            wp_enqueue_script(
                'gwpdr-discountrule-vjs',
                $dev_server_js,
                [ 'wp-i18n' ],
                '1.0',
                true
            );
        } else {
            wp_enqueue_script(
                'gwpdr-discountrule-vjs',
                $prod_js,
                [ 'wp-i18n' ],
                '1.0',
                true
            );

            wp_enqueue_style(
                'gwpdr-discountrule-styles',
                $prod_css,
                [],
                '1.0'
            );
        }

        wp_localize_script(
            'gwpdr-discountrule-vjs',
            'gwpdrPluginData',
            [
                'pluginUrl' => esc_url( plugin_dir_url(__DIR__) ),
                'restUrl'   => esc_url_raw( rest_url( trailingslashit('gwpdr-discountrules/v2') ) ),
                'nonce'     => wp_create_nonce( 'wp_rest' ),
                'proUrl'    => esc_url( 'https://giantwpsolutions.com/' ),
                'docsUrl'   => esc_url( 'https://www.docs.giantwpsolutions.com/' ),
                'proActive' => defined( 'GIANTWP_DISCOUNT_RULES_PRO_ACTIVE' ) && GIANTWP_DISCOUNT_RULES_PRO_ACTIVE,
                'primekit_search_url' => esc_url( admin_url( 'plugin-install.php?s=PrimeKit%20Addons&tab=search&type=term' ) ),
            ]
        );

    }

    /**
     * Frontend assets.
     * (We keep checkout + trigger in case you're using them.
     *  We do NOT enqueue any upsell bubble / floating widget scripts.)
     */
    public function register_frontend_plugin_assets() {
        // Checkout/cart helper
        wp_enqueue_script(
            'gwpdr-checkout',
            plugin_dir_url(__DIR__) . 'assets/js/gwpdr_checkout_ajax.js',
            [ 'jquery' ],
            time(),
            true
        );

        // Trigger script
        wp_enqueue_script(
            'gwpdr-trigger',
            plugin_dir_url(__DIR__) . 'assets/js/trigger.js',
            [ 'jquery' ],
            time(),
            true
        );

        // Localize AJAX helpers these scripts need
        wp_localize_script(
            'gwpdr-checkout',
            'gwpdr_checkout_ajax',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('gwpdr_nonce'),
            ]
        );

        wp_localize_script(
            'gwpdr-trigger',
            'gwpdrDiscountAjax',
            [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('gwpdr_trigger_nonce'),
            ]
        );

    }

    /**
     * Add type="module" for Vite/Vue build in admin.
     */
    public function add_attribute_type( $tag, $handle, $src ) {
        if ( 'gwpdr-discountrule-vjs' === $handle ) {
            return str_replace(
                '<script ',
                '<script type="module" ',
                $tag
            );
        }
        return $tag;
    }

    /**
     * Hide core update nags on our settings page.
     */
    public function disable_core_update_notifications() {
        $screen = get_current_screen();
        if ( $screen && $screen->id === 'woocommerce_page_giantwp-discount-rules' ) {
            remove_all_actions( 'admin_notices' );
            remove_all_actions( 'network_admin_notices' );
        }
    }
}
