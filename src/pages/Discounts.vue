<script setup>
import { ref } from "vue";
import DiscountTable from "../components/DiscountTable.vue";
import AddRuleModal from "../components/modals/AddRuleModal.vue";

const discountRules = ref([
  {
    id: 1,
    name: "COUPON123",
    type: "BOGO",
    startDate: "2024-12-01",
    endDate: "2024-12-31",
    status: true,
  },
  {
    id: 2,
    name: "SUMMER2024",
    type: "Percentage",
    startDate: "2024-06-01",
    endDate: "2024-09-01",
    status: false,
  },
]);

const showModal = ref(false);

const toggleStatus = (rule) => {
  rule.status = !rule.status;
};

const addNewRule = () => {
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};
</script>

<template>
  <div class="bg-white rounded-[10px] min-h-[250px] border border-gray-300 p-6">
    <h3 class="text-xl font-bold mb-6">
      {{ __("Discount Rules", "aio-woodiscount") }}
    </h3>

    <!-- Button to Add New Rule -->
    <div class="flex mb-8">
      <button
        @click="addNewRule"
        class="inline-flex h-8 items-center justify-center rounded-md px-4 text-sm font-medium duration-200 bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
        {{ __("Add New Rule", "aio-woodiscount") }}
      </button>
    </div>

    <!-- Discount Table -->
    <DiscountTable
      :discountRules="discountRules"
      :onToggleStatus="toggleStatus"
      :onEdit="(id) => console.log(`Edit rule with ID: ${id}`)"
      :onDelete="(id) => console.log(`Delete rule with ID: ${id}`)" />

    <!-- Modal Component -->
    <AddRuleModal :visible="showModal" @close="closeModal" />
  </div>
</template>
