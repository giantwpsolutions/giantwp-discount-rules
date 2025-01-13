<script setup>
import { ref, onMounted } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";
import {
  generalData,
  isLoadingGeneralData,
  generalDataError,
  loadGeneralData,
} from "@/data/generalDataFetch";

onMounted(() => {
  loadGeneralData();
});

const bogoType = ref("bogosame");
const buyProduct = ref(1);
const getProduct = ref(1);
const freeOrDiscount = ref("freeproduct");
const isRepeat = ref(true);
const discounttypeBogo = ref("fixedBogo");
</script>

<template>
  <div class="space-y-4 max-w-full">
    <div class="w-full max-w-md mb-6">
      <label
        for="bogotype"
        class="block text-sm font-medium pb-2 text-gray-900">
        {{ __("Bogo Type", "aio-woodiscount") }}
      </label>
      <el-select
        v-model="bogoType"
        placeholder="Please select"
        size="default"
        popper-class="custom-dropdown">
        <el-option
          :value="'bogosame'"
          :label="__('Buy One Get One (Same Product)', 'aio-woodiscount')">
          {{ __("Buy One Get One (Same Product)", "aio-woodiscount") }}
        </el-option>
        <el-option
          :value="'bogoxy'"
          :label="__('Buy X Get Y', 'aio-woodiscount')">
          <template #default>
            {{ __("Buy X Get Y", "aio-woodiscount") }}
            <el-tag type="danger" round effect="dark" color="#EF4444">{{
              __("Pro", "aio-woodisocunt")
            }}</el-tag>
          </template>
        </el-option>
      </el-select>
    </div>

    <!-- Pricing or product set -->
    <div v-if="bogoType === 'bogosame'" class="w-1/2">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <!-- Column 1 -->
        <div class="flex items-center space-x-2">
          <!-- Inline Label -->
          <label for="buyProduct" class="text-gray-950 text-sm">
            {{ __("Buy:", "aio-woodiscount") }}
          </label>
          <!-- el-input-number Component -->
          <el-input-number
            id="buyProduct"
            v-model="buyProduct"
            :min="1"
            :max="10"
            controls-position="right"
            @change="handleChange"
            class="w-full" />
        </div>

        <!-- Column 2 -->
        <div class="flex items-center space-x-2">
          <!-- Inline Label -->
          <label for="getProduct" class="text-gray-950 text-sm"
            >{{ __("Get:", "aio-woodiscount") }}
          </label>
          <!-- el-input-number Component -->
          <el-input-number
            id="getProduct"
            v-model="getProduct"
            :min="1"
            :max="10"
            controls-position="right"
            @change="handleChange"
            class="w-full" />
        </div>

        <!-- Column 3 -->
        <div>
          <el-radio-group v-model="freeOrDiscount">
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

    <!-- Fixed or percentage discount for bogo -->
    <div v-if="freeOrDiscount === 'discount_product'" class="w-3/4 mt-5">
      <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <!-- Column 1 -->
        <div>
          <label
            for="discounttypeBogo"
            class="block text-sm font-medium pb-2 text-gray-900">
            {{ __("Pricing Type", "aio-woodiscount") }}
          </label>
          <el-select
            v-model="discounttypeBogo"
            size="default"
            popper-class="custom-dropdown">
            <el-option
              :value="'fixedBogo'"
              :label="__('Fixed Discount', 'aio-woodiscount')">
              {{ __("Fixed Discount", "aio-woodiscount") }}
            </el-option>
            <el-option
              :value="'percentageBogo'"
              :label="__('Percentage Discount', 'aio-woodiscount')">
              {{ __("Percentage Discount", "aio-woodiscount") }}
            </el-option>
          </el-select>
        </div>

        <!-- Column 2 -->
        <div>
          <!-- Inline Label -->
          <label
            for="input2"
            class="block text-sm font-medium pb-2 text-gray-900"
            >{{ __("Pricing value", "aio-woodiscount") }}</label
          >
          <el-input
            v-model="input2"
            style="max-width: 600px"
            placeholder="Please input">
            <template #append>
              <span
                v-html="
                  discounttypeBogo === 'percentageBogo'
                    ? '%'
                    : generalData.currency_symbol || '$'
                "></span
            ></template>
          </el-input>
        </div>

        <!-- Column 3 -->
        <div>
          <label
            for="input2"
            class="block text-sm font-medium pb-2 text-gray-900">
            <div class="flex items-center space-x-1">
              <!-- Label Text -->
              <span>{{ __("Maximum Value", "aio-woodiscount") }}</span>
              <!-- Tooltip Icon -->
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
          <!-- el-input Component -->
          <el-input
            v-model="input2"
            style="max-width: 600px"
            placeholder="Please input"
            :disabled="discounttypeBogo === 'fixedBogo'">
            <template #append>
              <span v-html="generalData.currency_symbol || '$'"></span>
            </template>
          </el-input>
        </div>
      </div>
    </div>

    <!-- will it be repeated -->
    <div class="flex items-center gap-2 mt-6 mb-1">
      <el-switch
        v-model="isRepeat"
        inline-prompt
        :active-text="__('On', 'aio-woodiscount')"
        :inactive-text="__('Off', 'aio-woodiscount')" />
      <label class="text-sm font-medium text-gray-900 flex items-center gap-1">
        {{ __("Is Repeat?", "aio-woodiscount") }}
        <div class="group relative">
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
        </div>
      </label>
    </div>
  </div>
</template>

<style scoped></style>
