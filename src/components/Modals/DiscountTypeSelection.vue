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
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 p-4">
    <!-- Flat/Percentage Discount -->
    <div class="relative group">
      <button
        @click="() => selectDiscountType('Flat/Percentage')"
        :class="[
          'p-4 rounded-md text-center font-medium w-full',
          selectedType === 'Flat/Percentage'
            ? 'bg-blue-200'
            : 'bg-gray-100 hover:bg-blue-100 active:scale-95',
        ]">
        {{ __("Flat/Percentage", "all-in-one-woodiscount") }}
      </button>
      <div
        class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-700 text-white text-xs rounded py-1 px-2 w-48 text-center">
        {{
          __(
            "Apply a fixed amount or percentage discount",
            "all-in-one-woodiscount"
          )
        }}
      </div>
    </div>

    <!-- BOGO Discount -->
    <div class="relative group">
      <button
        @click="() => selectDiscountType('BOGO')"
        :class="[
          'p-4 rounded-md text-center font-medium w-full',
          selectedType === 'BOGO'
            ? 'bg-blue-200'
            : 'bg-gray-100 hover:bg-blue-100 active:scale-95',
        ]">
        {{ __("BOGO", "all-in-one-woodiscount") }}
      </button>
      <div
        class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-700 text-white text-xs rounded py-1 px-2 w-48 text-center">
        {{ __("Buy One Get One free discount", "all-in-one-woodiscount") }}
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
      class="relative group">
      <button
        @click="() => showUpgradeMessage(proFeature)"
        class="p-4 bg-gray-100 rounded-md text-center font-medium w-full cursor-not-allowed opacity-50">
        {{ __(proFeature, "all-in-one-woodiscount") }}
        <span
          class="absolute top-1 right-1 bg-red-500 text-white text-xs px-2 py-1 rounded"
          >Pro</span
        >
      </button>
    </div>
  </div>
</template>
