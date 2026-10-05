<script setup>
import { reactive, ref, onMounted, watch } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";
import { Delete, CirclePlus } from "@element-plus/icons-vue";
import { debounce } from "lodash";
import {
  generalData,
  isLoadingGeneralData,
  generalDataError,
  loadGeneralData,
} from "@/data/GeneralDataFetch.js";

//Define Props and Emits
const props = defineProps({
  getItem: { type: String, default: "alltogether" },
  value: { type: Array, default: () => [] },
});

// Define Emits
const emit = defineEmits(["update:getItem", "update:value"]);

//Reactive Local State
const getItem = ref(props.getItem);
const bulkDiscounts = ref([...props.value]);

// Sync when parent updates props (e.g. setFormData / template select)
watch(() => props.getItem, (val) => {
  if (val !== getItem.value) getItem.value = val;
});
watch(() => props.value, (val) => {
  if (JSON.stringify(val) !== JSON.stringify(bulkDiscounts.value))
    bulkDiscounts.value = [...val];
}, { deep: true });

// ** Add Bulk Discount Entry **
const addBulkDiscount = (e) => {
  e.preventDefault();

  bulkDiscounts.value.push({
    id: Date.now(),
    fromcount: 1,
    toCount: null,
    discountTypeBulk: "fixed",
    discountValue: null,
    maxValue: null,
  });
};

// Remove a Product Selection option
const removeDiscount = (id) => {
  bulkDiscounts.value = bulkDiscounts.value.filter(
    (discount) => discount.id !== id
  );
};

// **Emit Updated Get Product **
const updateBulkDiscount = () => {
  emit("update:value", [...bulkDiscounts.value]);
};

// **Emit is Repeat **
const updateGetItem = () => {
  emit("update:getItem", getItem.value);
};

watch(
  bulkDiscounts,
  (newVal) => {
    // console.log("Child bulk Discount Updated:", newVal);
    emit("update:value", [...newVal]);
  },
  { deep: true } // Single watcher handles all changes
);

// Single debounced watcher for all changes
const emitUpdates = debounce(() => {
  // console.log(
  //   "Child Bulk discount (Debounced):",
  //   JSON.parse(JSON.stringify(bulkDiscounts.value))
  // );
  emit("update:value", [...bulkDiscounts.value]);
}, 300);

watch(
  [bulkDiscounts, getItem],
  () => {
    emitUpdates();
  },
  { deep: true }
);
</script>

<template>
  <div class="tw-my-4 tw-border-t tw-border-b tw-py-6">
    <!-- Get Item Selector -->
    <div class="tw-flex tw-items-center tw-gap-3 tw-mb-6 tw-w-full sm:tw-w-72">
      <label class="tw-text-sm tw-font-medium tw-text-gray-700 tw-whitespace-nowrap">
        {{ __("Get Item", "giantwp-discount-rules") }}
      </label>
      <el-select
        v-model="getItem"
        @change="updateGetItem"
        size="default"
        popper-class="custom-dropdown"
        class="tw-flex-1">
        <el-option
          :value="'alltogether'"
          :label="__('All together', 'giantwp-discount-rules')" />
        <el-option
          :value="'iq_each'"
          :label="__('Item quantity each cart line', 'giantwp-discount-rules')" />
      </el-select>
    </div>

    <!-- Table -->
    <div class="tw-w-full tw-overflow-x-auto">
      <div class="tw-min-w-[600px]">
        <!-- Header Row -->
        <div class="tw-grid tw-gap-3 tw-mb-2 tw-px-1" style="grid-template-columns: 1fr 1fr 2fr 2fr 2fr 40px;">
          <span class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wide">
            {{ __("From", "giantwp-discount-rules") }}
          </span>
          <span class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wide">
            {{ __("To", "giantwp-discount-rules") }}
          </span>
          <span class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wide">
            {{ __("Discount Type", "giantwp-discount-rules") }}
          </span>
          <span class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wide">
            {{ __("Discount Value", "giantwp-discount-rules") }}
          </span>
          <span class="tw-text-xs tw-font-semibold tw-text-gray-500 tw-uppercase tw-tracking-wide">
            {{ __("Maximum Value", "giantwp-discount-rules") }}
          </span>
          <span></span>
        </div>

        <!-- Data Rows -->
        <div
          v-for="(bulkDiscount, index) in bulkDiscounts"
          :key="bulkDiscount.id"
          class="bulk-row tw-grid tw-gap-3 tw-items-center tw-py-2 tw-px-1 tw-rounded-lg tw-mb-1"
          :class="index % 2 === 0 ? 'tw-bg-gray-50' : 'tw-bg-white'"
          style="grid-template-columns: 1fr 1fr 2fr 2fr 2fr 40px;">
          <!-- From -->
          <el-input-number
            v-model="bulkDiscount.fromcount"
            @change="updateBulkDiscount"
            :min="1"
            size="large"
            controls-position="right"
            class="tw-w-full" />

          <!-- To -->
          <el-input-number
            v-model="bulkDiscount.toCount"
            @change="updateBulkDiscount"
            :min="1"
            size="large"
            controls-position="right"
            class="tw-w-full" />

          <!-- Discount Type -->
          <el-select
            v-model="bulkDiscount.discountTypeBulk"
            @change="updateBulkDiscount"
            size="large"
            class="tw-w-full"
            popper-class="custom-dropdown">
            <el-option :value="'fixed'" :label="__('Fixed', 'giantwp-discount-rules')" />
            <el-option :value="'percentage'" :label="__('Percentage', 'giantwp-discount-rules')" />
            <el-option :value="'flat_price'" :label="__('Flat Price', 'giantwp-discount-rules')" />
          </el-select>

          <!-- Discount Value -->
          <el-input
            v-model.number="bulkDiscount.discountValue"
            @change="updateBulkDiscount"
            size="large"
            :placeholder="__('Enter value', 'giantwp-discount-rules')">
            <template #append>
              <span v-html="bulkDiscount.discountTypeBulk === 'percentage' ? '%' : generalData.currency_symbol || '$'"></span>
            </template>
          </el-input>

          <!-- Max Value -->
          <el-input
            v-model.number="bulkDiscount.maxValue"
            @change="updateBulkDiscount"
            size="large"
            :disabled="bulkDiscount.discountTypeBulk === 'fixed' || bulkDiscount.discountTypeBulk === 'flat_price'"
            :placeholder="__('Enter value', 'giantwp-discount-rules')">
            <template #append>
              <span v-html="generalData.currency_symbol || '$'"></span>
            </template>
          </el-input>

          <!-- Delete -->
          <button
            type="button"
            @click="removeDiscount(bulkDiscount.id)"
            class="tw-flex tw-items-center tw-justify-center tw-w-8 tw-h-8 tw-rounded tw-text-red-400 hover:tw-text-red-600 hover:tw-bg-red-50 tw-transition-colors">
            <el-icon size="16"><Delete /></el-icon>
          </button>
        </div>
      </div>
    </div>

    <!-- Add Button -->
    <button
      @click="addBulkDiscount"
      class="tw-mt-4 tw-inline-flex tw-items-center tw-gap-2 tw-bg-brand-500 hover:tw-bg-brand-600 tw-text-white tw-text-sm tw-font-medium tw-rounded tw-px-4 tw-py-2 tw-transition-colors">
      <el-icon size="14"><CirclePlus /></el-icon>
      {{ __("Assign Bulk Discount", "giantwp-discount-rules") }}
    </button>
  </div>
</template>

<style scoped>
/* el-select size="large" doesn't consistently apply height to its wrapper; force it to match */
:deep(.bulk-row .el-select__wrapper) {
  height: 40px !important;
}
</style>
