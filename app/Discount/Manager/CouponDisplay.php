<?php
/**
 * Handles hiding of auto-generated coupons from the WooCommerce admin list.
 *
 * @package AIO_WooDiscount
 */

namespace AIO_WooDiscount\Discount\Manager;

defined( 'ABSPATH' ) || exit;

use AIO_WooDiscount\Traits\SingletonTrait;

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
            'key'     => 'aio_is_hidden_coupon',
            'compare' => 'NOT EXISTS',
        ];

        $query->set( 'meta_query', $meta_query );
    }
}
