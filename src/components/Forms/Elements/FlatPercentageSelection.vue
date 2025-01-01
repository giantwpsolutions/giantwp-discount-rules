<!-- FlatPercentageSelection -->
<script setup>
import { QuestionMarkCircleIcon } from '@heroicons/vue/24/solid'; // Import the icon
import { ref } from 'vue';

const discountType = ref("");
const discountValue = ref("");
const maxValue = ref("");
</script>

<template>
  <div class="space-y-4 max-w-lg ">

    <!-- Discount Type Selection -->

    <div class="w-full max-w-md mb-6">

      <label for="discountType" class="block text-sm font-medium text-gray-900">
        {{ __('Discount Type', 'aio-woodiscount') }}
      </label>

      <select
        v-model="discountType"
        id="discountType"
        class="mt-1.5 h-9 w-full rounded-md border-gray-300 text-gray-700 sm:text-sm"
      >
        <option value="">{{ __('Please select', 'aio-woodiscount') }}</option>
        <option value="fixed">{{ __('Fixed', 'aio-woodiscount') }}</option>
        <option value="percentage">{{ __('Percentage', 'aio-woodiscount') }}</option>
      </select>

    </div>

    <!-- Inline Fields: Discount Value and Maximum Value -->

     <!-- Discount Value -->
     <div class="relative">
      <label for="discountValue" class="block text-sm font-medium text-gray-900">
        {{ __('Discount Value', 'aio-woodiscount') }}
      </label>
      <input
        v-model="discountValue"
        type="number"
        id="discountValue"
        placeholder="{{ __('Enter discount value', 'aio-woodiscount') }}"
        class="w-full h-9 rounded-md border-gray-200 pr-10 shadow-sm sm:text-sm"
      />
      <span
        class="pointer-events-none absolute inset-y-0 end-0 grid w-10 place-content-center text-gray-500"
      >
        <!-- Dynamic Icon -->
        <template v-if="discountType === 'fixed'">
          <span class="text-lg leading-none">$</span>
        </template>
        <template v-else-if="discountType === 'percentage'">
          <span class="text-lg leading-none">%</span>
        </template>
        <template v-else>
          <!-- Default Icon -->
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-5 w-5">
            <path
              fill-rule="evenodd"
              d="M5.404 14.596A6.5 6.5 0 1116.5 10a1.25 1.25 0 01-2.5 0 4 4 0 10-.571 2.06A2.75 2.75 0 0018 10a8 8 0 10-2.343 5.657.75.75 0 00-1.06-1.06 6.5 6.5 0 01-9.193 0zM10 7.5a2.5 2.5 0 100 5 2.5 2.5 0 000-5z"
              clip-rule="evenodd"
            />
          </svg>
        </template>
      </span>
    </div>

    <!-- Maximum Value -->
    <div>
      <label for="maxValue" class="block text-sm font-medium text-gray-900">
        {{ __('Maximum Value', 'aio-woodiscount') }}
      </label>
      <input
        v-model="maxValue"
        type="number"
        id="maxValue"
        placeholder="{{ __('Enter maximum value', 'aio-woodiscount') }}"
        :disabled="discountType === 'fixed'"
        class="mt-1.5 w-full h-9 rounded-md border-gray-200 text-gray-700 sm:text-sm"
      />
    </div>
  </div>
</template>

<style scoped>
/* Optional: Custom styles for tooltip or other elements if needed */
</style>