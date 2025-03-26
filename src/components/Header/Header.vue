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
  <Disclosure as="nav" class="bg-gray-800 m-0 p-0" v-slot="{ open }">
    <div class="w-full m-0 px-0 sm:px-0 lg:px-0">
      <div class="flex h-16 items-center justify-between">
        <!-- Logo & Desktop Navigation -->
        <div class="flex items-center">
          <img :src="logoUrl" alt="Logo" class="h-10 w-auto mr-4 pl-10" />
          <div class="hidden md:block">
            <Navigation :navigation="navigation" />
          </div>
        </div>

        <!-- Mobile Menu Button -->
        <div class="-mr-2 flex md:hidden">
          <DisclosureButton
            class="relative inline-flex items-center justify-center rounded-md bg-gray-800 p-2 text-gray-400 hover:bg-gray-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800">
            <span class="sr-only">{{
              __("Open main menu", "giantwp-discount-rules")
            }}</span>
            <Bars3Icon v-if="!open" class="block size-6" aria-hidden="true" />
            <XMarkIcon v-else class="block size-6" aria-hidden="true" />
          </DisclosureButton>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <MobileMenu :navigation="navigation" v-if="open" />
  </Disclosure>
</template>

<style>
/* Ensure no margins or padding on the body and html */
body,
html {
  margin: 0;
  padding: 0;
}
</style>
