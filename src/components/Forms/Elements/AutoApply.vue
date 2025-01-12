<script setup>
import { ref } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";

const enableAutoApply = ref(false);
</script>

<template>
  <div class="space-y-4 w-5/6">
    <!-- Enable Conditions -->
    <div class="flex items-center gap-2 mt-6 mb-1">
      <el-switch
        v-model="enableAutoApply"
        inline-prompt
        :active-text="__('On', 'aio-woodiscount')"
        :inactive-text="__('Off', 'aio-woodiscount')" />
      <label class="text-sm font-medium text-gray-900 flex items-center gap-1">
        {{ __("Enable Auto Apply?", "aio-woodiscount") }}

        <div class="group relative">
          <el-tooltip
            class="box-item"
            effect="dark"
            :content="
              __(
                'If all conditions are met, the coupon will be applied automatically.',
                'aio-woodiscount'
              )
            "
            placement="top"
            popper-class="custom-tooltip">
            <QuestionMarkCircleIcon
              class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
          </el-tooltip>
        </div>
        <el-tag type="danger" round effect="dark" color="#EF4444">{{
          __("Pro", "aio-woodisocunt")
        }}</el-tag>
      </label>
    </div>
  </div>
</template>
