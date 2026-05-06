<script setup>
import { onMounted, ref } from "vue";
import { QuestionMarkCircleIcon, Cog6ToothIcon, CheckIcon } from "@heroicons/vue/24/outline";
import { CircleCheckFilled, CircleCloseFilled } from '@element-plus/icons-vue';
import Sidebar from "../components/Sidebar.vue";
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
  loadSettings();
  if (isProActive.value) {
    fetchLicenseStatus();
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
  <div class="tw-flex tw-gap-4 tw-m-4 tw-items-start">

    <!-- Main Settings -->
    <div class="tw-flex-1 tw-min-w-0">

      <!-- License (Pro only) -->
      <div v-if="isProActive" class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-mb-4 tw-overflow-hidden">
        <!-- Section header -->
        <div class="tw-flex tw-items-center tw-gap-3 tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
          <div class="tw-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-lg tw-bg-blue-50">
            <Cog6ToothIcon class="tw-h-5 tw-w-5 tw-text-blue-500" />
          </div>
          <div>
            <p class="tw-text-sm tw-font-bold tw-text-gray-800 tw-leading-tight">{{ __("License", "giantwp-discount-rules") }}</p>
            <p class="tw-text-xs tw-text-gray-400">{{ __("Manage your pro license key", "giantwp-discount-rules") }}</p>
          </div>
        </div>

        <!-- License row -->
        <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4">
          <div>
            <p class="tw-text-sm tw-font-semibold tw-text-gray-800">{{ __("License Key", "giantwp-discount-rules") }}</p>
            <div v-if="licenseStatus !== 'unknown'" class="tw-flex tw-items-center tw-gap-1 tw-mt-0.5">
              <template v-if="licenseStatus === 'valid'">
                <el-icon color="#22c55e"><CircleCheckFilled /></el-icon>
                <span class="tw-text-xs tw-text-green-600">{{ __("Your license is active", "giantwp-discount-rules") }}</span>
              </template>
              <template v-else>
                <el-icon color="#ef4444"><CircleCloseFilled /></el-icon>
                <span class="tw-text-xs tw-text-red-500">{{ __("License is invalid or expired", "giantwp-discount-rules") }}</span>
              </template>
            </div>
          </div>
          <div class="tw-flex tw-items-center tw-gap-2">
            <el-input
              v-model="licenseKey"
              style="width: 240px"
              :placeholder="__('Enter License Key', 'giantwp-discount-rules')"
            />
            <el-button
              :type="licenseStatus === 'valid' ? 'danger' : 'primary'"
              :loading="isLoadingLicense"
              @click="handleAction"
            >
              {{ licenseStatus === "valid" ? __("Deactivate", "giantwp-discount-rules") : __("Activate", "giantwp-discount-rules") }}
            </el-button>
          </div>
        </div>
      </div>

      <!-- General Settings -->
      <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-overflow-hidden">
        <!-- Section header -->
        <div class="tw-flex tw-items-center tw-gap-3 tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
          <div class="tw-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-lg tw-bg-amber-50">
            <Cog6ToothIcon class="tw-h-5 tw-w-5 tw-text-amber-500" />
          </div>
          <div>
            <p class="tw-text-sm tw-font-bold tw-text-gray-800 tw-leading-tight">{{ __("General Settings", "giantwp-discount-rules") }}</p>
            <p class="tw-text-xs tw-text-gray-400">{{ __("Configure discount rule behaviour", "giantwp-discount-rules") }}</p>
          </div>
        </div>

        <!-- Rule Apply On -->
        <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
          <div>
            <p class="tw-text-sm tw-font-semibold tw-text-gray-800">{{ __("Rule Apply On", "giantwp-discount-rules") }}</p>
            <p class="tw-text-xs tw-text-gray-400 tw-mt-0.5">{{ __("Which price the discount applies to", "giantwp-discount-rules") }}</p>
          </div>
          <el-select
            v-model="saveSettingsData.discountBasedOn"
            size="default"
            style="width: 180px"
            popper-class="custom-dropdown"
          >
            <el-option :value="'regular_price'" :label="__('Regular Price', 'giantwp-discount-rules')" />
            <el-option :value="'sale_price'"    :label="__('Sale Price', 'giantwp-discount-rules')" />
          </el-select>
        </div>

        <!-- Order Label -->
        <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
          <div>
            <div class="tw-flex tw-items-center tw-gap-1.5">
              <p class="tw-text-sm tw-font-semibold tw-text-gray-800">{{ __("Order Label", "giantwp-discount-rules") }}</p>
              <el-tooltip
                effect="dark"
                :content="__('Show discount label on order details page', 'giantwp-discount-rules')"
                placement="top"
                popper-class="custom-tooltip"
              >
                <QuestionMarkCircleIcon class="tw-h-3.5 tw-w-3.5 tw-text-gray-400 tw-cursor-pointer" />
              </el-tooltip>
            </div>
            <p class="tw-text-xs tw-text-gray-400 tw-mt-0.5">{{ __("Show discount label on order details page", "giantwp-discount-rules") }}</p>
          </div>
          <el-switch
            v-model="saveSettingsData.orderPageLabel"
            inline-prompt
            :active-text="__('On', 'giantwp-discount-rules')"
            :inactive-text="__('Off', 'giantwp-discount-rules')"
          />
        </div>

        <!-- Upsell Notification -->
        <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
          <div>
            <div class="tw-flex tw-items-center tw-gap-1.5">
              <p class="tw-text-sm tw-font-semibold tw-text-gray-800">{{ __("Upsell Notification", "giantwp-discount-rules") }}</p>
              <el-tooltip
                effect="dark"
                :content="__('Nudge customers with upsell offers at checkout', 'giantwp-discount-rules')"
                placement="top"
                popper-class="custom-tooltip"
              >
                <QuestionMarkCircleIcon class="tw-h-3.5 tw-w-3.5 tw-text-gray-400 tw-cursor-pointer" />
              </el-tooltip>
            </div>
            <p class="tw-text-xs tw-text-gray-400 tw-mt-0.5">{{ __("Nudge customers with upsell offers at checkout", "giantwp-discount-rules") }}</p>
          </div>
          <el-switch
            v-model="saveSettingsData.upsellNotificationWidget"
            inline-prompt
            :active-text="__('On', 'giantwp-discount-rules')"
            :inactive-text="__('Off', 'giantwp-discount-rules')"
          />
        </div>

        <!-- Product Badge -->
        <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4"
             :class="saveSettingsData.showProductBadge ? 'tw-border-b tw-border-gray-100' : ''">
          <div>
            <p class="tw-text-sm tw-font-semibold tw-text-gray-800">{{ __("Product Badge", "giantwp-discount-rules") }}</p>
            <p class="tw-text-xs tw-text-gray-400 tw-mt-0.5">{{ __("Show discount badge on product images in the shop", "giantwp-discount-rules") }}</p>
          </div>
          <el-switch
            v-model="saveSettingsData.showProductBadge"
            inline-prompt
            :active-text="__('On', 'giantwp-discount-rules')"
            :inactive-text="__('Off', 'giantwp-discount-rules')"
          />
        </div>

        <!-- Badge Color Options (visible when badge is on) -->
        <div v-if="saveSettingsData.showProductBadge" class="tw-px-5 tw-py-4 tw-bg-gray-50 tw-flex tw-flex-wrap tw-gap-6 tw-border-b tw-border-gray-100">
          <!-- Background Color -->
          <div class="tw-flex tw-items-center tw-gap-3">
            <div>
              <p class="tw-text-xs tw-font-semibold tw-text-gray-700 tw-mb-1">{{ __("Background Color", "giantwp-discount-rules") }}</p>
              <div class="tw-flex tw-items-center tw-gap-2">
                <input
                  type="color"
                  v-model="saveSettingsData.badgeBgColor"
                  class="tw-h-8 tw-w-10 tw-cursor-pointer tw-rounded tw-border tw-border-gray-300 tw-p-0.5"
                />
                <span class="tw-text-xs tw-text-gray-500 tw-font-mono">{{ saveSettingsData.badgeBgColor }}</span>
              </div>
            </div>
          </div>

          <!-- Text Color -->
          <div class="tw-flex tw-items-center tw-gap-3">
            <div>
              <p class="tw-text-xs tw-font-semibold tw-text-gray-700 tw-mb-1">{{ __("Text Color", "giantwp-discount-rules") }}</p>
              <div class="tw-flex tw-items-center tw-gap-2">
                <input
                  type="color"
                  v-model="saveSettingsData.badgeTextColor"
                  class="tw-h-8 tw-w-10 tw-cursor-pointer tw-rounded tw-border tw-border-gray-300 tw-p-0.5"
                />
                <span class="tw-text-xs tw-text-gray-500 tw-font-mono">{{ saveSettingsData.badgeTextColor }}</span>
              </div>
            </div>
          </div>

          <!-- Preview -->
          <div class="tw-flex tw-items-center tw-gap-3">
            <div>
              <p class="tw-text-xs tw-font-semibold tw-text-gray-700 tw-mb-1">{{ __("Preview", "giantwp-discount-rules") }}</p>
              <span
                :style="{ backgroundColor: saveSettingsData.badgeBgColor, color: saveSettingsData.badgeTextColor }"
                class="tw-inline-block tw-rounded tw-px-2 tw-py-1 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide"
              >
                SALE
              </span>
            </div>
          </div>
        </div>

        <!-- Margin Protection Guard (Upcoming) -->
        <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4 tw-border-b tw-border-gray-100 tw-opacity-60">
          <div>
            <p class="tw-text-sm tw-font-semibold tw-text-gray-800">{{ __("Margin Protection Guard", "giantwp-discount-rules") }}</p>
            <p class="tw-text-xs tw-text-gray-400 tw-mt-0.5">{{ __("Prevent discounts from going below your minimum profit margin", "giantwp-discount-rules") }}</p>
          </div>
          <span class="tw-inline-block tw-rounded tw-bg-blue-500 tw-px-2.5 tw-py-1 tw-text-xs tw-font-bold tw-text-white tw-uppercase tw-tracking-wide">
            {{ __("Upcoming", "giantwp-discount-rules") }}
          </span>
        </div>

        <!-- Save button -->
        <div class="tw-px-5 tw-py-4">
          <button
            @click="handleSaveSettings"
            :disabled="isLoadingSettings"
            class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-lg tw-bg-blue-600 tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-white tw-transition hover:tw-bg-blue-700 disabled:tw-opacity-60 disabled:tw-cursor-wait"
          >
            <CheckIcon class="tw-h-4 tw-w-4" />
            {{ isLoadingSettings ? __("Saving…", "giantwp-discount-rules") : __("Save Settings", "giantwp-discount-rules") }}
          </button>
        </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="tw-w-64 tw-shrink-0">
      <Sidebar />
    </div>
  </div>


</template>
