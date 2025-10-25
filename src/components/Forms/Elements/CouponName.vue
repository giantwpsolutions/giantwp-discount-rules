<script setup>
import { ref, defineProps, defineEmits, watch } from "vue";

// Props and Emit for v-model
const props = defineProps({
  modelValue: {
    type: String,
    required: true,
  },
});

const emit = defineEmits(["update:modelValue"]);

// Local state to bind the input value
const localCouponName = ref(props.modelValue);

// ✅ Watch for changes in `props.modelValue` and update `localCouponName`
watch(
  () => props.modelValue,
  (newVal) => {
    localCouponName.value = newVal;
  },
  { immediate: true }
);

// Watch for changes in local state and emit updates
watch(localCouponName, (newValue) => {
  emit("update:modelValue", newValue);
});
</script>

<template>
  <div class="tw-w-full tw-max-w-md tw-mb-6">
    <h4 class="tw-text-gray-950 tw-pb-2 tw-text-sm">
      {{ __("Coupon Name", "giantwp-discount-rules") }}
    </h4>
    <label
      for="aio_coupon"
      class="tw-relative tw-block tw-rounded-md tw-border tw-border-gray-200 tw-focus-within:border-blue-600 tw-focus-within:ring-blue-600">
      <input
        required
        type="text"
        id="aio_coupon"
        v-model="localCouponName"
        class="tw-peer tw-w-full tw-border-none tw-bg-transparent tw-placeholder-transparent tw-focus:border-transparent focus:tw-outline-none focus:tw-ring-0"
        :placeholder="__('Coupon Name', 'giantwp-discount-rules')" />
      <span
       class="tw-pointer-events-none tw-absolute tw-left-2.5 tw-top-0 tw--translate-y-1/2 tw-bg-white tw-px-1 tw-text-xs tw-text-gray-700 tw-transition-all tw-peer-placeholder-shown:tw-top-1/2 tw-peer-placeholder-shown:tw-text-sm tw-peer-focus:tw-top-0 tw-peer-focus:tw-text-xs">
        {{ __("Coupon Name", "giantwp-discount-rules") }}
      </span>
    </label>
  </div>
</template>

<style scoped>
/* Optional: Add any additional styles here */
</style>
