<script setup>
import { QuestionMarkCircleIcon } from "@heroicons/vue/24/solid"; // Importing the tooltip icon
import { ref } from "vue";

// Reactive variables
const discountType = ref("default"); // Default to 'fixed'
const discountValue = ref(null);
const maxValue = ref(null);

// Validation state
const errors = ref({
  discountType: "",
  discountValue: "",
  maxValue: "",
});

const validateFields = () => {
  errors.value.discountType = discountType.value
    ? ""
    : __("Please select a discount type", "aio-woodiscount");
  errors.value.discountValue = discountValue.value
    ? ""
    : __("Discount value is required", "aio-woodiscount");
  errors.value.discountType =
    discountType.value === "default"
      ? __("Please select a discount type", "aio-woodiscount")
      : "";
};

const handleSubmit = () => {
  validateFields();
  if (
    !errors.value.discountType &&
    !errors.value.discountValue &&
    !errors.value.maxValue
  ) {
    console.log("Form submitted:", {
      discountType: discountType.value,
      discountValue: discountValue.value,
      maxValue: maxValue.value,
    });
  }
};
</script>

<template>
  <div class="space-y-4 max-w-lg">
    <!-- Discount Type Selection -->
    <div class="w-full max-w-md mb-6">
      <label for="discountType" class="block text-sm font-medium text-gray-900">
        {{ __("Discount Type", "aio-woodiscount") }}
      </label>
      <select
        v-model="discountType"
        id="discountType"
        class="mt-1.5 h-8 w-full rounded-md border-gray-300 text-gray-700 sm:text-sm">
        <option value="default">
          {{ __("Please select", "aio-woodiscount") }}
        </option>
        <option value="fixed">{{ __("Fixed", "aio-woodiscount") }}</option>
        <option value="percentage">
          {{ __("Percentage", "aio-woodiscount") }}
        </option>
      </select>
      <p v-if="errors.discountType" class="text-red-600 text-sm mt-1">
        {{ errors.discountType }}
      </p>
    </div>

    <!-- Inline Fields: Discount Value and Maximum Value -->
    <div class="flex gap-4 items-start">
      <!-- Discount Value -->
      <div class="relative flex-1">
        <label
          for="discountValue"
          class="block text-sm font-medium text-gray-900 my-1">
          {{ __("Discount Value", "aio-woodiscount") }}
        </label>
        <div class="flex items-center">
          <input
            v-model="discountValue"
            type="number"
            id="discountValue"
            :placeholder="__('Enter discount value', 'aio-woodiscount')"
            class="w-full h-8 rounded-custom-aio-left border-gray-300 shadow-sm sm:text-sm pr-4" />
          <span
            class="ml-0 h-8 px-2 py-2 border rounded-custom-aio-right text-gray-700 bg-gray-100">
            {{ discountType === "percentage" ? "%" : "$" }}
          </span>
        </div>

        <p v-if="errors.discountValue" class="text-red-600 text-sm mt-1">
          {{ errors.discountValue }}
        </p>
      </div>

      <!-- Maximum Value -->
      <div class="relative flex-1">
        <label
          for="maxValue"
          class="text-sm font-medium text-gray-900 flex items-center gap-2 my-1">
          {{ __("Maximum Value", "aio-woodiscount") }}
          <div class="group relative">
            <el-tooltip
              class="box-item"
              effect="dark"
              :content="
                __('The maximum value that can be applied', 'aio-woodiscount')
              "
              placement="top"
              popper-class="custom-tooltip">
              <QuestionMarkCircleIcon
                class="w-4 h-4 text-gray-500 hover:text-gray-700 cursor-pointer" />
            </el-tooltip>
          </div>
        </label>
        <div class="flex items-center">
          <input
            v-model="maxValue"
            type="number"
            id="maxValue"
            :placeholder="__('Enter maximum value', 'aio-woodiscount')"
            :disabled="discountType === 'fixed' || discountType === 'default'"
            class="w-full h-8 rounded-custom-aio-left border-gray-300 shadow-sm sm:text-sm pr-4" />
          <span
            class="ml-0 px-2 py-2 h-8 border rounded-custom-aio-right text-gray-700 bg-gray-100">
            $
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Tooltip styling */
.group:hover .group-hover\:block {
  display: block;
}

.rounded-custom-aio-left {
  border-radius: 5px 0px 0px 5px;
}
.rounded-custom-aio-right {
  border-radius: 0px 5px 5px 0px;
}
</style>
