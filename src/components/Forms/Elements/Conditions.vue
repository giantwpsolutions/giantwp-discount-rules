<script setup>
import { reactive } from 'vue';

// Initial conditions data structure
const conditions = reactive([
  { id: Date.now(), field: '', operator: '', value: '' },
]);

// Options for the first field
const conditionOptions = [
  { label: 'Select', value: '' },
  { label: 'Cart Subtotal Price', value: 'cartPrice' },
  { label: 'Cart Quantity', value: 'cartQuantity' },
  { label: 'Cart Total Weight', value: 'cartWeight' },
  { label: 'Cart Item Product', value: 'cartItemProduct' },
  { label: 'Cart Item Variation', value: 'cartItemVariation' },
  { label: 'Cart Item Category', value: 'cartItemCategory' },
  { label: 'Cart Item Tag', value: 'cartItemTag' },
  { label: 'Cart Item Regular Price', value: 'cartItemPrice' },
  { label: 'Customer Is Logged In', value: 'isLoggedIn' },
  { label: 'Customer Role', value: 'customerRole' },
  { label: 'Specific Customer', value: 'specificCustomer' },
  { label: 'Customer Order Count', value: 'orderCount' },
  { label: 'Order History Category', value: 'orderCategory' },
  { label: 'Shipping Region', value: 'shippingRegion' },
  { label: 'Payment Method', value: 'paymentMethod' },
  { label: 'Applied Coupons', value: 'appliedCoupons' },
];

// Options for operators based on the selected first field
const operatorOptions = {
  default: ['Greater Than', 'Less Than', 'Equal or Greater Than', 'Equal or Less Than'],
  contain: ['Contains All', 'Contains in List', 'Not Contain in List'],
  isLoggedIn: ['Logged In', 'Not Logged In'],
  inList: ['In List', 'Not In List'],
};

// Add new condition
const addCondition = (event) => {
  // Prevent form submission or reload
  event.preventDefault();

  conditions.push({
    id: Date.now(),
    field: '',
    operator: '',
    value: '',
  });
};

// Remove condition
const removeCondition = (id) => {
  const index = conditions.findIndex((cond) => cond.id === id);
  if (index > -1) conditions.splice(index, 1);
};

// Dynamic operator options based on selected field
const getOperators = (field) => {
  if (
    ['cartItemProduct', 'cartItemVariation', 'cartItemCategory', 'cartItemTag'].includes(field)
  ) {
    return operatorOptions.contain;
  } else if (field === 'isLoggedIn') {
    return operatorOptions.isLoggedIn;
  } else if (['customerRole', 'specificCustomer'].includes(field)) {
    return operatorOptions.inList;
  }
  return operatorOptions.default;
};
</script>

<template>
  <div class="p-4">
    <h3 class="text-lg font-bold mb-4">{{ __('Add Conditions', 'aio-woodiscount') }}</h3>

    <!-- Dynamic Conditions -->
    <div v-for="condition in conditions" :key="condition.id" class="flex items-center gap-4 mb-4">
      <!-- First Field -->
      <select
        v-model="condition.field"
        class="border rounded p-2 flex-1"
        :aria-label="__('Condition Field', 'aio-woodiscount')"
      >
        <option
          v-for="option in conditionOptions"
          :key="option.value"
          :value="option.value"
        >
          {{ __(option.label, 'aio-woodiscount') }}
        </option>
      </select>

      <!-- Operator -->
      <select
        v-model="condition.operator"
        :disabled="!condition.field"
        class="border rounded p-2 flex-1"
        :aria-label="__('Condition Operator', 'aio-woodiscount')"
      >
        <option value="" disabled>{{ __('Select Operator', 'aio-woodiscount') }}</option>
        <option v-for="op in getOperators(condition.field)" :key="op" :value="op">
          {{ __(op, 'aio-woodiscount') }}
        </option>
      </select>

      <!-- Third Field: Value -->
      <input
        v-if="condition.field && condition.operator"
        v-model="condition.value"
        :type="['cartPrice', 'cartItemPrice'].includes(condition.field) ? 'number' : 'text'"
        :placeholder="__('Enter value', 'aio-woodiscount')"
        class="border rounded p-2 flex-1"
      />
      <span
        v-else-if="condition.field === 'isLoggedIn'"
        class="flex-1 text-center text-sm text-gray-500"
      >
        {{ __('No value required', 'aio-woodiscount') }}
      </span>

      <!-- Delete Button -->
      <button
        @click="removeCondition(condition.id)"
        class="text-red-500 border border-red-500 rounded p-2 hover:bg-red-100"
        :aria-label="__('Delete Condition', 'aio-woodiscount')"
      >
        {{ __('Delete', 'aio-woodiscount') }}
      </button>
    </div>

    <!-- Add Condition Button -->
    <button
      @click="addCondition"
      class="bg-blue-500 text-white rounded px-4 py-2 hover:bg-blue-600"
      :aria-label="__('Add Condition', 'aio-woodiscount')"
    >
      {{ __('Add Condition', 'aio-woodiscount') }}
    </button>
  </div>
</template>
