<?php
namespace GiantWP_Discount_Rules\Discount\Notification;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

defined( 'ABSPATH' ) || exit;

class SpendBased_Progress_Status {
    use SingletonTrait;

    /**
     * Build payload for cart/minicart spend-based promo messaging.
     *
     * @return array
     */
    public function get_cart_progress_payload() {
        if ( ! function_exists( 'WC' ) ) {
            return array( 'active' => false );
        }

        $cart = WC()->cart;
        if ( ! $cart || $cart->is_empty() ) {
            return array( 'active' => false );
        }

        $rules = $this->get_spend_rules();
        if ( empty( $rules ) ) {
            return array( 'active' => false );
        }

        foreach ( $rules as $rule ) {

            // Must be enabled.
            if ( ( $rule['status'] ?? '' ) !== 'on' ) {
                continue;
            }

            $progress = $this->prepare_progress_data( $rule, $cart );
            if ( empty( $progress['active'] ) ) {
                continue;
            }

            $cart_msg  = $this->build_offer_message( $progress );
            $short_msg = $cart_msg;

            return array(
                'active'         => true,
                'status'         => $progress['status'], // "in_progress" | "unlocked".
                'message_cart'   => $cart_msg,
                'message_single' => $short_msg,
            );
        }

        return array( 'active' => false );
    }

    /**
     * Prepare core spend/threshold progress info.
     *
     * @param array    $rule Rule data.
     * @param \WC_Cart $cart WC cart.
     * @return array
     */
    private function prepare_progress_data( $rule, $cart ) {
        $threshold = $this->get_threshold_amount_from_rule( $rule );

        $subtotal = floatval( $cart->get_subtotal() );

        $unlocked = ( $subtotal >= $threshold );

        $discount_label = $this->format_discount_label( $rule );

        return array(
            'active'         => true,
            'status'         => $unlocked ? 'unlocked' : 'in_progress',
            'threshold'      => $threshold,
            'discount_label' => $discount_label,
        );
    }

    /**
     * This is the text we show to the shopper.
     * We ALWAYS use this style:
     *   "Spend X and get Y 💸"
     *
     * @param array $progress Prepared progress data.
     * @return string
     */
    private function build_offer_message( $progress ) {

        /* translators: 1: spend threshold amount (formatted money), 2: reward/discount label. */
        $text = __( 'Spend %1$s and get %2$s 💸', 'giantwp-discount-rules' );

        return sprintf(
            $text,
            $this->format_money( $progress['threshold'] ),
            $progress['discount_label']
        );
    }

    /**
     * Figure out the "Spend X" threshold.
     * We try multiple possible keys because rule data format can vary.
     *
     * @param array $rule Rule data.
     * @return float
     */
    private function get_threshold_amount_from_rule( $rule ) {
        $candidates = array(
            'thresholdAmount',     // what we tried first.
            'cartSubtotal',        // common naming.
            'cartSpend',           // sometimes this is used.
            'minSubtotal',         // sometimes minimum cart amount.
            'min_amount',          // generic minimum.
            'minCartAmount',       // some plugins use this.
            'subtotalToQualify',   // custom naming.
        );

        foreach ( $candidates as $key ) {
            if ( isset( $rule[ $key ] ) && '' !== $rule[ $key ] && null !== $rule[ $key ] ) {
                $val = floatval( $rule[ $key ] );
                if ( $val > 0 ) {
                    return $val;
                }
            }
        }

        // last fallback if nothing valid was found.
        return 0.0;
    }

    /**
     * Turn rule into "10% off" or "৳200 off".
     *
     * @param array $rule Rule data.
     * @return string
     */
    private function format_discount_label( $rule ) {
        $mode  = $rule['discountMode']
            ?? $rule['fpDiscountType']
            ?? $rule['discountType']
            ?? 'percentage';

        $value = floatval( $rule['discountValue'] ?? 0 );

        // Percentage-style discount.
        if ( in_array( strtolower( $mode ), array( 'percentage', 'percent', '%', 'pct' ), true ) ) {

            $val_display = rtrim( rtrim( number_format( $value, 2, '.', '' ), '0' ), '.' );

            /* translators: %s: discount percentage value, without the percent sign (e.g. "20"). */
            $text = __( '%s%% off', 'giantwp-discount-rules' );

            return sprintf(
                $text,
                $val_display
            );
        }

        // Flat currency discount.
        if ( function_exists( 'wc_price' ) ) {

            /* translators: %s: formatted monetary discount amount (e.g. "$10.00"). */
            $text = __( '%s off', 'giantwp-discount-rules' );

            return sprintf(
                $text,
                wc_price( $value )
            );
        }

        /* translators: %s: formatted numeric discount amount (e.g. "10.00"). */
        $text = __( '%s off', 'giantwp-discount-rules' );

        return sprintf(
            $text,
            number_format( $value, 2 )
        );
    }

    /**
     * Format a numeric amount as money.
     *
     * @param float $amount Amount.
     * @return string
     */
    private function format_money( $amount ) {
        if ( function_exists( 'wc_price' ) ) {
            return wc_price( $amount );
        }
        return number_format( $amount, 2 ) . '৳';
    }

    /**
     * Load spend threshold rules from the DB.
     *
     * @return array
     */
    private function get_spend_rules() {
        // this is the correct DB option we established.
        $rules = maybe_unserialize(
            get_option( 'giantwp_flatpercentage_discount', array() )
        );


        return is_array( $rules ) ? $rules : array();
    }
}
