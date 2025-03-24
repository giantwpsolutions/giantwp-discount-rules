<script setup>
import { onMounted, ref } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";
import {
  licenseKey,
  licenseStatus,
  isLoadingLicense,
  fetchLicenseStatus,
  activateLicense,
  deactivateLicense,
} from "@/data/save-data/licenseApi";

import {
  saveSettingsData,
  loadSettings,
  saveSettings,
  isLoadingSettings,
} from "@/data/save-data/saveSettingsData";

import { settingsUpdate, errorMessage } from "@/data/message.js";

const { __ } = wp.i18n;

const isProActive = ref(false);

onMounted(() => {
  isProActive.value = !!pluginData?.proActive;

  if (isProActive.value) {
    fetchLicenseStatus();
    loadSettings();
  }
});

const handleAction = async () => {
  if (licenseStatus.value === "valid") {
    await deactivateLicense();
  } else {
    await activateLicense(licenseKey.value);
  }
};

const handleSaveSettings = async () => {
  try {
    await saveSettings();
    settingsUpdate();
  } catch (error) {
    errorMessage();
  }
};
</script>

<template>
  <div
    class="bg-white rounded-[10px] min-h-[250px] border border-gray-300 p-6 flex flex-col">
    <h4 class="text-xl font-bold mb-6">
      {{ __("Settings", "dealbuilder-discount-rules") }}
    </h4>

    <!-- License -->
    <div v-if="isProActive">
      <div class="w-full max-w-2xl flex items-center mb-2 gap-3">
        <label class="text-base font-medium text-dark w-32">
          {{ __("License Key", "dealbuilder-discount-rules") }}
        </label>

        <el-input
          v-model="licenseKey"
          style="width: 300px"
          :placeholder="
            __('Enter License Key', 'dealbuilder-discount-rules')
          " />

        <el-button
          :type="licenseStatus === 'valid' ? 'danger' : 'primary'"
          :loading="isLoadingLicense"
          @click="handleAction">
          {{
            licenseStatus === "valid"
              ? __("Deactivate", "dealbuilder-discount-rules")
              : __("Activate", "dealbuilder-discount-rules")
          }}
        </el-button>
      </div>

      <div
        v-if="licenseStatus !== 'unknown'"
        class="pl-32 text-sm mt-1 text-gray-700">
        <template v-if="licenseStatus === 'valid'">
          ✅ {{ __("Your license is active", "dealbuilder-discount-rules") }}
        </template>
        <template v-else>
          ❌
          {{
            __("License is invalid or expired", "dealbuilder-discount-rules")
          }}
        </template>
      </div>
    </div>

    <!-- General Settings -->
    <div class="mt-6">
      <!-- Discount Based On -->
      <div class="w-full max-w-2xl flex items-center mb-6 gap-3">
        <label class="text-base font-medium text-dark w-32">
          {{ __("Rule Apply on", "dealbuilder-discount-rules") }}
        </label>

        <el-select
          v-model="saveSettingsData.discountBasedOn"
          size="default"
          style="width: 240px"
          popper-class="custom-dropdown">
          <el-option
            :value="'regular_price'"
            :label="__('Regular Price', 'dealbuilder-discount-rules')" />
          <el-option
            :value="'sale_price'"
            :label="__('Sale Price', 'dealbuilder-discount-rules')" />
        </el-select>
      </div>

      <!-- Order Page Label -->
      <div class="w-full max-w-2xl flex items-center mb-6 gap-3">
        <label
          class="text-base font-medium text-dark w-32 flex items-center gap-2">
          {{ __("Order Label", "dealbuilder-discount-rules") }}
          <div class="group relative">
            <el-tooltip
              class="box-item"
              effect="dark"
              :content="
                __(
                  'Show a label on admin order page if discount applied',
                  'dealbuilder-discount-rules'
                )
              "
              placement="top"
              popper-class="custom-tooltip">
              <QuestionMarkCircleIcon
                class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
            </el-tooltip>
          </div>
        </label>

        <el-switch
          v-model="saveSettingsData.orderPageLabel"
          inline-prompt
          :active-text="__('On', 'dealbuilder-discount-rules')"
          :inactive-text="__('Off', 'dealbuilder-discount-rules')" />
      </div>

      <!-- Save Settings Button -->
      <div class="mt-4">
        <el-button
          type="primary"
          :loading="isLoadingSettings"
          @click="handleSaveSettings">
          {{ __("Save Settings", "dealbuilder-discount-rules") }}
        </el-button>
      </div>
    </div>
  </div>
</template>
