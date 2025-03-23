<script setup>
import { defineProps, defineEmits, ref, onMounted } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";

import { generalData, loadGeneralData } from "@/data/generalDataFetch";

// Props & Emits
const props = defineProps({
  fpDiscountType: { type: String, default: "fixed" },
  discountValue: { type: Number, default: null },
  maxValue: { type: Number, default: null },
});

const emit = defineEmits([
  "update:fpDiscountType",
  "update:discountValue",
  "update:maxValue",
]);

// Local state for reactivity
const selectedFPDiscountType = ref(props.fpDiscountType);
const discountValue = ref(props.discountValue);
const maxValue = ref(props.maxValue);

// Emit Updates when values change
const updateDiscountType = () => {
  emit("update:fpDiscountType", selectedFPDiscountType.value);
};

const updateDiscountValue = () => {
  emit("update:discountValue", discountValue.value);
};

const updateMaxValue = () => {
  emit("update:maxValue", maxValue.value);
};

// Fetch General Data on Mount
onMounted(() => {
  loadGeneralData();
});
</script>

<template>
  <div class="space-y-4 max-w-lg">
    <!-- Discount Type Selection -->
    <div class="w-full max-w-md mb-6">
      <label for="discountType" class="block text-sm font-medium text-gray-900">
        {{ __("Discount Type", "all-in-one-discount-rules") }}
      </label>
      <select
        v-model="selectedFPDiscountType"
        @change="updateDiscountType"
        id="discountType"
        class="mt-1.5 h-8 w-full rounded-md border-gray-300 text-gray-700 sm:text-sm">
        <option value="fixed">
          {{ __("Fixed", "all-in-one-discount-rules") }}
        </option>
        <option value="percentage">
          {{ __("Percentage", "all-in-one-discount-rules") }}
        </option>
      </select>
    </div>

    <!-- Discount Value & Max Value Fields -->
    <div class="flex flex-col md:flex-row gap-4 items-start w-full">
      <!-- Discount Value -->
      <div class="relative w-full md:flex-1">
        <label
          for="discountValue"
          class="block text-sm font-medium text-gray-900 my-1">
          {{ __("Discount Value", "all-in-one-discount-rules") }}
        </label>
        <div class="flex items-center">
          <input
            v-model="discountValue"
            @input="updateDiscountValue"
            type="number"
            id="discountValue"
            :placeholder="
              __('Enter discount value', 'all-in-one-discount-rules')
            "
            class="w-full h-8 rounded-custom-aio-left border border-gray-300 shadow-sm text-sm pr-4" />
          <span
            class="ml-0 h-8 px-2 py-2 border rounded-custom-aio-right text-gray-700 bg-gray-100 text-sm"
            v-html="
              selectedFPDiscountType === 'percentage'
                ? '%'
                : generalData.currency_symbol || '$'
            ">
          </span>
        </div>
      </div>

      <!-- Maximum Value -->
      <div class="relative w-full md:flex-1">
        <label
          for="maxValue"
          class="text-sm font-medium text-gray-900 flex items-center gap-2 my-1">
          {{ __("Maximum Value", "all-in-one-discount-rules") }}
          <el-tooltip
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
        </label>
        <div class="flex items-center">
          <input
            v-model="maxValue"
            @input="updateMaxValue"
            type="number"
            id="maxValue"
            :placeholder="
              __('Enter maximum value', 'all-in-one-discount-rules')
            "
            :disabled="selectedFPDiscountType === 'fixed'"
            class="w-full h-8 rounded-custom-aio-left border border-gray-300 shadow-sm text-sm pr-4" />
          <span
            class="ml-0 px-2 py-2 h-8 border rounded-custom-aio-right text-gray-700 bg-gray-100 text-sm"
            v-html="generalData.currency_symbol || '$'">
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Tooltip styling */
.group:hover .group-hover\:block {
  display: block;
}

.rounded-custom-aio-left {
  border-radius: 5px 0px 0px 5px;
}
.rounded-custom-aio-right {
  border-radius: 0px 5px 5px 0px;
}
</style>
