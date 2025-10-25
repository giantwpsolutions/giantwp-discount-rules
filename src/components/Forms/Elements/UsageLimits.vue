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
  },
  { deep: true }
);
</script>

<template>
  <div class="tw-space-y-4 tw-max-w-64 tw-mb-5">
    <!-- Enable Usage Toggle -->
    <div class="tw-flex tw-items-center tw-gap-2 tw-mt-6 tw-mb-1">
      <el-switch
        v-model="usageData.enableUsage"
        inline-prompt
        :active-text="__('On', 'giantwp-discount-rules')"
        :inactive-text="__('Off', 'giantwp-discount-rules')" />
      <label class="tw-text-sm tw-font-medium tw-text-gray-900 tw-flex tw-items-center tw-gap-1">
        {{ __("Enable Usage?", "giantwp-discount-rules") }}
        <el-tooltip
          effect="dark"
          :content="__('The maximum usage of this coupon.', 'giantwp-discount-rules')"
          placement="top">
          <QuestionMarkCircleIcon
            class="tw-w-4 tw-h-4 tw-text-gray-500 hover:tw-text-gray-700 tw-cursor-pointer" />
        </el-tooltip>
      </label>
    </div>

    <!-- Usage Limit -->
    <div v-if="usageData.enableUsage" class="tw-flex tw-gap-4 tw-items-start">
      <div class="tw-relative tw-flex-1">
        <label
          for="usageLimit"
          class="tw-block tw-text-sm tw-font-medium tw-text-gray-900 tw-my-1">
          {{ __("Usage Limits", "giantwp-discount-rules") }}
        </label>
        <div class="tw-flex tw-items-center">
          <span
            class="tw-ml-0 tw-h-8 tw-px-2 tw-py-2 tw-border tw-rounded-custom-aio-left tw-text-gray-700 tw-bg-gray-100">
            {{ __("Limits", "giantwp-discount-rules") }}
          </span>
          <input
            v-model.number="usageData.usageLimitsCount"
            type="number"
            id="usageLimit"
            min="0"
            step="1"
            class="tw-w-full tw-h-8 tw-rounded-custom-aio-right tw-border-gray-300 tw-shadow-sm tw-sm:tw-text-sm tw-pr-4" />
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
