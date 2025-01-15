<script setup>
import { reactive, ref } from "vue";
import { Delete } from "@element-plus/icons-vue";
import {
  conditionOptions,
  conditonsApplies,
  enableConditions,
  operatorOptions,
} from "@/data/conditionsData.js";

// Reactive Data
const { __ } = wp.i18n;
const conditions = reactive([
  {
    id: Date.now(),
    field: "cart_subtotal_price",
    operator: "greater_than",
    value: "100",
  },
]);

// Value Field Options for Dropdowns
const dropdownOptions = {
  cart_item_product: ["Product 1", "Product 2", "Product 3"],
  cartItemVariation: ["Variation 1", "Variation 2", "Variation 3"],
  specificCustomer: ["Customer A", "Customer B", "Customer C"],
};

// Add a New Condition
const addCondition = (event) => {
  event.preventDefault();
  conditions.push({
    id: Date.now(),
    field: "cart_subtotal_price",
    operator: "greater_than",
    value: "100",
  });
};

// Remove a Condition
const removeCondition = (id) => {
  const index = conditions.findIndex((cond) => cond.id === id);
  if (index > -1) conditions.splice(index, 1);
};

// Get Operators for the Selected Field
const getOperators = (field) => {
  if (
    field === "cart_item_product" ||
    field === "cart_item_variation" ||
    field === "cart_item_category" ||
    field === "cart_item_tag" ||
    field === "customer_order_history_category" ||
    field === "customer_order_history_category"
  ) {
    return operatorOptions.contain;
  }
  if (field === "isLoggedIn") {
    return operatorOptions.isLoggedIn;
  }
  return operatorOptions.default;
};

// Check if the Third Field is a Number Input
const isNumberField = (field) =>
  ["cart_quantity", "cart_total_weight"].includes(field);

// Check if the Third Field is a Number Input
const isPricingField = (field) => ["cart_subtotal_price"].includes(field);

// Check if the Third Field is a Dropdown
const isDropdownField = (field) =>
  ["cart_item_product", "cart_item_variation", "customer_specific"].includes(
    field
  );

// Get Dropdown Options for the Third Field
const getDropdownOptions = (field) => dropdownOptions[field] || [];
</script>

<template>
  <div class="space-y-4 w-5/6">
    <div class="flex items-center gap-2 mt-6 mb-1">
      <el-switch
        v-model="enableConditions"
        inline-prompt
        :active-text="__('On', 'aio-woodiscount')"
        :inactive-text="__('Off', 'aio-woodiscount')" />
      <label class="text-sm font-medium text-gray-900 flex items-center gap-1">
        {{ __("Enable Conditions?", "aio-woodiscount") }}
      </label>
    </div>

    <div class="space-y-4 w-full" v-if="enableConditions">
      <div class="flex items-center gap-2 mt-6 mb-1">
        <label
          class="text-sm font-medium text-gray-900 flex items-center gap-1">
          {{ __("Apply conditions if matches", "aio-woodiscount") }}
        </label>
        <div class="group relative">
          <el-radio-group v-model="conditonsApplies">
            <el-radio-button
              :label="__('Any', 'aio-woodiscount')"
              value="any" />
            <el-radio-button
              :label="__('All', 'aio-woodiscount')"
              value="all" />
          </el-radio-group>
        </div>
      </div>

      <label
        for="add_conditions"
        class="block text-sm font-medium text-gray-900 my-5">
        {{ __("Add Conditions", "aio-woodiscount") }}
      </label>

      <!-- Dynamic Conditions -->
      <div
        v-for="condition in conditions"
        :key="condition.id"
        class="flex items-center gap-4 mb-4">
        <div class="flex items-center w-full gap-4">
          <!-- Condition Options -->
          <select
            v-model="condition.field"
            class="border h-8 rounded p-2 flex-1 basis-1/4 text-sm text-gray-700 bg-white border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            :aria-label="__('Condition Field', 'aio-woodiscount')">
            <option value="">
              {{ __("Please select", "aio-woodiscount") }}
            </option>
            <template v-for="group in conditionOptions" :key="group.label">
              <optgroup
                :label="group.label"
                class="font-semibold text-gray-800">
                <option
                  v-for="option in group.options"
                  :key="option.value"
                  :value="option.value"
                  class="px-4 py-2 text-gray-700 hover:bg-gray-100">
                  {{ option.label }}
                </option>
              </optgroup>
            </template>
          </select>

          <!-- Operator -->
          <select
            v-model="condition.operator"
            class="border h-8 rounded p-2 flex basis-1/4 text-sm text-gray-700 bg-white border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            :aria-label="__('Condition Operator', 'aio-woodiscount')">
            <option value="" disabled>
              {{ __("Select Operator", "aio-woodiscount") }}
            </option>
            <option
              v-for="op in getOperators(condition.field)"
              :key="op.value"
              :value="op.value">
              {{ op.label }}
            </option>
          </select>

          <!-- Third Field -->
          <div class="flex basis-2/4">
            <input
              v-if="isNumberField(condition.field)"
              v-model="condition.value"
              type="number"
              placeholder="Enter a number"
              class="border rounded h-8 p-2 w-full text-sm text-gray-700 bg-white border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />

            <!-- pricing -->
            <div v-else-if="isPricingField(condition.field)" class="">
              <input
                v-model="usageLimitsCount"
                type="number"
                id="usageLimit"
                :placeholder="__('Enter Value', 'aio-woodiscount')"
                class="w-5/6 h-8 rounded-custom-aio-right border-gray-300 shadow-sm sm:text-sm pr-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
              <span
                class="ml-0 w-1/6 h-8 px-2.5 py-2.5 border rounded-custom-aio-right text-gray-700 bg-gray-100">
                $
              </span>
            </div>

            <select
              v-else-if="isDropdownField(condition.field)"
              v-model="condition.value"
              class="w-5/6 h-8 rounded-custom-aio-right border-gray-300 shadow-sm sm:text-sm pr-4 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <option value="" disabled>Select an option</option>
              <option
                v-for="option in getDropdownOptions(condition.field)"
                :key="option"
                :value="option">
                {{ option }}
              </option>
            </select>

            <span v-else class="text-gray-500">{{
              __("No value needed", "aio-woodiscount")
            }}</span>
          </div>
        </div>

        <!-- Delete Button -->
        <el-icon
          @click="removeCondition(condition.id)"
          size="20px"
          class="delete_icon">
          <Delete style="color: red; cursor: pointer" />
        </el-icon>
      </div>

      <!-- Add Condition Button -->
      <button
        @click="addCondition"
        class="bg-blue-500 text-white rounded px-4 py-2 hover:bg-blue-600"
        :aria-label="__('Add Condition', 'aio-woodiscount')">
        {{ __("Add Condition", "aio-woodiscount") }}
      </button>
    </div>
  </div>
</template>

<style>
.delete_icon {
  margin-left: 10px;
}
.rounded-custom-aio-right {
  border-radius: 0px 5px 5px 0px;
}
.rounded-custom-aio-left {
  border-radius: 5px 0px 0px 5px;
}
</style>
