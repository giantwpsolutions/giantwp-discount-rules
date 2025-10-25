<script setup>
import { ref, defineProps, defineEmits, onMounted, watch } from "vue";
import { debounce } from "lodash";
import { Delete } from "@element-plus/icons-vue";
import {
  conditionOptions,
  dropdownOptions,
  cascadeOptions,
  getOperators,
} from "@/data/form-data/conditionsData.js";

import {
  productOptions,
  loadProducts,
  variationOptions,
} from "@/data/productsFetch.js";

import {
  userOptions,
  roleOptions,
  loadUsersAndRoles,
} from "@/data/usersFetch.js";

import {
  categoryOptions,
  tagOptions,
  loadCategoriesAndTags,
} from "@/data/categoriesAndTagsFetch.js";

import {
  paymentGatewayOptions,
  loadPaymentGateways,
} from "@/data/paymentGatewaysFetch.js";

import {
  countriesOptions,
  loadCountriesAndStates,
} from "@/data/shippingFetch.js";

import { generalData, loadGeneralData } from "@/data/generalDataFetch";

// === Props & Emits ===
const props = defineProps({
  value: Array,
  toggle: Boolean,
  conditionsApplies: String,
});

const emit = defineEmits([
  "update:value",
  "update:toggle",
  "update:conditionsApplies",
]);

// === Reactive State ===
const enableConditions = ref(props.toggle);
const localConditions = ref([...props.value]);
const conditionsApplies = ref(props.conditionsApplies);

// === Load Data Sources ===
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
  } catch (err) {
    console.error("Error loading dropdown data:", err);
  }
});

// === Helpers ===
const isNumberField = (f) =>
  ["cart_quantity", "cart_total_weight", "customer_order_count"].includes(f);

const isPricingField = (f) =>
  ["cart_subtotal_price", "cart_item_regular_price"].includes(f);

const isDropdownField = (f) =>
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
  ].includes(f);

// === CRUD ===
const addCondition = (e) => {
  e.preventDefault();
  localConditions.value.push({
    id: Date.now(),
    field: "cart_subtotal_price",
    operator: "greater_than",
    value: "100",
  });
};

const removeCondition = (id) => {
  localConditions.value = localConditions.value.filter((c) => c.id !== id);
};

// === Emit Updates ===
watch(
  localConditions,
  (val) => emit("update:value", [...val]),
  { deep: true }
);

watch(enableConditions, () => emit("update:toggle", enableConditions.value));
watch(conditionsApplies, () => emit("update:conditionsApplies", conditionsApplies.value));

const emitUpdates = debounce(() => {
  emit("update:value", [...localConditions.value]);
}, 300);

watch([localConditions, enableConditions, conditionsApplies], emitUpdates, {
  deep: true,
});
</script>

<template>
  <div class="tw-space-y-4 tw-w-full md:tw-w-5/6">
    <!-- Enable Conditions -->
    <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-my-6">
      <div class="tw-shrink-0">
        <el-switch
          v-model="enableConditions"
          inline-prompt
          :active-text="__('On', 'giantwp-discount-rules')"
          :inactive-text="__('Off', 'giantwp-discount-rules')"
        />
      </div>
      <label class="tw-text-sm tw-font-medium tw-text-gray-900">
        {{ __("Enable Conditions?", "giantwp-discount-rules") }}
      </label>
    </div>

    <!-- Conditions Section -->
    <div v-if="enableConditions" class="tw-space-y-4 tw-w-full">
      <!-- Apply Conditions Mode -->
      <div
        class="tw-flex tw-flex-col sm:tw-flex-row tw-items-start sm:tw-items-center tw-gap-2 tw-mt-6 tw-mb-1"
      >
        <label class="tw-text-sm tw-font-medium tw-text-gray-900">
          {{ __("Apply conditions if matches", "giantwp-discount-rules") }}
        </label>
        <el-radio-group v-model="conditionsApplies">
          <el-radio-button label="any">
            {{ __("Any", "giantwp-discount-rules") }}
          </el-radio-button>
          <el-radio-button label="all">
            {{ __("All", "giantwp-discount-rules") }}
          </el-radio-button>
        </el-radio-group>
      </div>

      <!-- Add Conditions -->
      <label class="tw-block tw-text-sm tw-font-medium tw-text-gray-900">
        {{ __("Add Conditions", "giantwp-discount-rules") }}
      </label>

      <!-- Loop Through Conditions -->
      <div
        v-for="(condition, index) in localConditions"
        :key="condition.id"
        class="tw-mt-1"
      >
        <!-- OR / AND -->
        <div v-if="index > 0" class="tw-mb-2">
          <span class="tw-text-black tw-italic tw-text-sm">
            {{
              conditionsApplies === "any"
                ? __("Or", "giantwp-discount-rules")
                : __("And", "giantwp-discount-rules")
            }}
          </span>
        </div>

        <!-- Single Row -->
        <div
          class="tw-flex tw-items-center tw-gap-2 tw-mb-2 tw-flex-wrap"
        >
          <!-- Field -->
          <div class="tw-w-[25%]">
            <select
              v-model="condition.field"
              class="tw-w-full tw-h-8 tw-border tw-rounded tw-p-2 tw-text-sm"
            >
              <option value="">
                {{ __("Please select", "giantwp-discount-rules") }}
              </option>
              <template v-for="group in conditionOptions" :key="group.label">
                <optgroup
                  :label="group.label"
                  class="tw-font-semibold tw-text-gray-800"
                >
                  <option
                    v-for="option in group.options"
                    :key="option.value"
                    :value="option.value"
                  >
                    {{ option.label }}
                  </option>
                </optgroup>
              </template>
            </select>
          </div>

          <!-- Operator -->
          <div class="tw-w-[20%]">
            <select
              v-model="condition.operator"
              class="tw-w-full tw-h-8 tw-border tw-rounded tw-p-2 tw-text-sm"
            >
              <option
                v-for="op in getOperators(condition.field)"
                :key="op.value"
                :value="op.value"
              >
                {{ op.label }}
              </option>
            </select>
          </div>

          <!-- Value -->
          <div class="tw-w-[45%]">
            <el-select-v2
              v-if="isDropdownField(condition.field)"
              v-model="condition.value"
              :options="dropdownOptions[condition.field]"
              filterable
              multiple
              class="custom-select-v2 tw-w-full"
            />

            <el-input
              v-else-if="isPricingField(condition.field)"
              v-model.number="condition.value"
              placeholder="Please input"
              class="tw-w-full"
            >
              <template #append>
                <span v-html="generalData.currency_symbol || '$'"></span>
              </template>
            </el-input>

            <input
              v-else-if="isNumberField(condition.field)"
              v-model="condition.value"
              type="number"
              placeholder="Enter a number"
              class="tw-w-full tw-h-8 tw-border tw-rounded tw-p-2 tw-text-sm tw-text-gray-700 tw-bg-white tw-border-gray-300 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-blue-500 focus:tw-border-blue-500"
            />
          </div>

          <!-- Delete -->
          <div class="tw-w-[5%] tw-flex tw-justify-center tw-items-center">
            <el-icon
              @click="removeCondition(condition.id)"
              size="20px"
              class="tw-cursor-pointer tw-text-red-500"
            >
              <Delete />
            </el-icon>
          </div>
        </div>
      </div>

      <!-- Add Button -->
      <button
        @click="addCondition"
        class="tw-bg-blue-500 tw-text-white tw-rounded tw-px-4 tw-py-2 hover:tw-bg-blue-600"
      >
        {{ __("Add Condition", "giantwp-discount-rules") }}
      </button>
    </div>
  </div>
</template>

<style scoped>
.custom-select-v2 .el-select__wrapper {
  padding: 0 !important;
}
</style>
