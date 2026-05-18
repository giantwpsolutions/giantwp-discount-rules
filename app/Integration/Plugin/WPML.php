<?php
/**
 * WPML Integration.
 *
 * When WPML is active, product/category/tag IDs stored in a rule are in the
 * default language. A customer browsing in another language has different post IDs
 * in their cart. This integration expands each condition ID to include its
 * translation so the comparison works across all languages.
 *
 * @package GiantWP_Discount_Rules
 */

namespace GiantWP_Discount_Rules\Integration\Plugin;

defined( 'ABSPATH' ) || exit;

use GiantWP_Discount_Rules\Traits\SingletonTrait;

class WPML {

    use SingletonTrait;

    protected function __construct() {
        if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
            return;
        }

        add_filter( 'gwpdr_condition_product_ids',  [ $this, 'expand_post_ids' ] );
        add_filter( 'gwpdr_condition_category_ids', [ $this, 'expand_category_ids' ] );
        add_filter( 'gwpdr_condition_tag_ids',      [ $this, 'expand_tag_ids' ] );
    }

    /**
     * Expand a list of product/post IDs to include their current-language translations.
     *
     * @param int[] $ids
     * @return int[]
     */
    public function expand_post_ids( array $ids ): array {
        return $this->expand( $ids, 'product' );
    }

    /**
     * Expand a list of product_cat term IDs to include their translations.
     *
     * @param int[] $ids
     * @return int[]
     */
    public function expand_category_ids( array $ids ): array {
        return $this->expand( $ids, 'product_cat' );
    }

    /**
     * Expand a list of product_tag term IDs to include their translations.
     *
     * @param int[] $ids
     * @return int[]
     */
    public function expand_tag_ids( array $ids ): array {
        return $this->expand( $ids, 'product_tag' );
    }

    /**
     * For each ID, add the translated ID for the current language (if different).
     *
     * @param int[]  $ids
     * @param string $element_type  Post type or taxonomy name passed to wpml_object_id.
     * @return int[]
     */
    private function expand( array $ids, string $element_type ): array {
        $expanded = [];

        foreach ( $ids as $id ) {
            $expanded[] = (int) $id;

            $translated = (int) apply_filters( 'wpml_object_id', $id, $element_type, true );

            if ( $translated && $translated !== (int) $id ) {
                $expanded[] = $translated;
            }
        }

        return array_unique( $expanded );
    }
}
