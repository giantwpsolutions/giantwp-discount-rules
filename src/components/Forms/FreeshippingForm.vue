<script setup>
import { ref } from 'vue';

// Form state
const ruleName = ref('');
const minOrderAmount = ref(0);
const startDate = ref('');
const endDate = ref('');
const description = ref('');
const status = ref(true);

// Emits for form submission and cancellation
const emit = defineEmits(['save', 'cancel']);

// Save form function
const saveForm = () => {
  const formData = {
    ruleName: ruleName.value,
    minOrderAmount: minOrderAmount.value,
    startDate: startDate.value,
    endDate: endDate.value,
    description: description.value,
    status: status.value,
  };
  emit('save', formData);
};

// Cancel form function
const cancelForm = () => {
  emit('cancel');
};
</script>

<template>
  <div class="bg-white rounded-lg shadow-md p-6 w-full max-w-lg mx-auto">
    <h3 class="text-lg font-bold mb-4">{{ __('Free Shipping Rule', 'aio-woodiscount') }}</h3>

    <!-- Rule Name -->
    <div class="mb-4">
      <label class="block text-sm font-medium mb-2">{{ __('Rule Name', 'aio-woodiscount') }}</label>
      <input
        v-model="ruleName"
        type="text"
        class="w-full border rounded-md p-2"
        placeholder="Enter rule name"
      />
    </div>

    <!-- Minimum Order Amount -->
    <div class="mb-4">
      <label class="block text-sm font-medium mb-2">{{ __('Minimum Order Amount', 'aio-woodiscount') }}</label>
      <input
        v-model="minOrderAmount"
        type="number"
        class="w-full border rounded-md p-2"
        placeholder="Enter minimum order amount"
        min="0"
      />
    </div>

    <!-- Start Date -->
    <div class="mb-4">
      <label class="block text-sm font-medium mb-2">{{ __('Start Date', 'aio-woodiscount') }}</label>
      <input
        v-model="startDate"
        type="date"
        class="w-full border rounded-md p-2"
      />
    </div>

    <!-- End Date -->
    <div class="mb-4">
      <label class="block text-sm font-medium mb-2">{{ __('End Date', 'aio-woodiscount') }}</label>
      <input
        v-model="endDate"
        type="date"
        class="w-full border rounded-md p-2"
      />
    </div>

    <!-- Description -->
    <div class="mb-4">
      <label class="block text-sm font-medium mb-2">{{ __('Description', 'aio-woodiscount') }}</label>
      <textarea
        v-model="description"
        rows="3"
        class="w-full border rounded-md p-2"
        placeholder="Enter description"
      ></textarea>
    </div>

    <!-- Status Toggle -->
    <div class="mb-6 flex items-center">
      <label class="block text-sm font-medium mr-4">{{ __('Enable Rule', 'aio-woodiscount') }}</label>
      <input
        v-model="status"
        type="checkbox"
        class="h-5 w-5 text-blue-600"
      />
    </div>

    <!-- Buttons -->
    <div class="flex justify-end space-x-4">
      <button
        @click="cancelForm"
        class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400"
      >
        {{ __('Cancel', 'aio-woodiscount') }}
      </button>
      <button
        @click="saveForm"
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
      >
        {{ __('Save', 'aio-woodiscount') }}
      </button>
    </div>
  </div>
</template>

<style scoped>
input, textarea {
  outline: none;
}
</style>
