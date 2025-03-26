<script setup>
import { useRoute } from "vue-router";

// Props from parent component
const props = defineProps(["navigation"]);

// Get the current route
const route = useRoute();
</script>

<template>
  <div class="ml-10 flex items-baseline space-x-4">
    <template v-for="item in navigation" :key="item.name">
      <!-- Use router-link for internal links -->
      <router-link
        v-if="!item.isExternal"
        :to="item.href"
        :class="[
          route.path === item.href
            ? 'bg-gray-900 text-white'
            : 'text-gray-300 hover:bg-gray-700 hover:text-white',
          'rounded-md px-3 py-2 text-sm font-medium flex items-center',
        ]">
        <span>{{ item.name }}</span>
        <span
          v-if="item.isPro"
          class="ml-2 bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded">
          {{ __("Pro", "giantwp-discount-rules") }}
        </span>
      </router-link>

      <a
        v-else
        :href="item.href"
        target="_blank"
        rel="noopener noreferrer"
        class="text-gray-300 hover:bg-gray-700 hover:text-white rounded-md px-3 py-2 text-sm font-medium flex items-center">
        <span>{{ item.name }}</span>
        <span
          v-if="item.isPro"
          class="ml-2 bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded">
          {{ __("Pro", "giantwp-discount-rules") }}
        </span>
      </a>
    </template>
  </div>
</template>
