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
  <div class="space-y-4 max-w-full my-4 border-t border-b py-4">
    <!-- Get Item Selector -->
    <div class="flex flex-wrap gap-2 mt-6 mb-1 w-full sm:w-[30%]">
      <label
        class="text-sm font-medium text-gray-900 flex items-center gap-1 w-full sm:w-[25%]">
        {{ __("Get Item", "all-in-one-woodiscount") }}
      </label>
      <div class="w-full sm:w-[75%]">
        <el-select
          v-model="getItem"
          @change="updateGetItem"
          size="default"
          popper-class="custom-dropdown"
          class="w-full">
          <el-option
            :value="'alltogether'"
            :label="__('All together', 'all-in-one-woodiscount')" />
          <el-option
            :value="'iq_each'"
            :label="
              __('Item quantity each cart line', 'all-in-one-woodiscount')
            " />
        </el-select>
      </div>
    </div>

    <!-- Bulk Discounts -->
    <div
      v-for="(bulkDiscount, index) in bulkDiscounts"
      :key="bulkDiscount.id"
      class="max-w-full pt-4">
      <div class="flex flex-wrap gap-2">
        <!-- From -->
        <div class="w-full sm:w-[12%]">
          <label class="block text-sm font-medium pb-2 text-gray-900">
            {{ __("From", "all-in-one-woodiscount") }}
          </label>
          <el-input-number
            v-model="bulkDiscount.fromcount"
            @change="updateBulkDiscount"
            :min="1"
            controls-position="right"
            class="w-full" />
        </div>

        <!-- To -->
        <div class="w-full sm:w-[12%]">
          <label class="block text-sm font-medium pb-2 text-gray-900">
            {{ __("To", "all-in-one-woodiscount") }}
          </label>
          <el-input-number
            v-model="bulkDiscount.toCount"
            @change="updateBulkDiscount"
            :min="1"
            controls-position="right"
            class="w-full" />
        </div>

        <!-- Discount Type -->
        <div class="w-full sm:w-[22%]">
          <label class="block text-sm font-medium pb-2 text-gray-900">
            {{ __("Discount Type", "all-in-one-woodiscount") }}
          </label>
          <el-select
            v-model="bulkDiscount.discountTypeBulk"
            @change="updateBulkDiscount"
            size="default"
            class="w-full"
            popper-class="custom-dropdown">
            <el-option
              :value="'fixed'"
              :label="__('Fixed', 'all-in-one-woodiscount')" />
            <el-option
              :value="'percentage'"
              :label="__('Percentage', 'all-in-one-woodiscount')" />
            <el-option
              :value="'flat_price'"
              :label="__('Flat Price', 'all-in-one-woodiscount')" />
          </el-select>
        </div>

        <!-- Discount Value -->
        <div class="w-full sm:w-[20%]">
          <label class="block text-sm font-medium pb-2 text-gray-900">
            {{ __("Discount Value", "all-in-one-woodiscount") }}
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
        <div class="w-full sm:w-[20%]">
          <label class="block text-sm font-medium pb-2 text-gray-900">
            {{ __("Maximum Value", "all-in-one-woodiscount") }}
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
        <div class="w-full sm:w-[10%] flex items-center pt-6">
          <el-icon
            @click="removeDiscount(bulkDiscount.id)"
            size="20px"
            class="cursor-pointer text-red-500">
            <Delete />
          </el-icon>
        </div>
      </div>
    </div>

    <!-- Add Button -->
    <button
      @click="addBulkDiscount"
      class="bg-blue-500 text-white rounded px-4 py-2 hover:bg-blue-600 mt-4">
      {{ __("Assign Bulk Discount", "all-in-one-woodiscount") }}
    </button>
  </div>
</template>

<style scoped></style>
