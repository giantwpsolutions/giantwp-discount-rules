<script setup>
import { DisclosurePanel } from "@headlessui/vue";
import { useRoute } from "vue-router";

const props = defineProps(["navigation"]);
const route = useRoute();
</script>

<template>
  <DisclosurePanel class="md:hidden">
    <div class="space-y-1 px-2 pt-2 pb-3 sm:px-3">
      <template v-for="item in navigation" :key="item.name">
        <router-link
          v-if="!item.isExternal"
          :to="item.href"
          :class="[
            route.path === item.href
              ? 'bg-gray-900 text-white'
              : 'text-gray-300 hover:bg-gray-700 hover:text-white',
            'block rounded-md px-3 py-2 text-base font-medium',
          ]"
          :aria-current="item.current ? 'page' : undefined">
          {{ item.name }}
        </router-link>

        <a
          v-else
          :href="item.href"
          target="_blank"
          rel="noopener noreferrer"
          class="text-gray-300 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-base font-medium">
          {{ item.name }}
        </a>
      </template>
    </div>
  </DisclosurePanel>
</template>
