<script setup>
import { reactive, ref, onMounted, watch } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";
import { Delete, CirclePlus } from "@element-plus/icons-vue";
import { debounce } from "lodash";
import {
  bogoBuyProductOptions,
  bogoBuyProductOperator,
  getBogoBuyProductOperator,
  bogoSameProductBuyIsDropdown,
  getBogoSameProductDropdown,
  bogoSameProductDropdownOptions,
} from "@/data/form-data/bogoProductData.js";

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
  bogoApplies: { type: String, default: "any" },
});

const emit = defineEmits(["update:value", "update:bogoApplies"]);

// **Reactive State**
const buyProductsBogoSame = ref([...props.value]);
const bogoApplies = ref(props.bogoApplies);

// Fetch Api

onMounted(async () => {
  try {
    // Load products and variations
    await loadProducts();
    bogoSameProductDropdownOptions.product = productOptions.value; // Products
    bogoSameProductDropdownOptions.product_variation = variationOptions.value; // Variations

    //Load Category and Tags
    await loadCategoriesAndTags();
    bogoSameProductDropdownOptions.product_category = categoryOptions.value;
    bogoSameProductDropdownOptions.product_tags = tagOptions.value;

    //load general Data
    await loadGeneralData();
  } catch (error) {
    console.error("Error loading dropdown options:", error);
  }
});

// Add a New Product Selection option
const addProductBogoSame = (event) => {
  event.preventDefault();
  buyProductsBogoSame.value.push({
    id: Date.now(),
    field: "product",
    operator: "",
    value: [],
  });
};

// Remove a Product Selection option
const removeProductBogoSame = (id) => {
  const index = (buyProductsBogoSame.value = buyProductsBogoSame.value.filter(
    (product) => product.id !== id
  ));
  if (index > -1) buyProductsBogoSame.splice(index, 1);
};

const bogoSameProductisPricingField = (field) =>
  ["product_price"].includes(field);

const bogoSameProductisNumberField = (field) =>
  ["product_instock"].includes(field);

// **Emit Updated buy Product **
const updateBuyProductsBogoSame = () => {
  emit("update:value", [...buyProductsBogoSame.value]);
};

// **Emit bogoApplies Change**
const updateBogoApplies = () => {
  emit("update:bogoApplies", bogoApplies.value);
};

watch(
  buyProductsBogoSame,
  (newVal) => {
    // console.log("Child Conditions Updated:", newVal);
    emit("update:value", [...newVal]);
  },
  { deep: true } // Single watcher handles all changes
);

watch(bogoApplies, () => {
  updateBogoApplies();
});

// Single debounced watcher for all changes
const emitUpdates = debounce(() => {
  // console.log(
  //   "Child Conditions (Debounced):",
  //   JSON.parse(JSON.stringify(buyProductsBogoSame.value))
  // );
  emit("update:value", [...buyProductsBogoSame.value]);
}, 300);

watch(
  [buyProductsBogoSame, bogoApplies],
  () => {
    emitUpdates();
  },
  { deep: true }
);
</script>

<template>
  <div class="tw-space-y-4 tw-max-w-full tw-my-6 tw-border-t tw-border-b tw-py-6">
    <h3 class="tw-text-base tw-text-gray-950">
      <div class="tw-inline-flex tw-items-center tw-space-x-1">
        <span>{{ __("Buy Product", "giantwp-discount-rules") }}</span>
        <el-tooltip
          class="box-item"
          effect="dark"
          :content="
            __(
              'Which product will receive the BOGO rule?',
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

    <!-- Rule Applies -->
    <div class="tw-flex tw-items-center tw-gap-2 tw-mt-6 tw-mb-1">
      <label class="tw-text-sm tw-font-medium tw-text-gray-900 tw-flex tw-items-center tw-gap-1">
        {{
          __("Rules apply to products if matches", "giantwp-discount-rules")
        }}
      </label>
      <el-radio-group v-model="bogoApplies" @change="updateBogoApplies">
        <el-radio-button
          :label="__('Any', 'giantwp-discount-rules')"
          value="any" />
        <el-radio-button
          :label="__('All', 'giantwp-discount-rules')"
          value="all" />
      </el-radio-group>
    </div>

    <!-- Fields -->
    <div
      v-for="(buyProductBogoSame, index) in buyProductsBogoSame"
      :key="buyProductBogoSame.id">
      <div v-if="index > 0" class="tw-mb-2">
        <span class="tw-text-black tw-italic tw-text-sm">
          {{
            bogoApplies === "any"
              ? __("Or", "giantwp-discount-rules")
              : __("And", "giantwp-discount-rules")
          }}
        </span>
      </div>

      <div class="tw-grid tw-grid-cols-1 md:tw-grid-cols-4 tw-gap-3">
        <!-- Field 1 -->
        <div>
          <el-select
            v-model="buyProductBogoSame.field"
            clearable
            @change="updateBuyProductsBogoSame"
            class="tw-w-full">
            <el-option
              v-for="item in bogoBuyProductOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value" />
          </el-select>
        </div>

        <!-- Field 2 -->
        <div>
          <el-select
            v-if="getBogoBuyProductOperator(buyProductBogoSame.field)?.length"
            v-model="buyProductBogoSame.operator"
            @change="updateBuyProductsBogoSame"
            class="tw-w-full">
            <el-option
              v-for="item in getBogoBuyProductOperator(
                buyProductBogoSame.field
              )"
              :key="item.value"
              :label="item.label"
              :value="item.value" />
          </el-select>
        </div>

        <!-- Field 3 -->
        <div>
          <el-select-v2
            v-if="bogoSameProductBuyIsDropdown(buyProductBogoSame.field)"
            v-model="buyProductBogoSame.value"
            @change="updateBuyProductsBogoSame"
            :options="getBogoSameProductDropdown(buyProductBogoSame.field)"
            filterable
            multiple
            :loading="isLoadingProducts"
            class="custom-select-v2 tw-w-full"
            :placeholder="__('Select', 'giantwp-discount-rules')" />

          <el-input
            v-else-if="bogoSameProductisPricingField(buyProductBogoSame.field)"
            v-model="buyProductBogoSame.value"
            @change="updateBuyProductsBogoSame"
            class="tw-w-full">
            <template #append>
              <span v-html="generalData.currency_symbol || '$'"></span>
            </template>
          </el-input>

          <el-input-number
            v-else-if="bogoSameProductisNumberField(buyProductBogoSame.field)"
            v-model="buyProductBogoSame.value"
            @change="updateBuyProductsBogoSame"
            controls-position="right"
            class="tw-w-full" />
        </div>

        <!-- Field 4 -->
        <div class="flex items-center">
          <el-icon
            @click="removeProductBogoSame(buyProductBogoSame.id)"
            size="20px"
            color="red"
            class="tw-cursor-pointer tw-text-red-500">
            <Delete />
          </el-icon>
        </div>
      </div>
    </div>

    <!-- Add Button -->
    <button
      @click="addProductBogoSame"
      class="tw-bg-blue-500 tw-text-white tw-rounded tw-px-4 tw-py-2 tw-hover:bg-blue-600 tw-mt-4">
      {{ __("Assign Product", "giantwp-discount-rules") }}
    </button>
  </div>
</template>
