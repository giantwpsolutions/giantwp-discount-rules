<script setup>
import { ref } from "vue";

import CouponName from "./Elements/CouponName.vue";
import BogoSelection from "./Elements/BogoSelection.vue";
import BogoSameProductBuy from "./Elements/BogoSameProductBuy.vue";
</script>

<template>
  <form action="">
    <CouponName></CouponName>
    <BogoSelection></BogoSelection>
    <BogoSameProductBuy></BogoSameProductBuy>
  </form>
</template>
