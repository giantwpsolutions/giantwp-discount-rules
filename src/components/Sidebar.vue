<script setup>
import { ref } from "vue";
import {
  DocumentTextIcon,
  ChatBubbleLeftEllipsisIcon,
  UserGroupIcon,
  StarIcon,
} from "@heroicons/vue/24/outline";

const { __ } = wp.i18n;

const baseUrl = gwpdrPluginData.pluginUrl + "assets/images/";

const plugins = [
  {
    logo:        baseUrl + "primekit.png",
    name:        "PrimeKit Addons",
    desc:        __("Powerful Elementor Addons for WordPress", "giantwp-discount-rules"),
    slug:        gwpdrPluginData.primekit_slug,
    status:      gwpdrPluginData.primekit_status,
    activateUrl: gwpdrPluginData.primekit_activate_url,
  },
  {
    logo:        baseUrl + "quickcartshopping.png",
    name:        "Quick Cart Shopping",
    desc:        __("WooCommerce UX and Shopping Experience Booster", "giantwp-discount-rules"),
    slug:        gwpdrPluginData.quickcart_slug,
    status:      gwpdrPluginData.quickcart_status,
    activateUrl: gwpdrPluginData.quickcart_activate_url,
  },
  {
    logo:        baseUrl + "smartorderbump.svg",
    name:        "Giant Checkout Offers",
    desc:        __("Boost WooCommerce Sales with Smart Checkout Offers", "giantwp-discount-rules"),
    slug:        gwpdrPluginData.giantcheckoutoffers_slug,
    status:      gwpdrPluginData.giantcheckoutoffers_status,
    activateUrl: gwpdrPluginData.giantcheckoutoffers_activate_url,
  },
];

const helpLinks = [
  {
    icon:  DocumentTextIcon,
    label: __("Documentation", "giantwp-discount-rules"),
    url:   gwpdrPluginData.docsUrl,
  },
  {
    icon:  ChatBubbleLeftEllipsisIcon,
    label: __("Get Support", "giantwp-discount-rules"),
    url:   gwpdrPluginData.supportUrl,
  },
  {
    icon:  UserGroupIcon,
    label: __("Join Community", "giantwp-discount-rules"),
    url:   gwpdrPluginData.communityUrl,
  },
];

// initialise state from PHP-reported status
const initialStates = Object.fromEntries(
  plugins.map((p) => [
    p.slug,
    p.status === 'active'    ? 'active'    :
    p.status === 'installed' ? 'installed' : 'idle',
  ])
);
const installStates  = ref(initialStates);
const activateUrls   = ref(Object.fromEntries(plugins.map((p) => [p.slug, p.activateUrl || ''])));

const installPlugin = (plugin) => {
  const state = installStates.value[plugin.slug];
  if (state === 'installing' || state === 'installed' || state === 'active') return;

  installStates.value[plugin.slug] = 'installing';

  wp.updates.installPlugin({
    slug: plugin.slug,
    success: (response) => {
      installStates.value[plugin.slug] = 'installed';
      if (response.activateUrl) {
        activateUrls.value[plugin.slug] = response.activateUrl;
      }
    },
    error: (response) => {
      if (response.errorCode === 'folder_exists') {
        installStates.value[plugin.slug] = 'installed';
      } else {
        installStates.value[plugin.slug] = 'error';
      }
    },
  });
};

const activatePlugin = (plugin) => {
  const url = activateUrls.value[plugin.slug];
  if (url) window.location.href = url;
};
</script>

<template>
  <div class="tw-flex tw-flex-col tw-gap-4">

    <!-- Our Other Plugins -->
    <div class="tw-rounded-xl tw-border tw-border-gray-200 tw-bg-white tw-p-4">
      <h4 class="tw-text-sm tw-font-bold tw-text-gray-800 tw-mb-0.5">
        {{ __("Our Other Plugins", "giantwp-discount-rules") }}
      </h4>
      <p class="tw-text-xs tw-text-gray-400 tw-mb-4">
        {{ __("Explore more tools to enhance your store", "giantwp-discount-rules") }}
      </p>

      <div class="tw-flex tw-flex-col tw-gap-3">
        <div v-for="plugin in plugins" :key="plugin.name">
          <div class="tw-flex tw-items-start tw-gap-3 tw-mb-2">
            <img :src="plugin.logo" :alt="plugin.name" class="tw-h-10 tw-w-10 tw-shrink-0 tw-rounded-xl tw-object-cover" />
            <div class="tw-min-w-0">
              <p class="tw-text-sm tw-font-semibold tw-text-gray-800 tw-leading-tight">{{ plugin.name }}</p>
              <p class="tw-text-xs tw-text-gray-400 tw-leading-snug tw-mt-0.5">{{ plugin.desc }}</p>
            </div>
          </div>

          <!-- Active -->
          <div
            v-if="installStates[plugin.slug] === 'active'"
            class="tw-flex tw-items-center tw-justify-center tw-gap-1.5 tw-w-full tw-rounded-lg tw-border tw-border-green-200 tw-bg-green-50 tw-py-1.5 tw-text-xs tw-font-medium tw-text-green-600"
          >
            <span class="tw-inline-block tw-h-1.5 tw-w-1.5 tw-rounded-full tw-bg-green-500"></span>
            {{ __("Active", "giantwp-discount-rules") }}
          </div>

          <!-- Activate -->
          <button
            v-else-if="installStates[plugin.slug] === 'installed'"
            @click="activatePlugin(plugin)"
            class="tw-block tw-w-full tw-rounded-lg tw-border tw-border-brand-300 tw-bg-brand-50 tw-py-1.5 tw-text-center tw-text-xs tw-font-medium tw-text-brand-600 tw-transition hover:tw-bg-brand-100 hover:tw-border-brand-400"
          >
            {{ __("Activate", "giantwp-discount-rules") }}
          </button>

          <!-- Install / Installing / Error -->
          <button
            v-else
            @click="installPlugin(plugin)"
            :disabled="installStates[plugin.slug] === 'installing'"
            :class="[
              'tw-block tw-w-full tw-rounded-lg tw-border tw-py-1.5 tw-text-center tw-text-xs tw-font-medium tw-transition',
              installStates[plugin.slug] === 'installing'
                ? 'tw-border-brand-200 tw-bg-brand-50 tw-text-brand-400 tw-cursor-wait'
                : installStates[plugin.slug] === 'error'
                ? 'tw-border-red-200 tw-bg-red-50 tw-text-red-500 hover:tw-bg-red-100'
                : 'tw-border-gray-200 tw-text-gray-600 hover:tw-bg-gray-50 hover:tw-border-gray-300',
            ]"
          >
            <span v-if="installStates[plugin.slug] === 'installing'">{{ __("Installing…", "giantwp-discount-rules") }}</span>
            <span v-else-if="installStates[plugin.slug] === 'error'">{{ __("Try Again", "giantwp-discount-rules") }}</span>
            <span v-else>{{ __("Install", "giantwp-discount-rules") }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Need Help? -->
    <div class="tw-rounded-xl tw-border tw-border-gray-200 tw-bg-white tw-p-4">
      <h4 class="tw-text-sm tw-font-bold tw-text-gray-800 tw-mb-3">
        {{ __("Need Help?", "giantwp-discount-rules") }}
      </h4>
      <div class="tw-flex tw-flex-col tw-gap-1">
        <a
          v-for="link in helpLinks"
          :key="link.label"
          :href="link.url"
          target="_blank"
          class="tw-flex tw-items-center tw-gap-2.5 tw-rounded-lg tw-px-2 tw-py-2 tw-text-sm tw-text-gray-600 tw-transition hover:tw-bg-gray-50 hover:tw-text-gray-900"
        >
          <component :is="link.icon" class="tw-h-4 tw-w-4 tw-text-gray-400 tw-shrink-0" />
          {{ link.label }}
        </a>
      </div>
    </div>

    <!-- Rate Us -->
    <div class="tw-rounded-xl tw-bg-[#1e2d40] tw-p-4">
      <div class="tw-flex tw-gap-0.5 tw-mb-2">
        <StarIcon v-for="i in 5" :key="i" class="tw-h-4 tw-w-4 tw-text-yellow-400" />
      </div>
      <p class="tw-text-sm tw-font-bold tw-text-white tw-mb-1">
        {{ __("Enjoying the plugin?", "giantwp-discount-rules") }}
      </p>
      <p class="tw-text-xs tw-text-gray-400 tw-leading-relaxed tw-mb-3">
        {{ __("Rate us on WordPress.org and help us grow the community!", "giantwp-discount-rules") }}
      </p>
      <a
        href="https://wordpress.org/support/plugin/giantwp-discount-rules/reviews/#new-post"
        target="_blank"
        class="tw-block tw-w-full tw-rounded-lg tw-border tw-border-yellow-400 tw-py-1.5 tw-text-center tw-text-xs tw-font-semibold tw-text-yellow-400 tw-transition hover:tw-bg-yellow-400 hover:tw-text-[#1e2d40]"
      >
        ★ {{ __("Rate Now", "giantwp-discount-rules") }}
      </a>
    </div>

  </div>
</template>
