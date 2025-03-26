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
  <div class="space-y-4 w-5/6">
    <!-- Enable Auto Apply -->
    <div class="flex items-center gap-2 mt-6 mb-1">
      <el-switch
        v-model="enableAutoApply"
        inline-prompt
        :active-text="__('On', 'giantwp-discount-rules')"
        :inactive-text="__('Off', 'giantwp-discount-rules')" />
      <label class="text-sm font-medium text-gray-900 flex items-center gap-1">
        {{ __("Enable Auto Apply?", "giantwp-discount-rules") }}

        <div class="group relative">
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
              class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
          </el-tooltip>
        </div>
      </label>
    </div>
  </div>
</template>
