<script setup>
import { reactive, computed, defineExpose, watch } from "vue";

const { __ } = wp.i18n;

import BogoSameProductBuy from "./Elements/BogoSameProductBuy.vue";
import CouponName from "./Elements/CouponName.vue";
import BogoSelection from "./Elements/BogoSelection.vue";
import Conditions from "./Elements/Conditions.vue";
import UsageLimits from "./Elements/UsageLimits.vue";
import DateTimePicker from "./Elements/DateTimePicker.vue";

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
  buyProductCount: 1,
  getProductCount: 1,
  freeOrDiscount: "freeproduct",
  isRepeat: true,
  discounttypeBogo: null,
  discountValue: null,
  maxValue: null,
  bogoApplies: "any",
  buyProduct: [],
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
  conditionsApplies: "any",
  conditions: [],
});

// ✅ Format scheduling range properly
const scheduleRange = computed({
  get: () => {
    return [
      formData.schedule.startDate
        ? new Date(formData.schedule.startDate)
        : null,
      formData.schedule.endDate ? new Date(formData.schedule.endDate) : null,
    ];
  },
  set: (value) => {
    formData.schedule.startDate = value?.[0]?.toISOString() || null;
    formData.schedule.endDate = value?.[1]?.toISOString() || null;
    formData.schedule.scheduleRange = value;
  },
});

// ✅ Watch for edit mode data updates
watch(
  () => props.initialData,
  (newVal) => {
    if (newVal && Object.keys(newVal).length > 0) {
      Object.keys(formData).forEach((key) => {
        if (key in newVal && key !== "schedule") {
          formData[key] = newVal[key];
        }
      });

      if (newVal.schedule) {
        formData.schedule = {
          enableSchedule: newVal.schedule.enableSchedule || false,
          startDate: newVal.schedule.startDate || null,
          endDate: newVal.schedule.endDate || null,
          scheduleRange: [
            newVal.schedule.startDate
              ? new Date(newVal.schedule.startDate)
              : null,
            newVal.schedule.endDate ? new Date(newVal.schedule.endDate) : null,
          ],
        };
      }

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

defineExpose({
  getFormData: () => JSON.parse(JSON.stringify(formData)),
  validate: () => !!formData.couponName.trim(),
  setFormData: (data) => {
    Object.assign(formData, JSON.parse(JSON.stringify(data)));
  },
});
</script>

<template>
  <form action="" class="tw-space-y-6 tw-w-full tw-max-w-full">
    <!-- Coupon Name -->
    <CouponName v-model="formData.couponName" />

    <!-- BOGO Selection -->
    <BogoSelection
      v-model:buyProductCount="formData.buyProductCount"
      v-model:getProductCount="formData.getProductCount"
      v-model:freeOrDiscount="formData.freeOrDiscount"
      v-model:isRepeat="formData.isRepeat"
      v-model:discounttypeBogo="formData.discounttypeBogo"
      v-model:discountValue="formData.discountValue"
      v-model:maxValue="formData.maxValue"
    />

    <!-- Buy Product Section -->
    <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4 tw-bg-white tw-shadow-sm">
      <BogoSameProductBuy
        v-model:value="formData.buyProduct"
        v-model:bogoApplies="formData.bogoApplies"
      />
    </div>

    <!-- Schedule -->
    <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4 tw-bg-white tw-shadow-sm">
      <DateTimePicker
        v-model:enableSchedule="formData.schedule.enableSchedule"
        v-model:scheduleRange="scheduleRange"
      />
    </div>

    <!-- Usage Limits -->
    <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4 tw-bg-white tw-shadow-sm">
      <UsageLimits v-model="formData.usageLimits" />
    </div>

    <!-- Conditions -->
    <div class="tw-border tw-border-gray-200 tw-rounded-lg tw-p-4 tw-bg-white tw-shadow-sm">
      <Conditions
        v-model:value="formData.conditions"
        v-model:toggle="formData.enableConditions"
        v-model:conditionsApplies="formData.conditionsApplies"
      />
    </div>
  </form>
</template>
