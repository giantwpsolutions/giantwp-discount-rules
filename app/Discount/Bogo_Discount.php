<?php

  /**
 * Bogo Class.
 *
 * Handles Bogo Discount
 *
 * @package DealBuilder_Discount_Rules
 */

namespace DealBuilder_Discount_Rules\Discount;

defined( 'ABSPATH' ) || exit;


use DealBuilder_Discount_Rules\Discount\Condition\Conditions;
use DealBuilder_Discount_Rules\Discount\BogoBuyProduct\BogoBuy_Field;
use DealBuilder_Discount_Rules\Discount\BogoBuyProduct\BogoBuyProduct;
use DealBuilder_Discount_Rules\Discount\Manager\Discount_Helper;
use DealBuilder_Discount_Rules\Discount\UsageTrack\Bogo_Usage_Handler;
use DealBuilder_Discount_Rules\Traits\SingletonTrait;

/**
 * Class Bogo_Discount
 * Handles Buy One Get One discount logic, including free items and discounted items.
 */
class Bogo_Discount {

    use SingletonTrait;
    /**
     * Register WooCommerce hooks.
     */
    public function __construct() {
        add_action( 'woocommerce_cart_loaded_from_session', [ $this, 'maybe_apply_discount' ], 20 );
        add_action( 'woocommerce_before_calculate_totals', [ $this, 'adjust_discounted_items' ], PHP_INT_MAX );
    }

    /**
     * Evaluate BOGO rules and apply free or discounted items to the cart.
     *
     * @param \WC_Cart|null $cart
     * @return void
     */
    public function maybe_apply_discount( $cart = null ) {
        if ( is_null( $cart ) ) {
            $cart = WC()->cart;
        }

        if ( is_admin() && !defined( 'DOING_AJAX' ) ) return;
        if ( !$cart || $cart->is_empty() ) {
            WC()->session->__unset( '_db_bogo_applied_rules' );
            return;
        }


        // Clear previous BOGO data
        foreach ( $cart->get_cart() as $key => $item ) {
            if ( !empty( $item['dealbuilder_bogo_discount'] ) ) {
                unset( $cart->cart_contents[$key]['dealbuilder_bogo_discount'] );
            }

            if ( !empty( $item['db_bogo_free_item'] ) ) {
                $cart->remove_cart_item( $key );
            }
        }

        $rules = $this->get_discount_rules();
        if ( empty( $rules ) ) return;

        foreach ( $rules as $rule ) {
            if ( ! isset( $rule['discountType'] ) || strtolower( $rule['discountType'] ) !== 'bogo' ) continue;
            if ( ( $rule['status'] ?? '' ) !== 'on' ) continue;
            if ( !Discount_Helper::is_schedule_active( $rule ) ) continue;
            if ( !Discount_Helper::check_usage_limit( $rule ) ) continue;

            if (
                isset( $rule['enableConditions'] ) && $rule['enableConditions'] &&
                !Conditions::check_all( $cart, $rule['conditions'], $rule['conditionsApplies'] ?? 'all' )
            ) continue;

            if ( !BogoBuyProduct::check_all( $cart, $rule['buyProduct'] ?? [], $rule['bogoApplies'] ?? 'all' ) ) continue;

            $free_or_discount = $rule['freeOrDiscount'] ?? 'freeproduct';

            $applied = false;

            if ( $free_or_discount === 'freeproduct' ) {
                $this->apply_free_item( $rule );
                $applied = true;
            } else {
                $this->mark_discounted_items( $rule );
                $applied = true;
            }

            if ( $applied ) {
                WC()->session->set( '_db_bogo_applied_rules', [ $rule['id'] ] );
                Bogo_Usage_Handler::instance();
            } else {
                WC()->session->__unset( '_db_bogo_applied_rules' );
            }


            break;
        }
    }
 
    /**
     * Apply free items to the cart based on matched rule.
     *
     * @param array $rule
     * @return void
     */
    private function apply_free_item( $rule ) {
        $cart_items = WC()->cart->get_cart();
        $eligible   = $this->get_eligible_products( $cart_items, $rule['buyProduct'] );
        $buy_count  = intval( $rule['buyProductCount'] ?? 1 );
        $get_count  = intval( $rule['getProductCount'] ?? 1 );
        $repeat     = $rule['isRepeat'] ?? false;

        $total_quantity = array_sum( array_column( $eligible, 'quantity' ) );
        $repeat_times   = $repeat ? floor( $total_quantity / $buy_count ) : ( $total_quantity >= $buy_count ? 1 : 0 );
        $add_count      = $repeat_times * $get_count;

        $added = 0;
        while ( $added < $add_count ) {
            foreach ( $eligible as $item ) {
                if ( $added >= $add_count ) break;

                WC()->cart->add_to_cart(
                    $item['product_id'],
                    1,
                    $item['variation_id'] ?? 0,
                    [],
                    [
                        'db_bogo_free_item' => true,
                        'db_bogo_rule_id'   => $rule['id']
                    ]
                );

                $added++;
            }
        }
    }

    /**
     * Mark cart items with discount metadata for BOGO rules.
     *
     * @param array $rule
     * @return void
     */
    private function mark_discounted_items( $rule ) {
        $cart       = WC()->cart;
        $cart_items = $cart->get_cart();
        $eligible   = [];

        foreach ( $cart_items as $key => $item ) {
            if ( ! empty( $item['db_bogo_free_item'] ) ) continue;

            foreach ( $rule['buyProduct'] as $condition ) {
                $field = $condition['field'] ?? '';
                if ( method_exists( BogoBuy_Field::class, $field ) && BogoBuy_Field::$field( [$item], $condition ) ) {
                    $eligible[] = ['key' => $key, 'item' => $item];
                    break;
                }
            }
        }

        $buy_count = intval( $rule['buyProductCount'] ?? 1 );
        $get_count = intval( $rule['getProductCount'] ?? 1 );
        $repeat    = $rule['isRepeat'] ?? false;

        $total_quantity = array_sum( array_map( fn($i) => $i['item']['quantity'] ?? 0, $eligible ) );
        $needed_qty     = $buy_count + $get_count;
        $repeat_times   = $repeat ? floor( $total_quantity / $needed_qty ) : ( $total_quantity >= $needed_qty ? 1 : 0 );
        $discount_qty   = $repeat_times * $get_count;

        if ( $discount_qty <= 0 ) return;

        usort( $eligible, function ( $a, $b ) {
            return $a['item']['data']->get_price() <=> $b['item']['data']->get_price();
        });

        $discounted     = 0;
        $discount_type  = $rule['discounttypeBogo'] ?? 'fixed';
        $discount_value = floatval( $rule['discountValue'] ?? 0 );
        $max            = floatval( $rule['maxValue'] ?? 0 );
        $rule_id        = $rule['id'];

        foreach ( $eligible as $entry ) {
            $key  = $entry['key'];
            $item = &$cart->cart_contents[ $key ];

            $qty_to_discount = min( $item['quantity'], $discount_qty - $discounted );
            if ($qty_to_discount <= 0) continue;

            $item['dealbuilder_bogo_discount'] = [
                'rule_id' => $rule_id,
                'qty'     => $qty_to_discount,
                'type'    => $discount_type,
                'value'   => $discount_value,
                'max'     => $max
            ];

            $discounted += $qty_to_discount;
            if ( $discounted >= $discount_qty ) break;
        }
    }

    /**
     * Apply price adjustments for discounted items or set free item price to zero.
     *
     * @param \WC_Cart $cart
     * @return void
     */
    public function adjust_discounted_items( $cart ) {
        $settings          = maybe_unserialize( get_option( 'dealbuilder_discountrules_settings', [] ) );
        $use_regular_price = isset( $settings['discountBasedOn'] ) && $settings['discountBasedOn'] === 'regular_price';

        foreach ( $cart->get_cart() as $key => $item ) {
            if ( ! empty( $item['db_bogo_free_item'] ) ) {
                $item['data']->set_price( 0 );
                continue;
            }

            if ( ! empty( $item['dealbuilder_bogo_discount'] ) ) {
                $info            = $item['dealbuilder_bogo_discount'];
                $product         = $item['data'];
                $original_price  = $use_regular_price && $product instanceof \WC_Product ? $product->get_regular_price() : $product->get_price();
                $qty_to_discount = $info['qty'] ?? 0;

                $discount = $info['type'] === 'percentage'
                    ? ( $original_price * $info['value'] / 100 )
                    :  $info['value'];

                if ( $info['max'] > 0 ) {
                    $discount = min( $discount, $info['max'] );
                }

                if ( $item['quantity'] > $qty_to_discount ) {
                    $full_price_qty   = $item['quantity'] - $qty_to_discount;
                    $discounted_total = $qty_to_discount * ($original_price - $discount);
                    $normal_total     = $full_price_qty * $original_price;
                    $blended_price    = ( $discounted_total + $normal_total ) / $item['quantity'];
                    $item['data']->set_price( $blended_price );
                } else {
                    $item['data']->set_price( $original_price - $discount );
                }
            }
        }
    }

    /**
     * Get products from cart matching the rule's buy conditions.
     *
     * @param array $cart_items
     * @param array $buy_conditions
     * @return array
     */
    private function get_eligible_products( $cart_items, $buy_conditions ) {
        $eligible = [];
        foreach ( $cart_items as $item ) {
            if ( ! empty( $item['db_bogo_free_item'] ) ) continue;
            foreach ( $buy_conditions as $condition ) {
                $field = $condition['field'] ?? '';
                if ( method_exists( BogoBuy_Field::class, $field) && BogoBuy_Field::$field([$item], $condition ) ) {
                    $eligible[] = $item;
                    break;
                }
            }
        }
        return $eligible;
    }

    /**
     * Remove free items from cart by rule ID.
     *
     * @param string|int $rule_id
     * @return void
     */
    private function remove_bogo_items( $rule_id ) {
        foreach ( WC()->cart->get_cart() as $key => $item ) {
            if ( ! empty( $item['db_bogo_free_item'] ) && ( $item['db_bogo_rule_id'] ?? '' ) === $rule_id ) {
                WC()->cart->remove_cart_item( $key );
            }
        }
    }

    /**
     * Get BOGO discount rules from DB.
     *
     * @return array
     */
    private function get_discount_rules(): array {
        return maybe_unserialize( get_option( 'dealbuilder_bogo_discount', [] ) ) ?: [];
    }
}
