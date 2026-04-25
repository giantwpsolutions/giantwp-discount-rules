<!-- Discount.vue -->
<script setup>
// All Imports
import { ref, onMounted, toRaw, computed } from "vue";
import { TagIcon, BoltIcon, SignalIcon, StarIcon } from "@heroicons/vue/24/outline";
import Sidebar from "../components/Sidebar.vue";
import DiscountTable from "../components/DiscountTable.vue";
import AddRuleModal from "../components/Modals/AddRuleModal.vue";
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

const totalRules  = computed(() => discountRules.value.length);
const activeCount = computed(() => discountRules.value.filter(r => r.status === 'on').length);
const inactiveCount = computed(() => discountRules.value.filter(r => r.status === 'off').length);
const totalUsed  = computed(() => discountRules.value.reduce((sum, r) => sum + (Number(r.usedCount) || 0), 0));
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
  <div class="tw-flex tw-gap-4 tw-m-4 tw-items-start">

  <!-- Main Content -->
  <div class="tw-flex-1 tw-min-w-0 tw-bg-white tw-rounded-[10px] tw-border tw-border-gray-300 tw-p-6">
    <h3 class="tw-text-xl tw-font-bold tw-mb-6">
      {{ __("Discount Rules", "giantwp-discount-rules") }}
    </h3>

    <!-- Stats Bar -->
    <div class="tw-grid tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-4 tw-mb-6">
      <!-- Total Rules -->
      <div class="tw-flex tw-items-center tw-gap-4 tw-rounded-xl tw-border tw-border-gray-200 tw-bg-white tw-p-4 tw-shadow-sm">
        <div class="tw-flex tw-h-12 tw-w-12 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-lg tw-bg-blue-50">
          <TagIcon class="tw-h-6 tw-w-6 tw-text-blue-500" />
        </div>
        <div>
          <p class="tw-text-2xl tw-font-bold tw-leading-tight tw-text-gray-800">{{ totalRules }}</p>
          <p class="tw-text-sm tw-text-gray-500">{{ __("Total Rules", "giantwp-discount-rules") }}</p>
        </div>
      </div>

      <!-- Active -->
      <div class="tw-flex tw-items-center tw-gap-4 tw-rounded-xl tw-border tw-border-gray-200 tw-bg-white tw-p-4 tw-shadow-sm">
        <div class="tw-flex tw-h-12 tw-w-12 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-lg tw-bg-green-50">
          <BoltIcon class="tw-h-6 tw-w-6 tw-text-green-500" />
        </div>
        <div>
          <p class="tw-text-2xl tw-font-bold tw-leading-tight tw-text-gray-800">{{ activeCount }}</p>
          <p class="tw-text-sm tw-text-gray-500">{{ __("Active", "giantwp-discount-rules") }}</p>
        </div>
      </div>

      <!-- Inactive -->
      <div class="tw-flex tw-items-center tw-gap-4 tw-rounded-xl tw-border tw-border-gray-200 tw-bg-white tw-p-4 tw-shadow-sm">
        <div class="tw-flex tw-h-12 tw-w-12 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-lg tw-bg-yellow-50">
          <SignalIcon class="tw-h-6 tw-w-6 tw-text-yellow-500" />
        </div>
        <div>
          <p class="tw-text-2xl tw-font-bold tw-leading-tight tw-text-gray-800">{{ inactiveCount }}</p>
          <p class="tw-text-sm tw-text-gray-500">{{ __("Inactive", "giantwp-discount-rules") }}</p>
        </div>
      </div>

      <!-- Total Used -->
      <div class="tw-flex tw-items-center tw-gap-4 tw-rounded-xl tw-border tw-border-gray-200 tw-bg-white tw-p-4 tw-shadow-sm">
        <div class="tw-flex tw-h-12 tw-w-12 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-lg tw-bg-purple-50">
          <StarIcon class="tw-h-6 tw-w-6 tw-text-purple-500" />
        </div>
        <div>
          <p class="tw-text-2xl tw-font-bold tw-leading-tight tw-text-gray-800">{{ totalUsed }}</p>
          <p class="tw-text-sm tw-text-gray-500">{{ __("Total Used", "giantwp-discount-rules") }}</p>
        </div>
      </div>
    </div>

    <!-- Discount Table -->
    <DiscountTable
      :discountRules="discountRules"
      :onAdd="addNewRule"
      :onEdit="handleEdit"
      :onDelete="deleteRule"
      :onToggleStatus="toggleStatus" />

    <!-- Modal Component -->
    <AddRuleModal
      :visible="showModal"
      :editingRule="selectedDiscount"
      @close="closeModal"
      @discountUpdated="fetchDiscountRules" />
  </div>

  <!-- Sidebar -->
  <div class="tw-w-64 tw-shrink-0">
    <Sidebar />
  </div>

  </div>
</template>
