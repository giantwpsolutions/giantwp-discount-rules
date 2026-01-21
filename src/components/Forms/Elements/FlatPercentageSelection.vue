<script setup>
import { defineProps, defineEmits, ref, onMounted } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";

import { generalData, loadGeneralData } from "@/data/GeneralDataFetch.js";

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
  <div class="tw-space-y-4 tw-max-w-lg">
    <!-- Discount Type Selection -->
    <div class="tw-w-full tw-max-w-md tw-mb-6">
      <label
        for="discountType"
        class="tw-block tw-text-sm tw-font-medium tw-text-gray-900"
      >
        {{ __("Discount Type", "giantwp-discount-rules") }}
      </label>
      <select
        v-model="selectedFPDiscountType"
        @change="updateDiscountType"
        id="discountType"
        class="tw-mt-1.5 tw-h-8 tw-w-full tw-rounded-md tw-border-gray-300 tw-text-gray-700 tw-sm:text-sm"
      >
        <option value="fixed">
          {{ __("Fixed", "giantwp-discount-rules") }}
        </option>
        <option value="percentage">
          {{ __("Percentage", "giantwp-discount-rules") }}
        </option>
      </select>
    </div>

    <!-- Discount Value & Max Value Fields -->
    <!-- CHANGED THIS WRAPPER -->
    <div
      class="tw-flex tw-flex-row tw-flex-wrap tw-gap-4 tw-items-start tw-w-full"
    >
      <!-- Discount Value -->
      <div class="tw-relative tw-w-full sm:tw-w-auto tw-flex-1">
        <label
          for="discountValue"
          class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-my-1"
        >
          {{ __("Discount Value", "giantwp-discount-rules") }}
        </label>
        <div class="tw-flex tw-items-center">
          <input
            v-model="discountValue"
            @input="updateDiscountValue"
            type="number"
            id="discountValue"
            :placeholder="
              __('Enter discount value', 'giantwp-discount-rules')
            "
            class="tw-w-full tw-h-8 rounded-custom-aio-left tw-border tw-border-gray-300 tw-shadow-sm tw-text-sm tw-pr-4"
          />
          <span
            class="tw-ml-0 tw-h-8 tw-px-2 tw-py-2 tw-border rounded-custom-aio-right tw-text-gray-700 tw-bg-gray-100 tw-text-sm"
            v-html="
              selectedFPDiscountType === 'percentage'
                ? '%'
                : generalData.currency_symbol || '$'
            "
          >
          </span>
        </div>
      </div>

      <!-- Maximum Value -->
      <div class="tw-relative tw-w-full sm:tw-w-auto tw-flex-1">
        <label
          for="maxValue"
          class="tw-text-sm tw-font-medium tw-text-gray-900 tw-flex tw-items-center tw-gap-2 tw-my-1"
        >
          {{ __("Maximum Value", "giantwp-discount-rules") }}
          <el-tooltip
            effect="dark"
            :content="
              __(
                'The maximum value that can be applied',
                'giantwp-discount-rules'
              )
            "
            placement="top"
            popper-class="custom-tooltip"
          >
            <QuestionMarkCircleIcon
              class="tw-w-4 tw-h-4 tw-text-gray-500 hover:tw-text-gray-700 tw-cursor-pointer"
            />
          </el-tooltip>
        </label>
        <div class="tw-flex tw-items-center">
          <input
            v-model="maxValue"
            @input="updateMaxValue"
            type="number"
            id="maxValue"
            :placeholder="
              __('Enter maximum value', 'giantwp-discount-rules')
            "
            :disabled="selectedFPDiscountType === 'fixed'"
            class="tw-w-full tw-h-8 rounded-custom-aio-left tw-border tw-border-gray-300 tw-shadow-sm tw-text-sm tw-pr-4"
          />
          <span
            class="tw-ml-0 tw-px-2 tw-py-2 tw-h-8 tw-border rounded-custom-aio-right tw-text-gray-700 tw-bg-gray-100 tw-text-sm"
            v-html="generalData.currency_symbol || '$'"
          >
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
