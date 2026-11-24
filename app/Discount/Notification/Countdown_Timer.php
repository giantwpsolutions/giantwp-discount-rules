<?php
/**
 * Countdown Timer — renders a live sale-ends-in timer on product, shop, and cart pages
 * for any active rule that has a future schedule end date.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Discount\Notification;

defined( 'ABSPATH' ) || exit;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

class Countdown_Timer {

    use SingletonTrait;

    public function __construct() {
        add_action( 'woocommerce_before_single_product',  [ $this, 'render_product_countdown' ] );
        add_action( 'woocommerce_before_shop_loop',       [ $this, 'render_shop_countdown' ] );
        add_action( 'woocommerce_before_cart',            [ $this, 'render_cart_countdown' ] );
        add_action( 'woocommerce_before_checkout_form',   [ $this, 'render_cart_countdown' ] );
    }

    // -------------------------------------------------------------------------
    // Settings helpers
    // -------------------------------------------------------------------------

    private function is_enabled(): bool {
        $settings = get_option( 'giantwp_discountrules_settings', [] );
        return ! empty( $settings['countdownTimerEnabled'] );
    }

    private function get_label(): string {
        $settings = get_option( 'giantwp_discountrules_settings', [] );
        return ! empty( $settings['countdownLabel'] )
            ? esc_html( $settings['countdownLabel'] )
            : __( 'Sale ends in', 'giantwp-discount-rules' );
    }

    // -------------------------------------------------------------------------
    // Rule fetching
    // -------------------------------------------------------------------------

    private function get_active_expiring_rules(): array {
        $option_keys = [
            'giantwp_flatpercentage_discount',
            'giantwp_bogo_discount',
            'giantwp_shipping_discount',
            'giantwp_bxgy_discount',
            'giantwp_bulk_discount',
        ];

        $now      = current_time( 'timestamp' );
        $expiring = [];

        foreach ( $option_keys as $key ) {
            $rules = maybe_unserialize( get_option( $key, [] ) );
            if ( ! is_array( $rules ) ) continue;

            foreach ( $rules as $rule ) {
                if (
                    isset( $rule['status'] ) && $rule['status'] === 'on' &&
                    ! empty( $rule['schedule']['enableSchedule'] ) &&
                    ! empty( $rule['schedule']['endDate'] )
                ) {
                    $end_ts = strtotime( $rule['schedule']['endDate'] );
                    if ( $end_ts <= $now ) continue; // already expired

                    if ( ! empty( $rule['schedule']['startDate'] ) ) {
                        $start_ts = strtotime( $rule['schedule']['startDate'] );
                        if ( $start_ts > $now ) continue; // not started yet
                    }

                    $rule['_end_ts'] = $end_ts;
                    $expiring[]      = $rule;
                }
            }
        }

        // Sort: soonest expiring first
        usort( $expiring, fn( $a, $b ) => $a['_end_ts'] - $b['_end_ts'] );

        return $expiring;
    }

    // -------------------------------------------------------------------------
    // Applicability check
    // -------------------------------------------------------------------------

    private function rule_applies_to_product( array $rule, int $product_id ): bool {
        $has_conditions  = ! empty( $rule['enableConditions'] ) && ! empty( $rule['conditions'] );
        $has_buy_product = ! empty( $rule['buyProduct'] );

        // No conditions and no buyProduct → storewide rule, always applies
        if ( ! $has_conditions && ! $has_buy_product ) {
            return true;
        }

        $cat_ids = wc_get_product_term_ids( $product_id, 'product_cat' );
        $tag_ids = wc_get_product_term_ids( $product_id, 'product_tag' );

        // ── BOGO buyProduct check ──────────────────────────────────────────────
        if ( $has_buy_product ) {
            foreach ( $rule['buyProduct'] as $bp ) {
                $field = $bp['field'] ?? '';
                $value = $bp['value'] ?? [];
                if ( ! is_array( $value ) ) $value = [ $value ];
                $value = array_map( 'strval', $value );

                if ( $field === 'all_products' ) return true;

                if ( $field === 'product' ) {
                    if ( in_array( (string) $product_id, $value, true ) ) return true;
                }

                if ( $field === 'product_variation' ) {
                    foreach ( $value as $variation_id ) {
                        if ( wp_get_post_parent_id( (int) $variation_id ) === $product_id ) return true;
                    }
                }

                if ( $field === 'product_category' ) {
                    foreach ( $cat_ids as $cid ) {
                        if ( in_array( (string) $cid, $value, true ) ) return true;
                    }
                }

                if ( $field === 'product_tags' ) {
                    foreach ( $tag_ids as $tid ) {
                        if ( in_array( (string) $tid, $value, true ) ) return true;
                    }
                }
            }
        }

        // ── Conditions check (flat/percentage, BOGO extra conditions, etc.) ────
        if ( $has_conditions ) {
            foreach ( $rule['conditions'] as $condition ) {
                $field = $condition['field'] ?? '';
                $value = $condition['value'] ?? [];
                if ( ! is_array( $value ) ) $value = [ $value ];
                $value = array_map( 'strval', $value );

                if ( $field === 'cart_item_product' ) {
                    if ( in_array( (string) $product_id, $value, true ) ) return true;
                }

                if ( $field === 'cart_item_category' ) {
                    foreach ( $cat_ids as $cid ) {
                        if ( in_array( (string) $cid, $value, true ) ) return true;
                    }
                }

                if ( $field === 'cart_item_tag' ) {
                    foreach ( $tag_ids as $tid ) {
                        if ( in_array( (string) $tid, $value, true ) ) return true;
                    }
                }

                // Cart-based conditions can't be evaluated on product page → show as teaser
                $cart_fields = [ 'cart_subtotal_price', 'cart_quantity', 'cart_total_weight', 'cart_item_regular_price' ];
                if ( in_array( $field, $cart_fields, true ) ) return true;
            }
        }

        return false;
    }

    // -------------------------------------------------------------------------
    // Output
    // -------------------------------------------------------------------------

    private function render( int $end_ts ): void {
        $label   = $this->get_label();
        $iso_end = date( 'c', $end_ts );
        ?>
        <div class="gwpdr-countdown-wrap">
            <div class="gwp-cd" data-end="<?php echo esc_attr( $iso_end ); ?>">
                <span class="gwp-cd__title"><span class="gwp-cd__dot"></span><?php echo esc_html( $label ); ?></span>
                <div class="gwp-cd__timer">
                    <div class="gwp-cd__unit">
                        <span class="gwp-cd__num" data-u="d">00</span>
                        <span class="gwp-cd__txt"><?php esc_html_e( 'Days', 'giantwp-discount-rules' ); ?></span>
                    </div>
                    <span class="gwp-cd__sep">:</span>
                    <div class="gwp-cd__unit">
                        <span class="gwp-cd__num" data-u="h">00</span>
                        <span class="gwp-cd__txt"><?php esc_html_e( 'Hrs', 'giantwp-discount-rules' ); ?></span>
                    </div>
                    <span class="gwp-cd__sep">:</span>
                    <div class="gwp-cd__unit">
                        <span class="gwp-cd__num" data-u="m">00</span>
                        <span class="gwp-cd__txt"><?php esc_html_e( 'Min', 'giantwp-discount-rules' ); ?></span>
                    </div>
                    <span class="gwp-cd__sep">:</span>
                    <div class="gwp-cd__unit">
                        <span class="gwp-cd__num" data-u="s">00</span>
                        <span class="gwp-cd__txt"><?php esc_html_e( 'Sec', 'giantwp-discount-rules' ); ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function render_product_countdown(): void {
        if ( ! $this->is_enabled() ) return;

        $product_id = get_the_ID();
        if ( ! $product_id ) return;

        foreach ( $this->get_active_expiring_rules() as $rule ) {
            if ( $this->rule_applies_to_product( $rule, $product_id ) ) {
                $this->render( $rule['_end_ts'] );
                return;
            }
        }
    }

    public function render_shop_countdown(): void {
        if ( ! $this->is_enabled() ) return;

        // On shop/archive pages only show storewide rules (no product conditions)
        foreach ( $this->get_active_expiring_rules() as $rule ) {
            if ( empty( $rule['enableConditions'] ) || empty( $rule['conditions'] ) ) {
                $this->render( $rule['_end_ts'] );
                return;
            }
        }
    }

    public function render_cart_countdown(): void {
        if ( ! $this->is_enabled() ) return;

        $rules = $this->get_active_expiring_rules();
        if ( ! empty( $rules ) ) {
            $this->render( $rules[0]['_end_ts'] );
        }
    }
}
