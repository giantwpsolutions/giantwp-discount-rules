<?php

/**
 * Class Divi Theme Integration Support
 */

namespace GiantWP_Discount_Rules\Integration\Theme;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

defined('ABSPATH') || exit;

class DiviTheme{

    use SingletonTrait;

    /**
     * class constructor
     */

    protected function __construct() {
        add_filter( 'et_pb_module_content', [ $this, 'recalculate_discounts_inside_divi' ], 100, 4 );
    }

    /**
     * Ensures our discounts are applied when Divi renders the cart module.
     */
    public function recalculate_discounts_inside_divi( $content, $module, $attrs, $render_slug ) {
        if ( $render_slug === 'et_pb_wc_cart_products' && function_exists('WC') && WC()->cart ) {
            do_action( 'woocommerce_before_calculate_totals', WC()->cart );
        }
        return $content;
    }
}