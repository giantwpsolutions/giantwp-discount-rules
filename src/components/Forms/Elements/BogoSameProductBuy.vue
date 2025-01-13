<script setup>
import { ref } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";
</script>

<template>
  <div class="space-y-4 max-w-full my-6 border-t py-4">
    <h3 class="text-base text-gray-950">
      <div class="inline-flex items-center space-x-1">
        <!-- Heading Text -->
        <span>{{ __("Buy Product", "aio-woodiscount") }}</span>
        <!-- Tooltip Icon -->
        <el-tooltip
          class="box-item"
          effect="dark"
          :content="
            __('Which product will receive the BOGO rule?', 'aio-woodiscount')
          "
          placement="top"
          popper-class="custom-tooltip">
          <QuestionMarkCircleIcon
            class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
        </el-tooltip>
      </div>
    </h3>
  </div>
</template>
