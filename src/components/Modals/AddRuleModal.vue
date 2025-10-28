<script setup>
import { ref, defineEmits, watch, nextTick, computed, onMounted } from "vue";
import FlatPercentageForm from "../Forms/FlatPercentageForm.vue";
import Bogo from "../Forms/Bogo.vue";
import FreeshippingForm from "../Forms/FreeshippingForm.vue";
import BuyXGetY from "../Forms/BuyXGetY.vue";
import { saveFlatPercentageDiscount } from "@/data/save-data/saveFlatPercentageDiscount.js";
import { saveBogoData } from "@/data/save-data/saveBogoData.js";
import { saveShippingData } from "@/data/save-data/saveShippingData.js";
import { saveBuyXGetYData } from "@/data/save-data/saveBuyXGetYData.js";
import BulkDiscount from "../Forms/BulkDiscount.vue";
import { isEqual } from "lodash-es";
import { saveBulkDiscountData } from "../../data/save-data/saveBulkDiscountData";
import {
  licenseKey,
  licenseStatus,
  isLoadingLicense,
  fetchLicenseStatus,
  activateLicense,
  deactivateLicense,
} from "@/data/save-data/licenseApi";
import {
  discountCreatedMessage,
  warningMessage,
  errorMessage,
  updatedDiscountMessage,
} from "@/data/message";

const { __ } = wp.i18n;

const isLicenseActive = computed(() => {
  return gwpdrPluginData.proActive && licenseStatus.value === "valid";
});

onMounted(() => {
  if (gwpdrPluginData.proActive) {
    fetchLicenseStatus();
  }
});

const props = defineProps({
  visible: { type: Boolean, required: true },
  editingRule: { type: Object, default: () => ({}) },
});

const emit = defineEmits(["close", "discountUpdated"]);

const selectedDiscountsType = ref("");
const showForm = ref(false);
const isSaving = ref(false);
const isEditMode = ref(false);

// refs
const flatPercentageFormRef = ref(null);
const bogoFormRef = ref(null);
const freeShippingRef = ref(null);
const buyXGetYRef = ref(null);
const bulkDiscountRef = ref(null);

const proFeatures = [
  {
    name: __("Buy X Get Y", "giantwp-discount-rules"),
    description: __("Apply discounts Buy X product Get Y Product", "giantwp-discount-rules"),
    value: "Buy X Get Y",
  },
  {
    name: __("Shipping Discount", "giantwp-discount-rules"),
    description: __("Discounts based on Shipping", "giantwp-discount-rules"),
    value: "Shipping Discount",
  },
  {
    name: __("Bulk Discount", "giantwp-discount-rules"),
    description: __("Discounts based on bulk purchase", "giantwp-discount-rules"),
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

watch(() => props.visible, (isVisible) => {
  if (isVisible === false) {
    selectedDiscountsType.value = "";
    showForm.value = false;
  }
});

// load edit data
watch(
  () => props.editingRule,
  async (newVal) => {
    if (newVal && Object.keys(newVal).length > 0) {
      isEditMode.value = true;
      selectedDiscountsType.value = newVal.discountType;
      showForm.value = true;
      await nextTick();
      await nextTick();
      await nextTick();

      if (flatPercentageFormRef.value)
        flatPercentageFormRef.value.setFormData(structuredClone(newVal));
      else if (bogoFormRef.value)
        bogoFormRef.value.setFormData(structuredClone(newVal));
      else if (freeShippingRef.value)
        freeShippingRef.value.setFormData(structuredClone(newVal));
      else if (buyXGetYRef.value)
        buyXGetYRef.value.setFormData(structuredClone(newVal));
      else if (bulkDiscountRef.value)
        bulkDiscountRef.value.setFormData(structuredClone(newVal));
    }
  },
  { immediate: true, deep: true }
);

const saveForm = async () => {
  if (isSaving.value) return;
  isSaving.value = true;
  try {
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

    const data = activeForm.getFormData();

    if (isEditMode.value && data.id) {
      const original = JSON.stringify(props.editingRule);
      const current = JSON.stringify(data);
      if (original === current) {
        noChanges();
        isSaving.value = false;
        return;
      }

      if (selectedDiscountsType.value === "Flat/Percentage")
        await saveFlatPercentageDiscount.updateDiscount(data.id, data);
      else if (selectedDiscountsType.value === "Bogo")
        await saveBogoData.updateDiscount(data.id, data);
      else if (selectedDiscountsType.value === "Shipping Discount")
        await saveShippingData.updateDiscount(data.id, data);
      else if (selectedDiscountsType.value === "Buy X Get Y")
        await saveBuyXGetYData.updateDiscount(data.id, data);
      else if (selectedDiscountsType.value === "Bulk Discount")
        await saveBulkDiscountData.updateDiscount(data.id, data);

      updatedDiscountMessage();
    } else {
      if (selectedDiscountsType.value === "Flat/Percentage")
        await saveFlatPercentageDiscount.saveCoupon(data);
      else if (selectedDiscountsType.value === "Bogo")
        await saveBogoData.saveCoupon(data);
      else if (selectedDiscountsType.value === "Shipping Discount")
        await saveShippingData.saveCoupon(data);
      else if (selectedDiscountsType.value === "Buy X Get Y")
        await saveBuyXGetYData.saveCoupon(data);
      else if (selectedDiscountsType.value === "Bulk Discount")
        await saveBulkDiscountData.saveCoupon(data);

      discountCreatedMessage();
    }

    emit("discountUpdated");
    isEditMode.value = false;
    emit("close");
  } catch (e) {
    console.error("Save failed:", e);
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
      class="tw-fixed lg:tw-ml-16 tw-top-0 tw-left-0 tw-w-screen tw-h-screen tw-flex tw-items-center tw-justify-center tw-bg-gray-900 tw-bg-opacity-50 tw-z-50"
    >
      <div
        class="tw-bg-white tw-rounded-lg tw-shadow-lg tw-h-[75vh] tw-w-[80vw] tw-md:w-[75vw] tw-p-6 tw-grid tw-grid-rows-[auto,1fr,auto]"
      >
        <!-- Modal Header -->
        <div class="tw-border-b tw-pb-4 tw-mb-4 tw-flex tw-items-center tw-space-x-4">
          <button
            v-if="showForm"
            @click="goBack"
            class="tw-text-blue-600 hover:tw-text-blue-800"
            title="Back"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="tw-h-6 tw-w-6"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M15 19l-7-7 7-7"
              />
            </svg>
          </button>
          <h3 class="tw-text-lg tw-font-bold">
            {{
              showForm
                ? selectedDiscountsType
                : __("Select Discount Type", "giantwp-discount-rules")
            }}
          </h3>
        </div>

        <!-- Modal Content (scrolls) -->
        <div class="tw-border tw-rounded tw-p-6 tw-overflow-auto">
          <template v-if="!showForm">
            <div
              class="tw-grid tw-grid-cols-1 tw-mt-6 sm:tw-grid-cols-2 md:tw-grid-cols-3 tw-gap-6"
            >
              <!-- Flat/Percentage -->
              <div
                class="tw-relative tw-group tw-bg-gray-100 hover:tw-bg-blue-100 tw-rounded-md tw-p-6 tw-flex tw-flex-col tw-items-center"
              >
                <button
                  @click="() => selectDiscountType('Flat/Percentage')"
                  class="tw-w-full tw-text-center tw-font-medium"
                >
                  {{ __("Flat/Percentage", "giantwp-discount-rules") }}
                </button>
                <div
                  class="tw-absolute tw-bottom-full tw-mb-2 tw-hidden tw-group-hover:tw-block tw-bg-gray-700 tw-text-white tw-text-xs tw-rounded tw-py-1 tw-px-2 tw-w-48 tw-text-center"
                >
                  {{
                    __(
                      "Apply a fixed amount or percentage discount",
                      "giantwp-discount-rules"
                    )
                  }}
                </div>
              </div>

              <!-- BOGO -->
              <div
                class="tw-relative tw-group tw-bg-gray-100 hover:tw-bg-blue-100 tw-rounded-md tw-p-6 tw-flex tw-flex-col tw-items-center"
              >
                <button
                  @click="() => selectDiscountType('Bogo')"
                  class="tw-w-full tw-text-center tw-font-medium"
                >
                  {{ __("BOGO", "giantwp-discount-rules") }}
                </button>
                <div
                  class="tw-absolute tw-bottom-full tw-mb-2 tw-hidden tw-group-hover:tw-block tw-bg-gray-700 tw-text-white tw-text-xs tw-rounded tw-py-1 tw-px-2 tw-w-48 tw-text-center"
                >
                  {{
                    __("Buy One Get One free discount", "giantwp-discount-rules")
                  }}
                </div>
              </div>

              <!-- Pro Features -->
              <div
                v-for="(proFeature, index) in proFeatures"
                :key="index"
                class="tw-relative tw-group tw-bg-gray-100 hover:tw-bg-blue-100 tw-rounded-md tw-p-6 tw-flex tw-flex-col tw-items-center"
              >
                <button
                  :disabled="!isLicenseActive"
                  :class="
                    !isLicenseActive
                      ? 'tw-opacity-50 tw-cursor-not-allowed'
                      : ''
                  "
                  @click="() => selectDiscountType(proFeature.value)"
                  class="tw-w-full tw-text-center tw-font-medium"
                >
                  {{ __(proFeature.name, "giantwp-discount-rules") }}
                </button>
                <span
                  v-if="!isLicenseActive"
                  class="tw-absolute tw-top-1 tw-right-1 tw-bg-red-500 tw-text-white tw-text-xs tw-px-2 tw-py-1 tw-rounded"
                  >Pro</span
                >
                <div
                  class="tw-absolute tw-bottom-full tw-mb-2 tw-hidden tw-group-hover:tw-block tw-bg-gray-700 tw-text-white tw-text-xs tw-rounded tw-py-1 tw-px-2 tw-w-48 tw-text-center"
                >
                  {{ __(proFeature.description, "giantwp-discount-rules") }}
                </div>
              </div>
            </div>
          </template>

          <template v-else>
            <FlatPercentageForm
              v-if="selectedDiscountsType === 'Flat/Percentage' && showForm"
              ref="flatPercentageFormRef"
              :initialData="props.editingRule"
            />
            <Bogo
              v-else-if="selectedDiscountsType === 'Bogo'"
              ref="bogoFormRef"
              :initialData="props.editingRule"
            />
            <FreeshippingForm
              v-else-if="
                selectedDiscountsType === 'Shipping Discount' && showForm
              "
              ref="freeShippingRef"
              :initialData="props.editingRule"
            />
            <BuyXGetY
              v-else-if="selectedDiscountsType === 'Buy X Get Y'"
              ref="buyXGetYRef"
              :initialData="props.editingRule"
            />
            <BulkDiscount
              v-else-if="selectedDiscountsType === 'Bulk Discount'"
              ref="bulkDiscountRef"
              :initialData="props.editingRule"
            />
            <p v-else>
              {{ __("Form for", "giantwp-discount-rules") }}
              {{ selectedDiscountsType }}
            </p>
          </template>
        </div>

        <!-- Modal Footer -->
        <div class="tw-mt-4 tw-flex tw-justify-end tw-space-x-4">
          <button
            @click="emit('close')"
            class="tw-bg-gray-300 tw-text-gray-700 tw-px-4 tw-py-2 tw-rounded hover:tw-bg-gray-400"
          >
            {{ __("Close", "giantwp-discount-rules") }}
          </button>
          <button
            v-if="showForm"
            @click="saveForm"
            :disabled="isSaving"
            class="tw-bg-blue-600 tw-text-white tw-px-4 tw-py-2 tw-rounded hover:tw-bg-blue-700"
          >
            {{
              isSaving
                ? __("Saving...", "giantwp-discount-rules")
                : __("Save", "giantwp-discount-rules")
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
