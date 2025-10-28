<?php
/**
 * Handles hiding of auto-generated coupons from the WooCommerce admin list.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Discount\Manager;

defined( 'ABSPATH' ) || exit;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

/**
 * Class CouponDisplay
 */
class CouponDisplay {

    use SingletonTrait;

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'pre_get_posts', [ $this, 'hide_auto_generated_coupons' ] );
    }


    /**
     * Hide coupons with custom meta key from the WooCommerce Coupons admin list.
     *
     * @param \WP_Query $query The current WP_Query object.
     */
    public function hide_auto_generated_coupons( $query )
    {
        if ( ! is_admin() || !$query->is_main_query() ) return;
        if ( $query->get( 'post_type' ) !== 'shop_coupon' ) return;

        $meta_query = $query->get( 'meta_query' ) ?: [];

        $meta_query[] = [
            'key'     => 'gwpdr_is_hidden_coupon',
            'compare' => 'NOT EXISTS',
        ];

        $query->set( 'meta_query', $meta_query );
    }
}
