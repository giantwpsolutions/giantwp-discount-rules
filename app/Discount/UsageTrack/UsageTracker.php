<?php

namespace AIO_WooDiscount\Discount\UsageTrack;

defined('ABSPATH') || exit;

class UsageTracker
{
    public static function store_rule_ids($order_id, array $rule_ids, string $meta_key)
    {
        update_post_meta($order_id, $meta_key, $rule_ids);
        error_log("💾 Stored rule IDs in order #{$order_id} under meta '{$meta_key}': " . implode(', ', $rule_ids));
    }

    public static function increase_use_time($order_id, string $option_key, string $meta_key)
    {
        error_log("🔍 increase_use_time called on order #{$order_id} using meta {$meta_key}");

        $used_rule_ids = get_post_meta($order_id, $meta_key, true);
        if (empty($used_rule_ids) || !is_array($used_rule_ids)) {
            error_log("⚠️ No rule IDs found in order #{$order_id} for meta {$meta_key}");
            return;
        }

        $rules = maybe_unserialize(get_option($option_key, []));
        if (empty($rules)) {
            error_log("❌ No rules found in option {$option_key}");
            return;
        }

        $updated = false;

        foreach ($rules as &$rule) {
            if (in_array($rule['id'], $used_rule_ids, true)) {
                $rule['usedCount'] = isset($rule['usedCount']) ? intval($rule['usedCount']) + 1 : 1;
                error_log("✅ Increased usedCount for rule {$rule['id']} in order #{$order_id} (Now: {$rule['usedCount']})");
                $updated = true;
            }
        }

        if ($updated) {
            update_option($option_key, maybe_serialize($rules));
            error_log("💾 Updated {$option_key} with new usage counts");
        }
    }
}
