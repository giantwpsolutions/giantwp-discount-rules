<script setup>
import { ref } from "vue";

const bogoType = ref("default");
const buyProduct = ref(1);
const getProduct = ref(1);
</script>

<template>
  <div class="space-y-4 max-w-lg">
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
          :value="'default'"
          :label="__('Please select', 'aio-woodiscount')">
          {{ __("Please select", "aio-woodiscount") }}
        </el-option>
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

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
      <!-- Column 1 -->
      <div class="flex items-center space-x-2">
        <!-- Inline Label -->
        <label for="buyProduct" class="text-gray-700"> Buy: </label>
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
        <label for="getProduct" class="text-gray-700"> Get:</label>
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
        <!-- Custom Content -->
        <!-- Add your desired content here -->
      </div>
    </div>
  </div>
</template>

<style scoped></style>
