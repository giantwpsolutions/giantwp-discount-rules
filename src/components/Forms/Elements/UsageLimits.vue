<script setup>
import { reactive, defineProps, defineEmits, watch } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";

// **Props & Emits**
const props = defineProps({
  modelValue: {
    type: Object,
    required: true,
    default: () => ({
      enableUsage: false,
      usageLimitsCount: 0,
    }),
  },
});

const emit = defineEmits(["update:modelValue"]);

// ✅ Reactive Local Data
const usageData = reactive({
  enableUsage: props.modelValue.enableUsage ?? false,
  usageLimitsCount: Number(props.modelValue.usageLimitsCount) || 0,
});

// ✅ Sync props with local state
watch(
  () => props.modelValue,
  (newVal) => {
    usageData.enableUsage = newVal.enableUsage ?? false;
    usageData.usageLimitsCount = Number(newVal.usageLimitsCount) || 0;
  },
  { immediate: true, deep: true }
);

// ✅ Emit changes
watch(
  usageData,
  (newVal) => {
    emit("update:modelValue", { ...newVal });
    console.log("🟢 Emitting Usage Limits:", newVal);
  },
  { deep: true }
);
</script>

<template>
  <div class="space-y-4 max-w-64 mb-5">
    <!-- Enable Usage Toggle -->
    <div class="flex items-center gap-2 mt-6 mb-1">
      <el-switch
        v-model="usageData.enableUsage"
        inline-prompt
        :active-text="__('On', 'aio-woodiscount')"
        :inactive-text="__('Off', 'aio-woodiscount')" />
      <label class="text-sm font-medium text-gray-900 flex items-center gap-1">
        {{ __("Enable Usage?", "aio-woodiscount") }}
        <el-tooltip
          effect="dark"
          :content="__('The maximum usage of this coupon.', 'aio-woodiscount')"
          placement="top">
          <QuestionMarkCircleIcon
            class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
        </el-tooltip>
      </label>
    </div>

    <!-- Usage Limit -->
    <div v-if="usageData.enableUsage" class="flex gap-4 items-start">
      <div class="relative flex-1">
        <label
          for="usageLimit"
          class="block text-sm font-medium text-gray-900 my-1">
          {{ __("Usage Limits", "aio-woodiscount") }}
        </label>
        <div class="flex items-center">
          <span
            class="ml-0 h-8 px-2 py-2 border rounded-custom-aio-left text-gray-700 bg-gray-100">
            {{ __("Usage/", "aio-woodiscount") }}0
          </span>
          <input
            v-model.number="usageData.usageLimitsCount"
            type="number"
            id="usageLimit"
            min="0"
            step="1"
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
