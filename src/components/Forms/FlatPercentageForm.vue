<script setup>
import { reactive, computed, defineExpose, watch } from "vue";

const { __ } = wp.i18n;

import Conditions from "./Elements/Conditions.vue";
import CouponName from "./Elements/CouponName.vue";
import FlatPercentageSelection from "./Elements/FlatPercentageSelection.vue";
import DateTimePicker from "./Elements/DateTimePicker.vue";
import UsageLimits from "./Elements/UsageLimits.vue";
import AutoApply from "./Elements/AutoApply.vue";

// ** Define Props **
const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({}),
  },
});

// ✅ Reactive state for the form (Fix usageLimits structure)
const isProActive = !!gwpdrPluginData?.proActive;

const formData = reactive({
  id: null,
  couponName: "",
  fpDiscountType: "fixed",
  discountValue: null,
  maxValue: null,
  minMarginPercent: null,
  schedule: {
    enableSchedule: false,
    scheduleRange: [],
    startDate: null,
    endDate: null,
  },
  usageLimits: {
    enableUsage: false,
    usageLimitsCount: 0, // ✅ Ensure default is a number
  },
  enableConditions: false,
  conditionsApplies: "any",
  conditions: [],
});

// Ensure `scheduleRange` updates properly
const scheduleRange = computed({
  get: () => {
    // Return dates in proper format for the picker
    return [
      formData.schedule.startDate
        ? new Date(formData.schedule.startDate)
        : null,
      formData.schedule.endDate ? new Date(formData.schedule.endDate) : null,
    ];
  },
  set: (value) => {
    // Store dates in ISO string format
    formData.schedule.startDate = value?.[0]?.toISOString() || null;
    formData.schedule.endDate = value?.[1]?.toISOString() || null;
    formData.schedule.scheduleRange = value;
  },
});

// Watch for edit mode data updates
watch(
  () => props.initialData,
  (newVal) => {
    if (newVal && Object.keys(newVal).length > 0) {
      // console.log("🟢 Receiving Initial Data:", newVal);

      // Clone all top-level properties
      Object.keys(formData).forEach((key) => {
        if (key in newVal && key !== "schedule") {
          formData[key] = newVal[key];
        }
      });

      // Handle schedule data conversion
      if (newVal.schedule) {
        formData.schedule = {
          enableSchedule: newVal.schedule.enableSchedule || false,
          startDate: newVal.schedule.startDate || null,
          endDate: newVal.schedule.endDate || null,
          // Initialize scheduleRange from stored dates
          scheduleRange: [
            newVal.schedule.startDate
              ? new Date(newVal.schedule.startDate)
              : null,
            newVal.schedule.endDate ? new Date(newVal.schedule.endDate) : null,
          ],
        };
      }

      // Handle other nested objects
      if (newVal.usageLimits) {
        formData.usageLimits = { ...newVal.usageLimits };
      }
    }
  },
  { immediate: true, deep: true }
);

watch(
  () => formData,
  (newVal) => {
    // console.log("Full Form Data:", JSON.parse(JSON.stringify(newVal)));
  },
  { deep: true }
);

// ✅ Expose formData for saving
defineExpose({
  getFormData: () => JSON.parse(JSON.stringify(formData)), // Clone reactive object
  validate: () => {
    // console.log("🔍 Validate Check - Coupon Name:", formData.couponName);
    return !!formData.couponName.trim();
  },
  setFormData: (data) => {
    // console.log("🟢 Setting Form Data in Edit Mode:", data);
    Object.assign(formData, JSON.parse(JSON.stringify(data)));
  },
});
</script>

<template>
  <form action="">
    
    <CouponName v-model="formData.couponName"></CouponName>

    <FlatPercentageSelection
      v-model:fpDiscountType="formData.fpDiscountType"
      v-model:discountValue="formData.discountValue"
      v-model:maxValue="formData.maxValue">
    </FlatPercentageSelection>
    <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4 tw-bg-white tw-shadow-sm tw-mt-4">
    <DateTimePicker
      v-model:enableSchedule="formData.schedule.enableSchedule"
      v-model:scheduleRange="scheduleRange">
    </DateTimePicker>
    </div>
    <!-- ✅ Fix UsageLimits syncing -->
    <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4 tw-bg-white tw-shadow-sm tw-mt-4">
    <UsageLimits v-model="formData.usageLimits"></UsageLimits>
    </div>
    <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4 tw-bg-white tw-shadow-sm tw-mt-4">
    <Conditions
      v-model:value="formData.conditions"
      v-model:toggle="formData.enableConditions"
      v-model:conditionsApplies="formData.conditionsApplies">
    </Conditions>
  </div>

  <!-- Per-rule Min Margin (Pro) -->
  <div
    class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4 tw-bg-white tw-shadow-sm tw-mt-4"
    :class="{ 'tw-opacity-60': !isProActive }"
  >
    <div class="tw-flex tw-items-center tw-justify-between">
      <div>
        <div class="tw-flex tw-items-center tw-gap-2">
          <p class="tw-text-sm tw-font-semibold tw-text-gray-800">{{ __("Min Profit Margin", "giantwp-discount-rules") }}</p>
          <span
            v-if="!isProActive"
            class="tw-bg-red-500 tw-text-white tw-text-xs tw-font-bold tw-px-2 tw-py-0.5 tw-rounded"
          >{{ __("Pro", "giantwp-discount-rules") }}</span>
        </div>
        <p class="tw-text-xs tw-text-gray-400 tw-mt-0.5">
          {{ __("Overrides global margin setting for this rule. Requires Cost Price on products.", "giantwp-discount-rules") }}
        </p>
      </div>
      <div class="tw-flex tw-items-center tw-gap-2">
        <el-input-number
          v-model="formData.minMarginPercent"
          :min="0"
          :max="99"
          :step="1"
          :precision="1"
          :disabled="!isProActive"
          :placeholder="__('Global default', 'giantwp-discount-rules')"
          style="width: 140px"
        />
        <span class="tw-text-sm tw-text-gray-500">%</span>
      </div>
    </div>
  </div>
  </form>
</template>
