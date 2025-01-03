<script setup>
import { ref } from 'vue';

const enableSchedule = ref(false); // Controls the toggle state
const value2 = ref(''); // Holds the date-time picker range
</script>

<template>
  <div class="space-y-4 max-w-lg">
    <!-- Toggle Switch -->
    <div class="flex items-center gap-2 my-6">
      <el-switch v-model="enableSchedule" />
      <label class="text-sm font-medium text-gray-900">
        {{ __('Enable Schedule?', 'aio-woodiscount') }}
      </label>
    </div>

    <!-- Date-Time Picker -->
    <div v-if="enableSchedule" class="w-full my-6">
      <label for="discountValue" class="block text-sm mb-3 font-medium text-gray-900">
        {{ __('Start Date and End Date', 'aio-woodiscount') }}
      </label>
      <el-date-picker
        v-model="value2"
        type="datetimerange"
        :start-placeholder="__('Start date', 'aio-woodiscount')"
        :end-placeholder="__('End date', 'aio-woodiscount')"
        format="YYYY-MM-DD HH:mm:ss"
        date-format="YYYY/MM/DD ddd"
        time-format="A hh:mm:ss"
        class="aio-height"
      />
    </div>
  </div>
</template>



<style scoped>
/* Custom styles for date picker */
.el-input__inner {
  border: 2px solid black !important;
  padding: 10px !important;
}

.el-input__inner::placeholder {
  color: gray !important;
  font-size: 14px !important;
}

.aio-height .el-date-editor--datetimerange {
  height: auto !important;
}
</style>
