<?php
/**
 * Handles BOGO Free Item display and price override in cart.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Discount\Manager;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

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
            if ( empty( $values['gwpdr_bogo_free_item'] ) ) {
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
        if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
            return;
        }

        foreach ( $cart->get_cart() as $cart_item_key => $item ) {
            // Harden: ensure this is a flagged free item, an override price exists, and the product object is valid
            if (
                ! empty( $item['gwpdr_bogo_free_item'] )
                && isset( $item['override_price'] )
                && isset( $item['data'] )
                && is_object( $item['data'] )
                && method_exists( $item['data'], 'set_price' )
            ) {
                $item['data']->set_price( (float) $item['override_price'] );
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
        if ( ! empty( $cart_item['gwpdr_bogo_free_item'] ) ) {
            // Guard: default to 0.0 if override_price is not set
            $override = isset( $cart_item['override_price'] ) ? (float) $cart_item['override_price'] : 0.0;

            if ( $override <= 0.0 ) {
                return '<span class="gwpdr-free-price">' . esc_html__( 'Free', 'giantwp-discount-rules' ) . '</span>';
            }

            return wc_price( $override );
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
        if ( ! empty( $cart_item['gwpdr_bogo_free_item'] ) ) {
            // Guard: default to 0.0 if override_price is not set
            $override = isset( $cart_item['override_price'] ) ? (float) $cart_item['override_price'] : 0.0;

            if ( $override <= 0.0 ) {
                return '<span class="gwpdr-free-subtotal">' . esc_html__( 'Free', 'giantwp-discount-rules' ) . '</span>';
            }

            $qty = isset( $cart_item['quantity'] ) ? (int) $cart_item['quantity'] : 1;
            return wc_price( $override * $qty );
        }

        return $subtotal_html;
    }
}
