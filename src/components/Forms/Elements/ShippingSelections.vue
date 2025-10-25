<script setup>
import { reactive, defineProps, defineEmits, onMounted, watch } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";
import { generalData, loadGeneralData } from "@/data/generalDataFetch";

onMounted(() => {
  loadGeneralData();
});

// Props & Emits
const props = defineProps({
  shippingDiscountType: { type: String, default: "reduceFee" },
  pDiscountType: { type: String, default: "fixed" },
  discountValue: { type: Number, default: null },
  maxValue: { type: Number, default: null },
});

// Define Emits
const emit = defineEmits([
  "update:shippingDiscountType",
  "update:pDiscountType",
  "update:discountValue",
  "update:maxValue",
]);

// Reactive Local State
const localState = reactive({
  shippingDiscountType: props.shippingDiscountType,
  pDiscountType: props.pDiscountType,
  discountValue: props.discountValue,
  maxValue: props.maxValue,
});

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
    emit("update:shippingDiscountType", newState.shippingDiscountType);
    emit("update:pDiscountType", newState.pDiscountType);
    emit("update:discountValue", newState.discountValue);
    emit("update:maxValue", newState.maxValue);
  },
  { deep: true }
);
</script>

<template>
  <div class="tw-space-y-4 tw-max-w-full">
    <!-- Discount Type Selection -->
    <div class="tw-w-full tw-max-w-md tw-mb-6">
      <label for="discountType" class="tw-block tw-text-sm tw-font-medium tw-text-gray-900">
        {{ __("Discount Type", "giantwp-discount-rules") }}
      </label>
      <select
        v-model="localState.shippingDiscountType"
        id="discountType"
        class="tw-mt-1.5 tw-h-8 tw-w-full tw-rounded-md tw-border-gray-300 tw-text-gray-700 sm:tw-text-sm">
        <option value="reduceFee">
          {{ __("Shipping Fee Discount", "giantwp-discount-rules") }}
        </option>
        <option value="customFee">
          {{ __("Add Custom Fee", "giantwp-discount-rules") }}
        </option>
      </select>
    </div>

    <!-- Shipping Fee Reduce -->
    <div
      v-if="localState.shippingDiscountType === 'reduceFee'"
      class="tw-w-3/4 tw-mt-5">
      <div class="tw-grid tw-grid-cols-1 tw-gap-4 md:tw-grid-cols-3">
        <!-- Column 1 -->
        <div>
          <label class="tw-block tw-text-sm tw-font-medium tw-pb-2 tw-text-gray-900">
            {{ __("Pricing Type", "giantwp-discount-rules") }}
          </label>
          <el-select
            v-model="localState.pDiscountType"
            size="default"
            popper-class="custom-dropdown">
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

        <!-- Column 2 -->
        <div>
          <label class="tw-block tw-text-sm tw-font-medium tw-pb-2 tw-text-gray-900">
            {{ __("Pricing Value", "giantwp-discount-rules") }}
          </label>
          <el-input
            v-model.number="localState.discountValue"
            style="max-width: 600px"
            placeholder="Please input"
            class="tw-w-full">
            <template #append>
              <span
                v-html="
                  localState.pDiscountType === 'percentage'
                    ? '%'
                    : generalData.currency_symbol || '$'
                "></span>
            </template>
          </el-input>
        </div>

        <!-- Column 3 -->
        <div>
          <label class="tw-block tw-text-sm tw-font-medium tw-pb-2 tw-text-gray-900">
            <div class="tw-flex tw-items-center tw-space-x-1">
              <span>{{ __("Maximum Value", "giantwp-discount-rules") }}</span>
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
                popper-class="custom-tooltip tw-w-full">
                <QuestionMarkCircleIcon
                  class="tw-w-4 tw-h-4 tw-text-gray-500 hover:tw-text-gray-700 tw-cursor-pointer" />
              </el-tooltip>
            </div>
          </label>
          <el-input
            v-model.number="localState.maxValue"
            style="max-width: 600px"
            placeholder="Please input"
            :disabled="localState.pDiscountType === 'fixed'">
            <template #append>
              <span v-html="generalData.currency_symbol || '$'"></span>
            </template>
          </el-input>
        </div>
      </div>
    </div>

    <!-- Custom Fee -->
    <div
      v-if="localState.shippingDiscountType === 'customFee'"
      class="tw-w-3/4 tw-mt-5">
      <div class="tw-grid tw-grid-cols-1 tw-gap-4 md:tw-grid-cols-3">
        <!-- Column 1 -->
        <div>
          <label class="tw-block tw-text-sm tw-font-medium tw-pb-2 tw-text-gray-900">
            {{ __("Fee Type", "giantwp-discount-rules") }}
          </label>
          <el-select
            v-model="localState.pDiscountType"
            size="default"
            popper-class="custom-dropdown">
            <el-option
              :value="'fixed'"
              :label="__('Fixed Fee', 'giantwp-discount-rules')">
              {{ __("Fixed Fee", "giantwp-discount-rules") }}
            </el-option>
            <el-option
              :value="'percentage'"
              :label="__('Percentage Fee', 'giantwp-discount-rules')">
              {{ __("Percentage Fee", "giantwp-discount-rules") }}
            </el-option>
          </el-select>
        </div>

        <!-- Column 2 -->
        <div>
          <label class="tw-block tw-text-sm tw-font-medium tw-pb-2 tw-text-gray-900">
            {{ __("Fee Value", "giantwp-discount-rules") }}
          </label>
          <el-input
            v-model.number="localState.discountValue"
            style="max-width: 600px"
            placeholder="Please input">
            <template #append>
              <span
                v-html="
                  localState.pDiscountType === 'percentage'
                    ? '%'
                    : generalData.currency_symbol || '$'
                "></span>
            </template>
          </el-input>
        </div>

        <!-- Column 3 -->
        <div>
          <label class="tw-block tw-text-sm tw-font-medium tw-pb-2 tw-text-gray-900">
            <div class="tw-flex tw-items-center tw-space-x-1">
              <span>{{
                __("Maximum Fee Value", "giantwp-discount-rules")
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
            style="max-width: 600px"
            placeholder="Please input"
            :disabled="localState.pDiscountType === 'fixed'">
            <template #append>
              <span v-html="generalData.currency_symbol || '$'"></span>
            </template>
          </el-input>
        </div>
      </div>
    </div>
  </div>
</template>
