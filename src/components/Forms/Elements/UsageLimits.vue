<script setup>
import { ref } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";

// Reactive variables
const enableUsage = ref(false);
const usageLimitsCount = ref(null);
</script>

<template>
  <div class="space-y-4 max-w-64 mb-5">
    <!-- Enable Usage Toggle -->
    <div class="flex items-center gap-2 mt-6 mb-1">
      <el-switch
        v-model="enableUsage"
        inline-prompt
        :active-text="__('On', 'aio-woodiscount')"
        :inactive-text="__('Off', 'aio-woodiscount')" />
      <label class="text-sm font-medium text-gray-900 flex items-center gap-1">
        {{ __("Enable Usage?", "aio-woodiscount") }}
        <div class="group relative">
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
    </div>

    <!-- Usage Limit -->
    <div v-if="enableUsage" class="flex gap-4 items-start">
      <div class="relative flex-1">
        <label
          for="usageLimit"
          class="block text-sm font-medium text-gray-900 my-1">
          {{ __("Usage Limits", "aio-woodiscount") }}
        </label>
        <div class="flex items-center">
          <!-- Prefix -->
          <span
            class="ml-0 h-8 px-2 py-2 border rounded-custom-aio-left text-gray-700 bg-gray-100">
            Usage/{{ 0 }}
          </span>
          <!-- Input Field -->
          <input
            v-model="usageLimitsCount"
            type="number"
            id="usageLimit"
            :placeholder="__('Enter Usage Limit', 'aio-woodiscount')"
            class="w-full h-8 rounded-custom-aio-right border-gray-300 shadow-sm sm:text-sm pr-4" />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.rounded-custom-aio-left {
  border-radius: 5px 0px 0px 5px;
}

.rounded-custom-aio-right {
  border-radius: 0px 5px 5px 0px;
}
</style>
