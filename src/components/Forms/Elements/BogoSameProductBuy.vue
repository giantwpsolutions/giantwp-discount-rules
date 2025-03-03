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
    console.log("Child Conditions Updated:", newVal);
    emit("update:value", [...newVal]);
  },
  { deep: true } // Single watcher handles all changes
);

watch(bogoApplies, () => {
  updateBogoApplies();
});

// Single debounced watcher for all changes
const emitUpdates = debounce(() => {
  console.log(
    "Child Conditions (Debounced):",
    JSON.parse(JSON.stringify(buyProductsBogoSame.value))
  );
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
  <div class="space-y-4 max-w-full my-6 border-t border-b py-6">
    <h3 class="text-base text-gray-950">
      <div class="inline-flex items-center space-x-1">
        <!-- Heading Text -->
        <span>{{ __("Buy Product", "aio-woodiscount") }}</span>
        <!-- Tooltip Icon -->
        <el-tooltip
          class="box-item"
          effect="dark"
          :content="
            __('Which product will receive the BOGO rule?', 'aio-woodiscount')
          "
          placement="top"
          popper-class="custom-tooltip">
          <QuestionMarkCircleIcon
            class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
        </el-tooltip>
      </div>
    </h3>

    <div class="flex items-center gap-2 mt-6 mb-1">
      <label class="text-sm font-medium text-gray-900 flex items-center gap-1">
        {{ __("Rules apply to products if matches", "aio-woodiscount") }}
      </label>
      <div class="group relative">
        <el-radio-group v-model="bogoApplies" @change="updateBogoApplies">
          <el-radio-button :label="__('Any', 'aio-woodiscount')" value="any" />
          <el-radio-button :label="__('All', 'aio-woodiscount')" value="all" />
        </el-radio-group>
      </div>
    </div>

    <!-- All Fields  -->
    <div
      v-for="(buyProductBogoSame, index) in buyProductsBogoSame"
      :key="buyProductBogoSame.id">
      <!-- Add /Or text -->
      <div v-if="index > 0" class="mb-2">
        <span class="text-black italic text-sm">
          {{
            bogoApplies === "anyproduct"
              ? __("Or", "aio-woodiscount")
              : __("And", "aio-woodiscount")
          }}
        </span>
      </div>
      <div class="flex flex-wrap gap-2">
        <!-- Field 1: 30% width -->
        <div class="w-[25%]">
          <el-select
            v-model="buyProductBogoSame.field"
            clearable
            @change="updateBuyProductsBogoSame"
            style="">
            <el-option
              v-for="item in bogoBuyProductOptions"
              :key="item.value"
              :label="item.label"
              :value="item.value" />
          </el-select>
        </div>

        <!-- Field 2: 25% width -->
        <div class="w-[20%]">
          <el-select
            v-if="getBogoBuyProductOperator(buyProductBogoSame.field)?.length"
            v-model="buyProductBogoSame.operator"
            @change="updateBuyProductsBogoSame"
            style="">
            <el-option
              v-for="item in getBogoBuyProductOperator(
                buyProductBogoSame.field
              )"
              :key="item.value"
              :label="item.label"
              :value="item.value" />
          </el-select>
        </div>

        <!-- Field 3: 35% width -->
        <div class="w-[30%]">
          <el-select-v2
            v-if="bogoSameProductBuyIsDropdown(buyProductBogoSame.field)"
            v-model="buyProductBogoSame.value"
            @change="updateBuyProductsBogoSame"
            :options="getBogoSameProductDropdown(buyProductBogoSame.field)"
            :placeholder="__('Select', 'aio-woodiscount')"
            filterable
            multiple
            :loading="isLoadingProducts"
            class="custom-select-v2" />

          <el-input
            v-else-if="bogoSameProductisPricingField(buyProductBogoSame.field)"
            v-model="buyProductBogoSame.value"
            @change="updateBuyProductsBogoSame">
            <template #append
              ><span v-html="generalData.currency_symbol || '$'"></span
            ></template>
          </el-input>

          <el-input-number
            v-else-if="bogoSameProductisNumberField(buyProductBogoSame.field)"
            v-model="buyProductBogoSame.value"
            @change="updateBuyProductsBogoSame"
            class="mx-4"
            controls-position="right"
            style="width: -webkit-fill-available" />
        </div>

        <!-- Field 4: 10% width with icons -->
        <div class="w-[20%] flex items-center gap-4 border-gray-300 rounded">
          <el-icon
            @click="removeProductBogoSame(buyProductBogoSame.id)"
            size="20px"
            color="red"
            class="cursor-pointer text-red-500">
            <Delete />
          </el-icon>
        </div>
      </div>
    </div>
    <!-- Add Product Assign Button Button -->
    <button
      @click="addProductBogoSame"
      class="bg-blue-500 text-white rounded px-4 py-2 hover:bg-blue-600">
      {{ __("Assign Product", "aio-woodiscount") }}
    </button>
  </div>
</template>
