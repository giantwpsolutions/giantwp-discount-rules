<script setup>
import { reactive, computed, defineExpose, watch } from "vue";
import CouponName from "./Elements/CouponName.vue";
import ShippingSelections from "./Elements/ShippingSelections.vue";
import DateTimePicker from "./Elements/DateTimePicker.vue";
import UsageLimits from "./Elements/UsageLimits.vue";
import Conditions from "./Elements/Conditions.vue";
import AutoApply from "./Elements/AutoApply.vue";

// ** Define Props **
const props = defineProps({
  initialData: {
    type: Object,
    default: () => ({}),
  },
});

const formData = reactive({
  id: null,
  couponName: "",
  shippingDiscountType: "reduceFee",
  pDiscountType: "fixed",
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
    usageLimitsCount: null,
  },
  autoApply: false,
  enableConditions: false,
  conditionsApplies: "any",
  conditions: [],
});

// Formating Scheduling Range Properly
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
      console.log("🟢 Receiving Initial Data:", newVal);

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
    console.log("Full Form Datas:", JSON.parse(JSON.stringify(newVal)));
  },
  { deep: true }
);

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
  <CouponName v-model="formData.couponName"></CouponName>
  <ShippingSelections
    v-model:shippingDiscountType="formData.shippingDiscountType"
    v-model:pDiscountType="formData.pDiscountType"
    v-model:discountValue="formData.discountValue"
    v-model:maxValue="formData.maxValue"></ShippingSelections>
  <DateTimePicker
    v-model:enableSchedule="formData.schedule.enableSchedule"
    v-model:scheduleRange="scheduleRange">
  </DateTimePicker>
  <UsageLimits v-model="formData.usageLimits"></UsageLimits>
  <AutoApply v-model="formData.autoApply"> </AutoApply>
  <Conditions
    v-model:value="formData.conditions"
    v-model:toggle="formData.enableConditions"
    v-model:conditionsApplies="formData.conditionsApplies">
  </Conditions>
</template>
