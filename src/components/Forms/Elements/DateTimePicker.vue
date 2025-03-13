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
    <div class="flex items-center gap-2 my-6">
      <el-switch
        v-model="localEnableSchedule"
        inline-prompt
        :active-text="__('On', 'all-in-one-woodiscount')"
        :inactive-text="__('Off', 'all-in-one-woodiscount')" />
      <label class="text-sm font-medium text-gray-900">
        {{ __("Enable Schedule?", "all-in-one-woodiscount") }}
      </label>
    </div>

    <!-- Date-Time Picker -->
    <div v-if="localEnableSchedule" class="w-full my-6">
      <label class="block text-sm mb-3 font-medium text-gray-900">
        {{ __("Start Date and End Date", "all-in-one-woodiscount") }}
      </label>
      <el-date-picker
        v-model="localScheduleRange"
        type="datetimerange"
        :start-placeholder="__('Start date', 'all-in-one-woodiscount')"
        :end-placeholder="__('End date', 'all-in-one-woodiscount')"
        format="YYYY-MM-DD HH:mm:ss"
        date-format="YYYY/MM/DD ddd"
        time-format="A hh:mm:ss"
        class="my-custom-popper"
        size="large" />
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
