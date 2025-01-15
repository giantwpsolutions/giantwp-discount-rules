<script setup>
import { ref } from "vue";

import CouponName from "./Elements/CouponName.vue";
import BogoSelection from "./Elements/BogoSelection.vue";
import Conditions from "./Elements/Conditions.vue";
import UsageLimits from "./Elements/UsageLimits.vue";
import DateTimePicker from "./Elements/DateTimePicker.vue";
</script>

<template>
  <form action="">
    <CouponName></CouponName>
    <BogoSelection></BogoSelection>
    <UsageLimits></UsageLimits>
    <DateTimePicker></DateTimePicker>
    <Conditions></Conditions>
  </form>
</template>
