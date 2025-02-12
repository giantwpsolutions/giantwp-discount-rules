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

// ✅ Reactive state for the form (Fix usageLimits structure)
const formData = reactive({
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
  validate: () => !!formData.couponName.trim(),
});

// ✅ Watch `conditionsApplies` to debug updates
watch(
  () => formData.conditionsApplies,
  (newVal) => {
    console.log("🔹 Condition Applies Updated:", newVal);
  }
);
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

    <AutoApply></AutoApply>

    <Conditions
      v-model:value="formData.conditions"
      v-model:toggle="formData.enableConditions"
      v-model:conditionsApplies="formData.conditionsApplies">
    </Conditions>
  </form>
</template>
