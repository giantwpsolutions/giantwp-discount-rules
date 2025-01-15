<script setup>
import { reactive, ref, onMounted } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";
import { Delete, CirclePlus } from "@element-plus/icons-vue";
import {
  bogoBuyProductOptions,
  bogoBuyProductOperator,
  getBogoBuyProductOperator,
  bogoSameProductBuyIsDropdown,
  getBogoSameProductDropdown,
  bogoSameProductDropdownOptions,
} from "@/data/bogoProductData.js";

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

onMounted(async () => {
  try {
    // Load products and variations
    await loadProducts();
    bogoSameProductDropdownOptions.bogo_product = productOptions.value; // Products
    bogoSameProductDropdownOptions.bogo_product_variation =
      variationOptions.value; // Variations

    //Load Category and Tags
    await loadCategoriesAndTags();
    bogoSameProductDropdownOptions.bogo_product_category =
      categoryOptions.value;
    bogoSameProductDropdownOptions.bogo_product_tags = tagOptions.value;

    //load general Data
    await loadGeneralData();
  } catch (error) {
    console.error("Error loading dropdown options:", error);
  }
});

const productsBogoAppliesto = ref("anyproduct");

const buyProductsBogoSame = reactive([
  {
    id: Date.now(),
    field: "bogo_all_products",
    operator: "",
    value: "",
  },
]);

// Add a New Product Selection option
const addProductBogoSame = () => {
  buyProductsBogoSame.push({
    id: Date.now(),
    field: "bogo_all_products",
    operator: "",
    value: "",
  });
};

// Remove a Product Selection option
const removeProductBogoSame = (id) => {
  const index = buyProductsBogoSame.findIndex(
    (productBS) => productBS.id === id
  );
  if (index > -1) buyProductsBogoSame.splice(index, 1);
};

const bogoSameProductisPricingField = (field) =>
  ["bogo_product_price"].includes(field);

const bogoSameProductisNumberField = (field) =>
  ["bogo_product_instock"].includes(field);
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
        <el-radio-group v-model="productsBogoAppliesto">
          <el-radio-button
            :label="__('Any', 'aio-woodiscount')"
            value="anyproduct" />
          <el-radio-button
            :label="__('All', 'aio-woodiscount')"
            value="allproduct" />
        </el-radio-group>
      </div>
    </div>

    <!-- checking  -->
    <div
      v-for="(buyProductBogoSame, index) in buyProductsBogoSame"
      :key="buyProductBogoSame.id">
      <!-- Add /Or text -->
      <div v-if="index > 0" class="mb-2">
        <span class="text-black italic text-sm">
          {{
            productsBogoAppliesto === "anyproduct"
              ? __("Or", "aio-woodiscount")
              : __("And", "aio-woodiscount")
          }}
        </span>
      </div>
      <div class="flex flex-wrap gap-2">
        <!-- Field 1: 30% width -->
        <div class="w-[25%]">
          <el-select v-model="buyProductBogoSame.field" clearable style="">
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
            :options="getBogoSameProductDropdown(buyProductBogoSame.field)"
            :placeholder="__('Select', 'aio-woodiscount')"
            filterable
            multiple
            :loading="isLoadingProducts"
            class="custom-select-v2" />

          <el-input
            v-else-if="bogoSameProductisPricingField(buyProductBogoSame.field)"
            v-model="buyProductBogoSame.value">
            <template #append
              ><span v-html="generalData.currency_symbol || '$'"></span
            ></template>
          </el-input>

          <el-input-number
            v-else-if="bogoSameProductisNumberField(buyProductBogoSame.field)"
            v-model="buyProductBogoSame.value"
            class="mx-4"
            controls-position="right"
            style="width: -webkit-fill-available"
            @change="handleChange" />
        </div>

        <!-- Field 4: 10% width with icons -->
        <div class="w-[20%] flex items-center gap-4 border-gray-300 rounded">
          <el-icon
            @click="addProductBogoSame"
            size="20px"
            color="#409EFF"
            class="cursor-pointer text-blue-800">
            <CirclePlus />
          </el-icon>
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
  </div>
</template>
