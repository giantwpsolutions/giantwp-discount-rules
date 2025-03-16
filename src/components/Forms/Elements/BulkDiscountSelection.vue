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
    console.log("Child bulk Discount Updated:", newVal);
    emit("update:value", [...newVal]);
  },
  { deep: true } // Single watcher handles all changes
);

// Single debounced watcher for all changes
const emitUpdates = debounce(() => {
  console.log(
    "Child Bulk discount (Debounced):",
    JSON.parse(JSON.stringify(bulkDiscounts.value))
  );
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
    <div class="flex gap-2 mt-6 mb-1 w-[30%]">
      <label
        class="text-sm font-medium text-gray-900 w-[25%] flex items-center gap-1">
        {{ __("Get Item", "all-in-one-woodiscount") }}
      </label>
      <div class="group relative w-full">
        <el-select
          v-model="getItem"
          @change="updateGetItem"
          size="default"
          popper-class="custom-dropdown">
          <el-option
            :value="'alltogether'"
            :label="__('All together', 'all-in-one-woodiscount')">
            {{ __("All together", "all-in-one-woodiscount") }}
          </el-option>
          <el-option
            :value="'iq_each'"
            :label="
              __('Item quantity each cart line', 'all-in-one-woodiscount')
            ">
            {{ __("Item quantity each cart line", "all-in-one-woodiscount") }}
          </el-option>
        </el-select>
      </div>
    </div>

    <!-- Assign Discount -->

    <!-- All Fields  -->
    <div
      v-for="(bulkDiscount, index) in bulkDiscounts"
      :key="bulkDiscount.id"
      class="max-w-full pt-4">
      <!-- Add /Or text -->

      <div class="flex flex-wrap gap-2">
        <!-- Field 1: 15% width -->
        <div class="w-[12%]">
          <label class="block text-sm font-medium pb-2 text-gray-900">
            <div class="flex items-center space-x-1">
              <span>{{ __("From", "all-in-one-woodiscount") }}</span>
              <el-tooltip
                class="box-item"
                effect="dark"
                :content="
                  __(
                    'The maximum value that can be applied',
                    'all-in-one-woodiscount'
                  )
                "
                placement="top"
                popper-class="custom-tooltip">
                <QuestionMarkCircleIcon
                  class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
              </el-tooltip>
            </div>
          </label>
          <el-input-number
            id="buyProductCount"
            v-model="bulkDiscount.fromcount"
            @change="updateBulkDiscount"
            :min="1"
            controls-position="right"
            class="w-full" />
        </div>
        <!-- Field 2: 30% width -->
        <div class="w-[12%]">
          <label class="block text-sm font-medium pb-2 text-gray-900">
            <div class="flex items-center space-x-1">
              <span>{{ __("To", "all-in-one-woodiscount") }}</span>
              <el-tooltip
                class="box-item"
                effect="dark"
                :content="
                  __(
                    'The maximum value that can be applied',
                    'all-in-one-woodiscount'
                  )
                "
                placement="top"
                popper-class="custom-tooltip">
                <QuestionMarkCircleIcon
                  class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
              </el-tooltip>
            </div>
          </label>
          <el-input-number
            id="buyProductCount"
            v-model="bulkDiscount.toCount"
            @change="updateBulkDiscount"
            :min="1"
            controls-position="right"
            class="w-full" />
        </div>

        <!-- Field 3: 30% width -->
        <div class="w-[22%]">
          <label class="block text-sm font-medium pb-2 text-gray-900">
            <div class="flex items-center space-x-1">
              <span>{{ __("Discount Type", "all-in-one-woodiscount") }}</span>
              <el-tooltip
                class="box-item"
                effect="dark"
                :content="
                  __(
                    'The maximum value that can be applied',
                    'all-in-one-woodiscount'
                  )
                "
                placement="top"
                popper-class="custom-tooltip">
                <QuestionMarkCircleIcon
                  class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
              </el-tooltip>
            </div>
          </label>
          <el-select
            v-model="bulkDiscount.discountTypeBulk"
            @change="updateBulkDiscount"
            size="default"
            popper-class="custom-dropdown">
            <el-option
              :value="'fixed'"
              :label="__('Fixed', 'all-in-one-woodiscount')">
              {{ __("Fixed", "all-in-one-woodiscount") }}
            </el-option>
            <el-option
              :value="'percentage'"
              :label="__('Percentage', 'all-in-one-woodiscount')">
              {{ __("Percentage", "all-in-one-woodiscount") }}
            </el-option>
            <el-option
              :value="'flat_price'"
              :label="__('Flat Price', 'all-in-one-woodiscount')">
              {{ __("Flat Price", "all-in-one-woodiscount") }}
            </el-option>
          </el-select>
        </div>

        <!-- Field 4: 25% width -->
        <div class="w-[20%]">
          <label class="block text-sm font-medium pb-2 text-gray-900">
            <div class="flex items-center space-x-1">
              <span>{{ __("Discount Value", "all-in-one-woodiscount") }}</span>
              <el-tooltip
                class="box-item"
                effect="dark"
                :content="
                  __(
                    'The maximum value that can be applied',
                    'all-in-one-woodiscount'
                  )
                "
                placement="top"
                popper-class="custom-tooltip">
                <QuestionMarkCircleIcon
                  class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
              </el-tooltip>
            </div>
          </label>
          <el-input
            v-model.number="bulkDiscount.discountValue"
            @change="updateBulkDiscount"
            style="max-width: 600px"
            placeholder="Please input">
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

        <!-- Field 5: 35% width -->
        <div class="w-[20%]">
          <label class="block text-sm font-medium pb-2 text-gray-900">
            <div class="flex items-center space-x-1">
              <span>{{ __("Maximum Value", "all-in-one-woodiscount") }}</span>
              <el-tooltip
                class="box-item"
                effect="dark"
                :content="
                  __(
                    'The maximum value that can be applied',
                    'all-in-one-woodiscount'
                  )
                "
                placement="top"
                popper-class="custom-tooltip">
                <QuestionMarkCircleIcon
                  class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
              </el-tooltip>
            </div>
          </label>
          <el-input
            v-model.number="bulkDiscount.maxValue"
            @change="updateBulkDiscount"
            style="max-width: 600px"
            placeholder="Please input"
            :disabled="
              bulkDiscount.discountTypeBulk === 'fixed' || 'flat_price'
            ">
            <template #append>
              <span v-html="generalData.currency_symbol || '$'"></span>
            </template>
          </el-input>
        </div>

        <!-- Field 6: 10% width with icons -->
        <div
          class="flex items-center w-[10%] pt-4 gap-4 border-gray-300 rounded">
          <el-icon
            @click="removeDiscount(bulkDiscount.id)"
            size="20px"
            color="red"
            class="cursor-pointer text-red-500">
            <Delete />
          </el-icon>
        </div>
      </div>
    </div>

    <!-- End Assign Discount -->

    <!-- Add Product Assign Button Button -->
    <button
      @click="addBulkDiscount"
      class="bg-blue-500 text-white rounded px-4 py-2 hover:bg-blue-600">
      {{ __("Assign Bulk Discount", "all-in-one-woodiscount") }}
    </button>
  </div>
</template>

<style scoped></style>
