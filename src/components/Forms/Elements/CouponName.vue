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
  <div class="w-full max-w-md mb-6">
    <h4 class="text-gray-950 pb-2 text-sm">
      {{ __("Coupon Name", "aio-woodiscount") }}
    </h4>
    <label
      for="aio_coupon"
      class="relative block rounded-md border border-gray-200 focus-within:border-blue-600 focus-within:ring-blue-600">
      <input
        type="text"
        id="aio_coupon"
        v-model="localCouponName"
        class="peer w-full border-none bg-transparent placeholder-transparent focus:border-transparent focus:outline-none focus:ring-0"
        :placeholder="__('Coupon Name', 'aio-woodiscount')" />
      <span
        class="pointer-events-none absolute left-2.5 top-0 -translate-y-1/2 bg-white px-1 text-xs text-gray-700 transition-all peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-sm peer-focus:top-0 peer-focus:text-xs">
        {{ __("Coupon Name", "aio-woodiscount") }}
      </span>
    </label>
  </div>
</template>

<style scoped>
/* Optional: Add any additional styles here */
</style>
