<!-- Discount.vue -->
<script setup>
import { ref, onMounted } from "vue";
import DiscountTable from "../components/DiscountTable.vue";
import AddRuleModal from "../components/modals/AddRuleModal.vue";
import {
  fetchFlatPercentageRule,
  discountRules,
} from "../api/services/flatPercentageDataServices";
import { saveFlatPercentageDiscount } from "@/data/save-data/saveFlatPercentageDiscount.js";
import { deleteMessage } from "@/data/message.js";

const showModal = ref(false);
const selectedDiscount = ref(null); // Track selected rule for editing

const fetchDiscountRules = async () => {
  discountRules.value = await fetchFlatPercentageRule();
  console.log("🟢 Discount rules updated:", discountRules.value);
};
onMounted(fetchDiscountRules);

const deleteRule = async (id) => {
  try {
    await saveFlatPercentageDiscount.deleteCoupon(id);
    await fetchDiscountRules();

    deleteMessage();
  } catch (error) {
    console.error("❌ Failed to delete:", error);
  }
};
const toggleStatus = async (rule) => {
  try {
    const newStatus = rule.status === "on" ? "off" : "on";

    // Store original data
    const originalData = JSON.parse(JSON.stringify(rule));

    // Optimistic update
    rule.status = newStatus;

    // Send only the status field
    await saveFlatPercentageDiscount.updateDiscount(rule.id, {
      status: newStatus,
    });

    // Force full refresh
    await fetchDiscountRules();
  } catch (error) {
    // Revert on error
    Object.assign(rule, originalData);
    console.error("Status update failed:", error);
  }
};

// ✅ Handle Edit Click
const handleEdit = async (rule) => {
  selectedDiscount.value = structuredClone({
    ...rule,
    discountType: "Flat/Percentage", // Ensure this matches your type
  });
  showModal.value = true;
};
// ✅ Handle New Entry
const addNewRule = () => {
  selectedDiscount.value = null; // ✅ Reset for new entry
  showModal.value = true;
};

// ✅ Close Modal
const closeModal = () => {
  selectedDiscount.value = null;
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
      @toggle-status="toggleStatus"
      :onEdit="handleEdit"
      :onDelete="deleteRule" />

    <!-- Modal Component -->
    <AddRuleModal
      :visible="showModal"
      :editingRule="selectedDiscount"
      @close="closeModal"
      @discountUpdated="fetchDiscountRules" />
  </div>
</template>
