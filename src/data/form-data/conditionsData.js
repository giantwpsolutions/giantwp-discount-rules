import { reactive, ref, onMounted } from 'vue';
const { __ } = wp.i18n;


//State for conditions usages (on/off)
export const enableConditions = ref(false);

// State for conditions application (any/all)
export const conditonsApplies = ref('any');


// Reactive data for conditions
export const conditions = reactive([
    { id: Date.now(), field: '', operator: '', value: '' },
]);

// Dynamic condition options with i18n
export const conditionOptions = [
    {
        label: __('Cart', 'giantwp-discount-rules'),
        options: [
            { label: __('Cart Subtotal Price', 'giantwp-discount-rules'), value: 'cart_subtotal_price' },
            { label: __('Cart Quantity', 'giantwp-discount-rules'), value: 'cart_quantity' },
            { label: __('Cart Total Weight', 'giantwp-discount-rules'), value: 'cart_total_weight' },
        ],
    },
    {
        label: __('Cart Items', 'giantwp-discount-rules'),
        options: [
            { label: __('Cart Item - Product', 'giantwp-discount-rules'), value: 'cart_item_product' },
            { label: __('Cart Item - Variation', 'giantwp-discount-rules'), value: 'cart_item_variation' },
            { label: __('Cart Item - Category', 'giantwp-discount-rules'), value: 'cart_item_category' },
            { label: __('Cart Item - Tag', 'giantwp-discount-rules'), value: 'cart_item_tag' },
            { label: __('Cart Item - Regular Price', 'giantwp-discount-rules'), value: 'cart_item_regular_price' },
        ],
    },
    {
        label: __('Customer', 'giantwp-discount-rules'),
        options: [
            { label: __('Customer Is Logged In', 'giantwp-discount-rules'), value: 'customer_is_logged_in' },
            { label: __('Customer Role', 'giantwp-discount-rules'), value: 'customer_role' },
            { label: __('Specific Customer', 'giantwp-discount-rules'), value: 'specific_customer' },
        ],
    },
    {
        label: __('Purchase History', 'giantwp-discount-rules'),
        options: [
            { label: __('Customer Order Count', 'giantwp-discount-rules'), value: 'customer_order_count' },
            { label: __('Order History Product', 'giantwp-discount-rules'), value: 'customer_order_history_product' },
            { label: __('Order History Category', 'giantwp-discount-rules'), value: 'customer_order_history_category' },
            // { label: __('Shipping Region', 'giantwp-discount-rules'), value: 'customer_shipping_region' },
        ],
    },
    {
        label: __('Others', 'giantwp-discount-rules'),
        options: [
            { label: __('Payment Method', 'giantwp-discount-rules'), value: 'payment_method' },
            // { label: __('Applied Coupons', 'giantwp-discount-rules'), value: 'applied_coupons' },
        ],
    },
];


// Operator Options
export const operatorOptions = {
    default: [
        { label: __('Greater Than', 'giantwp-discount-rules'), value: 'greater_than' },
        { label: __('Less Than', 'giantwp-discount-rules'), value: 'less_than' },
        {
            label: __('Equal or Greater Than', 'giantwp-discount-rules'),
            value: 'equal_greater_than',
        },
        {
            label: __('Equal or Less Than', 'giantwp-discount-rules'),
            value: 'equal_less_than',
        },
    ],

    contain: [
        { label: __('Contains All', 'giantwp-discount-rules'), value: 'contain_all' },
        {
            label: __('Contains in List', 'giantwp-discount-rules'),
            value: 'contain_in_list',
        },
        {
            label: __('Not Contain in List', 'giantwp-discount-rules'),
            value: 'not_contain_inlist',
        },
    ],

    isLoggedIn: [
        {
            label: __('Logged In', 'giantwp-discount-rules'),
            value: 'logged_in',
        },
        {
            label: __('Not Logged In', 'giantwp-discount-rules'),
            value: 'not_logged_in',
        },
    ],

    inList: [
        {
            label: __('In List', 'giantwp-discount-rules'),
            value: 'in_list',
        },
        {
            label: __('Not in List', 'giantwp-discount-rules'),
            value: 'not_in_list',
        },
    ],
};



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





// Value Field Options for Dropdowns
export const dropdownOptions = reactive({
    cart_item_product: [],
    cart_item_variation: [],
    customer_role: [],
    specific_customer: [],
    cart_item_category: [],
    cart_item_tag: [],
    payment_method: [],
    customer_order_history_category: [],
});

//value for Cascade dropdown options

export const cascadeOptions = reactive({
    customer_shipping_region: [],
});

// Get Operators for the Selected Field
export const getOperators = (field) => {
    if (
        field === "cart_item_product" ||
        field === "cart_item_variation" ||
        field === "cart_item_category" ||
        field === "cart_item_tag" ||
        field === "customer_order_history_category" ||
        field === "customer_order_history_product"
    ) {
        return operatorOptions.contain;
    }
    if (
        field === "customer_role" ||
        field === "specific_customer" ||
        field === "payment_method" ||
        field === "customer_shipping_region"
    ) {
        return operatorOptions.inList;
    }
    if (field === "customer_is_logged_in") {
        return operatorOptions.isLoggedIn;
    }
    return operatorOptions.default;
};