<script setup>
import { Disclosure, DisclosureButton } from '@headlessui/vue';
import { Bars3Icon, XMarkIcon } from '@heroicons/vue/24/outline';

defineProps(['navigation']);
</script>

<template>
  <DisclosurePanel class="md:hidden">
    <div class="space-y-1 px-2 pt-2 pb-3 sm:px-3">
      <DisclosureButton
        v-for="item in navigation"
        :key="item.name"
        :as="'a'"
        :href="item.href"
        :class="[item.current ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white', 'block rounded-md px-3 py-2 text-base font-medium']"
        :aria-current="item.current ? 'page' : undefined"
      >
        {{ item.name }}
      </DisclosureButton>
    </div>
  </DisclosurePanel>
</template>
