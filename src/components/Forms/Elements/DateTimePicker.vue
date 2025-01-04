<script setup>
import { ref } from 'vue';

const enableSchedule = ref(false); // Controls the toggle state
const value2 = ref(''); // Holds the date-time picker range
</script>

<template>
  <div class="space-y-4 w-2xl">
    <!-- Toggle Switch -->
    <div class="flex items-center gap-2 my-6">
      <el-switch v-model="enableSchedule" inline-prompt :active-text="__( 'On', 'aio-woodiscount')" :inactive-text="__( 'Off', 'aio-woodiscount')" />
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
        class="my-custom-popper"
        size="large"
      />
    </div>
  </div>
</template>



<style >
.my-custom-popper{
  border: 0.5px solid #818181;

}


</style>
