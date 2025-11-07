<?php

/**
 * Compare two numeric values using a given operator.
 *
 * @since: 1.0
 * 
 * @param float|int $left     The left-hand value to compare.
 * @param string    $operator The comparison operator.
 * @param float|int $right    The right-hand value to compare.
 *
 * @return bool True if the comparison passes, false otherwise.
 */
function gwpdr_compare_numaric_value($left, $operator, $right)
{
    switch ($operator) {
        case 'greater_than':
            return $left > $right;
        case 'less_than':
            return $left < $right;
        case 'equal_greater_than':
            return $left >= $right;
        case 'equal_less_than':
            return $left <= $right;
        default:
            return false;
    }
}


/**
 * Compare cart product IDs with a list of condition product IDs using the given operator.
 * @since 1.0
 *
 * @param array  $cart_ids       An array of product IDs currently in the cart.
 * @param string $operator       The comparison operator to use.
 * @param array  $condition_ids  An array of product IDs defined in the condition.
 *
 * @return bool True if the condition is satisfied, false otherwise.
 */
function gwpdr_compare_cart_items($cart_ids, $operator, $condition_ids)
{
    switch ($operator) {
        case 'contain_in_list':
            return !empty(array_intersect($cart_ids, $condition_ids));

        case 'not_contain_in_list':
            return empty(array_intersect($cart_ids, $condition_ids));

        case 'contain_all':
            return count(array_intersect($cart_ids, $condition_ids)) === count($condition_ids);

        default:
            return false;
    }
}


/**
 * Compare a value or values against a condition list using a specified operator.
 *
 * @since 1.0
 *
 * @param string|array $current   The current value(s) to test (string or array).
 * @param string       $operator  Comparison operator (in_list, not_in_list, contain_all).
 * @param array        $expected  The list of expected values.
 *
 * @return bool True if the comparison passes, false otherwise.
 */
function gwpdr_compare_list($current, $operator, $expected)
{
    if (!is_array($expected)) {
        return false;
    }

    // Convert string to array for unified handling
    $current      = is_array($current) ? $current : [$current];
    $intersection = array_intersect($current, $expected);

    switch ($operator) {
        case 'in_list':
            return !empty($intersection);

        case 'not_in_list':
            return empty($intersection);

        case 'contain_all':
            return count($intersection) === count($expected);

        default:
            return false;
    }
}


function gwpdr_check_woocommerce_hpos()
{
    if (class_exists(\Automattic\WooCommerce\Utilities\OrderUtil::class)) {
        if (\Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled()) {
            return true;
        }
    }
    return false;
}

function gwpdr_WoocommerceDeactivationAlert()
{
?>
    <div class="notice notice-error is-dismissible">
        <p>
            <?php esc_html_e(
                'WooCommerce is deactivated! The "All-in-One Discount Rules" plugin requires WooCommerce to function properly. Please reactivate WooCommerce.',
                'giantwp-discount-rules'
            ); ?>
        </p>
    </div>
<?php
}


function gwpdr_WoocommerceMissingAlert()
{
?>
    <div class="notice notice-error is-dismissible">
        <p>
            <?php esc_html_e(
                'All-in-One Discount Rules requires WooCommerce to be installed and active.',
                'giantwp-discount-rules'
            ); ?>
        </p>
    </div>
<?php
}


//Apsero Client Api 


/**
 * Initialize the plugin tracker
 *
 * @return void
 */
function gwpdr_appsero_init_tracker() {

    $client = new Appsero\Client(
        'bebad348-5e49-4169-971a-06b0825da653',
        'GiantWP Discount Rules – Dynamic Pricing & BOGO Deals for WooCommerce',
        __FILE__
    );

    $client->insights()->init();
}



