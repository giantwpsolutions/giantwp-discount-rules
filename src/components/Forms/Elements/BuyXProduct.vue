<script setup>
import { reactive, ref, onMounted, watch } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";
import { Delete } from "@element-plus/icons-vue";
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
} from "@/data/generalDataFetch";

// **Props & Emits**
const props = defineProps({
  value: { type: Array, default: () => [] },
  buyXApplies: { type: String, default: "any" },
});

const emit = defineEmits(["update:value", "update:buyXApplies"]);

// **Reactive State**
const buyXProducts = ref([...props.value]);
const buyXApplies = ref(props.buyXApplies);

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
  buyXProducts.value.push({
    id: Date.now(),
    buyProductCount: 0,
    field: "product",
    operator: "",
    value: [],
  });
};

// Remove a Product Selection option
const removeProduct = (id) => {
  buyXProducts.value = buyXProducts.value.filter(
    (product) => product.id !== id
  );
};

const productisPricingField = (field) => ["product_price"].includes(field);

const productisNumberField = (field) => ["product_instock"].includes(field);

// **Emit Updated buy Product **
const updateBuyXProducts = () => {
  emit("update:value", [...buyXProducts.value]);
};

// **Emit Buyx Change**
const updateBuyXApplies = () => {
  emit("update:buyXApplies", buyXApplies.value);
};

watch(
  buyXProducts,
  (newVal) => {
    // console.log("Child Conditions Updated:", newVal);
    emit("update:value", [...newVal]);
  },
  { deep: true } // Single watcher handles all changes
);

watch(buyXApplies, () => {
  updateBuyXApplies();
});

// Single debounced watcher for all changes
const emitUpdates = debounce(() => {
  // console.log(
  //   "Child Conditions (Debounced):",
  //   JSON.parse(JSON.stringify(buyXProducts.value))
  // );
  emit("update:value", [...buyXProducts.value]);
}, 300);

watch(
  [buyXProducts, buyXApplies],
  () => {
    emitUpdates();
  },
  { deep: true }
);
</script>

<template>
  <div class="tw-space-y-4 tw-w-full tw-my-6 tw-border-t tw-border-b tw-py-6">
    <h3 class="tw-text-base tw-text-gray-950">
      <div class="tw-inline-flex tw-items-center tw-space-x-1">
        <span>{{ __("Buy X Product", "giantwp-discount-rules") }}</span>
        <el-tooltip
          class="box-item"
          effect="dark"
          :content="
            __(
              'Which product need to buy to get the rule?',
              'giantwp-discount-rules'
            )
          "
          placement="top"
          popper-class="custom-tooltip">
          <QuestionMarkCircleIcon
            class="tw-w-4 tw-h-4 tw-text-gray-500 tw-hover:text-gray-700 tw-cursor-pointer" />
        </el-tooltip>
      </div>
    </h3>

    <!-- Apply logic radio -->
    <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-mt-6 tw-mb-1">
      <label class="tw-text-sm tw-font-medium tw-text-gray-900">
        {{
          __("Rules apply to products if matches", "giantwp-discount-rules")
        }}
      </label>
      <el-radio-group v-model="buyXApplies" @change="updateBuyXApplies">
        <el-radio-button
          :label="__('Any', 'giantwp-discount-rules')"
          value="any" />
        <el-radio-button
          :label="__('All', 'giantwp-discount-rules')"
          value="all" />
      </el-radio-group>
    </div>

    <!-- Buy Product Condition Rows -->
    <div
      v-for="(buyXProduct, index) in buyXProducts"
      :key="buyXProduct.id"
      class="tw-w-full">
      <div v-if="index > 0" class="mb-2">
        <span class="tw-text-black tw-italic tw-text-sm">
          {{
            buyXApplies === "any"
              ? __("Or", "giantwp-discount-rules")
              : __("And", "giantwp-discount-rules")
          }}
        </span>
      </div>

      <div class="tw-flex tw-flex-wrap tw-gap-2 md:tw-gap-3 tw-tems-start">
        <!-- Quantity (1 column on mobile, 12% on desktop) -->
        <div class="tw-w-full md:tw-w-[12%]">
          <el-input-number
            v-model="buyXProduct.buyProductCount"
            @change="updateBuyXProducts"
            :min="1"
            :max="10"
            controls-position="right"
            class="tw-w-full" />
        </div>

        <!-- Field -->
        <div class="tw-w-full md:tw-w-[20%]">
          <el-select
            v-model="buyXProduct.field"
            clearable
            @change="updateBuyXProducts"
            class="tw-w-full">
            <el-option
              v-for="item in productOption"
              :key="item.value"
              :label="item.label"
              :value="item.value" />
          </el-select>
        </div>

        <!-- Operator -->
        <div class="tw-w-full md:tw-w-[20%]">
          <el-select
            v-if="getProductOperator(buyXProduct.field)?.length"
            v-model="buyXProduct.operator"
            @change="updateBuyXProducts"
            class="tw-w-full">
            <el-option
              v-for="item in getProductOperator(buyXProduct.field)"
              :key="item.value"
              :label="item.label"
              :value="item.value" />
          </el-select>
        </div>

        <!-- Value -->
        <div class="tw-w-full md:tw-w-[30%]">
          <el-select-v2
            v-if="ProductIsDropdown(buyXProduct.field)"
            v-model="buyXProduct.value"
            @change="updateBuyXProducts"
            :options="getProductDropdown(buyXProduct.field)"
            filterable
            multiple
            class="custom-select-v2 tw-w-full" />

          <el-input
            v-else-if="productisPricingField(buyXProduct.field)"
            v-model="buyXProduct.value"
            @change="updateBuyXProducts"
            class="tw-w-full">
            <template #append>
              <span v-html="generalData.currency_symbol || '$'"></span>
            </template>
          </el-input>

          <el-input-number
            v-else-if="productisNumberField(buyXProduct.field)"
            v-model="buyXProduct.value"
            @change="updateBuyXProducts"
            controls-position="right"
            class="tw-w-full" />
        </div>

        <!-- Delete -->
        <div class="tw-w-full sm:tw-w-[10%] tw-flex tw-items-center tw-pt-2">
          <el-icon
            @click="removeProduct(buyXProduct.id)"
            size="20px"
            class="tw-cursor-pointer tw-text-red-500">
            <Delete />
          </el-icon>
        </div>
      </div>
    </div>

    <!-- Add Product Button -->
    <button
      @click="addProduct"
      class="tw-mt-4 tw-bg-blue-500 tw-text-white tw-rounded tw-px-4 tw-py-2 tw-hover:bg-blue-600">
      {{ __("Assign Buy Product", "giantwp-discount-rules") }}
    </button>
  </div>
</template>
