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
} from "@/data/generalDataFetch";

// **Props & Emits**
const props = defineProps({
  value: { type: Array, default: () => [] },
  getYApplies: { type: String, default: "any" },
  isrepeat: { type: Boolean, default: true },
  freeorDiscount: { type: String, default: "free_product" },
  discountTypeBxgy: { type: String, default: "fixed" },
  discountValue: { type: Number, default: null },
  maxValue: { type: Number, default: null },
});

const emit = defineEmits([
  "update:value",
  "update:getYApplies",
  "update:isrepeat",
  "update:freeorDiscount",
  "update:discountTypeBxgy",
  "update:discountValue",
  "update:maxValue",
]);

// **Reactive State**
const getYProducts = ref([...props.value]);
const getYApplies = ref(props.getYApplies);
const isrepeat = ref(props.isrepeat);
const freeorDiscount = ref(props.freeorDiscount);
const discountTypeBxgy = ref(props.discountTypeBxgy);
const discountValue = ref(props.discountValue);
const maxValue = ref(props.maxValue);

// Watch freeOrDiscount and reset discount-related fields if switched to 'freeproduct'
watch(
  () => freeorDiscount,
  (newVal, oldVal) => {
    if (newVal === "free_product" && oldVal === "discount_product") {
      //Reset discount-related fields
      discountTypeBxgy = "fixed";
      discountValue = null;
      maxValue = null;
    }
  }
);

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
  getYProducts.value.push({
    id: Date.now(),
    getProductCount: 1,
    field: "product",
    operator: "",
    value: [],
  });
};

// Remove a Product Selection option
const removeProduct = (id) => {
  getYProducts.value = getYProducts.value.filter(
    (product) => product.id !== id
  );
};

const productisPricingField = (field) => ["product_price"].includes(field);

const productisNumberField = (field) => ["product_instock"].includes(field);

// **Emit Updated Get Product **
const updateGetYProducts = () => {
  emit("update:value", [...getYProducts.value]);
};
// **Emit is Repeat **
const updateIsrepeat = () => {
  emit("update:isrepeat", isrepeat.value);
};
// **Emit Updated Free Or Discount **
const updateFreeorDiscount = () => {
  emit("update:freeorDiscount", freeorDiscount.value);
};
// **Emit Updated Discount Type **
const updateDiscountTypeBxgy = () => {
  emit("update:discountTypeBxgy", discountTypeBxgy.value);
};
// **Emit Updated Discount Value **
const updateDiscountValue = () => {
  emit("update:discountValue", discountValue.value);
};
// **Emit Updated Max Value **
const updateMaxValue = () => {
  emit("update:maxValue", maxValue.value);
};

// **Emit Buyx Change**
const updateGetYApplies = () => {
  emit("update:getYApplies", getYApplies.value);
};

watch(
  getYProducts,
  (newVal) => {
    // console.log("Child Conditions Updated:", newVal);
    emit("update:value", [...newVal]);
  },
  { deep: true } // Single watcher handles all changes
);

watch(getYApplies, () => {
  updateGetYApplies();
});
watch(isrepeat, () => {
  updateIsrepeat();
});
watch(freeorDiscount, () => {
  updateFreeorDiscount();
});
watch(discountTypeBxgy, () => {
  updateDiscountTypeBxgy();
});
watch(discountValue, () => {
  updateDiscountValue();
});
watch(maxValue, () => {
  updateMaxValue();
});

// Single debounced watcher for all changes
const emitUpdates = debounce(() => {
  // console.log(
  //   "Child Get Y (Debounced):",
  //   JSON.parse(JSON.stringify(getYProducts.value))
  // );
  emit("update:value", [...getYProducts.value]);
}, 300);

watch(
  [getYProducts, getYApplies],
  () => {
    emitUpdates();
  },
  { deep: true }
);
</script>

<template>
  <div class="space-y-4 max-w-full mt-2 mb-6 border-b py-6">
    <h3 class="text-base text-gray-950">
      <div class="inline-flex items-center space-x-1">
        <span>{{ __("Get Y Product", "giantwp-discount-rules") }}</span>
        <el-tooltip
          class="box-item"
          effect="dark"
          :content="
            __('Which product will get the rule?', 'giantwp-discount-rules')
          "
          placement="top"
          popper-class="custom-tooltip">
          <QuestionMarkCircleIcon
            class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
        </el-tooltip>
      </div>
    </h3>

    <div class="flex flex-wrap items-center gap-2 mt-6 mb-1">
      <el-switch
        v-model="isrepeat"
        @change="updateIsrepeat"
        inline-prompt
        :active-text="__('On', 'giantwp-discount-rules')"
        :inactive-text="__('Off', 'giantwp-discount-rules')" />
      <label class="text-sm font-medium text-gray-900 flex items-center gap-1">
        {{ __("Is Repeat?", "giantwp-discount-rules") }}
        <el-tooltip
          class="box-item"
          effect="dark"
          :content="
            __(
              'Discount will apply once or repeat after each quantities matching',
              'giantwp-discount-rules'
            )
          "
          placement="top"
          popper-class="custom-tooltip">
          <QuestionMarkCircleIcon
            class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
        </el-tooltip>
      </label>
    </div>

    <!-- Get Item: Free or Discount -->
    <div class="flex flex-wrap items-center gap-2 mt-6 mb-1">
      <label class="text-sm font-medium text-gray-900 flex items-center gap-1">
        {{ __("Get Item", "giantwp-discount-rules") }}
      </label>
      <el-radio-group v-model="freeorDiscount" @change="updateFreeorDiscount">
        <el-radio-button
          :label="__('Free', 'giantwp-discount-rules')"
          value="free_product" />
        <el-radio-button
          :label="__('Discount', 'giantwp-discount-rules')"
          value="discount_product" />
      </el-radio-group>
    </div>

    <!-- Discount Config -->
    <div v-if="freeorDiscount === 'discount_product'" class="w-full mt-5">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Pricing Type -->
        <div>
          <label class="block text-sm font-medium pb-2 text-gray-900">
            {{ __("Pricing Type", "giantwp-discount-rules") }}
          </label>
          <el-select
            v-model="discountTypeBxgy"
            @change="updateDiscountTypeBxgy"
            size="default"
            popper-class="custom-dropdown">
            <el-option
              :value="'fixed'"
              :label="__('Fixed Discount', 'giantwp-discount-rules')" />
            <el-option
              :value="'percentage'"
              :label="__('Percentage Discount', 'giantwp-discount-rules')" />
          </el-select>
        </div>

        <!-- Pricing Value -->
        <div>
          <label class="block text-sm font-medium pb-2 text-gray-900">
            {{ __("Pricing Value", "giantwp-discount-rules") }}
          </label>
          <el-input v-model.number="discountValue" placeholder="Please input">
            <template #append>
              <span
                v-html="
                  discountTypeBxgy === 'percentage'
                    ? '%'
                    : generalData.currency_symbol || '$'
                "></span>
            </template>
          </el-input>
        </div>

        <!-- Max Value -->
        <div>
          <label class="block text-sm font-medium pb-2 text-gray-900">
            <div class="flex items-center space-x-1">
              <span>{{ __("Maximum Value", "giantwp-discount-rules") }}</span>
              <el-tooltip
                effect="dark"
                :content="
                  __(
                    'The maximum value that can be applied',
                    'giantwp-discount-rules'
                  )
                "
                placement="top"
                popper-class="custom-tooltip">
                <QuestionMarkCircleIcon
                  class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
              </el-tooltip>
            </div>
          </label>
          <el-input
            v-model.number="maxValue"
            placeholder="Please input"
            :disabled="discountTypeBxgy === 'fixed'">
            <template #append>
              <span v-html="generalData.currency_symbol || '$'"></span>
            </template>
          </el-input>
        </div>
      </div>
    </div>

    <!-- Match condition type -->
    <div class="flex items-center gap-2 mt-6 mb-1">
      <label class="text-sm font-medium text-gray-900 flex items-center gap-1">
        {{ __("Rules apply to products if matches", "giantwp-discount-rules") }}
      </label>
      <el-radio-group v-model="getYApplies" @change="updateGetYApplies">
        <el-radio-button
          :label="__('Any', 'giantwp-discount-rules')"
          value="any" />
        <el-radio-button
          :label="__('All', 'giantwp-discount-rules')"
          value="all" />
      </el-radio-group>
    </div>

    <!-- All Fields -->
    <div
      v-for="(getYProduct, index) in getYProducts"
      :key="getYProduct.id"
      class="w-full">
      <div v-if="index > 0" class="mb-2">
        <span class="text-black italic text-sm">
          {{
            getYApplies === "any"
              ? __("Or", "giantwp-discount-rules")
              : __("And", "giantwp-discount-rules")
          }}
        </span>
      </div>
      <div class="flex flex-wrap gap-2">
        <!-- Quantity -->
        <div class="w-full sm:w-[12%]">
          <el-input-number
            v-model="getYProduct.getProductCount"
            placeholder="Qty"
            controls-position="right"
            :max="100"
            class="w-full" />
        </div>

        <!-- Field -->
        <div class="w-full sm:w-[20%]">
          <el-select
            v-model="getYProduct.field"
            clearable
            @change="updateGetYProducts">
            <el-option
              v-for="item in productOption"
              :key="item.value"
              :label="item.label"
              :value="item.value" />
          </el-select>
        </div>

        <!-- Operator -->
        <div class="w-full sm:w-[20%]">
          <el-select
            v-if="getProductOperator(getYProduct.field)?.length"
            v-model="getYProduct.operator"
            @change="updateGetYProducts">
            <el-option
              v-for="item in getProductOperator(getYProduct.field)"
              :key="item.value"
              :label="item.label"
              :value="item.value" />
          </el-select>
        </div>

        <!-- Value -->
        <div class="w-full sm:w-[30%]">
          <el-select-v2
            v-if="ProductIsDropdown(getYProduct.field)"
            v-model="getYProduct.value"
            :options="getProductDropdown(getYProduct.field)"
            filterable
            multiple
            :loading="isLoadingProducts"
            class="custom-select-v2" />

          <el-input
            v-else-if="productisPricingField(getYProduct.field)"
            v-model="getYProduct.value">
            <template #append>
              <span v-html="generalData.currency_symbol || '$'"></span>
            </template>
          </el-input>

          <el-input-number
            v-else-if="productisNumberField(getYProduct.field)"
            v-model="getYProduct.value"
            controls-position="right"
            class="w-full" />
        </div>

        <!-- Delete -->
        <div class="w-full sm:w-[10%] flex items-center">
          <el-icon
            @click="removeProduct(getYProduct.id)"
            size="20px"
            class="cursor-pointer text-red-500">
            <Delete />
          </el-icon>
        </div>
      </div>
    </div>

    <!-- Add Button -->
    <button
      @click="addProduct"
      class="bg-blue-500 text-white rounded px-4 py-2 hover:bg-blue-600">
      {{ __("Assign Get Product", "giantwp-discount-rules") }}
    </button>
  </div>
</template>
