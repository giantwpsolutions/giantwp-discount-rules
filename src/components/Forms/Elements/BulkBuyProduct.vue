<script setup>
import { reactive, ref, onMounted, watch } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";
import { Delete, CirclePlus } from "@element-plus/icons-vue";
import { debounce } from "lodash";
import {
  productOption,
  productOperator,
  getProductOperator,
  ProductIsDropdown,
  getProductDropdown,
  productDropdownOptions,
} from "@/data/form-data/buyXGetYProductData.js";

import {
  productOptions,
  isLoadingProducts,
  productError,
  loadProducts,
  variationOptions,
} from "@/data/productsFetch.js";

import {
  categoryOptions,
  tagOptions,
  isLoadingCategoriesTags,
  loadCategoriesAndTags,
} from "@/data/categoriesAndTagsFetch.js";

import {
  generalData,
  isLoadingGeneralData,
  generalDataError,
  loadGeneralData,
} from "@/data/GeneralDataFetch.js";

// **Props & Emits**
const props = defineProps({
  value: { type: Array, default: () => [] },
  getApplies: { type: String, default: "any" },
});

const emit = defineEmits(["update:value", "update:getApplies"]);

// **Reactive State**
const buyProducts = ref([...props.value]);
const getApplies = ref(props.getApplies);

// Fetch Api

onMounted(async () => {
  try {
    // Load products and variations
    await loadProducts();
    productDropdownOptions.product = productOptions.value; // Products
    productDropdownOptions.product_variation = variationOptions.value; // Variations

    //Load Category and Tags
    await loadCategoriesAndTags();
    productDropdownOptions.product_category = categoryOptions.value;
    productDropdownOptions.product_tags = tagOptions.value;

    //load general Data
    await loadGeneralData();
  } catch (error) {
    console.error("Error loading dropdown options:", error);
  }
});

// Add a New Product Selection option
const addProduct = (event) => {
  event.preventDefault();
  buyProducts.value.push({
    id: Date.now(),
    field: "product",
    operator: "",
    value: [],
  });
};

// Remove a Product Selection option
const removeProduct = (id) => {
  buyProducts.value = buyProducts.value.filter((product) => product.id !== id);
};

const productisPricingField = (field) => ["product_price"].includes(field);

const productisNumberField = (field) => ["product_instock"].includes(field);

// **Emit Updated Get Product **
const updateBuyProducts = () => {
  emit("update:value", [...buyProducts.value]);
};

// **Emit Buyx Change**
const updateGetApplies = () => {
  emit("update:getApplies", getApplies.value);
};

watch(
  buyProducts,
  (newVal) => {
    // console.log("Child Conditions Updated:", newVal);
    emit("update:value", [...newVal]);
  },
  { deep: true } // Single watcher handles all changes
);

watch(getApplies, () => {
  updateGetApplies();
});

// Single debounced watcher for all changes
const emitUpdates = debounce(() => {
  // console.log(
  //   "Child Get Y (Debounced):",
  //   JSON.parse(JSON.stringify(buyProducts.value))
  // );
  emit("update:value", [...buyProducts.value]);
}, 300);

watch(
  [buyProducts, getApplies],
  () => {
    emitUpdates();
  },
  { deep: true }
);
</script>

<template>
  <div class="tw-mt-2 tw-mb-6 tw-border-b tw-py-6">
    <!-- Header -->
    <h3 class="tw-text-base tw-font-semibold tw-text-gray-900 tw-mb-4">
      <span class="tw-inline-flex tw-items-center tw-gap-1">
        {{ __("Select Product", "giantwp-discount-rules") }}
        <el-tooltip
          effect="dark"
          :content="__('Which product will get the bulk rule?', 'giantwp-discount-rules')"
          placement="top"
          popper-class="custom-tooltip">
          <QuestionMarkCircleIcon class="tw-w-4 tw-h-4 tw-text-gray-400 tw-cursor-pointer" />
        </el-tooltip>
      </span>
    </h3>

    <!-- Match Condition -->
    <div class="tw-flex tw-items-center tw-gap-3 tw-mb-6">
      <label class="tw-text-sm tw-font-medium tw-text-gray-700">
        {{ __("Rules apply to products if matches", "giantwp-discount-rules") }}
      </label>
      <el-radio-group v-model="getApplies" @change="updateGetApplies">
        <el-radio-button :label="__('Any', 'giantwp-discount-rules')" value="any" />
        <el-radio-button :label="__('All', 'giantwp-discount-rules')" value="all" />
      </el-radio-group>
    </div>

    <!-- Table -->
    <div class="tw-w-full tw-overflow-x-auto">
      <div class="tw-min-w-[500px]">
        <!-- Header Row -->
        <div class="tw-grid tw-gap-3 tw-mb-2 tw-px-1" style="grid-template-columns: 2fr 1.5fr 3fr 40px;">
          <span class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wide">
            {{ __("Type", "giantwp-discount-rules") }}
          </span>
          <span class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wide">
            {{ __("Condition", "giantwp-discount-rules") }}
          </span>
          <span class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wide">
            {{ __("Value", "giantwp-discount-rules") }}
          </span>
          <span></span>
        </div>

        <!-- Data Rows -->
        <template v-for="(buyProduct, index) in buyProducts" :key="buyProduct.id">
          <!-- Or / And separator -->
          <div v-if="index > 0" class="tw-flex tw-items-center tw-gap-2 tw-my-1 tw-px-1">
            <span class="tw-text-xs tw-font-semibold tw-text-blue-500 tw-uppercase tw-bg-blue-50 tw-px-2 tw-py-0.5 tw-rounded">
              {{ getApplies === "any" ? __("Or", "giantwp-discount-rules") : __("And", "giantwp-discount-rules") }}
            </span>
            <div class="tw-flex-1 tw-border-t tw-border-dashed tw-border-gray-200"></div>
          </div>

          <div
            class="product-row tw-grid tw-gap-3 tw-items-center tw-py-2 tw-px-1 tw-rounded-lg"
            :class="index % 2 === 0 ? 'tw-bg-gray-50' : 'tw-bg-white'"
            style="grid-template-columns: 2fr 1.5fr 3fr 40px;">
            <!-- Type -->
            <el-select
              v-model="buyProduct.field"
              clearable
              size="large"
              @change="updateBuyProducts"
              class="tw-w-full">
              <el-option
                v-for="item in productOption"
                :key="item.value"
                :label="item.label"
                :value="item.value" />
            </el-select>

            <!-- Operator -->
            <el-select
              v-if="getProductOperator(buyProduct.field)?.length"
              v-model="buyProduct.operator"
              size="large"
              @change="updateBuyProducts"
              class="tw-w-full">
              <el-option
                v-for="item in getProductOperator(buyProduct.field)"
                :key="item.value"
                :label="item.label"
                :value="item.value" />
            </el-select>
            <div v-else></div>

            <!-- Value -->
            <el-select-v2
              v-if="ProductIsDropdown(buyProduct.field)"
              v-model="buyProduct.value"
              size="large"
              @change="updateBuyProducts"
              :options="getProductDropdown(buyProduct.field)"
              :placeholder="__('Select', 'giantwp-discount-rules')"
              filterable
              multiple
              :loading="isLoadingProducts"
              class="custom-select-v2 tw-w-full" />

            <el-input
              v-else-if="productisPricingField(buyProduct.field)"
              v-model="buyProduct.value"
              size="large"
              @change="updateBuyProducts"
              class="tw-w-full">
              <template #append>
                <span v-html="generalData.currency_symbol || '$'"></span>
              </template>
            </el-input>

            <el-input-number
              v-else-if="productisNumberField(buyProduct.field)"
              v-model="buyProduct.value"
              size="large"
              @change="updateBuyProducts"
              class="tw-w-full"
              controls-position="right" />

            <div v-else></div>

            <!-- Delete -->
            <button
              type="button"
              @click="removeProduct(buyProduct.id)"
              class="tw-flex tw-items-center tw-justify-center tw-w-8 tw-h-8 tw-rounded tw-text-red-400 hover:tw-text-red-600 hover:tw-bg-red-50 tw-transition-colors">
              <el-icon size="16"><Delete /></el-icon>
            </button>
          </div>
        </template>
      </div>
    </div>

    <!-- Add Button -->
    <button
      @click="addProduct"
      class="tw-mt-4 tw-inline-flex tw-items-center tw-gap-2 tw-bg-brand-500 hover:tw-bg-brand-600 tw-text-white tw-text-sm tw-font-medium tw-rounded tw-px-4 tw-py-2 tw-transition-colors">
      <el-icon size="14"><CirclePlus /></el-icon>
      {{ __("Assign Bulk Product", "giantwp-discount-rules") }}
    </button>
  </div>
</template>

<style scoped>
:deep(.product-row .el-select__wrapper),
:deep(.product-row .el-select-v2__wrapper) {
  height: 40px !important;
  overflow: hidden;
}
</style>
