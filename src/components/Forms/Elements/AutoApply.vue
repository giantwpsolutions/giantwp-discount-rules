<script setup>
import { defineProps, defineEmits, ref, watch } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";

// **Props & Emits**
const props = defineProps({
  modelValue: {
    type: Boolean,
    required: false,
    default: false, // ✅ Ensure default value
  },
});

const emit = defineEmits(["update:modelValue"]);

// **Reactive State**
const enableAutoApply = ref(props.modelValue);

// **Sync Prop with Local State**
watch(
  () => props.modelValue,
  (newVal) => {
    enableAutoApply.value = newVal;
  },
  { immediate: true }
);

// **Emit Changes Correctly**
watch(enableAutoApply, (newVal) => {
  emit("update:modelValue", newVal);
});
</script>

<template>
  <div class="tw-space-y-4 tw-w-5/6">
    <!-- Enable Auto Apply -->
    <div class="tw-flex tw-items-center tw-gap-2 tw-mt-6 tw-mb-1">
      <el-switch
        v-model="enableAutoApply"
        inline-prompt
        :active-text="__('On', 'giantwp-discount-rules')"
        :inactive-text="__('Off', 'giantwp-discount-rules')" />
      <label class="tw-text-sm tw-font-medium tw-text-gray-900 tw-flex tw-items-center tw-gap-1">
        {{ __("Enable Auto Apply?", "giantwp-discount-rules") }}

        <div class="tw-group tw-relative">
          <el-tooltip
            class="box-item"
            effect="dark"
            :content="
              __(
                'If all conditions are met, the coupon will be applied automatically.',
                'giantwp-discount-rules'
              )
            "
            placement="top"
            popper-class="custom-tooltip">
            <QuestionMarkCircleIcon
              class="tw-w-4 tw-h-4 tw-text-gray-500 tw-hover:text-gray-700 tw-cursor-pointer" />
          </el-tooltip>
        </div>
      </label>
    </div>
  </div>
</template>
