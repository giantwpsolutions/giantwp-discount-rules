<script setup>
import { ref, reactive, computed, watch } from "vue";

import CouponName from "./Elements/CouponName.vue";
import BogoSelection from "./Elements/BogoSelection.vue";
import Conditions from "./Elements/Conditions.vue";
import UsageLimits from "./Elements/UsageLimits.vue";
import DateTimePicker from "./Elements/DateTimePicker.vue";

const formData = reactive({
  couponName: "",

  schedule: {
    enableSchedule: false,
    scheduleRange: [],
    startDate: null,
    endDate: null,
  },
  usageLimits: {
    enableUsage: false,
    usageLimitsCount: null,
  },
  enableConditions: false,
  conditions: [],
});

// ✅ Computed property to handle scheduleRange updates
const scheduleRange = computed({
  get: () => formData.schedule.scheduleRange,
  set: (value) => {
    formData.schedule.scheduleRange = value;
    formData.schedule.startDate = value?.[0] || null;
    formData.schedule.endDate = value?.[1] || null;
  },
});

// ✅ Computed for Usage Limits (Fix Vue reactivity issues)
const usageLimits = computed({
  get: () => formData.usageLimits,
  set: (value) => {
    formData.usageLimits.enableUsage = value.enableUsage ?? false;
    formData.usageLimits.usageLimitsCount = value.usageLimitsCount ?? null;
  },
});

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

defineExpose({
  getFormData: () => JSON.parse(JSON.stringify(formData)), // Clone reactive object
  validate: () => !!formData.couponName.trim(),
});

watch(
  () => formData.conditions,
  (newVal) => {
    console.log("PARENT CONDITIONS:", newVal);
  },
  { deep: true }
);
</script>

<template>
  <form action="">
    <CouponName v-model="formData.couponName"></CouponName>
    <BogoSelection></BogoSelection>
    <UsageLimits></UsageLimits>
    <!-- ✅ Keep using a single DateTimePicker but store startDate & endDate separately -->
    <DateTimePicker
      v-model:enableSchedule="formData.schedule.enableSchedule"
      v-model:scheduleRange="scheduleRange">
    </DateTimePicker>
    <!-- ✅ Fix UsageLimits syncing -->
    <UsageLimits v-model="formData.usageLimits"></UsageLimits>
    <Conditions
      v-model:value="formData.conditions"
      v-model:toggle="formData.enableConditions">
    </Conditions>
  </form>
</template>
