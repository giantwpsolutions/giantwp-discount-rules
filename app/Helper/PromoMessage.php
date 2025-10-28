<?php
namespace GiantWP_Discount_Rules\Helper;

defined('ABSPATH') || exit;

use GiantWP_Discount_Rules\Traits\SingletonTrait;
use GiantWP_Discount_Rules\Discount\Notification\Bxgy_Promo_Message;
use GiantWP_Discount_Rules\Discount\Notification\Bogo_Progress_Status;
use GiantWP_Discount_Rules\Discount\Notification\Shipping_Promo_Message;

class PromoMessage {
    use SingletonTrait;

    public function __construct() {
        // PDP under Add to Cart
        add_action(
            'woocommerce_single_product_summary',
            [ $this, 'render_product_message' ],
            25
        );

        // Cart totals box (right side)
        add_action(
            'woocommerce_cart_totals_before_shipping',
            [ $this, 'render_cart_totals_message' ],
            5
        );

        // Cart line-items area (left side, after each cart item name/details)
        add_action(
            'woocommerce_after_cart_item_name',
            [ $this, 'render_cart_lineitem_message' ],
            15,
            2 // <-- we need cart item + cart item key
        );

        add_filter( 'admin_footer_text', [$this, 'gwpdr_admin_footer_text' ] );


    }

    /**
     * PDP message (Buy X Get Y / BOGO / Shipping-on-this-product)
     */
    public function render_product_message() {
        if ( ! function_exists( 'WC' ) ) return;

        global $product;
        if ( ! $product ) return;

        $product_id = $product->get_id();

        // 1. BXGY
        if ( class_exists( Bxgy_Promo_Message::class ) ) {
            $bxgy_offer = Bxgy_Promo_Message::instance()->get_offer_for_product_page( $product_id );
            if ( ! empty( $bxgy_offer['active'] ) ) {
                $this->output_green_box( $bxgy_offer['message_offer'] );
                return;
            }
        }

        // 2. BOGO
        if ( class_exists( Bogo_Progress_Status::class ) ) {
            $bogo_offer = Bogo_Progress_Status::instance()->get_offer_for_product_page( $product_id );
            if ( ! empty( $bogo_offer['active'] ) ) {
                $this->output_green_box( $bogo_offer['message_offer'] );
                return;
            }
        }

        // 3. Shipping promo applies to this product
        if ( class_exists( Shipping_Promo_Message::class ) ) {
            $ship_offer = Shipping_Promo_Message::instance()->get_offer_for_product_page( $product_id );
            if ( ! empty( $ship_offer['active'] ) ) {
                $this->output_green_box( $ship_offer['message_offer'] );
                return;
            }
        }
    }

    /**
     * Cart totals box (right column).
     * Example: "🚚 Add $20 more to unlock FREE shipping"
     */
    public function render_cart_totals_message() {
        if ( ! function_exists( 'WC' ) ) return;
        if ( ! class_exists( Shipping_Promo_Message::class ) ) return;

        $data = Shipping_Promo_Message::instance()->get_offer_for_cart();

        if ( empty( $data['active'] ) || empty( $data['message_cart'] ) ) {
            return;
        }

        echo '<div class="gwpdr-shipping-offer" style="
            margin-bottom:12px;
            padding:10px 12px;
            border-radius:8px;
            background:#eef6ff;
            border:1px solid #3b82f6;
            color:#1e3a8a;
            font-weight:500;
            font-size:14px;
            line-height:1.4;
        ">';
        echo wp_kses_post( $data['message_cart'] );
        echo '</div>';
    }

    /**
     * Cart line item area (left column under product details).
     *
     * Goal here:
     * - If a shipping promo comes from buying THIS product specifically
     *   (like "Buy this and get shipping for 10৳")
     *   we show it under that cart item.
     *
     * @param array $cart_item
     * @param string $cart_item_key
     */
    public function render_cart_lineitem_message( $cart_item, $cart_item_key ) {
        if ( ! function_exists( 'WC' ) ) return;
        if ( ! class_exists( Shipping_Promo_Message::class ) ) return;

        $product_id = ! empty( $cart_item['product_id'] ) ? (int) $cart_item['product_id'] : 0;
        if ( ! $product_id ) return;

        $ship_offer = Shipping_Promo_Message::instance()->get_offer_for_product_page( $product_id );

        // We ONLY show shipping messages that are product-triggered
        // (not threshold "spend 100" messages) in the line items.
        if ( empty( $ship_offer['active'] ) ) return;
        $msg = $ship_offer['message_offer'];
        if ( empty( $msg ) ) return;

        echo '<div class="gwpdr-cart-line-shipping" style="
            margin:8px 0 12px 0;
            padding:8px 10px;
            border-radius:6px;
            background:#eef6ff;
            border:1px solid #3b82f6;
            color:#1e3a8a;
            font-weight:500;
            font-size:13px;
            line-height:1.4;
            max-width:400px;
        ">';
        echo wp_kses_post( $msg );
        echo '</div>';
    }

    /**
     * Shared green box for PDP promos.
     */
    private function output_green_box( $message ) {
        echo '<div class="gwpdr-promo-offer" style="
            margin-top:12px;
            padding:10px 12px;
            border-radius:8px;
            background:#e8fff2;
            border:1px solid #10b981;
            color:#065f46;
            font-weight:500;
            font-size:14px;
            line-height:1.4;
        ">';
        echo wp_kses_post( $message );
        echo '</div>';
    }


   public function gwpdr_admin_footer_text( $text ) {
    $screen = get_current_screen();

    // Only show on your plugin admin pages
    if ( strpos( $screen->id, 'giantwp-discount-rules' ) === false ) {
        return $text; // keep default footer elsewhere
    }

    return sprintf(
        '<i>If you like the plugin please rate us <span style="color:#1A7EFB;">★★★★★</span> on <a href="https://wordpress.org/support/plugin/giantwp-discount-rules/reviews/" target="_blank" style="text-decoration:none;color:#2271b1;">WordPress.org</a> to help us spread the word ♥ from the Giant WP Solutions team.</i>'
    );
}
}
