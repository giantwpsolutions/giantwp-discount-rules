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
      // Reset discount-related fields
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
  <div class="tw-space-y-4 tw-max-w-full">
    <!-- Pricing or product set -->
<div class="md:tw-w-3/4 sm:tw-w-full">
  <div
    class="tw-flex tw-flex-col md:tw-flex-row md:tw-items-center md:tw-space-x-6 tw-space-y-4 md:tw-space-y-0">
    <!-- Column 1 -->
    <div class="tw-flex tw-items-center tw-space-x-2 tw-w-full md:tw-w-auto">
      <label
        for="buyProductCount"
        class="tw-text-gray-950 tw-text-sm tw-whitespace-nowrap">
        {{ __("Buy:", "giantwp-discount-rules") }}
      </label>
      <el-input-number
        id="buyProductCount"
        v-model="localState.buyProductCount"
        :min="1"
        controls-position="right"
        class="tw-w-full sm:tw-w-60 md:tw-w-80" />
    </div>

    <!-- Column 2 -->
    <div class="tw-flex tw-items-center tw-space-x-2 tw-w-full md:tw-w-auto">
      <label
        for="getProductCount"
        class="tw-text-gray-950 tw-text-sm tw-whitespace-nowrap">
        {{ __("Get:", "giantwp-discount-rules") }}
      </label>
      <el-input-number
        id="getProductCount"
        v-model="localState.getProductCount"
        :min="1"
        controls-position="right"
        class="tw-w-full tw-max-w-xs" />
    </div>

    <!-- Column 3 -->
    <div class="tw-w-full md:tw-w-auto">
      <el-radio-group v-model="localState.freeOrDiscount">
        <el-radio-button
          :label="__('Free', 'giantwp-discount-rules')"
          value="freeproduct" />
        <el-radio-button
          :label="__('Discount', 'giantwp-discount-rules')"
          value="discount_product" />
      </el-radio-group>
    </div>
  </div>
</div>


    <!-- Fixed or percentage discount for BOGO -->
    <div
      v-if="localState.freeOrDiscount === 'discount_product'"
      class="md:tw-w-3/4 tw-mt-5">
      <div
        class="tw-grid tw-gap-6 md:tw-grid-cols-3 tw-grid-cols-1 tw-items-end">
        <!-- Column 1: Pricing Type -->
        <div class="tw-w-full">
          <label class="tw-block tw-text-sm tw-font-medium tw-pb-2 tw-text-gray-900">
            {{ __("Pricing Type", "giantwp-discount-rules") }}
          </label>
          <el-select
            v-model="localState.discounttypeBogo"
            size="default"
            popper-class="custom-dropdown"
            class="tw-w-full">
            <el-option
              :value="'fixed'"
              :label="__('Fixed Discount', 'giantwp-discount-rules')">
              {{ __("Fixed Discount", "giantwp-discount-rules") }}
            </el-option>
            <el-option
              :value="'percentage'"
              :label="__('Percentage Discount', 'giantwp-discount-rules')">
              {{ __("Percentage Discount", "giantwp-discount-rules") }}
            </el-option>
          </el-select>
        </div>

        <!-- Column 2: Discount Value -->
        <div class="tw-w-full">
          <label class="tw-block tw-text-sm tw-font-medium tw-pb-2 tw-text-gray-900">
            {{ __("Pricing Value", "giantwp-discount-rules") }}
          </label>
          <el-input
            v-model.number="localState.discountValue"
            placeholder="Please input"
            class="tw-w-full">
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
        <div class="tw-w-full">
          <label class="tw-block tw-text-sm tw-font-medium tw-pb-2 tw-text-gray-900">
            <div class="tw-flex tw-items-center tw-space-x-1">
              <span>{{
                __("Maximum Value", "giantwp-discount-rules")
              }}</span>
              <el-tooltip
                class="box-item"
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
                  class="tw-w-4 tw-h-4 tw-text-gray-500 hover:tw-text-gray-700 tw-cursor-pointer" />
              </el-tooltip>
            </div>
          </label>
          <el-input
            v-model.number="localState.maxValue"
            placeholder="Please input"
            :disabled="localState.discounttypeBogo === 'fixed'"
            class="tw-w-full">
            <template #append>
              <span v-html="generalData.currency_symbol || '$'"></span>
            </template>
          </el-input>
        </div>
      </div>
    </div>

    <!-- Is Repeat -->
    <div class="tw-flex tw-items-center tw-gap-2 tw-mt-6 tw-mb-1">
      <el-switch
        v-model="localState.isRepeat"
        inline-prompt
        :active-text="__('On', 'giantwp-discount-rules')"
        :inactive-text="__('Off', 'giantwp-discount-rules')" />
      <label class="tw-text-sm tw-font-medium tw-text-gray-900 tw-flex tw-items-center tw-gap-1">
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
            class="tw-w-4 tw-h-4 tw-text-gray-500 hover:tw-text-gray-700 tw-cursor-pointer" />
        </el-tooltip>
      </label>
    </div>
  </div>
</template>


<style scoped></style>
