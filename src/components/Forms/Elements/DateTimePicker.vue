<script setup>
import { ref, watch, defineProps, defineEmits, onMounted } from "vue";

// Props and Emits
const props = defineProps({
  enableSchedule: Boolean,
  scheduleRange: Array,
});

const emit = defineEmits(["update:enableSchedule", "update:scheduleRange"]);

// ✅ Create local refs that are initialized with props
const localEnableSchedule = ref(props.enableSchedule);
const localScheduleRange = ref(props.scheduleRange ?? []);

// ✅ Watch props to update local state if parent changes values
watch(
  () => props.enableSchedule,
  (newVal) => {
    localEnableSchedule.value = newVal;
  }
);

watch(
  () => props.scheduleRange,
  (newVal) => {
    localScheduleRange.value = newVal ?? [];
  }
);

// ✅ Watch local state to emit updates back to parent
watch(localEnableSchedule, (newVal) => {
  emit("update:enableSchedule", newVal);
});

watch(localScheduleRange, (newVal) => {
  emit("update:scheduleRange", newVal);
});

// Ensure the data is set properly when the component is mounted
onMounted(() => {
  if (!props.scheduleRange) {
    localScheduleRange.value = [];
  }
});
</script>

<template>
  <div>
    <!-- Toggle Switch -->
    <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-my-6">
      <div class="tw-shrink-0">
        <el-switch
          v-model="localEnableSchedule"
          inline-prompt
          :active-text="__('On', 'giantwp-discount-rules')"
          :inactive-text="__('Off', 'giantwp-discount-rules')" />
      </div>

      <label class="tw-text-sm tw-font-medium tw-text-gray-900 tw-whitespace-nowrap">
        {{ __("Enable Schedule?", "giantwp-discount-rules") }}
      </label>
    </div>

    <!-- Date-Time Picker -->
    <div v-if="localEnableSchedule" class="tw-w-full tw-my-6">
      <label class="tw-block tw-text-sm tw-mb-3 tw-font-medium tw-text-gray-900">
        {{ __("Start Date and End Date", "giantwp-discount-rules") }}
      </label>
      <el-date-picker
        v-model="localScheduleRange"
        type="datetimerange"
        :start-placeholder="__('Start date', 'giantwp-discount-rules')"
        :end-placeholder="__('End date', 'giantwp-discount-rules')"
        format="YYYY-MM-DD HH:mm:ss"
        date-format="YYYY/MM/DD ddd"
        time-format="A hh:mm:ss"
        class="tw-w-full my-custom-popper"
        size="large" 

  />
        
    </div>
  </div>
</template>

<style>
.my-custom-popper {
  border: 0.5px solid #818181;
}

.el-range-editor--large.el-input__wrapper {
  height: 34px !important;
}
</style>
