<script setup>
import { reactive, defineProps, defineEmits, onMounted, watch } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";
import { generalData, loadGeneralData } from "@/data/generalDataFetch";

onMounted(() => {
  loadGeneralData();
});

// Props & Emits
const props = defineProps({
  buyProductCount: { type: Number, default: 1 },
  getProductCount: { type: Number, default: 1 },
  freeOrDiscount: { type: String, default: "freeproduct" },
  isRepeat: { type: Boolean, default: true },
  discounttypeBogo: { type: String, default: "fixed" },
  discountValue: { type: Number, default: null },
  maxValue: { type: Number, default: null },
});

// Define Emits
const emit = defineEmits([
  "update:buyProductCount",
  "update:getProductCount",
  "update:freeOrDiscount",
  "update:isRepeat",
  "update:discounttypeBogo",
  "update:discountValue",
  "update:maxValue",
]);

// Reactive Local State
const localState = reactive({
  buyProductCount: props.buyProductCount,
  getProductCount: props.getProductCount,
  freeOrDiscount: props.freeOrDiscount,
  isRepeat: props.isRepeat,
  discounttypeBogo: props.discounttypeBogo,
  discountValue: props.discountValue,
  maxValue: props.maxValue,
});

// Watch freeOrDiscount and reset discount-related fields if switched to 'freeproduct'
watch(
  () => localState.freeOrDiscount,
  (newVal, oldVal) => {
    if (newVal === "freeproduct" && oldVal === "discount_product") {
      //Reset discount-related fields
      localState.discounttypeBogo = "fixed";
      localState.discountValue = null;
      localState.maxValue = null;
    }
  }
);

// Watch for Prop Changes
watch(
  () => props,
  (newProps) => {
    Object.assign(localState, newProps);
  },
  { deep: true, immediate: true }
);

// Watch for Local State Changes and Emit Updates
watch(
  localState,
  (newState) => {
    emit("update:buyProductCount", newState.buyProductCount);
    emit("update:getProductCount", newState.getProductCount);
    emit("update:freeOrDiscount", newState.freeOrDiscount);
    emit("update:isRepeat", newState.isRepeat);
    emit("update:discounttypeBogo", newState.discounttypeBogo);
    emit("update:discountValue", Number(newState.discountValue));
    emit("update:maxValue", Number(newState.maxValue));
  },
  { deep: true }
);
</script>

<template>
  <div class="space-y-4 max-w-full">
    <!-- Pricing or product set -->
    <div class="md:w-3/4 sm:w-full">
      <div
        class="flex flex-col md:flex-row md:items-center md:space-x-6 space-y-4 md:space-y-0">
        <!-- Column 1 -->
        <div class="flex items-center space-x-2 w-full md:w-auto">
          <label
            for="buyProductCount"
            class="text-gray-950 text-sm whitespace-nowrap">
            {{ __("Buy:", "all-in-one-woodiscount") }}
          </label>
          <el-input-number
            id="buyProductCount"
            v-model="localState.buyProductCount"
            :min="1"
            controls-position="right"
            class="w-full sm:w-60 md:w-80" />
        </div>

        <!-- Column 2 -->
        <div class="flex items-center space-x-2 w-full md:w-auto">
          <label
            for="getProductCount"
            class="text-gray-950 text-sm whitespace-nowrap">
            {{ __("Get:", "all-in-one-woodiscount") }}
          </label>
          <el-input-number
            id="getProductCount"
            v-model="localState.getProductCount"
            :min="1"
            controls-position="right"
            class="w-full max-w-xs" />
        </div>

        <!-- Column 3 -->
        <div class="w-full md:w-auto">
          <el-radio-group v-model="localState.freeOrDiscount">
            <el-radio-button
              :label="__('Free', 'all-in-one-woodiscount')"
              value="freeproduct" />
            <el-radio-button
              :label="__('Discount', 'all-in-one-woodiscount')"
              value="discount_product" />
          </el-radio-group>
        </div>
      </div>
    </div>

    <!-- Fixed or percentage discount for BOGO -->
    <div
      v-if="localState.freeOrDiscount === 'discount_product'"
      class="md:w-3/4 mt-5">
      <div
        class="flex flex-col md:flex-row md:items-end md:space-x-6 space-y-4 md:space-y-0">
        <!-- Column 1: Pricing Type -->
        <div class="w-full md:w-1/3">
          <label class="block text-sm font-medium pb-2 text-gray-900">
            {{ __("Pricing Type", "all-in-one-woodiscount") }}
          </label>
          <el-select
            v-model="localState.discounttypeBogo"
            size="default"
            popper-class="custom-dropdown"
            class="w-full">
            <el-option
              :value="'fixed'"
              :label="__('Fixed Discount', 'all-in-one-woodiscount')">
              {{ __("Fixed Discount", "all-in-one-woodiscount") }}
            </el-option>
            <el-option
              :value="'percentage'"
              :label="__('Percentage Discount', 'all-in-one-woodiscount')">
              {{ __("Percentage Discount", "all-in-one-woodiscount") }}
            </el-option>
          </el-select>
        </div>

        <!-- Column 2: Discount Value -->
        <div class="w-full md:w-1/3">
          <label class="block text-sm font-medium pb-2 text-gray-900">
            {{ __("Pricing Value", "all-in-one-woodiscount") }}
          </label>
          <el-input
            v-model.number="localState.discountValue"
            placeholder="Please input"
            class="w-full">
            <template #append>
              <span
                v-html="
                  localState.discounttypeBogo === 'percentage'
                    ? '%'
                    : generalData.currency_symbol || '$'
                "></span>
            </template>
          </el-input>
        </div>

        <!-- Column 3: Max Value -->
        <div class="w-full md:w-1/3">
          <label class="block text-sm font-medium pb-2 text-gray-900">
            <div class="flex items-center space-x-1">
              <span>{{ __("Maximum Value", "all-in-one-woodiscount") }}</span>
              <el-tooltip
                class="box-item"
                effect="dark"
                :content="
                  __(
                    'The maximum value that can be applied',
                    'all-in-one-woodiscount'
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
            v-model.number="localState.maxValue"
            placeholder="Please input"
            :disabled="localState.discounttypeBogo === 'fixed'"
            class="w-full">
            <template #append>
              <span v-html="generalData.currency_symbol || '$'"></span>
            </template>
          </el-input>
        </div>
      </div>
    </div>

    <!-- Is Repeat -->
    <div class="flex items-center gap-2 mt-6 mb-1">
      <el-switch
        v-model="localState.isRepeat"
        inline-prompt
        :active-text="__('On', 'all-in-one-woodiscount')"
        :inactive-text="__('Off', 'all-in-one-woodiscount')" />
      <label class="text-sm font-medium text-gray-900 flex items-center gap-1">
        {{ __("Is Repeat?", "all-in-one-woodiscount") }}
        <el-tooltip
          class="box-item"
          effect="dark"
          :content="
            __(
              'Discount will apply once or repeat after each quantities matching',
              'all-in-one-woodiscount'
            )
          "
          placement="top"
          popper-class="custom-tooltip">
          <QuestionMarkCircleIcon
            class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
        </el-tooltip>
      </label>
    </div>
  </div>
</template>

<style scoped></style>
