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
} from "@/data/generalDataFetch";

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
  <div class="tw-space-y-4 tw-max-w-full tw-my-4 tw-border-t tw-border-b tw-py-4">
    <!-- Get Item Selector -->
    <div class="tw-flex tw-flex-wrap tw-gap-2 tw-mt-6 tw-mb-1 tw-w-full sm:tw-w-[30%]">
      <label
        class="tw-text-sm tw-font-medium tw-text-gray-900 tw-flex tw-items-center tw-gap-1 tw-w-full sm:tw-w-[25%]">
        {{ __("Get Item", "giantwp-discount-rules") }}
      </label>
      <div class="tw-w-full tw-sm:w-[75%]">
        <el-select
          v-model="getItem"
          @change="updateGetItem"
          size="default"
          popper-class="custom-dropdown"
          class="tw-w-full">
          <el-option
            :value="'alltogether'"
            :label="__('All together', 'giantwp-discount-rules')" />
          <el-option
            :value="'iq_each'"
            :label="
              __('Item quantity each cart line', 'giantwp-discount-rules')
            " />
        </el-select>
      </div>
    </div>

    <!-- Bulk Discounts -->
    <div
      v-for="(bulkDiscount, index) in bulkDiscounts"
      :key="bulkDiscount.id"
      class="tw-max-w-full tw-pt-4">
      <div class="tw-flex tw-flex-wrap tw-gap-2">
        <!-- From -->
        <div class="tw-w-full sm:tw-w-[12%]">
          <label class="tw-block tw-text-sm tw-font-medium tw-pb-2 tw-text-gray-900">
            {{ __("From", "giantwp-discount-rules") }}
          </label>
          <el-input-number
            v-model="bulkDiscount.fromcount"
            @change="updateBulkDiscount"
            :min="1"
            controls-position="right"
            class="tw-w-full" />
        </div>

        <!-- To -->
        <div class="tw-w-full sm:tw-w-[12%]">
          <label class="tw-block tw-text-sm tw-font-medium tw-pb-2 tw-text-gray-900">
            {{ __("To", "giantwp-discount-rules") }}
          </label>
          <el-input-number
            v-model="bulkDiscount.toCount"
            @change="updateBulkDiscount"
            :min="1"
            controls-position="right"
            class="tw-w-full" />
        </div>

        <!-- Discount Type -->
        <div class="tw-w-full sm:tw-w-[22%]">
          <label class="tw-block tw-text-sm tw-font-medium tw-pb-2 tw-text-gray-900">
            {{ __("Discount Type", "giantwp-discount-rules") }}
          </label>
          <el-select
            v-model="bulkDiscount.discountTypeBulk"
            @change="updateBulkDiscount"
            size="default"
            class="tw-w-full"
            popper-class="custom-dropdown">
            <el-option
              :value="'fixed'"
              :label="__('Fixed', 'giantwp-discount-rules')" />
            <el-option
              :value="'percentage'"
              :label="__('Percentage', 'giantwp-discount-rules')" />
            <el-option
              :value="'flat_price'"
              :label="__('Flat Price', 'giantwp-discount-rules')" />
          </el-select>
        </div>

        <!-- Discount Value -->
        <div class="tw-w-full sm:tw-w-[20%]">
          <label class="tw-block tw-text-sm tw-font-medium tw-pb-2 tw-text-gray-900">
            {{ __("Discount Value", "giantwp-discount-rules") }}
          </label>
          <el-input
            v-model.number="bulkDiscount.discountValue"
            @change="updateBulkDiscount"
            placeholder="Enter value">
            <template #append>
              <span
                v-html="
                  bulkDiscount.discountTypeBulk === 'percentage'
                    ? '%'
                    : generalData.currency_symbol || '$'
                "></span>
            </template>
          </el-input>
        </div>

        <!-- Max Value -->
        <div class="tw-w-full sm:tw-w-[20%]">
          <label class="tw-block tw-text-sm tw-font-medium tw-pb-2 tw-text-gray-900">
            {{ __("Maximum Value", "giantwp-discount-rules") }}
          </label>
          <el-input
            v-model.number="bulkDiscount.maxValue"
            @change="updateBulkDiscount"
            :disabled="
              bulkDiscount.discountTypeBulk === 'fixed' ||
              bulkDiscount.discountTypeBulk === 'flat_price'
            "
            placeholder="Enter value">
            <template #append>
              <span v-html="generalData.currency_symbol || '$'"></span>
            </template>
          </el-input>
        </div>

        <!-- Delete Icon -->
        <div class="tw-w-full sm:tw-w-[10%] tw-flex tw-items-center tw-pt-6">
          <el-icon
            @click="removeDiscount(bulkDiscount.id)"
            size="20px"
            class="tw-cursor-pointer tw-text-red-500">
            <Delete />
          </el-icon>
        </div>
      </div>
    </div>

    <!-- Add Button -->
    <button
      @click="addBulkDiscount"
      class="tw-bg-blue-500 tw-text-white tw-rounded tw-px-4 tw-py-2 tw-hover:bg-blue-600 tw-mt-4">
      {{ __("Assign Bulk Discount", "giantwp-discount-rules") }}
    </button>
  </div>
</template>

<style scoped></style>
