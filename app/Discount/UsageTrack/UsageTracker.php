<?php
  /**
 * UsageTracker Class.
 *
 * Handles saving and incrementing usage count for various discount types.
 *
 * @package AIO_DiscountRules\Discount\UsageTrack
 */

namespace AIO_DiscountRules\Discount\UsageTrack;

defined( 'ABSPATH' ) || exit;

/**
 * Class UsageTracker
 */
class UsageTracker {

    /**
     * Store rule IDs into order meta.
     *
     * @param int    $order_id Order ID.
     * @param array  $rule_ids Rule ID array.
     * @param string $meta_key Meta key to store under.
     */
    public static function store_rule_ids( $order_id, array $rule_ids, string $meta_key ) {
        update_post_meta( $order_id, $meta_key, $rule_ids );
    }

      /**
     * Increase usage count for rules stored in order meta.
     *
     * @param int    $order_id  Order ID.
     * @param string $option_key Option key where rules are stored.
     * @param string $meta_key  Meta key where applied rule IDs are saved.
     */
    public static function increase_use_time( $order_id, string $option_key, string $meta_key ) {

        $used_rule_ids = get_post_meta( $order_id, $meta_key, true);
        if ( empty( $used_rule_ids ) || ! is_array( $used_rule_ids ) ) {
            return;
        }

        $rules = maybe_unserialize( get_option( $option_key, [] ) );
        if ( empty( $rules ) ) {
            return;
        }

        $updated = false;

        foreach ( $rules as &$rule ) {
            if ( in_array( $rule['id'], $used_rule_ids, true ) ) {
                $rule['usedCount'] = isset( $rule['usedCount'] ) ? intval( $rule['usedCount'] ) + 1 : 1;
                $updated = true;
            }
        }

        if ( $updated ) {
            update_option( $option_key, maybe_serialize( $rules ) );
        }
    }
}
