<!-- src/App.vue -->
<script setup>
import Header from './components/Header/Header.vue';
import { onMounted } from 'vue';

</script>

<template>
  <div>
    <Header />
    <div class="p-4">
      <router-view />
    </div>
  </div>

</template>

<style scoped>
/* Optional global styles */
</style>