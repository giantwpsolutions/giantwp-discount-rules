<script setup>
import { ref, defineEmits, watch, nextTick } from "vue";
import FlatPercentageForm from "../Forms/FlatPercentageForm.vue";
import Bogo from "../Forms/Bogo.vue";
import FreeshippingForm from "../Forms/FreeshippingForm.vue";
import BuyXGetY from "../Forms/BuyXGetY.vue";
import { saveFlatPercentageDiscount } from "@/data/save-data/saveFlatPercentageDiscount.js";
import { saveBogoData } from "@/data/save-data/saveBogoData.js";
import { saveShippingData } from "@/data/save-data/saveShippingData.js";
import { saveBuyXGetYData } from "@/data/save-data/saveBuyXGetYData.js";
import BulkDiscount from "../Forms/BulkDiscount.vue";
import { saveBulkDiscountData } from "../../data/save-data/saveBulkDiscountData";
import {
  discountCreatedMessage,
  warningMessage,
  errorMessage,
  updatedDiscountMessage,
} from "@/data/message";

const props = defineProps({
  visible: {
    type: Boolean,
    required: true,
  },
  editingRule: {
    type: Object,
    default: () => ({}), // ✅ Ensure it is always an object
  },
});

const emit = defineEmits(["close", "discountUpdated"]);

const selectedDiscountsType = ref("");
const showForm = ref(false);
const isSaving = ref(false);
const isEditMode = ref(false);

//Define Form ref
const flatPercentageFormRef = ref(null);
const bogoFormRef = ref(null);
const freeShippingRef = ref(null);
const buyXGetYRef = ref(null);
const bulkDiscountRef = ref(null);

const proFeatures = [
  {
    name: "Buy X Get Y",
    description: "Apply discounts Buy X product Get Y Product",
    value: "Buy X Get Y",
  },
  {
    name: "Shipping Discount",
    description: "Discounts based on Shipping",
    value: "Shipping Discount",
  },
  {
    name: "Bulk Discount",
    description: "Discounts based on bulk purchase",
    value: "Bulk Discount",
  },
];

const goBack = () => {
  showForm.value = false;
  selectedDiscountsType.value = "";
};

const selectDiscountType = (type) => {
  selectedDiscountsType.value = type;
  showForm.value = true;
};

watch(
  () => props.visible, // 👀 Watch modal visibility
  (isVisible) => {
    if (isVisible === false) {
      // Reset form when modal is closed
      selectedDiscountsType.value = "";
      showForm.value = false;
    }
  }
);

// ** Watch Editing Rule to Load Data into Form **
watch(
  () => props.editingRule,
  async (newVal) => {
    if (newVal && Object.keys(newVal).length > 0) {
      isEditMode.value = true;
      selectedDiscountsType.value = newVal.discountType;
      showForm.value = true;

      // Wait for the form component to be rendered
      await nextTick();
      await nextTick();
      await nextTick();

      if (flatPercentageFormRef.value) {
        console.log("🟢 Passing Data to Form:", newVal);
        flatPercentageFormRef.value.setFormData(structuredClone(newVal));
      } else if (bogoFormRef.value) {
        bogoFormRef.value.setFormData(structuredClone(newVal));
      } else if (freeShippingRef.value) {
        freeShippingRef.value.setFormData(structuredClone(newVal));
      } else if (buyXGetYRef.value) {
        buyXGetYRef.value.setFormData(structuredClone(newVal));
      } else if (bulkDiscountRef.value) {
        bulkDiscountRef.value.setFormData(structuredClone(newVal));
      }
    }
  },
  { immediate: true, deep: true }
);

const saveForm = async () => {
  if (isSaving.value) return;
  isSaving.value = true;

  try {
    let formData = null;
    let activeForm = null;

    switch (selectedDiscountsType.value) {
      case "Flat/Percentage":
        activeForm = flatPercentageFormRef.value;
        break;
      case "Bogo":
        activeForm = bogoFormRef.value;
        break;
      case "Shipping Discount":
        activeForm = freeShippingRef.value;
        break;
      case "Buy X Get Y":
        activeForm = buyXGetYRef.value;
        break;
      case "Bulk Discount":
        activeForm = bulkDiscountRef.value;
        break;
    }

    if (!activeForm || !activeForm.validate()) {
      warningMessage();
      return;
    }

    formData = activeForm.getFormData();

    if (isEditMode.value && formData.id) {
      console.log("🟡 Editing Existing Discount:", formData);
      if (selectedDiscountsType.value === "Flat/Percentage") {
        await saveFlatPercentageDiscount.updateDiscount(formData.id, formData);
      } else if (selectedDiscountsType.value === "Bogo") {
        await saveBogoData.updateDiscount(formData.id, formData);
      } else if (selectedDiscountsType.value === "Shipping Discount") {
        await saveShippingData.updateDiscount(formData.id, formData);
      } else if (selectedDiscountsType.value === "Buy X Get Y") {
        await saveBuyXGetYData.updateDiscount(formData.id, formData);
      } else if (selectedDiscountsType.value === "Bulk Discount") {
        await saveBulkDiscountData.updateDiscount(formData.id, formData);
      }

      updatedDiscountMessage();
    } else {
      console.log("🟢 Creating New Discount:", formData);
      if (selectedDiscountsType.value === "Flat/Percentage") {
        await saveFlatPercentageDiscount.saveCoupon(formData);
      } else if (selectedDiscountsType.value === "Bogo") {
        await saveBogoData.saveCoupon(formData);
      } else if (selectedDiscountsType.value === "Shipping Discount") {
        await saveShippingData.saveCoupon(formData);
      } else if (selectedDiscountsType.value === "Buy X Get Y") {
        await saveBuyXGetYData.saveCoupon(formData);
      } else if (selectedDiscountsType.value === "Bulk Discount") {
        await saveBulkDiscountData.saveCoupon(formData);
      }

      discountCreatedMessage();
    }

    emit("discountUpdated");

    isEditMode.value = false;
    emit("close");
  } catch (error) {
    console.error("Save failed:", error);
    errorMessage();
  } finally {
    isSaving.value = false;
  }
};
</script>

<template>
  <transition name="modal-zoom-fade">
    <div
      v-if="visible"
      class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 z-50">
      <div
        class="bg-white rounded-lg shadow-lg h-[75vh] w-[70vw] p-6 flex flex-col">
        <!-- Modal Header -->
        <div class="border-b pb-4 mb-4 flex items-center space-x-4">
          <button
            v-if="showForm"
            @click="goBack"
            class="text-blue-600 hover:text-blue-800"
            title="Back">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-6 w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor">
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          <h3 class="text-lg font-bold">
            {{
              showForm
                ? selectedDiscountsType
                : __("Select Discount Type", "aio-woodiscount")
            }}
          </h3>
        </div>

        <!-- Modal Content -->
        <div class="flex-1 border rounded p-6 overflow-auto">
          <template v-if="!showForm">
            <div
              class="grid grid-cols-1 mt-6 sm:grid-cols-2 md:grid-cols-3 gap-6">
              <!-- Flat/Percentage -->
              <div
                class="relative group bg-gray-100 hover:bg-blue-100 rounded-md p-6 flex flex-col items-center">
                <button
                  @click="() => selectDiscountType('Flat/Percentage')"
                  class="w-full text-center font-medium">
                  {{ __("Flat/Percentage", "aio-woodiscount") }}
                </button>
                <div
                  class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-700 text-white text-xs rounded py-1 px-2 w-48 text-center">
                  {{
                    __(
                      "Apply a fixed amount or percentage discount",
                      "aio-woodiscount"
                    )
                  }}
                </div>
              </div>

              <!-- BOGO -->
              <div
                class="relative group bg-gray-100 hover:bg-blue-100 rounded-md p-6 flex flex-col items-center">
                <button
                  @click="() => selectDiscountType('Bogo')"
                  class="w-full text-center font-medium">
                  {{ __("BOGO", "aio-woodiscount") }}
                </button>
                <div
                  class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-700 text-white text-xs rounded py-1 px-2 w-48 text-center">
                  {{ __("Buy One Get One free discount", "aio-woodiscount") }}
                </div>
              </div>

              <!-- Pro Features -->
              <div
                v-for="(proFeature, index) in proFeatures"
                :key="index"
                class="relative group bg-gray-100 hover:bg-blue-100 rounded-md p-6 flex flex-col items-center">
                <button
                  @click="() => selectDiscountType(proFeature.value)"
                  class="w-full text-center font-medium">
                  {{ __(proFeature.name, "aio-woodiscount") }}
                </button>
                <span
                  class="absolute top-1 right-1 bg-red-500 text-white text-xs px-2 py-1 rounded">
                  Pro
                </span>
                <div
                  class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-700 text-white text-xs rounded py-1 px-2 w-48 text-center">
                  {{ __(proFeature.description, "aio-woodiscount") }}
                </div>
              </div>
            </div>
          </template>

          <template v-else>
            <!-- Dynamically Render the Form Based on Selected Discount Type -->
            <FlatPercentageForm
              v-if="selectedDiscountsType === 'Flat/Percentage' && showForm"
              ref="flatPercentageFormRef"
              :initialData="props.editingRule" />
            <Bogo
              v-else-if="selectedDiscountsType === 'Bogo'"
              ref="bogoFormRef"
              :initialData="props.editingRule" />

            <FreeshippingForm
              v-else-if="
                selectedDiscountsType === 'Shipping Discount' && showForm
              "
              ref="freeShippingRef"
              :initialData="props.editingRule" />
            <BuyXGetY
              v-else-if="selectedDiscountsType === 'Buy X Get Y'"
              ref="buyXGetYRef"
              :initialData="props.editingRule" />
            <BulkDiscount
              v-else-if="selectedDiscountsType === 'Bulk Discount'"
              ref="bulkDiscountRef"
              :initialData="props.editingRule" />
            <p v-else>
              {{ __("Form for", "aio-woodiscount") }}
              {{ selectedDiscountsType }}
            </p>
          </template>
        </div>

        <!-- Modal Footer -->
        <div class="mt-4 flex justify-end space-x-4">
          <button
            @click="emit('close')"
            class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400">
            {{ __("Close", "aio-woodiscount") }}
          </button>
          <button
            v-if="showForm"
            @click="saveForm"
            :disabled="isSaving"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            {{
              isSaving
                ? __("Saving...", "aio-woodiscount")
                : __("Save", "aio-woodiscount")
            }}
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<style scoped>
.modal-zoom-fade-enter-active,
.modal-zoom-fade-leave-active {
  transition: all 0.5s ease;
}
.modal-zoom-fade-enter-from,
.modal-zoom-fade-leave-to {
  opacity: 0;
  transform: scale(0.9);
}
</style>
