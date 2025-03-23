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
  <div class="space-y-4 max-w-full">
    <!-- Discount Type Selection -->
    <div class="w-full max-w-md mb-6">
      <label for="discountType" class="block text-sm font-medium text-gray-900">
        {{ __("Discount Type", "all-in-one-discount-rules") }}
      </label>
      <select
        v-model="localState.shippingDiscountType"
        id="discountType"
        class="mt-1.5 h-8 w-full rounded-md border-gray-300 text-gray-700 sm:text-sm">
        <option value="reduceFee">
          {{ __("Shipping Fee Discount", "all-in-one-discount-rules") }}
        </option>
        <option value="customFee">
          {{ __("Add Custom Fee", "all-in-one-discount-rules") }}
        </option>
      </select>
    </div>

    <!-- Shipping Fee Reduce -->
    <div
      v-if="localState.shippingDiscountType === 'reduceFee'"
      class="w-3/4 mt-5">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <!-- Column 1 -->
        <div>
          <label class="block text-sm font-medium pb-2 text-gray-900">
            {{ __("Pricing Type", "all-in-one-discount-rules") }}
          </label>
          <el-select
            v-model="localState.pDiscountType"
            size="default"
            popper-class="custom-dropdown">
            <el-option
              :value="'fixed'"
              :label="__('Fixed Discount', 'all-in-one-discount-rules')">
              {{ __("Fixed Discount", "all-in-one-discount-rules") }}
            </el-option>
            <el-option
              :value="'percentage'"
              :label="__('Percentage Discount', 'all-in-one-discount-rules')">
              {{ __("Percentage Discount", "all-in-one-discount-rules") }}
            </el-option>
          </el-select>
        </div>

        <!-- Column 2 -->
        <div>
          <label class="block text-sm font-medium pb-2 text-gray-900">
            {{ __("Pricing Value", "all-in-one-discount-rules") }}
          </label>
          <el-input
            v-model.number="localState.discountValue"
            style="max-width: 600px"
            placeholder="Please input"
            class="w-full">
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
          <label class="block text-sm font-medium pb-2 text-gray-900">
            <div class="flex items-center space-x-1">
              <span>{{
                __("Maximum Value", "all-in-one-discount-rules")
              }}</span>
              <el-tooltip
                class="box-item"
                effect="dark"
                :content="
                  __(
                    'The maximum value that can be applied',
                    'all-in-one-discount-rules'
                  )
                "
                placement="top"
                popper-class="custom-tooltip w-full">
                <QuestionMarkCircleIcon
                  class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
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
      class="w-3/4 mt-5">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <!-- Column 1 -->
        <div>
          <label class="block text-sm font-medium pb-2 text-gray-900">
            {{ __("Fee Type", "all-in-one-discount-rules") }}
          </label>
          <el-select
            v-model="localState.pDiscountType"
            size="default"
            popper-class="custom-dropdown">
            <el-option
              :value="'fixed'"
              :label="__('Fixed Fee', 'all-in-one-discount-rules')">
              {{ __("Fixed Fee", "all-in-one-discount-rules") }}
            </el-option>
            <el-option
              :value="'percentage'"
              :label="__('Percentage Fee', 'all-in-one-discount-rules')">
              {{ __("Percentage Fee", "all-in-one-discount-rules") }}
            </el-option>
          </el-select>
        </div>

        <!-- Column 2 -->
        <div>
          <label class="block text-sm font-medium pb-2 text-gray-900">
            {{ __("Fee Value", "all-in-one-discount-rules") }}
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
          <label class="block text-sm font-medium pb-2 text-gray-900">
            <div class="flex items-center space-x-1">
              <span>{{
                __("Maximum Fee Value", "all-in-one-discount-rules")
              }}</span>
              <el-tooltip
                class="box-item"
                effect="dark"
                :content="
                  __(
                    'The maximum value that can be applied',
                    'all-in-one-discount-rules'
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
