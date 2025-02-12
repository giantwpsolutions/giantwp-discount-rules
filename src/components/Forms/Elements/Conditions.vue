<script setup>
import { reactive, ref, defineProps, defineEmits, onMounted, watch } from "vue";
import { debounce } from "lodash";
import { Delete } from "@element-plus/icons-vue";
import {
  conditionOptions,
  operatorOptions,
  dropdownOptions,
  cascadeOptions,
  getOperators,
} from "@/data/conditionsData.js";

import {
  productOptions,
  isLoadingProducts,
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
  loadPaymentGateways,
} from "@/data/paymentGatewaysFetch.js";

import {
  countriesOptions,
  isLoadingCountries,
  loadCountriesAndStates,
} from "@/data/shippingFetch.js";

import { generalData, loadGeneralData } from "@/data/generalDataFetch";

// **Props & Emits**
const props = defineProps({
  value: Array, // Parent-passed conditions
  toggle: Boolean, // Enable/Disable conditions
  conditionsApplies: String, // Enable/Disable conditions
});

const emit = defineEmits([
  "update:value",
  "update:toggle",
  "update:conditionsApplies",
]);

// **Reactive State**
const enableConditions = ref(props.toggle);
const localConditions = ref([...props.value]);
const conditionsApplies = ref(props.conditionsApplies);

// **Sync Props with Local State**
onMounted(async () => {
  try {
    await loadProducts();
    dropdownOptions.cart_item_product = productOptions.value;
    dropdownOptions.customer_order_history_product = productOptions.value;
    dropdownOptions.cart_item_variation = variationOptions.value;

    await loadUsersAndRoles();
    dropdownOptions.customer_role = roleOptions.value;
    dropdownOptions.specific_customer = userOptions.value;

    await loadCategoriesAndTags();
    dropdownOptions.cart_item_category = categoryOptions.value;
    dropdownOptions.customer_order_history_category = categoryOptions.value;
    dropdownOptions.cart_item_tag = tagOptions.value;

    await loadPaymentGateways();
    dropdownOptions.payment_method = paymentGatewayOptions.value;

    await loadCountriesAndStates();
    cascadeOptions.customer_shipping_region = countriesOptions.value;

    await loadGeneralData();
  } catch (error) {
    console.error("Error loading dropdown options:", error);
  }
});

// **Add a New Condition**
const addCondition = (event) => {
  event.preventDefault();
  localConditions.value.push({
    id: Date.now(),
    field: "cart_subtotal_price",
    operator: "greater_than",
    value: "100",
  });
};

// **Remove a Condition**
const removeCondition = (id) => {
  localConditions.value = localConditions.value.filter(
    (cond) => cond.id !== id
  );
};

// **Emit Updated Conditions**
const updateConditions = () => {
  emit("update:value", [...localConditions.value]);
};

// **Emit Toggle Change**
const updateEnableConditions = () => {
  emit("update:toggle", enableConditions.value);
};

// **Emit conditionsApplies Change**
const updateConditionsApplies = () => {
  emit("update:conditionsApplies", conditionsApplies.value);
};
// **Check Field Type**
const isNumberField = (field) =>
  ["cart_quantity", "cart_total_weight", "customer_order_count"].includes(
    field
  );

const isPricingField = (field) =>
  ["cart_subtotal_price", "cart_item_regular_price"].includes(field);

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

const isCascadeField = (field) => ["customer_shipping_region"].includes(field);

// **Dropdown & Cascader Options**
const getDropdownOptions = (field) => dropdownOptions[field] || [];
const getCascadeOptions = (field) => cascadeOptions[field] || [];

watch(
  localConditions,
  (newVal) => {
    console.log("Child Conditions Updated:", newVal);
    emit("update:value", [...newVal]);
  },
  { deep: true } // Single watcher handles all changes
);

watch(enableConditions, () => {
  updateEnableConditions();
});

watch(conditionsApplies, () => {
  updateConditionsApplies();
});

// Single debounced watcher for all changes
const emitUpdates = debounce(() => {
  console.log(
    "Child Conditions (Debounced):",
    JSON.parse(JSON.stringify(localConditions.value))
  );
  emit("update:value", [...localConditions.value]);
}, 300);

watch(
  [localConditions, enableConditions, conditionsApplies],
  () => {
    emitUpdates();
  },
  { deep: true }
);
</script>

<template>
  <div class="space-y-4 w-5/6">
    <!-- Enable Conditions -->
    <div class="flex items-center gap-2 mt-6 mb-1">
      <el-switch
        v-model="enableConditions"
        @change="updateEnableConditions"
        inline-prompt
        :active-text="__('On', 'aio-woodiscount')"
        :inactive-text="__('Off', 'aio-woodiscount')" />
      <label class="text-sm font-medium text-gray-900">
        {{ __("Enable Conditions?", "aio-woodiscount") }}
      </label>
    </div>

    <!-- Conditions Section -->
    <div class="space-y-4 w-full" v-if="enableConditions">
      <!-- Apply Conditions Mode (Any/All) -->
      <div class="flex items-center gap-2 mt-6 mb-1">
        <label
          class="text-sm font-medium text-gray-900 flex items-center gap-1">
          {{ __("Apply conditions if matches", "aio-woodiscount") }}
        </label>
        <el-radio-group
          v-model="conditionsApplies"
          @change="updateConditionsApplies">
          <el-radio-button :label="__('Any', 'aio-woodiscount')" value="any" />

          <el-radio-button :label="__('All', 'aio-woodiscount')" value="all" />
        </el-radio-group>
      </div>

      <!-- condition add -->
      <label class="block text-sm font-medium text-gray-900">
        {{ __("Add Conditions", "aio-woodiscount") }}
      </label>

      <div
        v-for="(condition, index) in localConditions"
        :key="condition.id"
        class="mt-1">
        <!-- OR/AND Separator -->
        <div v-if="index > 0" class="mb-2">
          <span class="text-black italic text-sm">
            {{
              conditionsApplies === "any"
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
              class="w-full h-8 border rounded p-2 text-sm"
              @change="updateConditions">
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
                    :value="option.value">
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
              class="w-full h-8 border rounded p-2 text-sm"
              @change="updateConditions">
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
            <el-select-v2
              v-if="isDropdownField(condition.field)"
              v-model="condition.value"
              :options="getDropdownOptions(condition.field)"
              filterable
              multiple
              class="custom-select-v2"
              @change="updateConditions" />

            <input
              v-else
              v-model="condition.value"
              type="text"
              class="w-full h-8 border rounded p-2 text-sm"
              @input="updateConditions" />
          </div>

          <!-- Delete Button -->
          <div class="w-[5%] flex justify-center items-center">
            <el-icon
              @click="removeCondition(condition.id)"
              size="20px"
              class="cursor-pointer text-red-500">
              <Delete />
            </el-icon>
          </div>
        </div>
      </div>

      <!-- Add Condition Button -->
      <button
        @click="addCondition"
        class="bg-blue-500 text-white rounded px-4 py-2 hover:bg-blue-600">
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
