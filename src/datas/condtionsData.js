import { reactive, ref } from 'vue';


// State for conditions application (any/all)
export const conditonsApplies = ref('any');

// Reactive data for conditions
export const conditions = reactive([
    { id: Date.now(), field: '', operator: '', value: '' },
]);

// Dynamic condition options with i18n
export const conditionOptions = [
    {
        label: __('Cart', 'all-in-one-woodiscount'),
        options: [
            { label: __('Cart Subtotal Price', 'all-in-one-woodiscount'), value: 'cart_subtotal_price' },
            { label: __('Cart Quantity', 'all-in-one-woodiscount'), value: 'cart_quantity' },
            { label: __('Cart Total Weight', 'all-in-one-woodiscount'), value: 'cart_total_weight' },
        ],
    },
    {
        label: __('Cart Items', 'all-in-one-woodiscount'),
        options: [
            { label: __('Cart Item - Product', 'all-in-one-woodiscount'), value: 'cart_item_product' },
            { label: __('Cart Item - Variation', 'all-in-one-woodiscount'), value: 'cart_item_variation' },
            { label: __('Cart Item - Category', 'all-in-one-woodiscount'), value: 'cart_item_category' },
            { label: __('Cart Item - Tag', 'all-in-one-woodiscount'), value: 'cart_item_tag' },
            { label: __('Cart Item - Regular Price', 'all-in-one-woodiscount'), value: 'cart_item_regular_price' },
        ],
    },
    {
        label: __('Customer', 'all-in-one-woodiscount'),
        options: [
            { label: __('Customer Is Logged In', 'all-in-one-woodiscount'), value: 'customer_is_logged_in' },
            { label: __('Customer Role', 'all-in-one-woodiscount'), value: 'customer_role' },
            { label: __('Specific Customer', 'all-in-one-woodiscount'), value: 'customer_specific' },
        ],
    },
    {
        label: __('Purchase History', 'all-in-one-woodiscount'),
        options: [
            { label: __('Customer Order Count', 'all-in-one-woodiscount'), value: 'customer_order_count' },
            { label: __('Order History Category', 'all-in-one-woodiscount'), value: 'customer_order_history_category' },
            { label: __('Shipping Region', 'all-in-one-woodiscount'), value: 'customer_shipping_region' },
        ],
    },
    {
        label: __('Others', 'all-in-one-woodiscount'),
        options: [
            { label: __('Payment Method', 'all-in-one-woodiscount'), value: 'payment_method' },
            { label: __('Applied Coupons', 'all-in-one-woodiscount'), value: 'applied_coupons' },
        ],
    },
];

// Function to add a new condition
export const addCondition = () => {
    conditions.push({
        id: Date.now(),
        field: '',
        operator: '',
        value: '',
    });
};

// Function to remove a condition by ID
export const removeCondition = (id) => {
    const index = conditions.findIndex((cond) => cond.id === id);
    if (index > -1) conditions.splice(index, 1);
};
