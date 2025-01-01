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

  <div class="join drop-shadow">
  <input class="join-item btn" type="radio" name="radio-15" aria-label="Radio 1" checked/>
  <input class="join-item btn" type="radio" name="radio-15" aria-label="Radio 2" />
  <input class="join-item btn" type="radio" name="radio-15" aria-label="Radio 3" />
</div>
</template>

<style scoped>
/* Optional global styles */
</style>