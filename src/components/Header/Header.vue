<script setup>
import { Disclosure, DisclosureButton } from "@headlessui/vue";
import { Bars3Icon, XMarkIcon } from "@heroicons/vue/24/outline";
import Navigation from "./Navigation.vue";
import MobileMenu from "./MobileMenu.vue";

const { __ } = wp.i18n;

// Navigation links with the Upgrade link marked as external
const navigation = [
  {
    name: __("Discounts", "giantwp-discount-rules"),
    href: "/",
    current: true,
  },
  {
    name: __("Settings", "giantwp-discount-rules"),
    href: "/settings",
    current: false,
  },
  {
    name: __("Upgrade", "giantwp-discount-rules"),
    href: pluginData.proUrl,
    current: false,
    isPro: true,
    isExternal: true,
  },
];

// Dynamically set the path to the logo
const logoUrl = `${pluginData.pluginUrl}assets/images/giantwp_discount_rules.png`;
</script>

<template>
  <Disclosure as="nav" class="tw-bg-gray-800 tw-m-0 tw-p-0" v-slot="{ open }">
    <div class="tw-w-full tw-m-0 tw-px-0 tw-sm:px-0 tw-lg:px-0">
      <div class="tw-flex tw-h-16 tw-items-center tw-justify-between">
        <!-- Logo & Desktop Navigation -->
        <div class="tw-flex tw-items-center">
          <img :src="logoUrl" alt="Logo" class="tw-h-10 tw-w-auto tw-mr-4 tw-pl-10" />
          <!-- ✅ FIXED: corrected breakpoint prefix -->
          <div class="tw-hidden md:tw-block">
            <Navigation :navigation="navigation" />
          </div>
        </div>

        <!-- Mobile Menu Button -->
        <!-- ✅ FIXED: corrected breakpoint prefix -->
        <div class="tw--mr-2 tw-flex md:tw-hidden">
          <DisclosureButton
            class="tw-relative tw-inline-flex tw-items-center tw-justify-center tw-rounded-md tw-bg-gray-800 tw-p-2 tw-text-gray-400 tw-hover:tw-bg-gray-700 tw-hover:tw-text-white tw-focus:tw-outline-none tw-focus:tw-ring-2 tw-focus:tw-ring-white tw-focus:tw-ring-offset-2 tw-focus:tw-ring-offset-gray-800">
            <span class="sr-only">{{
              __("Open main menu", "giantwp-discount-rules")
            }}</span>
            <Bars3Icon v-if="!open" class="tw-block tw-size-6" aria-hidden="true" />
            <XMarkIcon v-else class="tw-block tw-size-6" aria-hidden="true" />
          </DisclosureButton>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <MobileMenu :navigation="navigation" v-if="open" />
  </Disclosure>
</template>

<style>
body,
html {
  margin: 0;
  padding: 0;
}
</style>
