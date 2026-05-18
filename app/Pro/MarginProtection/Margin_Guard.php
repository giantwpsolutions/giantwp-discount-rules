<?php
/**
 * Margin Protection Guard — caps discounts to protect profit margins.
 *
 * Pro-only. Hooks into the flat/percentage discount pipeline via
 * gwpdr_fp_discount_amount and caps the discount when it would push
 * a product below its minimum margin floor.
 *
 * Three layers (all optional, any combination works):
 *   1. Per-product cost price  (_gwpdr_cost_price product meta)
 *   2. Per-rule min margin %   (minMarginPercent field on the rule)
 *   3. Global max discount cap (globalMaxDiscount in settings)
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Pro\MarginProtection;

defined( 'ABSPATH' ) || exit;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

class Margin_Guard {

    use SingletonTrait;

    const OPTION_KEY = 'giantwp_margin_protection_settings';

    protected function __construct() {
        if ( ! self::is_pro_active() ) {
            return;
        }
        add_filter( 'gwpdr_fp_discount_amount', [ $this, 'cap_discount' ], 10, 3 );
    }

    public static function is_pro_active(): bool {
        return defined( 'GIANTWP_DISCOUNT_RULES_PRO_ACTIVE' ) && GIANTWP_DISCOUNT_RULES_PRO_ACTIVE;
    }

    /**
     * Cap the calculated discount so no margin floor is breached.
     *
     * @param float    $discount_amount  Calculated discount before capping.
     * @param array    $rule             The discount rule being applied.
     * @param \WC_Cart $cart             Current WooCommerce cart.
     * @return float   Capped discount amount.
     */
    public function cap_discount( float $discount_amount, array $rule, \WC_Cart $cart ): float {
        $settings = self::get_settings();

        if ( empty( $settings['enabled'] ) ) {
            return $discount_amount;
        }

        // Layer 3: Global max discount cap (% of cart subtotal)
        $global_max_pct = floatval( $settings['globalMaxDiscount'] ?? 0 );
        if ( $global_max_pct > 0 ) {
            $cart_subtotal   = $cart->get_subtotal();
            $max_by_global   = $cart_subtotal * $global_max_pct / 100;
            $discount_amount = min( $discount_amount, $max_by_global );
        }

        // Layer 1 + 2: Per-product cost price + per-rule / global min margin
        $min_margin_pct = floatval( $rule['minMarginPercent'] ?? $settings['globalMinMargin'] ?? 0 );
        $max_by_cost    = $this->compute_cost_based_cap( $cart, $min_margin_pct );
        if ( $max_by_cost !== null ) {
            $discount_amount = min( $discount_amount, $max_by_cost );
        }

        return max( 0.0, $discount_amount );
    }

    /**
     * Compute the maximum discount that keeps all cost-priced items above
     * the minimum margin threshold.
     *
     * Items without a cost price are treated as freely discountable
     * (they do not constrain the cap).
     *
     * @param \WC_Cart $cart
     * @param float    $min_margin_pct  Minimum margin % (0–99).
     * @return float|null  Max allowed discount, or null if no cost data exists.
     */
    private function compute_cost_based_cap( \WC_Cart $cart, float $min_margin_pct ): ?float {
        $max_allowed   = 0.0;
        $has_cost_data = false;
        $margin_rate   = max( 0.0, min( 99.9, $min_margin_pct ) ) / 100;

        foreach ( $cart->get_cart() as $item ) {
            /** @var \WC_Product $product */
            $product = $item['data'];
            $qty     = (int) $item['quantity'];
            $price   = floatval( $product->get_price() );

            // Try variation first, then parent product
            $cost = floatval( get_post_meta( $product->get_id(), '_gwpdr_cost_price', true ) );
            if ( $cost <= 0 && $product->get_parent_id() ) {
                $cost = floatval( get_post_meta( $product->get_parent_id(), '_gwpdr_cost_price', true ) );
            }

            if ( $cost <= 0 ) {
                // No cost price set — no constraint on this item
                $max_allowed += $price * $qty;
                continue;
            }

            $has_cost_data = true;

            // Minimum selling price to maintain the margin floor:
            //   margin = (price - cost) / price >= margin_rate
            //   => price >= cost / (1 - margin_rate)
            $min_price        = $margin_rate > 0 ? ( $cost / ( 1 - $margin_rate ) ) : $cost;
            $max_per_unit     = max( 0.0, $price - $min_price );
            $max_allowed     += $max_per_unit * $qty;
        }

        return $has_cost_data ? $max_allowed : null;
    }

    // ------------------------------------------------------------------ //
    //  Settings helpers (static so the API controller can call them too)   //
    // ------------------------------------------------------------------ //

    public static function get_settings(): array {
        $defaults = [
            'enabled'           => false,
            'globalMaxDiscount' => 0,
            'globalMinMargin'   => 0,
        ];
        $saved = maybe_unserialize( get_option( self::OPTION_KEY, [] ) );
        return is_array( $saved ) ? array_merge( $defaults, $saved ) : $defaults;
    }

    public static function save_settings( array $data ): void {
        $clean = [
            'enabled'           => (bool) ( $data['enabled'] ?? false ),
            'globalMaxDiscount' => floatval( $data['globalMaxDiscount'] ?? 0 ),
            'globalMinMargin'   => floatval( $data['globalMinMargin'] ?? 0 ),
        ];
        update_option( self::OPTION_KEY, $clean );
    }
}
