<?php
namespace GiantWP_Discount_Rules\Discount\Notification;

use GiantWP_Discount_Rules\Discount\Manager\Discount_Helper;
use GiantWP_Discount_Rules\Discount\Condition\Conditions;
use GiantWP_Discount_Rules\Traits\SingletonTrait;

defined('ABSPATH') || exit;

class Bogo_Progress_Status {
    use SingletonTrait;

    /**
     * Returns promo data for cart/minicart context.
     * We don't actually render a floating bubble anymore, but this is still
     * used for cart messaging logic and eligibility checks.
     */
    public function get_progress_for_cart() {
        if ( ! function_exists('WC') ) {
            return [ 'active' => false ];
        }

        $cart = WC()->cart;
        if ( ! $cart || $cart->is_empty() ) {
            return [ 'active' => false ];
        }

        $rules = $this->get_bogo_rules();
        if ( empty( $rules ) ) {
            return [ 'active' => false ];
        }

        foreach ( $rules as $rule ) {
            if ( $this->is_rule_valid( $rule, $cart ) ) {
                $progress = $this->calculate_progress( $cart, $rule );
                if ( ! empty( $progress['active'] ) ) {
                    return $progress;
                }
            }
        }

        return [ 'active' => false ];
    }

    /**
     * Returns message for single product page (PDP).
     * This is what we display under the Add to Cart button.
     */
    public function get_offer_for_product_page( $product_id ) {
        $product_id = intval( $product_id );
        if ( ! $product_id ) {
            return [ 'active' => false ];
        }

        $cart = function_exists('WC') ? WC()->cart : null;

        foreach ( $this->get_bogo_rules() as $rule ) {
            if (
                $this->is_rule_valid( $rule, $cart ) &&
                $this->rule_targets_product( $rule, $product_id )
            ) {
                return [
                    'active'        => true,
                    'message_offer' => $this->describe_offer_for_customer( $rule ),
                ];
            }
        }

        return [ 'active' => false ];
    }

    /**
     * Check if a given rule is allowed to run right now.
     * - discountType must be 'bogo'
     * - status must be 'on'
     * - schedule (if enabled) must be active
     * - usage limit (if enabled) must allow
     * - cart/customer conditions (if enabled) must match
     */
    private function is_rule_valid( $rule, $cart ) {
        if ( strtolower( $rule['discountType'] ?? '' ) !== 'bogo' ) {
            return false;
        }

        if ( ($rule['status'] ?? '') !== 'on' ) {
            return false;
        }

        // Schedule: only enforced if enabled
        if ( ! empty( $rule['schedule']['enableSchedule'] ) ) {
            if ( ! Discount_Helper::is_schedule_active( $rule ) ) {
                return false;
            }
        }

        // Usage limit: only enforced if enabled
        if ( ! empty( $rule['usageLimits']['enableUsage'] ) ) {
            if ( ! Discount_Helper::check_usage_limit( $rule ) ) {
                return false;
            }
        }

        // Conditions: only enforced if enabled
        if ( ! empty( $rule['enableConditions'] ) ) {
            $conditions_pass = Conditions::check_all(
                $cart,
                $rule['conditions'] ?? [],
                $rule['conditionsApplies'] ?? 'all'
            );
            if ( ! $conditions_pass ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Does this rule apply to this specific product?
     *
     * Right now we only support matching explicit product IDs.
     * (Category, tag, etc. could be extended later.)
     */
    private function rule_targets_product( $rule, $product_id ) {
        if ( empty( $rule['buyProduct'] ) ) {
            return false;
        }

        $product_id = intval( $product_id );

        foreach ( $rule['buyProduct'] as $cond ) {
            $field    = strtolower( $cond['field'] ?? '' );
            $operator = strtolower( $cond['operator'] ?? '' );
            $values   = $cond['value'] ?? [];

            // normalize to int
            $values = array_map( 'intval', (array) $values );

            // product IN [123, 456]
            if (
                in_array( $field, [ 'product', 'product_id' ], true ) &&
                in_array( $operator, [ 'in', 'in_list' ], true ) &&
                in_array( $product_id, $values, true )
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Core logic:
     * - How many qualifying units are in cart
     * - How many more to unlock
     * - Whether unlocked already
     * - Which message we should show
     */
    private function calculate_progress( $cart, $rule ) {
        $buy_count  = max( 1, intval( $rule['buyProductCount'] ?? 1 ) );
        $get_count  = max( 1, intval( $rule['getProductCount'] ?? 1 ) );

        $eligible_qty      = 0;
        $first_product_id  = null;

        foreach ( $cart->get_cart() as $item ) {
            // ignore auto-added free items
            if ( ! empty( $item['gwpdr_bogo_free_item'] ) ) {
                continue;
            }

            foreach ( $rule['buyProduct'] ?? [] as $cond ) {
                $field    = strtolower( $cond['field'] ?? '' );
                $operator = strtolower( $cond['operator'] ?? '' );
                $values   = $cond['value'] ?? [];

                $values           = array_map( 'intval', (array) $values );
                $line_product_id  = intval( $item['product_id'] ?? 0 );

                if (
                    in_array( $field, [ 'product', 'product_id' ], true ) &&
                    in_array( $operator, [ 'in', 'in_list' ], true ) &&
                    in_array( $line_product_id, $values, true )
                ) {
                    $eligible_qty += intval( $item['quantity'] ?? 0 );
                    if ( ! $first_product_id ) {
                        $first_product_id = $line_product_id;
                    }
                    break;
                }
            }
        }

        // No qualifying product in cart at all? Then don't show.
        if ( ! $first_product_id ) {
            return [ 'active' => false ];
        }

        $product_link_html = sprintf(
            '<a href="%s">%s</a>',
            esc_url( get_permalink( $first_product_id ) ),
            esc_html( get_the_title( $first_product_id ) )
        );

        $remaining_to_unlock = max( 0, $buy_count - $eligible_qty );
        $is_unlocked         = ( $eligible_qty >= $buy_count );

        if ( $is_unlocked ) {
            list( $msg_cart, $msg_single ) = $this->build_unlocked_messages(
                $rule,
                $get_count,
                $product_link_html
            );

            return [
                'active'         => true,
                'status'         => 'unlocked',
                'remaining_qty'  => 0,
                'message_cart'   => $msg_cart,
                'message_single' => $msg_single,
            ];
        }

        // not unlocked yet (upsell state)
        list( $msg_cart, $msg_single ) = $this->build_in_progress_messages(
            $rule,
            $remaining_to_unlock,
            $get_count,
            $product_link_html
        );

        return [
            'active'         => true,
            'status'         => 'in_progress',
            'remaining_qty'  => $remaining_to_unlock,
            'message_cart'   => $msg_cart,
            'message_single' => $msg_single,
        ];
    }

    /**
     * Human-friendly PDP line like:
     *  🎉 Buy 2 and get 1 free!
     *  🎉 Buy 2 and get 1 with 20% off ✨
     *  🎉 Buy 2 and get 1 with $5 off ✨
     */
    private function describe_offer_for_customer( $rule ) {
        $buy_count   = intval( $rule['buyProductCount'] ?? 1 );
        $get_count   = intval( $rule['getProductCount'] ?? 1 );
        $mode        = $rule['freeOrDiscount'] ?? 'freeproduct';
        $disc_type   = $rule['discounttypeBogo'] ?? 'percentage';
        $disc_value  = $rule['discountValue'] ?? 0;

        if ( $mode === 'freeproduct' ) {
            return sprintf(
                __( '🎉 Buy %1$d and get %2$d free!', 'giantwp-discount-rules' ),
                $buy_count,
                $get_count
            );
        }

        if ( $disc_type === 'percentage' ) {
            return sprintf(
                __( '🎉 Buy %1$d and get %2$d with %3$s off ✨', 'giantwp-discount-rules' ),
                $buy_count,
                $get_count,
                sprintf( '%s%%', $disc_value )
            );
        }

        // fixed $ off
        return sprintf(
            __( '🎉 Buy %1$d and get %2$d with %3$s off ✨', 'giantwp-discount-rules' ),
            $buy_count,
            $get_count,
            wc_price( $disc_value )
        );
    }

    /**
     * Build messages while customer is still working toward the deal.
     * Returns [ $message_for_cart, $message_for_single ]
     */
    private function build_in_progress_messages( $rule, $remaining_needed, $get_count, $product_link_html ) {
        $buy_count   = intval( $rule['buyProductCount'] ?? 1 );
        $mode        = $rule['freeOrDiscount'] ?? 'freeproduct';
        $disc_type   = $rule['discounttypeBogo'] ?? 'percentage';
        $disc_value  = $rule['discountValue'] ?? 0;

        if ( $mode === 'freeproduct' ) {
            $cart_msg = sprintf(
                __( 'Add %1$d more %2$s to unlock your free gift 🎁', 'giantwp-discount-rules' ),
                $remaining_needed,
                $product_link_html
            );
            $single_msg = sprintf(
                __( 'Buy %1$d and get %2$d free 🚀', 'giantwp-discount-rules' ),
                $buy_count,
                $get_count
            );
            return [ $cart_msg, $single_msg ];
        }

        if ( $disc_type === 'percentage' ) {
            $cart_msg = sprintf(
                __( 'Add %1$d more %2$s to unlock %3$s off 💸', 'giantwp-discount-rules' ),
                $remaining_needed,
                $product_link_html,
                sprintf( '%s%%', $disc_value )
            );
            $single_msg = sprintf(
                __( 'Buy %1$d and get %2$d with %3$s off ✨', 'giantwp-discount-rules' ),
                $buy_count,
                $get_count,
                sprintf( '%s%%', $disc_value )
            );
            return [ $cart_msg, $single_msg ];
        }

        // fixed discount value
        $cart_msg = sprintf(
            __( 'Add %1$d more %2$s to unlock %3$s off 💸', 'giantwp-discount-rules' ),
            $remaining_needed,
            $product_link_html,
            wc_price( $disc_value )
        );
        $single_msg = sprintf(
            __( 'Buy %1$d and get %2$d with %3$s off ✨', 'giantwp-discount-rules' ),
            $buy_count,
            $get_count,
            wc_price( $disc_value )
        );
        return [ $cart_msg, $single_msg ];
    }

    /**
     * Build messages for unlocked state (customer already qualified).
     * Returns [ $message_for_cart, $message_for_single ]
     */
    private function build_unlocked_messages( $rule, $get_count, $product_link_html ) {
        $mode        = $rule['freeOrDiscount'] ?? 'freeproduct';
        $disc_type   = $rule['discounttypeBogo'] ?? 'percentage';
        $disc_value  = $rule['discountValue'] ?? 0;

        if ( $mode === 'freeproduct' ) {
            $cart_msg = sprintf(
                __( 'Reward unlocked 🎁 You get %1$d %2$s FREE!', 'giantwp-discount-rules' ),
                $get_count,
                $product_link_html
            );
            $single_msg = sprintf(
                __( 'Deal active: %d free item(s)! 🎉', 'giantwp-discount-rules' ),
                $get_count
            );
            return [ $cart_msg, $single_msg ];
        }

        if ( $disc_type === 'percentage' ) {
            $cart_msg = sprintf(
                __( 'Discount unlocked ✅ %1$d %2$s will get %3$s off!', 'giantwp-discount-rules' ),
                $get_count,
                $product_link_html,
                sprintf( '%s%%', $disc_value )
            );
            $single_msg = sprintf(
                __( 'Deal active: %1$d item(s) discounted by %2$s 💸', 'giantwp-discount-rules' ),
                $get_count,
                sprintf( '%s%%', $disc_value )
            );
            return [ $cart_msg, $single_msg ];
        }

        $cart_msg = sprintf(
            __( 'Discount unlocked ✅ %1$d %2$s will get %3$s off!', 'giantwp-discount-rules' ),
            $get_count,
            $product_link_html,
            wc_price( $disc_value )
        );
        $single_msg = sprintf(
            __( 'Deal active: %1$d item(s) discounted by %2$s 💸', 'giantwp-discount-rules' ),
            $get_count,
            wc_price( $disc_value )
        );

        return [ $cart_msg, $single_msg ];
    }

    /**
     * Pull rules from DB.
     */
    private function get_bogo_rules() {
        $rules = maybe_unserialize( get_option( 'giantwp_bogo_discount', [] ) );
        return is_array( $rules ) ? $rules : [];
    }
}
