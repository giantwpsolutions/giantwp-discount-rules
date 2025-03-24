<?php
/**
 * Handles BOGO Free Item display and price override in cart.
 *
 * @package DealBuilder_Discount_Rules
 */

namespace DealBuilder_Discount_Rules\Discount\Manager;

use DealBuilder_Discount_Rules\Traits\SingletonTrait;

defined('ABSPATH') || exit;

/**
 * Class Bogo_Free_Item_Handler
 */
class Bogo_Free_Item_Handler {

    use SingletonTrait;
    /**
     * Constructor.
     */
    public function __construct() {

        add_action( 'woocommerce_before_calculate_totals', [ $this, 'apply_custom_prices' ], PHP_INT_MAX );
        add_filter( 'woocommerce_cart_item_price', [ $this, 'filter_cart_price' ], PHP_INT_MAX, 3 );
        add_filter( 'woocommerce_cart_item_subtotal', [ $this, 'filter_cart_subtotal' ], PHP_INT_MAX, 3 );

        // Prevent free items from being updated manually
        add_filter( 'woocommerce_update_cart_validation', function ( $check, $cart_item_key, $values ) {
            if ( empty( $values['db_bogo_free_item'] ) ) {
                return $check;
            }
            $cart = WC()->cart;
            if ( ! $cart || ! isset( $cart->cart_contents[ $cart_item_key ] ) ) {
                return false;
            }
            return $check;
        }, 1000, 3 );
    }

    /**
     * Apply custom prices for BOGO free items.
     *
     * @param \WC_Cart $cart WooCommerce cart object.
     */
    public function apply_custom_prices( $cart ) {
        if ( is_admin() && ! defined('DOING_AJAX') ) return;

        foreach ( $cart->get_cart() as $item ) {
            if ( ! empty( $item['db_bogo_free_item'] ) && isset( $item['override_price'] ) ) {
                $item['data']->set_price( floatval( $item['override_price'] ) );
            }
        }
    }

    /**
     * Filter price display in cart for free BOGO items.
     *
     * @param string $price_html    Original price HTML.
     * @param array  $cart_item     Cart item array.
     * @param string $cart_item_key Item key.
     *
     * @return string
     */
    public function filter_cart_price( $price_html, $cart_item, $cart_item_key ) {
        if ( ! empty( $cart_item['db_bogo_free_item'] ) ) {
            if ( floatval( $cart_item['override_price'] ) === 0.0 ) {
                return '<span class="db-free-price">' . esc_html__('Free', 'dealbuilder-discount-rules') . '</span>';
            }
            return wc_price( floatval( $cart_item['override_price'] ) );
        }

        return $price_html;
    }

    /**
     * Filter subtotal display for BOGO free items.
     *
     * @param string $subtotal_html Subtotal HTML.
     * @param array  $cart_item     Cart item array.
     * @param string $cart_item_key Item key.
     *
     * @return string
     */
    public function filter_cart_subtotal( $subtotal_html, $cart_item, $cart_item_key ) {
        if ( ! empty( $cart_item['db_bogo_free_item'] ) ) {
            $price = floatval( $cart_item['override_price'] ?? 0.0 );
            if ( $price === 0.0 ) {
                return '<span class="db-free-subtotal">' . esc_html__('Free', 'dealbuilder-discount-rules') . '</span>';
            }
            return wc_price( $price * $cart_item['quantity'] );
        }

        return $subtotal_html;
    }
}
