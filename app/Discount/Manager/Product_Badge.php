<?php
/**
 * Product Badge — renders a discount badge on shop/archive product images.
 * Badge is shown ONLY when the product is explicitly targeted by an active rule.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Discount\Manager;

defined( 'ABSPATH' ) || exit;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

class Product_Badge {

    use SingletonTrait;

    /** @var array|null product_id => label map (explicitly targeted products). */
    private $product_badge_map = null;

    /** @var string|null Badge label for global rules (no product targeting). */
    private $global_badge_label = null;

    /** @var array Plugin settings. */
    private $settings = [];

    public function __construct() {
        $this->settings = get_option( 'giantwp_discountrules_settings', [] );

        if ( empty( $this->settings['showProductBadge'] ) ) {
            return;
        }

        // Remove WooCommerce & theme default sale badges
        remove_action( 'woocommerce_before_shop_loop_item_title',    'woocommerce_show_product_loop_sale_flash', 10 );
        remove_action( 'woocommerce_before_single_product_summary',  'woocommerce_show_product_sale_flash',      10 );

        add_action( 'wp_head',                                        [ $this, 'output_badge_styles' ] );
        add_action( 'woocommerce_before_shop_loop_item_title',        [ $this, 'render_badge' ], 9 );
        add_action( 'woocommerce_before_single_product_summary',      [ $this, 'render_badge' ], 9 );
    }

    /* -----------------------------------------------------------------------
     * CSS
     * -------------------------------------------------------------------- */

    public function output_badge_styles() {
        $bg   = sanitize_hex_color( $this->settings['badgeBgColor']   ?? '#1c4a96' ) ?: '#1c4a96';
        $text = sanitize_hex_color( $this->settings['badgeTextColor'] ?? '#ffffff' ) ?: '#ffffff';
        ?>
        <style id="gwpdr-badge-styles">
            ul.products li.product,
            .woocommerce ul.products li.product,
            .products .product,
            .woocommerce div.product,
            .woocommerce-page div.product {
                position: relative !important;
            }

            .gwpdr-product-badge {
                position      : absolute !important;
                top           : 10px !important;
                left          : 10px !important;
                right         : auto !important;
                z-index       : 99 !important;
                display       : inline-block !important;
                background-color: <?php echo esc_attr( $bg ); ?> !important;
                color         : <?php echo esc_attr( $text ); ?> !important;
                font-size     : 11px !important;
                font-weight   : 700 !important;
                font-family   : inherit !important;
                line-height   : 1 !important;
                letter-spacing: 0.6px !important;
                text-transform: uppercase !important;
                padding       : 5px 9px !important;
                border-radius : 4px !important;
                border        : none !important;
                outline       : none !important;
                box-shadow    : 0 2px 6px rgba(0,0,0,0.18) !important;
                pointer-events: none !important;
                text-shadow   : none !important;
            }
        </style>
        <?php
    }

    /* -----------------------------------------------------------------------
     * Render
     * -------------------------------------------------------------------- */

    public function render_badge() {
        global $product;
        if ( ! $product instanceof \WC_Product ) {
            return;
        }

        $map   = $this->get_product_badge_map();
        $pid   = $product->get_id();
        $label = $map[ $pid ] ?? $this->global_badge_label;

        if ( ! $label ) {
            return;
        }

        echo '<span class="gwpdr-product-badge">' . esc_html( $label ) . '</span>';
    }

    /* -----------------------------------------------------------------------
     * Build product → badge map (runs once per request)
     * -------------------------------------------------------------------- */

    private function get_product_badge_map(): array {
        if ( $this->product_badge_map !== null ) {
            return $this->product_badge_map;
        }

        $this->product_badge_map = [];

        $option_keys = [
            'giantwp_flatpercentage_discount',
            'giantwp_bogo_discount',
            'giantwp_bxgy_discount',
            'giantwp_bulk_discount',
        ];

        $all_rules = [];
        foreach ( $option_keys as $key ) {
            $rows = maybe_unserialize( get_option( $key, [] ) );
            if ( is_array( $rows ) ) {
                $all_rules = array_merge( $all_rules, $rows );
            }
        }

        // Priority: bogo > flat/percentage > bulk > buy x get y
        // Lower number = higher priority
        $priority = [
            'bogo'            => 1,
            'flat/percentage' => 2,
            'bulk discount'   => 3,
            'buy x get y'     => 4,
        ];

        $global_priority = PHP_INT_MAX;

        foreach ( $all_rules as $rule ) {
            if ( ( $rule['status'] ?? 'off' ) !== 'on' ) {
                continue;
            }

            $type = strtolower( $rule['discountType'] ?? '' );
            if ( ! isset( $priority[ $type ] ) ) {
                continue;
            }

            $label    = $this->get_label( $rule, $type );
            $products = $this->get_targeted_product_ids( $rule, $type );

            if ( empty( $products ) ) {
                // Global rule — applies to all products unless overridden by a specific rule
                $rule_priority = $priority[ $type ];
                if ( $rule_priority < $global_priority ) {
                    $this->global_badge_label = $label;
                    $global_priority          = $rule_priority;
                }
                continue;
            }

            foreach ( $products as $pid ) {
                if ( ! isset( $this->product_badge_map[ $pid ] ) ) {
                    $this->product_badge_map[ $pid ] = $label;
                }
            }
        }

        return $this->product_badge_map;
    }

    /* -----------------------------------------------------------------------
     * Badge label per rule type
     * -------------------------------------------------------------------- */

    private function get_label( array $rule, string $type ): string {
        switch ( $type ) {
            case 'bogo':
                return 'BUY 1 GET 1';

            case 'flat/percentage':
                $val   = $rule['discountValue'] ?? '';
                $ptype = strtolower( $rule['fpDiscountType'] ?? 'percentage' );
                return ( $ptype === 'percentage' && $val !== '' ) ? $val . '% OFF' : 'SALE';

            case 'bulk discount':
                return 'BULK DEAL';

            case 'buy x get y':
                return 'SPECIAL OFFER';
        }

        return 'SALE';
    }

    /* -----------------------------------------------------------------------
     * Extract explicitly targeted product IDs from a rule.
     * Returns [] if no specific products are selected (global rule → no badge).
     * -------------------------------------------------------------------- */

    private function get_targeted_product_ids( array $rule, string $type ): array {
        $ids = [];

        switch ( $type ) {
            case 'bogo':
                // buyProduct is an array of condition objects: [{field, operator, value:[ids]}]
                $ids = $this->extract_ids_from_product_conditions( (array) ( $rule['buyProduct'] ?? [] ) );
                break;

            case 'buy x get y':
                $buy = $this->extract_ids_from_product_conditions( (array) ( $rule['buyProduct'] ?? [] ) );
                $get = $this->extract_ids_from_product_conditions( (array) ( $rule['getProduct'] ?? [] ) );
                $ids = array_unique( array_merge( $buy, $get ) );
                break;

            case 'bulk discount':
                $ids = $this->extract_ids_from_product_conditions( (array) ( $rule['buyProducts'] ?? [] ) );
                break;

            case 'flat/percentage':
                if ( ! empty( $rule['enableConditions'] ) && ! empty( $rule['conditions'] ) ) {
                    $ids = $this->extract_ids_from_conditions( (array) $rule['conditions'] );
                }
                break;
        }

        return array_values( array_filter( array_unique( $ids ) ) );
    }

    /**
     * Parse BOGO/Bulk/BXGY product condition arrays.
     * Each entry: { field: "product"|"product_category"|"all_products"|..., value: [ids] }
     * Returns [] when field is "all_products" (global rule, badge on everything).
     */
    private function extract_ids_from_product_conditions( array $conditions ): array {
        $product_ids  = [];
        $category_ids = [];

        foreach ( $conditions as $condition ) {
            $field = $condition['field'] ?? '';
            $value = (array) ( $condition['value'] ?? [] );

            if ( $field === 'all_products' ) {
                // Global — caller will treat empty return as a global rule
                return [];
            }

            if ( in_array( $field, [ 'product', 'product_variation' ], true ) ) {
                foreach ( $value as $v ) {
                    $product_ids[] = (int) $v;
                }
            }

            if ( $field === 'product_category' ) {
                foreach ( $value as $v ) {
                    $category_ids[] = (int) $v;
                }
            }
        }

        if ( ! empty( $category_ids ) ) {
            $cat_products = get_posts( [
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'tax_query'      => [ [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $category_ids,
                ] ],
            ] );
            $product_ids = array_merge( $product_ids, $cat_products );
        }

        return array_unique( array_map( 'intval', $product_ids ) );
    }

    /* -----------------------------------------------------------------------
     * Parse conditions to find explicitly listed product or category IDs,
     * then expand category IDs to product IDs.
     * -------------------------------------------------------------------- */

    private function extract_ids_from_conditions( array $conditions ): array {
        $product_ids  = [];
        $category_ids = [];

        foreach ( $conditions as $condition ) {
            $field = $condition['field'] ?? '';
            $value = (array) ( $condition['value'] ?? [] );

            if ( $field === 'cart_item_product' ) {
                foreach ( $value as $v ) {
                    $product_ids[] = (int) $v;
                }
            }

            if ( $field === 'cart_item_category' ) {
                foreach ( $value as $v ) {
                    $category_ids[] = (int) $v;
                }
            }
        }

        // Expand category IDs → product IDs
        if ( ! empty( $category_ids ) ) {
            $cat_products = get_posts( [
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'tax_query'      => [ [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $category_ids,
                ] ],
            ] );
            $product_ids = array_merge( $product_ids, $cat_products );
        }

        return array_unique( array_map( 'intval', $product_ids ) );
    }
}
