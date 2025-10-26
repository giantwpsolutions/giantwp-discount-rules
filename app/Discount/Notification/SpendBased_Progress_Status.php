<?php
namespace GiantWP_Discount_Rules\Discount\Notification;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

defined('ABSPATH') || exit;

class SpendBased_Progress_Status {
    use SingletonTrait;

    public function get_cart_progress_payload() {
        if ( ! function_exists('WC') ) {
            return [ 'active' => false ];
        }

        $cart = WC()->cart;
        if ( ! $cart || $cart->is_empty() ) {
            return [ 'active' => false ];
        }

        $rules = $this->get_spend_rules();
        if ( empty($rules) ) {
            error_log('GWPDR: No spend rules found');
            return [ 'active' => false ];
        }

        foreach ( $rules as $rule ) {
            // log the rule so we can inspect the structure
            error_log('GWPDR: spend rule candidate = ' . print_r($rule, true));

            // must be enabled
            if ( ($rule['status'] ?? '') !== 'on' ) {
                continue;
            }

            $progress = $this->prepare_progress_data( $rule, $cart );
            if ( empty($progress['active']) ) {
                continue;
            }

            $cart_msg  = $this->build_offer_message( $progress );
            $short_msg = $cart_msg;

            return [
                'active'         => true,
                'status'         => $progress['status'], // "in_progress" | "unlocked"
                'message_cart'   => $cart_msg,
                'message_single' => $short_msg,
            ];
        }

        return [ 'active' => false ];
    }

    private function prepare_progress_data( $rule, $cart ) {
        // ✅ new: we don't assume thresholdAmount anymore
        $threshold = $this->get_threshold_amount_from_rule( $rule );

        $subtotal = floatval( $cart->get_subtotal() );

        $unlocked = ($subtotal >= $threshold);

        $discount_label = $this->format_discount_label( $rule );

        error_log(
            'GWPDR spend calc: threshold=' . $threshold .
            ' subtotal=' . $subtotal .
            ' unlocked=' . ($unlocked ? 'yes' : 'no') .
            ' discount_label=' . $discount_label
        );

        return [
            'active'         => true,
            'status'         => $unlocked ? 'unlocked' : 'in_progress',
            'threshold'      => $threshold,
            'discount_label' => $discount_label,
        ];
    }

    /**
     * This is the text we show to the shopper.
     * We ALWAYS use this style:
     *   "Spend X and get Y 💸"
     */
    private function build_offer_message( $progress ) {
        return sprintf(
            __('Spend %s and get %s 💸', 'giantwp-discount-rules'),
            $this->format_money( $progress['threshold'] ),
            $progress['discount_label']
        );
    }

    /**
     * Figure out the "Spend X" threshold.
     * We try multiple possible keys because we don't yet know how your rule is saved.
     *
     * After we see the log once, we'll lock in the correct key.
     */
    private function get_threshold_amount_from_rule( $rule ) {
        $candidates = [
            'thresholdAmount',     // what we tried first
            'cartSubtotal',        // common naming
            'cartSpend',           // sometimes this is used
            'minSubtotal',         // sometimes minimum cart amount
            'min_amount',          // generic minimum
            'minCartAmount',       // some plugins use this
            'subtotalToQualify',   // custom naming
        ];

        foreach ( $candidates as $key ) {
            if ( isset($rule[$key]) && $rule[$key] !== '' && $rule[$key] !== null ) {
                $val = floatval($rule[$key]);
                if ( $val > 0 ) {
                    return $val;
                }
            }
        }

        // last fallback, if truly nothing found / or rule was saved as 0
        return 0.0;
    }

    /**
     * Turn rule into "10% off" or "৳200 off"
     */
    private function format_discount_label( $rule ) {
        $mode  = $rule['discountMode']
            ?? $rule['fpDiscountType']
            ?? $rule['discountType']
            ?? 'percentage';

        $value = floatval($rule['discountValue'] ?? 0);

        // % case
        if ( in_array(strtolower($mode), ['percentage','percent','%','pct'], true) ) {
            $val_display = rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
            return sprintf(__('%s%% off', 'giantwp-discount-rules'), $val_display);
        }

        // flat number case
        if ( function_exists('wc_price') ) {
            return sprintf(__('%s off', 'giantwp-discount-rules'), wc_price($value));
        }

        return sprintf(__('%s off', 'giantwp-discount-rules'), number_format($value, 2));
    }

    private function format_money($amount) {
        if ( function_exists('wc_price') ) {
            return wc_price($amount);
        }
        return number_format($amount, 2) . '৳';
    }

    private function get_spend_rules() {
        // this is the correct DB option we established
        $rules = maybe_unserialize(
            get_option('giantwp_flatpercentage_discount', [])
        );

        error_log('GWPDR: spend rules raw load = ' . print_r($rules, true));

        return is_array($rules) ? $rules : [];
    }
}
