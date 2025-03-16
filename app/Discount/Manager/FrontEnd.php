<?php

namespace AIO_WooDiscount\Discount\Manager;

use AIO_WooDiscount\Discount\BogoBuyProduct\BogoBuyProduct;


class FrontEnd
{
    public function __construct()
    {
        add_action('woocommerce_after_add_to_cart_button', [$this, 'render_bulk_pricing_table']);
    }


    /**
     * Display bulk pricing table on single product page.
     */
    public function render_bulk_pricing_table()
    {
        global $product;

        if (!$product || !is_product()) return;

        $product_id = $product->get_id();
        $rules = maybe_unserialize(get_option('aio_bulk_discount', []));
        if (empty($rules)) return;

        foreach ($rules as $rule) {
            if (($rule['status'] ?? '') !== 'on') continue;
            if (($rule['discountType'] ?? '') !== 'bulk discount') continue;

            // Only show for matching product
            $buy_conditions = $rule['buyProducts'] ?? [];
            $applies = $rule['getApplies'] ?? 'any';
            $single_cart = WC()->cart; // cart is used only to evaluate matching

            if (!BogoBuyProduct::check_all($single_cart, $buy_conditions, $applies)) {
                continue;
            }

            $ranges = $rule['bulkDiscounts'] ?? [];
            if (empty($ranges)) continue;

            // ✅ Output table
            echo '<div class="aio-bulk-pricing-table" style="margin-top:20px;">';
            echo '<h4 style="margin-bottom:10px;">' . esc_html__('Bulk Pricing', 'all-in-one-woodiscount') . '</h4>';
            echo '<table style="width:100%; border-collapse:collapse; border:1px solid #ccc;">';
            echo '<thead><tr>';
            echo '<th style="border:1px solid #ccc; padding:8px;">' . esc_html__('Range', 'all-in-one-woodiscount') . '</th>';
            echo '<th style="border:1px solid #ccc; padding:8px;">' . esc_html__('Discount Type', 'all-in-one-woodiscount') . '</th>';
            echo '<th style="border:1px solid #ccc; padding:8px;">' . esc_html__('Value', 'all-in-one-woodiscount') . '</th>';
            echo '</tr></thead><tbody>';

            foreach ($ranges as $range) {
                $from = intval($range['fromcount'] ?? 0);
                $to = intval($range['toCount'] ?? 0);
                $type = sanitize_text_field($range['discountTypeBulk'] ?? 'fixed');
                $value = floatval($range['discountValue'] ?? 0);
                $max = floatval($range['maxValue'] ?? 0);

                $range_label = $from . ' - ' . ($to > 0 ? $to : esc_html__('Unlimited', 'all-in-one-woodiscount'));

                $type_label = match ($type) {
                    'percentage' => esc_html__('Percentage', 'all-in-one-woodiscount'),
                    'flat_price'       => esc_html__('Flat Price', 'all-in-one-woodiscount'),
                    default      => esc_html__('Fixed Discount', 'all-in-one-woodiscount'),
                };

                echo '<tr>';
                echo '<td style="border:1px solid #ccc; padding:8px;">' . esc_html($range_label) . '</td>';
                echo '<td style="border:1px solid #ccc; padding:8px;">' . esc_html($type_label) . '</td>';
                echo '<td style="border:1px solid #ccc; padding:8px;">';

                if ($type === 'percentage') {
                    echo esc_html($value . '%');
                    if ($max > 0) {
                        echo ' (' . sprintf(esc_html__('Max: %s', 'all-in-one-woodiscount'), wc_price($max)) . ')';
                    }
                } elseif ($type === 'flat') {
                    echo wc_price($value); // flat price
                } else {
                    echo '' . wc_price($value); // fixed discount
                }

                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody></table></div>';

            break; // Only display first matching rule
        }
    }
}
