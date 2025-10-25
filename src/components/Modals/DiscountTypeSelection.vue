<script setup>
import { ref } from "vue";

const selectedType = ref("");
const showUpgradeMessage = (type) => {
  // console.log(`Upgrade to Pro for access to ${type}`);
};

const selectDiscountType = (type) => {
  selectedType.value = type; // Update the active state
  $emit("select", type); // Emit the type to parent
};
</script>

<template>
  <div class="tw-grid tw-grid-cols-1 tw-sm:grid-cols-2 tw-md:grid-cols-3 tw-gap-6 tw-p-4">
    <!-- Flat/Percentage Discount -->
    <div class="tw-relative tw-group">
      <button
        @click="() => selectDiscountType('Flat/Percentage')"
        :class="[
          'tw-p-4 tw-rounded-md tw-text-center tw-font-medium tw-w-full',
          selectedType === 'Flat/Percentage'
            ? 'tw-bg-blue-200'
            : 'tw-bg-gray-100 tw-hover:bg-blue-100 tw-active:scale-95',
        ]">
        {{ __("Flat/Percentage", "giantwp-discount-rules") }}
      </button>
      <div
        class="tw-absolute tw-bottom-full tw-mb-2 tw-hidden tw-group-hover:block tw-bg-gray-700 tw-text-white tw-text-xs tw-rounded tw-py-1 tw-px-2 tw-w-48 tw-text-center">
        {{
          __(
            "Apply a fixed amount or percentage discount",
            "giantwp-discount-rules"
          )
        }}
      </div>
    </div>

    <!-- BOGO Discount -->
    <div class="tw-relative tw-group">
      <button
        @click="() => selectDiscountType('BOGO')"
        :class="[
          'tw-p-4 tw-rounded-md tw-text-center tw-font-medium tw-w-full',
          selectedType === 'BOGO'
            ? 'tw-bg-blue-200'
            : 'tw-bg-gray-100 tw-hover:bg-blue-100 tw-active:scale-95',
        ]">
        {{ __("BOGO", "giantwp-discount-rules") }}
      </button>
      <div
        class="tw-absolute tw-bottom-full tw-mb-2 tw-hidden tw-group-hover:block tw-bg-gray-700 tw-text-white tw-text-xs tw-rounded tw-py-1 tw-px-2 tw-w-48 tw-text-center">
        {{ __("Buy One Get One free discount", "giantwp-discount-rules") }}
      </div>
    </div>

    <!-- Pro Features -->
    <div
      v-for="proFeature in [
        'Bulk Discount',
        'Cart Based',
        'Payment Method Based',
        'Combo Discount',
        'Category Based',
      ]"
      :key="proFeature"
      class="tw-relative tw-group">
      <button
        @click="() => showUpgradeMessage(proFeature)"
        class="tw-p-4 tw-bg-gray-100 tw-rounded-md tw-text-center tw-font-medium tw-w-full tw-cursor-not-allowed tw-opacity-50">
        {{ __(proFeature, "giantwp-discount-rules") }}
        <span
          class="tw-absolute tw-top-1 tw-right-1 tw-bg-red-500 tw-text-white tw-text-xs tw-px-2 tw-py-1 tw-rounded"
          >Pro</span
        >
      </button>
    </div>
  </div>
</template>