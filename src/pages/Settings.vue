<script setup>
import { onMounted, ref } from "vue";
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid";
import { CircleCheckFilled, CircleCloseFilled } from '@element-plus/icons-vue';
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
  isProActive.value = !!gwpdrPluginData?.proActive;

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

const primeKitUrl = `${gwpdrPluginData.pluginUrl}assets/images/primekit.png`;
const quickCartLogo = `${gwpdrPluginData.pluginUrl}assets/images/quickcartshopping.png`;
const primeKitSearch = gwpdrPluginData.primekit_search_url
</script>
<template>
  <!-- Outer wrapper: adds spacing and light background -->
  <div class="tw-px-8 tw-py-4">
    <!-- FLEX: now responsive (column on mobile, row on lg+) -->
    <div class="tw-flex tw-flex-col lg:tw-flex-row tw-gap-6">

      <!-- LEFT COLUMN: main settings -->
      <div class="tw-flex-1">
        <div
          class="tw-bg-white tw-rounded-[10px] tw-min-h-[250px] tw-border tw-border-gray-300 tw-p-6 tw-flex tw-flex-col tw-shadow-sm"
        >
          <h4 class="tw-text-xl tw-font-bold tw-mb-6">
            {{ __("Settings", "giantwp-discount-rules") }}
          </h4>

          <!-- License -->
          <div v-if="isProActive">
            <div class="tw-w-full tw-max-w-2xl tw-flex tw-flex-col md:tw-flex-row tw-items-start md:tw-items-center tw-mb-2 tw-gap-3">
              <label class="tw-text-base tw-font-medium tw-text-dark tw-w-32">
                {{ __("License Key", "giantwp-discount-rules") }}
              </label>

              <el-input
                v-model="licenseKey"
                class="tw-w-full md:tw-w-auto"
                style="width: 300px"
                :placeholder="__('Enter License Key', 'giantwp-discount-rules')"
              />

              <el-button
                :type="licenseStatus === 'valid' ? 'danger' : 'primary'"
                :loading="isLoadingLicense"
                @click="handleAction"
              >
                {{
                  licenseStatus === "valid"
                    ? __("Deactivate", "giantwp-discount-rules")
                    : __("Activate", "giantwp-discount-rules")
                }}
              </el-button>
            </div>

            <div
              v-if="licenseStatus !== 'unknown'"
              class="md:tw-pl-32 tw-text-sm tw-mt-1 tw-text-gray-700 tw-flex tw-items-center tw-gap-1"
            >
              <template v-if="licenseStatus === 'valid'">
                <el-icon color="#22c55e"><CircleCheckFilled /></el-icon>
                {{ __("Your license is active", "giantwp-discount-rules") }}
              </template>
              <template v-else>
                <el-icon color="#ef4444"><CircleCloseFilled /></el-icon>
                {{ __("License is invalid or expired", "giantwp-discount-rules") }}
              </template>
            </div>
          </div>

          <!-- General Settings -->
          <div class="tw-mt-6">

            <!-- Discount Based On -->
            <div class="tw-w-full tw-max-w-2xl tw-flex tw-flex-col md:tw-flex-row tw-items-start md:tw-items-center tw-mb-6 tw-gap-3">
              <label class="tw-text-base tw-font-medium tw-text-dark tw-w-32">
                {{ __("Rule Apply on", "giantwp-discount-rules") }}
              </label>

              <el-select
                v-model="saveSettingsData.discountBasedOn"
                size="default"
                style="width: 240px"
                popper-class="custom-dropdown"
              >
                <el-option
                  :value="'regular_price'"
                  :label="__('Regular Price', 'giantwp-discount-rules')"
                />
                <el-option
                  :value="'sale_price'"
                  :label="__('Sale Price', 'giantwp-discount-rules')"
                />
              </el-select>
            </div>

            <!-- Order Page Label -->
            <div class="tw-w-full tw-max-w-2xl tw-flex tw-flex-col md:tw-flex-row tw-items-start md:tw-items-center tw-mb-6 tw-gap-3">
              <label
                class="tw-text-base tw-font-medium tw-text-dark tw-w-32 tw-flex tw-items-center tw-gap-2"
              >
                {{ __("Order Label", "giantwp-discount-rules") }}
                <div class="tw-group tw-relative">
                  <el-tooltip
                    class="box-item"
                    effect="dark"
                    :content="
                      __(
                        'Show a label on admin order page if discount applied',
                        'giantwp-discount-rules'
                      )
                    "
                    placement="top"
                    popper-class="custom-tooltip"
                  >
                    <QuestionMarkCircleIcon
                      class="tw-w-4 tw-h-4 tw-text-gray-500 tw-hover:text-gray-700 tw-cursor-pointer"
                    />
                  </el-tooltip>
                </div>
              </label>

              <el-switch
                v-model="saveSettingsData.orderPageLabel"
                inline-prompt
                :active-text="__('On', 'giantwp-discount-rules')"
                :inactive-text="__('Off', 'giantwp-discount-rules')"
              />
            </div>

            <!-- Upsell Notification Widget -->
            <div class="tw-w-full tw-max-w-2xl tw-flex tw-flex-col md:tw-flex-row tw-items-start md:tw-items-center tw-mb-6 tw-gap-3">
              <label
                class="tw-text-base tw-font-medium tw-text-dark tw-w-32 tw-flex tw-items-center tw-gap-2"
              >
                {{ __("Upsell Notification", "giantwp-discount-rules") }}
                <div class="tw-group tw-relative">
                  <el-tooltip
                    class="box-item"
                    effect="dark"
                    :content="
                      __(
                        'Show a small notification at the bottom for upsell',
                        'giantwp-discount-rules'
                      )
                    "
                    placement="top"
                    popper-class="custom-tooltip"
                  >
                    <QuestionMarkCircleIcon
                      class="tw-w-4 tw-h-4 tw-text-gray-500 tw-hover:text-gray-700 tw-cursor-pointer"
                    />
                  </el-tooltip>
                </div>
              </label>

              <el-switch
                v-model="saveSettingsData.upsellNotificationWidget"
                inline-prompt
                :active-text="__('On', 'giantwp-discount-rules')"
                :inactive-text="__('Off', 'giantwp-discount-rules')"
              />
            </div>

            <!-- Save Settings Button -->
            <div class="tw-mt-4">
              <el-button
                type="primary"
                :loading="isLoadingSettings"
                @click="handleSaveSettings"
              >
                {{ __("Save Settings", "giantwp-discount-rules") }}
              </el-button>
            </div>
          </div>
        </div>
      </div>

      <!-- RIGHT COLUMN: Our Other Plugins -->
      <!-- responsive width: full on mobile, fixed on desktop -->
      <div class="tw-w-full lg:tw-w-[320px] tw-flex tw-flex-col tw-gap-6">

        <div
          class="tw-bg-white tw-rounded-[10px] tw-border tw-border-gray-300 tw-p-4 tw-flex tw-flex-col tw-gap-4 tw-shadow-sm"
        >
          <div class="tw-text-md tw-font-semibold tw-text-gray-900">
            {{ __("Our Other Plugins", "giantwp-discount-rules") }}
          </div>

          <!-- PrimeKit -->
          <div class="tw-flex tw-items-start tw-gap-3">
            <div
              class="tw-w-10 tw-h-10 tw-rounded tw-bg-gray-100 tw-flex tw-items-center tw-justify-center tw-text-[10px] tw-font-semibold tw-text-gray-700"
            >
              <img :src="primeKitUrl" />
            </div>

            <div class="tw-flex-1">
              <div class="tw-text-sm tw-font-semibold tw-text-gray-900">
                PrimeKit Addons
              </div>
              <div class="tw-text-[12px] tw-leading-snug tw-text-gray-600">
                {{ __('UI addons / enhancements for Elementor Page builders.', 'giantwp-discount-rules') }}
              </div>
              <div class="tw-mt-2">
                <a
                  :href="primeKitSearch"
                  target="_blank"
                  class="tw-text-[12px] tw-font-medium tw-text-blue-600 hover:tw-text-blue-700"
                >
                  Install
                </a>
              </div>
            </div>
          </div>

          <!-- Quick Cart Shopping -->
          <div class="tw-flex tw-items-start tw-gap-3">
            <div
              class="tw-w-10 tw-h-10 tw-rounded tw-bg-gray-100 tw-flex tw-items-center tw-justify-center tw-text-[10px] tw-font-semibold tw-text-gray-700"
            >
              <img :src="quickCartLogo">
            </div>

            <div class="tw-flex-1">
              <div class="tw-text-sm tw-font-semibold tw-text-gray-900">
                Quick Cart Shopping
                <span
                  class="tw-ml-2 tw-inline-block tw-text-[10px] tw-px-2 tw-py-[2px] tw-rounded tw-bg-yellow-100 tw-text-yellow-700 tw-font-medium"
                >
                  {{ __('UpComing', 'giantwp-discount-rules') }}
                </span>
              </div>
              <div class="tw-text-[12px] tw-leading-snug tw-text-gray-600">
                {{ __('Woocommerce UI and Shopping Experience Booster', 'giantwp-discount-rules') }}
              </div>
              <div class="tw-mt-2">
                <a
                  href="https://www.giantwpsolutions.com/"
                  target="_blank"
                  class="tw-text-[12px] tw-font-medium tw-text-grey-600"
                >
                  {{ __('Coming Soon', 'giantwp-discount-rules') }}
                </a>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>
</template>
