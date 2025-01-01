<?php 

namespace AIO_WooDiscount;

use AIO_WooDiscount\Traits\SingletonTrait;

/**
 * Assets Loading class
 */
class Assets {

    use SingletonTrait;

    public function __construct() {
        error_log( 'Assets class constructor triggered' ); // Add this log to confirm constructor is firing

        add_action( 'admin_enqueue_scripts', [ $this, 'register_plugin_assets' ], 50 );

        add_filter( 'script_loader_tag', [ $this, 'add_attribute_type' ], 10, 3 );
    }

    /**
     * Load assets
     */
    public function register_plugin_assets() {
        if ( ! is_admin() ) {
            return;
        }
    
        $screen = get_current_screen();
        if ( ! $screen || $screen->id !== 'woocommerce_page_aio-woodiscount' ) {
            return;
        }
    
        wp_enqueue_script( 'wp-i18n' );
    
        $is_dev = defined( 'WP_DEBUG' ) && WP_DEBUG;
        $dev_server_js_loader = 'http://localhost:5173/src/main.js';
        $prod_js_loader = plugin_dir_url( __DIR__ ) . 'dist/assets/main.js';
        $prod_css_loader = plugin_dir_url( __DIR__ ) . 'dist/assets/main.css';
    
        if ( $is_dev ) {
            wp_enqueue_script(
                'aio-woodiscount-vjs',
                $dev_server_js_loader,
                [ 'wp-i18n' ],
                '1.0',
                true
            );
        } else {
            wp_enqueue_script(
                'aio-woodiscount-vjs',
                $prod_js_loader,
                [ 'wp-i18n' ],
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
    
        $pro_url = esc_url( 'https://giantwpsolutions.com/' );
    
        wp_localize_script( 'aio-woodiscount-vjs', 'pluginData', [
            'pluginUrl' => plugin_dir_url( __DIR__ ),
            'restUrl'   => rest_url( 'aio-woodiscount/v1/' ),
            'proUrl'    => $pro_url,
        ] );
    }
    

    /**
     * Add type="module" to the script tag
     *
     * @param string $tag The script tag.
     * @param string $handle The script handle.
     * @param string $src The script source URL.
     *
     * @return string Modified script tag.
     */
    public function add_attribute_type( $tag, $handle, $src ) {
        if ( 'aio-woodiscount-vjs' === $handle ) {
            error_log( "Script modified for handle: {$handle}" ); // Debug log
            $tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
        }
        return $tag;
    }
}
