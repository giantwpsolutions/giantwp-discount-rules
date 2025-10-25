<script setup>
import { DisclosurePanel } from "@headlessui/vue";
import { useRoute } from "vue-router";

const props = defineProps(["navigation"]);
const route = useRoute();
</script>

<template>
  <DisclosurePanel class="tw-md:hidden">
    <div class="tw-space-y-1 tw-px-2 tw-pt-2 tw-pb-3 tw-sm:px-3">
      <template v-for="item in navigation" :key="item.name">
        <router-link
          v-if="!item.isExternal"
          :to="item.href"
          :class="[
            route.path === item.href
              ? 'tw-bg-gray-900 tw-text-white'
              : 'tw-text-gray-300 tw-hover:bg-gray-700 tw-hover:text-white',
            'tw-block tw-rounded-md tw-px-3 tw-py-2 tw-text-base tw-font-medium',
          ]"
          :aria-current="item.current ? 'page' : undefined">
          {{ item.name }}
        </router-link>

        <a
          v-else
          :href="item.href"
          target="_blank"
          rel="noopener noreferrer"
          class="tw-text-gray-300 tw-hover:bg-gray-700 tw-hover:text-white tw-block tw-rounded-md tw-px-3 tw-py-2 text-base font-medium">
          {{ item.name }}
        </a>
      </template>
    </div>
  </DisclosurePanel>
</template>
