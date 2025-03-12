<?php

namespace AIO_WooDiscount;

use AIO_WooDiscount\Traits\SingletonTrait;

/**
 * Assets Loading class
 */
class Assets
{

    use SingletonTrait;

    public function __construct()
    {

        add_action('admin_enqueue_scripts', [$this, 'register_plugin_assets'], 50);
        add_action('admin_enqueue_scripts', [$this, 'register_frontend_plugin_assets'], 50);


        add_filter('script_loader_tag', [$this, 'add_attribute_type'], 10, 3);
        add_action('in_admin_header', [$this, 'disable_core_update_notifications']);
        add_action('wp_enqueue_scripts', [$this, 'register_frontend_plugin_assets'], 50);
    }

    /**
     * Load assets
     */
    public function register_plugin_assets()
    {
        if (! is_admin()) {
            return;
        }

        $screen = get_current_screen();
        if (! $screen || $screen->id !== 'woocommerce_page_aio-woodiscount') {
            return;
        }

        wp_enqueue_script('wp-i18n');
        wp_enqueue_script('wp-api-fetch');

        $is_dev               = defined('WP_DEBUG') && WP_DEBUG;
        $dev_server_js_loader = 'http://localhost:5173/src/main.js';
        $prod_js_loader       = plugin_dir_url(__DIR__) . 'dist/assets/main.js';
        $prod_css_loader      = plugin_dir_url(__DIR__) . 'dist/assets/main.css';

        if ($is_dev) {
            wp_enqueue_script(
                'aio-woodiscount-vjs',
                $dev_server_js_loader,
                ['wp-i18n'],
                '1.0',
                true
            );
        } else {
            wp_enqueue_script(
                'aio-woodiscount-vjs',
                $prod_js_loader,
                ['wp-i18n'],
                '1.0',
                true
            );

            wp_enqueue_style(
                'aio-woodiscount-styles',
                $prod_css_loader,
                [],
                '1.0'
            );
        }

        $pro_url = esc_url('https://giantwpsolutions.com/');

        wp_localize_script('aio-woodiscount-vjs', 'pluginData', [
            'pluginUrl' => esc_url(plugin_dir_url(__DIR__)),
            'restUrl'   => esc_url_raw(rest_url(trailingslashit('aio-woodiscount/v2'))),
            'nonce'     => wp_create_nonce('wp_rest'),
            'proUrl'    => esc_url($pro_url),

        ]);
    }


    public function register_frontend_plugin_assets()
    {
        wp_enqueue_script('aio-checkout', plugin_dir_url(__DIR__) . 'assets/js/aio_checkout_ajax.js', array('jquery'), time(), true);

        wp_localize_script('aio-checkout', 'aio_checkout_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('aio_nonce'),
        ]);
    }


    /**
     * Add type = "module" to the script tag
     *
     * @param string $tag The script tag.
     * @param string $handle The script handle.
     * @param string $src The script source URL.
     *
     * @return string Modified script tag.
     */
    public function add_attribute_type($tag, $handle, $src)
    {
        if ('aio-woodiscount-vjs' === $handle) {
            error_log("Script modified for handle: {$handle}");  // Debug log
            $tag = '<script type="module" src="' . esc_url($src) . '"></script>';
        }
        return $tag;
    }

    /**
     * Remove Wordpress Core Notice from Plugin admin page
     *
     */
    public function disable_core_update_notifications()
    {
        $current_screen = get_current_screen();
        if ($current_screen && $current_screen->id === 'woocommerce_page_aio-woodiscount') {
            remove_all_actions('admin_notices');  // Removes ALL admin notices
            remove_all_actions('network_admin_notices');
        }
    }
}
