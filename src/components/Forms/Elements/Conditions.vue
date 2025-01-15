<script setup>
import { reactive, ref, onMounted } from "vue";
import { Delete } from "@element-plus/icons-vue";
import {
  conditionOptions,
  conditonsApplies,
  enableConditions,
  operatorOptions,
  dropdownOptions,
  cascadeOptions,
  getOperators,
} from "@/data/conditionsData.js";

import {
  productOptions,
  isLoadingProducts,
  productError,
  loadProducts,
  variationOptions,
} from "@/data/productsFetch.js";

import {
  userOptions,
  roleOptions,
  isLoadingUsersAndRoles,
  loadUsersAndRoles,
} from "@/data/usersFetch.js";

import {
  categoryOptions,
  tagOptions,
  isLoadingCategoriesTags,
  loadCategoriesAndTags,
} from "@/data/categoriesAndTagsFetch.js";

import {
  paymentGatewayOptions,
  isLoadingPaymentGateways,
  paymentGatewayError,
  loadPaymentGateways,
} from "@/data/paymentGatewaysFetch.js";

import {
  countriesOptions,
  isLoadingCountries,
  loadCountriesAndStates,
} from "@/data/shippingFetch.js";

import {
  generalData,
  isLoadingGeneralData,
  generalDataError,
  loadGeneralData,
} from "@/data/generalDataFetch";

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

//API fetching Data

onMounted(async () => {
  try {
    // Load products and variations
    await loadProducts();
    dropdownOptions.cart_item_product = productOptions.value; // Products
    dropdownOptions.customer_order_history_product = productOptions.value; // Products
    dropdownOptions.cart_item_variation = variationOptions.value; // Variations

    // Load users and roles
    await loadUsersAndRoles();
    dropdownOptions.customer_role = roleOptions.value; // Roles
    dropdownOptions.specific_customer = userOptions.value; // Users

    //Load Category and Tags
    await loadCategoriesAndTags();
    dropdownOptions.cart_item_category = categoryOptions.value;
    dropdownOptions.customer_order_history_category = categoryOptions.value;
    dropdownOptions.cart_item_tag = tagOptions.value;

    //load Payment gateways
    await loadPaymentGateways();
    dropdownOptions.payment_method = paymentGatewayOptions.value;

    //load countries and states
    await loadCountriesAndStates();
    cascadeOptions.customer_shipping_region = countriesOptions.value;

    //load general Data
    await loadGeneralData();
  } catch (error) {
    console.error("Error loading dropdown options:", error);
  }
});

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

// Check if the Third Field is a Number Input
const isNumberField = (field) =>
  ["cart_quantity", "cart_total_weight", "customer_order_count"].includes(
    field
  );

// Check if the Third Field is a Number Input
const isPricingField = (field) =>
  ["cart_subtotal_price", "cart_item_regular_price"].includes(field);

// Check if the Third Field is a Dropdown
const isDropdownField = (field) =>
  [
    "cart_item_product",
    "cart_item_variation",
    "customer_role",
    "specific_customer",
    "cart_item_category",
    "cart_item_tag",
    "payment_method",
    "customer_order_history_category",
    "customer_order_history_product",
  ].includes(field);

const isCascadefield = (field) => ["customer_shipping_region"].includes(field);

// Get Dropdown Options for the Third Field
const getDropdownOptions = (field) => dropdownOptions[field] || [];

//Get Cascade Options for the third field

const getCascadeOptions = (field) => cascadeOptions[field] || [];
</script>

<template>
  <div class="space-y-4 w-5/6">
    <!-- Enable Conditions -->
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
        v-for="(condition, index) in conditions"
        :key="condition.id"
        class="mt-1">
        <!-- OR/AND Separator -->
        <div v-if="index > 0" class="mb-2">
          <span class="text-black italic text-sm">
            {{
              conditonsApplies === "any"
                ? __("Or", "aio-woodiscount")
                : __("And", "aio-woodiscount")
            }}
          </span>
        </div>
        <div class="flex items-center gap-2 mb-2">
          <!-- Field Selector -->
          <div class="w-[30%]">
            <select
              v-model="condition.field"
              class="w-full h-8 border rounded p-2 text-sm text-gray-700 bg-white border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
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
          </div>

          <!-- Operator Selector -->
          <div class="w-[20%]">
            <select
              v-model="condition.operator"
              class="w-full h-8 border rounded p-2 text-sm text-gray-700 bg-white border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              :aria-label="__('Condition Operator', 'aio-woodiscount')">
              <option
                v-for="op in getOperators(condition.field)"
                :key="op.value"
                :value="op.value">
                {{ op.label }}
              </option>
            </select>
          </div>

          <!-- Value Input -->
          <div class="w-[45%]">
            <input
              v-if="isNumberField(condition.field)"
              v-model="condition.value"
              type="number"
              placeholder="Enter a number"
              class="w-full h-8 border rounded p-2 text-sm text-gray-700 bg-white border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />

            <div
              v-else-if="isPricingField(condition.field)"
              class="flex items-center">
              <input
                v-model="condition.value"
                type="number"
                placeholder="Enter Value"
                class="w-5/6 h-8 border-gray-300 text-sm rounded-l border-r-0 p-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
              <span
                class="w-1/6 h-8 flex items-center justify-center bg-gray-200 border border-l-0 border-gray-300 rounded-r"
                v-html="generalData.currency_symbol || '$'">
              </span>
            </div>

            <el-select-v2
              v-else-if="isDropdownField(condition.field)"
              v-model="condition.value"
              :options="getDropdownOptions(condition.field)"
              :placeholder="__('Select', 'aio-woodiscount')"
              filterable
              multiple
              :loading="isLoadingProducts"
              class="custom-select-v2" />

            <el-cascader
              v-else-if="isCascadefield(condition.field)"
              :options="countriesOptions"
              :props="{ multiple: true, checkStrictly: true }"
              :show-all-levels="false"
              clearable
              class="full-width custom-cascade"
              filterable
              :loading="isLoadingCountries"
              placeholder="Select Continent, Country, and State" />

            <!-- <span v-else class = "text-gray-500">
              {{ __("No value needed", "aio-woodiscount") }}
            </span> -->
          </div>

          <!-- Delete Button -->
          <div class="w-[5%] flex justify-center items-center">
            <el-icon
              @click="removeCondition(condition.id)"
              size="20px"
              color="red"
              class="cursor-pointer text-red-500">
              <Delete />
            </el-icon>
          </div>
        </div>
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
.el-select__input {
  border: none;
}
.el-select__wrapper {
  padding: 0px 12px !important;
}
.el-select__input:focus-visible {
  border: none;
}
</style>
