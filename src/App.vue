<!-- src/App.vue -->
<script setup>
import Header from './components/Header/Header.vue';
import { onMounted } from 'vue';

</script>

<template>
  <div class="tw-flex tw-flex-col tw-h-full">
    <Header />
    <div class="tw-flex-1 tw-overflow-y-auto">
      <router-view />
    </div>
  </div>
</template>

<style scoped>
/* Optional global styles */
</style>