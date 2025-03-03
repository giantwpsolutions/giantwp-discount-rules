<script setup>
import { reactive, defineProps, defineEmits, onMounted, watch } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";
import { generalData, loadGeneralData } from "@/data/generalDataFetch";

onMounted(() => {
  loadGeneralData();
});

// Props & Emits
const props = defineProps({
  buyProductCount: { type: Number, default: 1 },
  getProductCount: { type: Number, default: 1 },
  freeOrDiscount: { type: String, default: "freeproduct" },
  isRepeat: { type: Boolean, default: true },
  discounttypeBogo: { type: String, default: "fixed" },
  discountValue: { type: Number, default: null },
  maxValue: { type: Number, default: null },
});

// Define Emits
const emit = defineEmits([
  "update:buyProductCount",
  "update:getProductCount",
  "update:freeOrDiscount",
  "update:isRepeat",
  "update:discounttypeBogo",
  "update:discountValue",
  "update:maxValue",
]);

// Reactive Local State
const localState = reactive({
  buyProductCount: props.buyProductCount,
  getProductCount: props.getProductCount,
  freeOrDiscount: props.freeOrDiscount,
  isRepeat: props.isRepeat,
  discounttypeBogo: props.discounttypeBogo,
  discountValue: props.discountValue,
  maxValue: props.maxValue,
});

// Watch for Prop Changes
watch(
  () => props,
  (newProps) => {
    Object.assign(localState, newProps);
  },
  { deep: true, immediate: true }
);

// Watch for Local State Changes and Emit Updates
watch(
  localState,
  (newState) => {
    emit("update:buyProductCount", newState.buyProductCount);
    emit("update:getProductCount", newState.getProductCount);
    emit("update:freeOrDiscount", newState.freeOrDiscount);
    emit("update:isRepeat", newState.isRepeat);
    emit("update:discounttypeBogo", newState.discounttypeBogo);
    emit("update:discountValue", Number(newState.discountValue));
    emit("update:maxValue", Number(newState.maxValue));
  },
  { deep: true }
);
</script>

<template>
  <div class="space-y-4 max-w-full">
    <!-- Pricing or product set -->
    <div class="w-1/2">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <!-- Column 1 -->
        <div class="flex items-center space-x-2">
          <label for="buyProductCount" class="text-gray-950 text-sm">
            {{ __("Buy:", "aio-woodiscount") }}
          </label>
          <el-input-number
            id="buyProductCount"
            v-model="localState.buyProductCount"
            :min="1"
            :max="10"
            controls-position="right"
            class="w-full" />
        </div>

        <!-- Column 2 -->
        <div class="flex items-center space-x-2">
          <label for="getProductCount" class="text-gray-950 text-sm">
            {{ __("Get:", "aio-woodiscount") }}
          </label>
          <el-input-number
            id="getProductCount"
            v-model="localState.getProductCount"
            :min="1"
            :max="10"
            controls-position="right"
            class="w-full" />
        </div>

        <!-- Column 3 -->
        <div>
          <el-radio-group v-model="localState.freeOrDiscount">
            <el-radio-button
              :label="__('Free', 'aio-woodiscount')"
              value="freeproduct" />
            <el-radio-button
              :label="__('Discount', 'aio-woodiscount')"
              value="discount_product" />
          </el-radio-group>
        </div>
      </div>
    </div>

    <!-- Fixed or percentage discount for BOGO -->
    <div
      v-if="localState.freeOrDiscount === 'discount_product'"
      class="w-3/4 mt-5">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <!-- Column 1 -->
        <div>
          <label class="block text-sm font-medium pb-2 text-gray-900">
            {{ __("Pricing Type", "aio-woodiscount") }}
          </label>
          <el-select
            v-model="localState.discounttypeBogo"
            size="default"
            popper-class="custom-dropdown">
            <el-option
              :value="'fixed'"
              :label="__('Fixed Discount', 'aio-woodiscount')">
              {{ __("Fixed Discount", "aio-woodiscount") }}
            </el-option>
            <el-option
              :value="'percentage'"
              :label="__('Percentage Discount', 'aio-woodiscount')">
              {{ __("Percentage Discount", "aio-woodiscount") }}
            </el-option>
          </el-select>
        </div>

        <!-- Column 2 -->
        <div>
          <label class="block text-sm font-medium pb-2 text-gray-900">
            {{ __("Pricing Value", "aio-woodiscount") }}
          </label>
          <el-input
            v-model.number="localState.discountValue"
            style="max-width: 600px"
            placeholder="Please input">
            <template #append>
              <span
                v-html="
                  localState.discounttypeBogo === 'percentage'
                    ? '%'
                    : generalData.currency_symbol || '$'
                "></span>
            </template>
          </el-input>
        </div>

        <!-- Column 3 -->
        <div>
          <label class="block text-sm font-medium pb-2 text-gray-900">
            <div class="flex items-center space-x-1">
              <span>{{ __("Maximum Value", "aio-woodiscount") }}</span>
              <el-tooltip
                class="box-item"
                effect="dark"
                :content="
                  __(
                    'The maximum usage of this coupon. Once the discount rule is applied in the completed order, it is counted as a discount use.',
                    'aio-woodiscount'
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
            v-model.number="localState.maxValue"
            style="max-width: 600px"
            placeholder="Please input"
            :disabled="localState.discounttypeBogo === 'fixed'">
            <template #append>
              <span v-html="generalData.currency_symbol || '$'"></span>
            </template>
          </el-input>
        </div>
      </div>
    </div>

    <!-- Is Repeat -->
    <div class="flex items-center gap-2 mt-6 mb-1">
      <el-switch
        v-model="localState.isRepeat"
        inline-prompt
        :active-text="__('On', 'aio-woodiscount')"
        :inactive-text="__('Off', 'aio-woodiscount')" />
      <label class="text-sm font-medium text-gray-900 flex items-center gap-1">
        {{ __("Is Repeat?", "aio-woodiscount") }}
        <el-tooltip
          class="box-item"
          effect="dark"
          :content="
            __(
              'Discount will apply once or repeat after each quantities matching',
              'aio-woodiscount'
            )
          "
          placement="top"
          popper-class="custom-tooltip">
          <QuestionMarkCircleIcon
            class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
        </el-tooltip>
      </label>
    </div>
  </div>
</template>

<style scoped></style>
