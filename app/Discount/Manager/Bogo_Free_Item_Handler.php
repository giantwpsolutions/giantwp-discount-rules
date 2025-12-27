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
        add_filter( 'woocommerce_cart_item_price', [ $this, 'filter_cart_price' ], 999999999, 3 );
        add_filter( 'woocommerce_cart_item_subtotal', [ $this, 'filter_cart_subtotal' ], 999999999, 3 );

        // This hook works for BOTH traditional and block-based carts
        add_filter( 'woocommerce_get_item_data', [ $this, 'add_free_badge_to_cart_item' ], 10, 2 );

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
     * Add Free badge to cart item data (works for both traditional and block carts).
     *
     * @param array $item_data Cart item data.
     * @param array $cart_item Cart item.
     * @return array
     */
    public function add_free_badge_to_cart_item( $item_data, $cart_item ) {
        // Check if this is a BOGO free item
        if ( ! empty( $cart_item['gwpdr_bogo_free_item'] ) ) {
            $item_data[] = [
                'name'    => '',
                'key'     => '',
                'value'   => '<span class="gwpdr-free-badge">' . esc_html__( 'Free', 'giantwp-discount-rules' ) . '</span>',
                'display' => '',
            ];
        }

        return $item_data;
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
        $product = $cart_item['data'];
        $current_price = $product->get_price();

        // Handle BOGO free items
        if ( ! empty( $cart_item['gwpdr_bogo_free_item'] ) ) {
            $original_price = $product->get_regular_price();

            if ( $current_price <= 0 ) {
                if ( $original_price > 0 ) {
                    return '<del>' . wc_price( $original_price ) . '</del> <span class="gwpdr-free-badge">' . esc_html__( 'Free', 'giantwp-discount-rules' ) . '</span>';
                }
                return '<span class="gwpdr-free-badge">' . esc_html__( 'Free', 'giantwp-discount-rules' ) . '</span>';
            }

            return $price_html;
        }

        // Handle BOGO discounted items
        if ( ! empty( $cart_item['giantwp_bogo_discount'] ) ) {
            $info = $cart_item['giantwp_bogo_discount'];
            $settings = maybe_unserialize( get_option( 'giantwp_discountrules_settings', [] ) );
            $use_regular = isset( $settings['discountBasedOn'] ) && $settings['discountBasedOn'] === 'regular_price';

            // Get the original price before discount
            $original_price = $use_regular ? $product->get_regular_price() : $product->get_regular_price();

            if ( $current_price <= 0 ) {
                if ( $original_price > 0 ) {
                    return '<del>' . wc_price( $original_price ) . '</del> <span class="gwpdr-free-badge">' . esc_html__( 'Free', 'giantwp-discount-rules' ) . '</span>';
                }
                return '<span class="gwpdr-free-badge">' . esc_html__( 'Free', 'giantwp-discount-rules' ) . '</span>';
            }

            // Calculate savings for discounted BOGO items
            if ( $original_price > $current_price ) {
                $savings = $original_price - $current_price;
                if ( $savings > 0 ) {
                    return '<del>' . wc_price( $original_price ) . '</del> ' . wc_price( $current_price ) . ' <span class="gwpdr-save-badge">' . sprintf( esc_html__( 'Save %s', 'giantwp-discount-rules' ), wc_price( $savings ) ) . '</span>';
                }
            }
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
        $product = $cart_item['data'];
        $qty = isset( $cart_item['quantity'] ) ? (int) $cart_item['quantity'] : 1;
        $current_price = $product->get_price();
        $current_total = $current_price * $qty;

        // Handle BOGO free items
        if ( ! empty( $cart_item['gwpdr_bogo_free_item'] ) ) {
            $original_price = $product->get_regular_price();
            $original_total = $original_price * $qty;

            if ( $current_total <= 0 ) {
                if ( $original_total > 0 ) {
                    return '<del>' . wc_price( $original_total ) . '</del> <span class="gwpdr-free-badge">' . esc_html__( 'Free', 'giantwp-discount-rules' ) . '</span>';
                }
                return '<span class="gwpdr-free-badge">' . esc_html__( 'Free', 'giantwp-discount-rules' ) . '</span>';
            }

            return $subtotal_html;
        }

        // Handle BOGO discounted items
        if ( ! empty( $cart_item['giantwp_bogo_discount'] ) ) {
            $settings = maybe_unserialize( get_option( 'giantwp_discountrules_settings', [] ) );
            $use_regular = isset( $settings['discountBasedOn'] ) && $settings['discountBasedOn'] === 'regular_price';
            $original_price = $use_regular ? $product->get_regular_price() : $product->get_regular_price();
            $original_total = $original_price * $qty;

            if ( $current_total <= 0 ) {
                if ( $original_total > 0 ) {
                    return '<del>' . wc_price( $original_total ) . '</del> <span class="gwpdr-free-badge">' . esc_html__( 'Free', 'giantwp-discount-rules' ) . '</span>';
                }
                return '<span class="gwpdr-free-badge">' . esc_html__( 'Free', 'giantwp-discount-rules' ) . '</span>';
            }

            // Calculate savings
            $savings = $original_total - $current_total;
            if ( $savings > 0 ) {
                return '<del>' . wc_price( $original_total ) . '</del> ' . wc_price( $current_total ) . ' <span class="gwpdr-save-badge" style="display: inline-block; background: #000; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; text-transform: uppercase; margin-left: 5px;">' . \sprintf( esc_html__( 'Save %s', 'giantwp-discount-rules' ), wc_price( $savings ) ) . '</span>';
            }
        }

        return $subtotal_html;
    }
}
