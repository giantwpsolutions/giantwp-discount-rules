<script setup>
import { ref } from 'vue';

// Define reactive license key
const licenseKey = ref('');

// Save settings function
const saveSettings = () => {
  console.log('License Key:', licenseKey.value);
  // Add logic to save the license key (e.g., via REST API or AJAX)
};
const { __ } = wp.i18n;


</script>

<template>
  <div class="bg-white rounded-[10px] min-h-[250px] border border-gray-300 p-6 flex flex-col">
    <h3 class="text-xl font-bold mb-6">{{ __('General Settings', 'aio-woodiscount') }}</h3>

    <!-- License Input Field with Label on the Left -->
    <div class="w-full max-w-md flex items-center mb-6">
      <label for="license" class="text-base font-medium text-dark w-32">
        {{ __('License Key', 'aio-woodiscount') }}
      </label>
      <input
        type="text"
        id="license"
        v-model="licenseKey"
        :placeholder="__('Enter your license key', 'aio-woodiscount')"
        class="flex-1 bg-gray-100 rounded-md border border-gray-300 h-10 px-5 text-gray-700 outline-none transition focus:border-blue-500"
      />
    </div>
 

    <!-- Spacer to Push Button to the Bottom -->
    <div class="flex-grow"></div>

    <!-- Save Button -->
    <div class="w-full">
      <button
        @click="saveSettings"
        class="bg-blue-600 text-white font-medium py-3 px-6 rounded-md hover:bg-blue-700 transition w-full md:w-auto"
      >
        {{ __('Save Changes', 'aio-woodiscount') }}
      </button>
    </div>
  </div>
</template>

<style scoped>
</style>