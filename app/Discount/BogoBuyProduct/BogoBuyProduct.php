<?php

namespace AIO_WooDiscount\Discount\BogoBuyProduct;

use AIO_WooDiscount\Discount\BogoBuyProduct\BogoBuy_Field;

class BogoBuyProduct
{
    public static function check_all($cart, $conditions, $match_type = 'all')
    {
        $cart_items = $cart->get_cart();
        $results    = [];

        foreach ($conditions as $condition) {
            $field  = $condition['type'] ?? $condition['field'];
            $result = false;

            switch ($field) {
                case 'product':
                    $result = BogoBuy_Field::product($cart_items, $condition);
                    break;
                case 'product_variation':
                    $result = BogoBuy_Field::product_variation($cart_items, $condition);
                    break;
                case 'product_tags':
                    $result = BogoBuy_Field::product_tags($cart_items, $condition);
                    break;
                case 'product_category':
                    $result = BogoBuy_Field::product_category($cart_items, $condition);
                    break;
                case 'product_price':
                    $result = BogoBuy_Field::product_price($cart_items, $condition);
                    break;
                case 'product_instock':
                    $result = BogoBuy_Field::product_instock($cart_items, $condition);
                    break;
                case 'all_products':
                    $result = BogoBuy_Field::all_products($cart_items, $condition);
                    break;
                default:
                    do_action('aio_woodiscount_unhandled_bogo_condition', $condition);
                    break;
            }

            $results[] = $result;

            if ($match_type === 'any' && $result) return true;
            if ($match_type === 'all' && !$result) return false;
        }

        return $match_type === 'all';
    }
}
