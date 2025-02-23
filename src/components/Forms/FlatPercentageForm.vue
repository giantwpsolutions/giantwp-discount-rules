<script setup>
import { reactive, computed, defineExpose, watch } from "vue";

const { __ } = wp.i18n;

import Conditions from "./Elements/Conditions.vue";
import CouponName from "./Elements/CouponName.vue";
import FlatPercentageSelection from "./Elements/FlatPercentageSelection.vue";
import DateTimePicker from "./Elements/DateTimePicker.vue";
import UsageLimits from "./Elements/UsageLimits.vue";
import CheckProduct from "./Elements/CheckProduct.vue";
import AutoApply from "./Elements/AutoApply.vue";

// ** Define Props **
const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({}),
  },
});

// ✅ Reactive state for the form (Fix usageLimits structure)
const formData = reactive({
  id: null,
  couponName: "",
  fpDiscountType: "fixed",
  discountValue: null,
  maxValue: null,
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
  autoApply: false,
  enableConditions: false,
  conditionsApplies: "any",
  conditions: [],
});

// ✅ Ensure `scheduleRange` updates properly
const scheduleRange = computed({
  get: () => formData.schedule.scheduleRange,
  set: (value) => {
    formData.schedule.scheduleRange = value;
    formData.schedule.startDate = value?.[0] || null;
    formData.schedule.endDate = value?.[1] || null;
  },
});

// Watch for edit mode data updates
watch(
  () => props.initialData,
  (newVal) => {
    if (newVal && Object.keys(newVal).length > 0) {
      console.log("🟢 Receiving Initial Data:", newVal);
      Object.keys(formData).forEach((key) => {
        if (key in newVal) {
          formData[key] = newVal[key];
        }
      });

      // Handle nested objects
      if (newVal.schedule) {
        formData.schedule = { ...newVal.schedule };
      }
      if (newVal.usageLimits) {
        formData.usageLimits = { ...newVal.usageLimits };
      }
    }
  },
  { immediate: true, deep: true }
);

// ✅ Debugging Watches
watch(
  () => formData.usageLimits,
  (newVal) => {
    console.log("🟡 Parent Usage Limits Updated:", newVal);
  },
  { deep: true }
);

// ✅ Debugging Watches
watch(
  () => formData.conditions,
  (newVal) => {
    console.log("Conditions Updated:", JSON.parse(JSON.stringify(newVal)));
  },
  { deep: true }
);

watch(
  () => formData,
  (newVal) => {
    console.log("Full Form Data:", JSON.parse(JSON.stringify(newVal)));
  },
  { deep: true }
);

// ✅ Expose formData for saving
defineExpose({
  getFormData: () => JSON.parse(JSON.stringify(formData)), // Clone reactive object
  validate: () => {
    console.log("🔍 Validate Check - Coupon Name:", formData.couponName);
    return !!formData.couponName.trim();
  },
  setFormData: (data) => {
    console.log("🟢 Setting Form Data in Edit Mode:", data);
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

    <DateTimePicker
      v-model:enableSchedule="formData.schedule.enableSchedule"
      v-model:scheduleRange="scheduleRange">
    </DateTimePicker>

    <!-- ✅ Fix UsageLimits syncing -->
    <UsageLimits v-model="formData.usageLimits"></UsageLimits>

    <AutoApply v-model="formData.autoApply"> </AutoApply>

    <Conditions
      v-model:value="formData.conditions"
      v-model:toggle="formData.enableConditions"
      v-model:conditionsApplies="formData.conditionsApplies">
    </Conditions>
  </form>
</template>
