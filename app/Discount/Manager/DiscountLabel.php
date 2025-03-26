<?php
  /**
 * Display applied discount rules in WooCommerce Orders list.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Discount\Manager;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

/**
 * Class DiscountLabel
 *
 * Adds a custom column in the WooCommerce Orders table to indicate which
 * discount types were applied to each order (BOGO, BXGY, Bulk, Shipping).
 *
 * Only displays if the "orderPageLabel" setting is enabled.
 *
 */
class DiscountLabel {

    use SingletonTrait;
    
    /**
     * Constructor.
     *
     * Sets up hooks to modify the WooCommerce orders table to display applied discounts.
     */
    public function __construct() {
        // Only load if the setting is enabled
        $settings = maybe_unserialize( get_option( 'giantwp_discountrules_settings', [] ) );
        if ( empty( $settings['orderPageLabel'] ) ) {
            return;
        }

        // Classic WooCommerce order screen
        add_filter( 'manage_edit-shop_order_columns', [ $this, 'add_discount_column' ], 20 );
        add_action( 'manage_shop_order_posts_custom_column', [ $this, 'render_discount_column' ], 20, 2 );

        // HPOS screen (WooCommerce block editor interface)
        add_filter( 'manage_woocommerce_page_wc-orders_columns', [ $this, 'add_discount_column' ], 20);
        add_action( 'manage_woocommerce_page_wc-orders_custom_column', [ $this, 'render_discount_column' ], 20, 2 );
    }

    /**
     * Add the custom discount column to the orders table.
     *
     * @param array $columns Existing columns in the orders table.
     * @return array Modified columns with discount label added.
     */
    public function add_discount_column( $columns ) {
        $new_columns = [];

        foreach ( $columns as $key => $label ) {
            $new_columns[$key] = $label;
            if ( 'order_status' === $key ) {
                $new_columns['gwp_discount_label'] = __( 'Discount Rules', 'giantwp-discount-rules' );
            }
        }

        if ( ! isset( $new_columns['gwp_discount_label'] ) ) {
            $new_columns['gwp_discount_label'] = __( 'Discount Rules', 'giantwp-discount-rules' );
        }

        return $new_columns;
    }

    /**
     * Render the content for the custom discount column in the orders table.
     *
     * @param string $column   Column identifier.
     * @param int    $post_id  Order ID.
     */
    public function render_discount_column( $column, $post_id ) {
        if ( $column !== 'gwp_discount_label' ) {
            return;
        }

        $labels = [];

        $meta_keys = [
            '_gwp_bogo_applied_rules'     => __( 'BOGO', 'giantwp-discount-rules' ),
            '_gwp_bxgy_applied_rules'     => __( 'Buy X Get Y', 'giantwp-discount-rules' ),
            '_gwp_bulk_applied_rules'     => __( 'Bulk', 'giantwp-discount-rules' ),
            '_gwp_shipping_applied_rules' => __( 'Shipping', 'giantwp-discount-rules' ),
        ];

        $order = wc_get_order( $post_id );

        foreach ( $meta_keys as $meta_key => $label ) {
            $value = $order ? $order->get_meta( $meta_key, true ) : get_post_meta( $post_id, $meta_key, true );

            // Accept both arrays or strings
            if ( ! empty( $value ) ) {
                $labels[] = $label;
            }
        }

        echo ! empty( $labels ) ? wp_kses_post( implode( '<br>', array_map( 'esc_html', array_unique( $labels ) ) ) ) : '<em>' . esc_html__( 'None', 'giantwp-discount-rules' ) . '</em>';

    }
}
