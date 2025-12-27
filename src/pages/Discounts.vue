<!-- Discount.vue -->
<script setup>
// All Imports
import { ref, onMounted, toRaw } from "vue";
import DiscountTable from "../components/DiscountTable.vue";
import AddRuleModal from "../components/modals/AddRuleModal.vue";
import {
  fetchAllDiscountRules,
  discountRules,
} from "../api/services/fetchAllDiscountRules";
import { saveFlatPercentageDiscount } from "@/data/save-data/saveFlatPercentageDiscount.js";
import { deleteMessage, updatedDiscountStatus } from "@/data/message.js";
import { saveBogoData } from "@/data/save-data/saveBogoData.js";
import { saveShippingData } from "@/data/save-data/saveShippingData.js";
import { saveBuyXGetYData } from "@/data/save-data/saveBuyXGetYData.js";
import { saveBulkDiscountData } from "@/data/save-data/saveBulkDiscountData.js";

//Reactive state

const showModal = ref(false);
const selectedDiscount = ref(null);
const fetchDiscountRules = async () => {
  discountRules.value = await fetchAllDiscountRules();
  // console.log("🟢 Discount rules updated:", discountRules.value);
};

//APi Fetching
onMounted(fetchDiscountRules);

//Deleting Rules
const deleteRule = async (rule) => {
  try {
    // console.log("Deleting Rule now:", rule);

    if (rule.discountType === "flat/percentage") {
      await saveFlatPercentageDiscount.deleteCoupon(rule.id);
    } else if (rule.discountType === "bogo") {
      await saveBogoData.deleteCoupon(rule.id);
    } else if (rule.discountType === "shipping discount") {
      await saveShippingData.deleteCoupon(rule.id);
    } else if (rule.discountType === "buy x get y") {
      await saveBuyXGetYData.deleteCoupon(rule.id);
    } else if (rule.discountType === "bulk discount") {
      await saveBulkDiscountData.deleteCoupon(rule.id);
    }

    await fetchDiscountRules();
    deleteMessage();
  } catch (error) {
    console.error("❌ Failed to delete:", error);
  }
};

const toggleStatus = async (rule) => {
  // console.log(
  //   `🔄 Toggling Status for ID: ${rule.id} | Current Status: ${rule.status}`
  // );

  try {
    let response;

    // Determine which update function to use based on discountType
    if (rule.discountType.toLowerCase() === "bogo") {
      // console.log("📡 Updating BOGO Discount...");
      response = await saveBogoData.updateDiscount(rule.id, {
        status: rule.status,
      });
    } else if (rule.discountType.toLowerCase() === "flat/percentage") {
      // console.log("📡 Updating Flat/Percentage Discount...");
      response = await saveFlatPercentageDiscount.updateDiscount(rule.id, {
        status: rule.status,
      });
    } else if (rule.discountType.toLowerCase() === "shipping discount") {
      // console.log("📡 Updating Shipping Discount...");
      response = await saveShippingData.updateDiscount(rule.id, {
        status: rule.status,
      });
    } else if (rule.discountType.toLowerCase() === "buy x get y") {
      // console.log("📡 Updating Buy X Get Y Discount...");
      response = await saveBuyXGetYData.updateDiscount(rule.id, {
        status: rule.status,
      });
    } else if (rule.discountType.toLowerCase() === "bulk discount") {
      // console.log("📡 Updating Buy BulkDiscount...");
      response = await saveBulkDiscountData.updateDiscount(rule.id, {
        status: rule.status,
      });
    }

    updatedDiscountStatus();
    // console.log("API Response:", response);

    if (!response || !response.success) {
      console.error("❌ API Failed to Update Status:", response);
      return;
    }

    // Refresh the Discount List
    await fetchAllDiscountRules();
  } catch (error) {
    console.error("❌ Status update failed:", error);
  }
};

// Handle Edit Click
const handleEdit = async (rule) => {
  // Capitalize each word, keeping the original delimiter ("/" or " ")
  const formattedSelectedDiscount = rule.discountType
    .split(/([/ ])/)
    .map((word) =>
      word.trim()
        ? word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
        : word
    )
    .join("");

  selectedDiscount.value = structuredClone({
    ...toRaw(rule),
    discountType: formattedSelectedDiscount,
  });
  showModal.value = true;
};

// Handle New Entry
const addNewRule = () => {
  selectedDiscount.value = null;
  showModal.value = true;

  // Emit reset event to AddRuleModal
  setTimeout(() => {
    showModal.value = true;
  }, 50);
};

// Close Modal
const closeModal = () => {
  // Reset everything to default state
  selectedDiscount.value = null;

  // Close modal
  showModal.value = false;
};
</script>

<template>
  <div class="tw-bg-white tw-rounded-[10px] tw-min-h-[250px] tw-border tw-border-gray-300 tw-p-6 tw-m-4">
    <h3 class="tw-text-xl tw-font-bold tw-mb-6">
      {{ __("Discount Rules", "giantwp-discount-rules") }}
    </h3>

    <!-- Button to Add New Rule -->
    <div class="tw-flex tw-mb-8">
      <button
        @click="addNewRule"
        class="tw-inline-flex tw-h-8 tw-items-center tw-justify-center tw-rounded-md tw-px-4 tw-text-sm tw-font-medium tw-duration-200 tw-bg-blue-600 tw-text-white tw-hover:bg-blue-700 tw-focus:outline-none tw-focus:ring-2 tw-focus:ring-blue-500">
        {{ __("Add New Rule", "giantwp-discount-rules") }}
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
