<?php
/**
 * Adds a Cost Price field to WooCommerce product pages.
 *
 * Saved as _gwpdr_cost_price on both simple products and individual variations.
 * Used by Margin_Guard to compute the maximum allowed discount.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Pro\MarginProtection;

defined( 'ABSPATH' ) || exit;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

class Product_Cost_Meta {

    use SingletonTrait;

    protected function __construct() {
        // Simple / grouped / external products
        add_action( 'woocommerce_product_options_pricing', [ $this, 'add_cost_price_field' ] );
        add_action( 'woocommerce_process_product_meta',    [ $this, 'save_cost_price_field' ] );

        // Variable product variations
        add_action( 'woocommerce_variation_options_pricing', [ $this, 'add_variation_cost_field' ], 10, 3 );
        add_action( 'woocommerce_save_product_variation',    [ $this, 'save_variation_cost_field' ], 10, 2 );
    }

    public function add_cost_price_field(): void {
        woocommerce_wp_text_input( [
            'id'          => '_gwpdr_cost_price',
            'label'       => __( 'Cost Price', 'giantwp-discount-rules' ) . ' (' . get_woocommerce_currency_symbol() . ')',
            'placeholder' => '0.00',
            'desc_tip'    => true,
            'description' => __( 'Your cost for this product. Used by GiantWP Discount Rules Margin Protection to prevent discounts from going below your profit floor.', 'giantwp-discount-rules' ),
            'type'        => 'text',
            'data_type'   => 'price',
        ] );
    }

    public function save_cost_price_field( int $post_id ): void {
        /* translators: not a translatable string — meta key sanitization only. */
        $cost = isset( $_POST['_gwpdr_cost_price'] )
            ? wc_clean( wp_unslash( $_POST['_gwpdr_cost_price'] ) )
            : '';
        update_post_meta( $post_id, '_gwpdr_cost_price', wc_format_decimal( $cost ) );
    }

    public function add_variation_cost_field( int $loop, array $variation_data, \WP_Post $variation ): void {
        woocommerce_wp_text_input( [
            'id'            => "_gwpdr_cost_price_{$loop}",
            'name'          => "_gwpdr_cost_price[{$loop}]",
            'value'         => get_post_meta( $variation->ID, '_gwpdr_cost_price', true ),
            'label'         => __( 'Cost Price', 'giantwp-discount-rules' ) . ' (' . get_woocommerce_currency_symbol() . ')',
            'placeholder'   => '0.00',
            'desc_tip'      => true,
            'description'   => __( 'Your cost for this variation. Used for margin protection.', 'giantwp-discount-rules' ),
            'type'          => 'text',
            'data_type'     => 'price',
            'wrapper_class' => 'form-row form-row-first',
        ] );
    }

    public function save_variation_cost_field( int $variation_id, int $loop ): void {
        $cost = isset( $_POST['_gwpdr_cost_price'][ $loop ] )
            ? wc_clean( wp_unslash( $_POST['_gwpdr_cost_price'][ $loop ] ) )
            : '';
        update_post_meta( $variation_id, '_gwpdr_cost_price', wc_format_decimal( $cost ) );
    }
}
