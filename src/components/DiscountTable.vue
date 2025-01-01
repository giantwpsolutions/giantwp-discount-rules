<script setup>
  import { defineProps } from 'vue';

// Props for discount rules and event handlers
const props = defineProps({
  discountRules: {
    type: Array,
    required: true,
  },
  onEdit: {
    type: Function,
    required: true,
  },
  onDelete: {
    type: Function,
    required: true,
  },
  onToggleStatus: {
    type: Function,
    required: true,
  },
});
</script>

<template>
  <div class="overflow-x-auto">
    <table class="min-w-full table-auto border-collapse border border-gray-200">
      <thead>
        <tr class="bg-gray-100 text-left">
          <th class="px-4 py-2 border-b w-12">
            <input type="checkbox" class="h-5 w-5">
          </th>
          <th class="px-4 py-2 border-b">Discount Name</th>
          <th class="px-4 py-2 border-b">Type</th>
          <th class="px-4 py-2 border-b">Start Date</th>
          <th class="px-4 py-2 border-b">End Date</th>
          <th class="px-4 py-2 border-b">Status</th>
          <th class="px-4 py-2 border-b">Actions</th>
        </tr>
      </thead>
      <tbody>
        <!-- No Data Row -->
        <tr v-if="discountRules.length === 0">
          <td colspan="7" class="text-center px-4 py-6 text-gray-500">
            {{ __('No discount rules created', 'aio-woodiscount') }}
          </td>
        </tr>

        <!-- Discount Rule Rows -->
        <tr v-for="rule in discountRules" :key="rule.id">
          <td class="px-4 py-2 border-b">
            <input type="checkbox" class="h-5 w-5">
          </td>
          <td class="px-4 py-2 border-b">{{ rule.name }}</td>
          <td class="px-4 py-2 border-b">{{ rule.type }}</td>
          <td class="px-4 py-2 border-b">{{ rule.startDate }}</td>
          <td class="px-4 py-2 border-b">{{ rule.endDate }}</td>
          <td class="px-4 py-2 border-b">
            <label class="inline-flex relative items-center cursor-pointer">
              <input
              type="checkbox"
              :checked="rule.status"
              @change="() => onToggleStatus(rule)"
              class="sr-only peer"
              />
              <span
              class="w-11 h-6 bg-gray-200 rounded-full peer-checked:bg-blue-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all"
              ></span>
            </label>
          </td>
          <td class="px-4 py-2 border-b">
            <!-- Edit Button with Tooltip -->
            <div class="relative group inline-block">
              <button @click="() => onEdit(rule.id)" class="text-blue-600 hover:text-blue-800 mr-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 3.5l-9 9L5 21l8.5-2.5 9-9-6-6z" />
                </svg>
              </button>
              <span class="absolute bottom-full mb-1 hidden group-hover:block bg-gray-700 text-white text-xs rounded py-1 px-2 whitespace-nowrap">
                Edit
              </span>
            </div>
            <!-- Delete Button with Red Bin Icon and Tooltip -->
            <div class="relative group inline-block">
              <button @click="() => onDelete(rule.id)" class="text-red-600 hover:text-red-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M9 3V4H4V6H5L6 21C6 21.552 6.448 22 7 22H17C17.552 22 18 21.552 18 21L19 6H20V4H15V3H9ZM8 6H16L15 20H9L8 6Z"/>
                </svg>
              </button>
              <span class="absolute bottom-full mb-1 hidden group-hover:block bg-gray-700 text-white text-xs rounded py-1 px-2 whitespace-nowrap">
                Delete
              </span>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  
</template>
