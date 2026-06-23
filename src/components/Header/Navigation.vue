<script setup>
import { useRoute } from "vue-router";

// Props from parent component
const props = defineProps(["navigation"]);

// Get the current route
const route = useRoute();
</script>

<template>
  <div class="tw-ml-10 tw-flex tw-items-baseline tw-space-x-4">
    <template v-for="item in navigation" :key="item.name">
      <!-- Use router-link for internal links -->
      <!-- Upcoming (non-clickable) -->
      <span
        v-if="item.isUpcoming"
        class="tw-text-gray-400 tw-cursor-default tw-rounded-md tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-flex tw-items-center tw-select-none"
        :title="__('Coming soon', 'giantwp-discount-rules')"
      >
        {{ item.name }}
        <span class="tw-ml-2 tw-bg-brand-500 tw-text-white tw-text-xs tw-font-bold tw-px-2 tw-py-0.5 tw-rounded">
          {{ __("Upcoming", "giantwp-discount-rules") }}
        </span>
      </span>

      <!-- Internal link -->
      <router-link
        v-else-if="!item.isExternal"
        :to="item.href"
        :class="[
          route.path === item.href
            ? 'tw-bg-gray-900 tw-text-white'
            : 'tw-text-gray-300 tw-hover:bg-gray-700 tw-hover:text-white',
          'tw-rounded-md tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-flex tw-items-center',
        ]">
        <span>{{ item.name }}</span>
        <span
          v-if="item.isPro"
          class="tw-ml-2 tw-bg-red-500 tw-text-white tw-text-xs tw-font-bold tw-px-2 tw-py-0.5 tw-rounded">
          {{ __("Pro", "giantwp-discount-rules") }}
        </span>
      </router-link>

      <!-- External link -->
      <a
        v-else
        :href="item.href"
        target="_blank"
        rel="noopener noreferrer"
        class="tw-text-gray-300 tw-hover:bg-gray-700 tw-hover:text-white tw-rounded-md tw-px-3 tw-py-2 tw-text-sm tw-font-medium tw-flex tw-items-center">
        <span>{{ item.name }}</span>
        <span
          v-if="item.isPro"
          class="tw-ml-2 tw-bg-red-500 tw-text-white tw-text-xs tw-font-bold tw-px-2 tw-py-0.5 tw-rounded">
          {{ __("Pro", "giantwp-discount-rules") }}
        </span>
      </a>
    </template>
  </div>
</template>
