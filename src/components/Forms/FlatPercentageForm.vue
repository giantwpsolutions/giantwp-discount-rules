<script setup>
  import { ref } from 'vue';

  const { __ } = wp.i18n;
  import Conditions from './Elements/Conditions.vue';
  import CouponName from './Elements/CouponName.vue';
  import FlatPercentageSelection from './Elements/FlatPercentageSelection.vue';
</script>

<template>

<CouponName></CouponName>
<FlatPercentageSelection></FlatPercentageSelection>


</template>


<style scoped>
</style>