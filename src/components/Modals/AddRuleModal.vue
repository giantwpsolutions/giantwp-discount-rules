<script setup>
import { ref, defineEmits, watch, nextTick, computed, onMounted, toRaw } from "vue";
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
  ReceiptPercentIcon,
  GiftIcon,
  CubeIcon,
  TruckIcon,
  RectangleStackIcon,
  BoltIcon,
  TagIcon,
  Cog6ToothIcon,
} from "@heroicons/vue/24/outline";
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
const activeTab = ref("templates");

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

const quickTemplates = [
  {
    id: "bogo-1-1",
    name: __("BOGO Deal", "giantwp-discount-rules"),
    desc: __("Buy 1, Get 1 free. Perfect for fashion, shoes, accessories.", "giantwp-discount-rules"),
    type: "Bogo",
    icon: GiftIcon,
    iconBg: "tw-bg-orange-50",
    iconColor: "tw-text-orange-500",
    badge: __("Popular", "giantwp-discount-rules"),
    badgeBg: "tw-bg-orange-100",
    badgeText: "tw-text-orange-600",
    tags: ["Buy 1 Get 1", "Free product"],
    isPro: false,
    data: {
      couponName: "BOGO Deal",
      discountType: "bogo",
      buyProductCount: 1,
      getProductCount: 1,
      freeOrDiscount: "freeproduct",
      isRepeat: true,
      discounttypeBogo: null,
      discountValue: null,
      maxValue: null,
      bogoApplies: "any",
      buyProduct: [],
      status: "on",
      schedule: { enableSchedule: false, startDate: null, endDate: null },
      usageLimits: { enableUsage: false, usageLimitsCount: 0 },
      enableConditions: false,
      conditionsApplies: "any",
      conditions: [],
    },
  },
  {
    id: "first-purchase",
    name: __("First Purchase Discount", "giantwp-discount-rules"),
    desc: __("Give new customers a discount on their very first order.", "giantwp-discount-rules"),
    type: "Flat/Percentage",
    icon: TagIcon,
    iconBg: "tw-bg-pink-50",
    iconColor: "tw-text-pink-500",
    badge: __("New Customers", "giantwp-discount-rules"),
    badgeBg: "tw-bg-green-100",
    badgeText: "tw-text-green-600",
    tags: ["% Discount", "New users", "Condition"],
    isPro: false,
    data: {
      couponName: "First Purchase Discount",
      discountType: "flat/percentage",
      fpDiscountType: "percentage",
      discountValue: 15,
      maxValue: null,
      status: "on",
      schedule: { enableSchedule: false, startDate: null, endDate: null },
      usageLimits: { enableUsage: true, usageLimitsCount: 1 },
      enableConditions: true,
      conditionsApplies: "any",
      conditions: [
        { id: 1, field: "customer_order_count", operator: "less_than", value: "1" },
      ],
    },
  },
  {
    id: "flash-sale",
    name: __("Flash Sale", "giantwp-discount-rules"),
    desc: __("Limited-time percentage or fixed discount with a countdown.", "giantwp-discount-rules"),
    type: "Flat/Percentage",
    icon: BoltIcon,
    iconBg: "tw-bg-yellow-50",
    iconColor: "tw-text-yellow-500",
    badge: __("Time-Limited", "giantwp-discount-rules"),
    badgeBg: "tw-bg-red-100",
    badgeText: "tw-text-red-500",
    tags: ["% or Fixed", "Schedule", "Limited uses"],
    isPro: false,
    data: {
      couponName: "Flash Sale",
      discountType: "flat/percentage",
      fpDiscountType: "percentage",
      discountValue: 30,
      maxValue: null,
      status: "on",
      schedule: { enableSchedule: true, startDate: null, endDate: null },
      usageLimits: { enableUsage: true, usageLimitsCount: 100 },
      enableConditions: false,
      conditionsApplies: "any",
      conditions: [],
    },
  },
  {
    id: "bogo-2-1",
    name: __("Buy 2 Get 1 Free", "giantwp-discount-rules"),
    desc: __("Buy any 2 products and get the 3rd one completely free.", "giantwp-discount-rules"),
    type: "Bogo",
    icon: GiftIcon,
    iconBg: "tw-bg-brand-50",
    iconColor: "tw-text-brand-500",
    badge: __("Popular", "giantwp-discount-rules"),
    badgeBg: "tw-bg-orange-100",
    badgeText: "tw-text-orange-600",
    tags: ["Buy 2 Get 1", "Free product", "Repeatable"],
    isPro: false,
    data: {
      couponName: "Buy 2 Get 1 Free",
      discountType: "bogo",
      buyProductCount: 2,
      getProductCount: 1,
      freeOrDiscount: "freeproduct",
      isRepeat: true,
      discounttypeBogo: null,
      discountValue: null,
      maxValue: null,
      bogoApplies: "any",
      buyProduct: [],
      status: "on",
      schedule: { enableSchedule: false, startDate: null, endDate: null },
      usageLimits: { enableUsage: false, usageLimitsCount: 0 },
      enableConditions: false,
      conditionsApplies: "any",
      conditions: [],
    },
  },
  {
    id: "free-shipping",
    name: __("Free Shipping", "giantwp-discount-rules"),
    desc: __("Offer free shipping on all orders or above a minimum amount.", "giantwp-discount-rules"),
    type: "Shipping Discount",
    icon: TruckIcon,
    iconBg: "tw-bg-purple-50",
    iconColor: "tw-text-purple-500",
    badge: __("Pro Only", "giantwp-discount-rules"),
    badgeBg: "tw-bg-gray-100",
    badgeText: "tw-text-gray-500",
    tags: ["Free shipping", "Min. order", "Condition"],
    isPro: true,
    data: {
      couponName: "Free Shipping",
      discountType: "shipping discount",
      shippingDiscountType: "reduceFee",
      pDiscountType: "percentage",
      discountValue: 100,
      maxValue: null,
      status: "on",
      schedule: { enableSchedule: false, startDate: null, endDate: null },
      usageLimits: { enableUsage: false, usageLimitsCount: 0 },
      enableConditions: true,
      conditionsApplies: "any",
      conditions: [],
    },
  },
  {
    id: "bulk-15",
    name: __("Bulk Buy 15% Off", "giantwp-discount-rules"),
    desc: __("Encourage larger orders with tiered quantity-based discounts.", "giantwp-discount-rules"),
    type: "Bulk Discount",
    icon: RectangleStackIcon,
    iconBg: "tw-bg-teal-50",
    iconColor: "tw-text-teal-500",
    badge: __("Pro Only", "giantwp-discount-rules"),
    badgeBg: "tw-bg-gray-100",
    badgeText: "tw-text-gray-500",
    tags: ["5+ items", "15% off", "Tiered"],
    isPro: true,
    data: {
      couponName: "Bulk Buy 15% Off",
      discountType: "bulk discount",
      getItem: "alltogether",
      bulkDiscounts: [
        { fromcount: 5, toCount: 10, discountTypeBulk: "percentage", discountValue: 15, maxValue: null },
      ],
      getApplies: "any",
      buyProducts: [],
      status: "on",
      schedule: { enableSchedule: false, startDate: null, endDate: null },
      usageLimits: { enableUsage: false, usageLimitsCount: 0 },
      enableConditions: false,
      conditionsApplies: "any",
      conditions: [],
    },
  },
];

const selectTemplate = async (template) => {
  if (template.isPro && !isLicenseActive.value) return;
  selectedDiscountsType.value = template.type;
  showForm.value = true;
  await nextTick();
  await nextTick();
  await nextTick();
  const data = template.data;
  if (template.type === "Flat/Percentage" && flatPercentageFormRef.value)
    flatPercentageFormRef.value.setFormData(data);
  else if (template.type === "Bogo" && bogoFormRef.value)
    bogoFormRef.value.setFormData(data);
  else if (template.type === "Shipping Discount" && freeShippingRef.value)
    freeShippingRef.value.setFormData(data);
  else if (template.type === "Bulk Discount" && bulkDiscountRef.value)
    bulkDiscountRef.value.setFormData(data);
};

watch(() => props.visible, (isVisible) => {
  if (isVisible === false) {
    selectedDiscountsType.value = "";
    showForm.value = false;
    activeTab.value = "templates";
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
        flatPercentageFormRef.value.setFormData(structuredClone(toRaw(newVal)));
      else if (bogoFormRef.value)
        bogoFormRef.value.setFormData(structuredClone(toRaw(newVal)));
      else if (freeShippingRef.value)
        freeShippingRef.value.setFormData(structuredClone(toRaw(newVal)));
      else if (buyXGetYRef.value)
        buyXGetYRef.value.setFormData(structuredClone(toRaw(newVal)));
      else if (bulkDiscountRef.value)
        bulkDiscountRef.value.setFormData(structuredClone(toRaw(newVal)));
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
        class="tw-bg-white tw-rounded-lg tw-shadow-lg tw-h-[85vh] md:tw-h-[75vh] tw-w-[95vw] md:tw-w-[80vw] tw-p-4 md:tw-p-6 tw-grid tw-grid-rows-[auto,1fr,auto]"
      >
        <!-- Modal Header -->
        <div class="tw-border-b tw-pb-4 tw-mb-4 tw-flex tw-items-center tw-space-x-4">
          <button
            v-if="showForm"
            @click="goBack"
            class="tw-text-brand-600 hover:tw-text-brand-800"
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

            <!-- Subtitle -->
            <p class="tw-text-sm tw-text-gray-500 tw-mb-4">
              {{ __("Start from a ready-made template or build from scratch", "giantwp-discount-rules") }}
            </p>

            <!-- Tabs -->
            <div class="tw-border-b tw-border-gray-200 tw-mb-6">
              <div class="tw-flex tw-gap-6">
                <button
                  @click="activeTab = 'templates'"
                  :class="[
                    'tw-flex tw-items-center tw-gap-1.5 tw-pb-3 tw-text-sm tw-font-medium tw-transition tw-border-b-2 -tw-mb-px',
                    activeTab === 'templates'
                      ? 'tw-border-brand-600 tw-text-brand-600'
                      : 'tw-border-transparent tw-text-gray-500 hover:tw-text-gray-700',
                  ]"
                >
                  <ReceiptPercentIcon class="tw-h-4 tw-w-4" />
                  {{ __("Quick Start Templates", "giantwp-discount-rules") }}
                </button>

                <button
                  @click="activeTab = 'custom'"
                  :class="[
                    'tw-flex tw-items-center tw-gap-1.5 tw-pb-3 tw-text-sm tw-font-medium tw-transition tw-border-b-2 -tw-mb-px',
                    activeTab === 'custom'
                      ? 'tw-border-brand-600 tw-text-brand-600'
                      : 'tw-border-transparent tw-text-gray-500 hover:tw-text-gray-700',
                  ]"
                >
                  <Cog6ToothIcon class="tw-h-4 tw-w-4" />
                  {{ __("Custom Rule", "giantwp-discount-rules") }}
                </button>
              </div>
            </div>

            <!-- Tab: Quick Start Templates -->
            <div v-if="activeTab === 'templates'" class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 md:tw-grid-cols-3 tw-gap-4">
              <button
                v-for="tpl in quickTemplates"
                :key="tpl.id"
                @click="selectTemplate(tpl)"
                :disabled="tpl.isPro && !isLicenseActive"
                :class="[
                  'tw-flex tw-flex-col tw-rounded-2xl tw-border tw-p-4 tw-text-left tw-transition tw-group',
                  tpl.isPro && !isLicenseActive
                    ? 'tw-border-gray-200 tw-bg-white tw-opacity-60 tw-cursor-not-allowed'
                    : 'tw-border-gray-200 tw-bg-white tw-shadow-sm hover:tw-shadow-md hover:tw-border-gray-300 tw-cursor-pointer',
                ]"
              >
                <!-- Top row: icon + badge -->
                <div class="tw-flex tw-items-start tw-justify-between tw-mb-3">
                  <div :class="['tw-flex tw-h-10 tw-w-10 tw-items-center tw-justify-center tw-rounded-xl', tpl.iconBg]">
                    <component :is="tpl.icon" :class="['tw-h-5 tw-w-5', tpl.iconColor]" />
                  </div>
                  <span :class="['tw-rounded-full tw-px-2.5 tw-py-0.5 tw-text-[11px] tw-font-semibold', tpl.badgeBg, tpl.badgeText]">
                    {{ tpl.badge }}
                  </span>
                </div>
                <!-- Title -->
                <p class="tw-text-sm tw-font-bold tw-text-gray-900 tw-mb-1">{{ tpl.name }}</p>
                <!-- Description -->
                <p class="tw-text-xs tw-text-gray-500 tw-leading-relaxed tw-mb-3 tw-line-clamp-2">{{ tpl.desc }}</p>
                <!-- Tags + Arrow -->
                <div class="tw-flex tw-items-end tw-justify-between tw-mt-auto">
                  <div class="tw-flex tw-flex-wrap tw-gap-1">
                    <span
                      v-for="tag in tpl.tags"
                      :key="tag"
                      class="tw-rounded-full tw-bg-gray-100 tw-px-2 tw-py-0.5 tw-text-[11px] tw-text-gray-600"
                    >{{ tag }}</span>
                  </div>
                  <span class="tw-text-gray-300 tw-text-base tw-ml-2 group-hover:tw-text-gray-500 tw-transition">→</span>
                </div>
              </button>
            </div>

            <!-- Tab: Custom Rule -->
            <div v-if="activeTab === 'custom'" class="tw-grid tw-grid-cols-1 sm:tw-grid-cols-2 md:tw-grid-cols-3 tw-gap-4">

              <!-- Flat / Percentage -->
              <button
                @click="() => selectDiscountType('Flat/Percentage')"
                class="tw-relative tw-flex tw-flex-col tw-items-center tw-gap-3 tw-rounded-xl tw-border tw-border-gray-200 tw-bg-white tw-p-6 tw-text-center tw-shadow-sm tw-transition hover:tw-border-brand-400 hover:tw-shadow-md tw-cursor-pointer"
              >
                <div class="tw-flex tw-h-14 tw-w-14 tw-items-center tw-justify-center tw-rounded-xl tw-bg-brand-50">
                  <ReceiptPercentIcon class="tw-h-7 tw-w-7 tw-text-brand-500" />
                </div>
                <span class="tw-font-semibold tw-text-gray-800">{{ __("Flat / Percentage", "giantwp-discount-rules") }}</span>
                <span class="tw-text-xs tw-text-gray-500">{{ __("Fixed amount or % off", "giantwp-discount-rules") }}</span>
              </button>

              <!-- BOGO -->
              <button
                @click="() => selectDiscountType('Bogo')"
                class="tw-relative tw-flex tw-flex-col tw-items-center tw-gap-3 tw-rounded-xl tw-border tw-border-gray-200 tw-bg-white tw-p-6 tw-text-center tw-shadow-sm tw-transition hover:tw-border-orange-400 hover:tw-shadow-md tw-cursor-pointer"
              >
                <div class="tw-flex tw-h-14 tw-w-14 tw-items-center tw-justify-center tw-rounded-xl tw-bg-orange-50">
                  <GiftIcon class="tw-h-7 tw-w-7 tw-text-orange-500" />
                </div>
                <span class="tw-font-semibold tw-text-gray-800">{{ __("BOGO", "giantwp-discount-rules") }}</span>
                <span class="tw-text-xs tw-text-gray-500">{{ __("Buy one, get one free/discounted", "giantwp-discount-rules") }}</span>
              </button>

              <!-- Buy X Get Y -->
              <div
                class="tw-relative tw-flex tw-flex-col tw-items-center tw-gap-3 tw-rounded-xl tw-border tw-border-gray-200 tw-bg-white tw-p-6 tw-text-center tw-shadow-sm tw-transition"
                :class="isLicenseActive ? 'hover:tw-border-pink-400 hover:tw-shadow-md tw-cursor-pointer' : 'tw-opacity-60 tw-cursor-not-allowed'"
                @click="isLicenseActive ? selectDiscountType('Buy X Get Y') : null"
              >
                <span class="tw-absolute tw-top-2 tw-right-2 tw-rounded tw-bg-red-500 tw-px-2 tw-py-0.5 tw-text-[10px] tw-font-bold tw-text-white" v-if="!isLicenseActive">PRO</span>
                <div class="tw-flex tw-h-14 tw-w-14 tw-items-center tw-justify-center tw-rounded-xl tw-bg-pink-50">
                  <CubeIcon class="tw-h-7 tw-w-7 tw-text-pink-500" />
                </div>
                <span class="tw-font-semibold tw-text-gray-800">{{ __("Buy X Get Y", "giantwp-discount-rules") }}</span>
                <span class="tw-text-xs tw-text-gray-500">{{ __("Buy a set, get another free", "giantwp-discount-rules") }}</span>
              </div>

              <!-- Shipping Discount -->
              <div
                class="tw-relative tw-flex tw-flex-col tw-items-center tw-gap-3 tw-rounded-xl tw-border tw-border-gray-200 tw-bg-white tw-p-6 tw-text-center tw-shadow-sm tw-transition"
                :class="isLicenseActive ? 'hover:tw-border-purple-400 hover:tw-shadow-md tw-cursor-pointer' : 'tw-opacity-60 tw-cursor-not-allowed'"
                @click="isLicenseActive ? selectDiscountType('Shipping Discount') : null"
              >
                <span class="tw-absolute tw-top-2 tw-right-2 tw-rounded tw-bg-red-500 tw-px-2 tw-py-0.5 tw-text-[10px] tw-font-bold tw-text-white" v-if="!isLicenseActive">PRO</span>
                <div class="tw-flex tw-h-14 tw-w-14 tw-items-center tw-justify-center tw-rounded-xl tw-bg-purple-50">
                  <TruckIcon class="tw-h-7 tw-w-7 tw-text-purple-500" />
                </div>
                <span class="tw-font-semibold tw-text-gray-800">{{ __("Shipping Discount", "giantwp-discount-rules") }}</span>
                <span class="tw-text-xs tw-text-gray-500">{{ __("Free or reduced shipping rules", "giantwp-discount-rules") }}</span>
              </div>

              <!-- Bulk Discount -->
              <div
                class="tw-relative tw-flex tw-flex-col tw-items-center tw-gap-3 tw-rounded-xl tw-border tw-border-gray-200 tw-bg-white tw-p-6 tw-text-center tw-shadow-sm tw-transition"
                :class="isLicenseActive ? 'hover:tw-border-green-400 hover:tw-shadow-md tw-cursor-pointer' : 'tw-opacity-60 tw-cursor-not-allowed'"
                @click="isLicenseActive ? selectDiscountType('Bulk Discount') : null"
              >
                <span class="tw-absolute tw-top-2 tw-right-2 tw-rounded tw-bg-red-500 tw-px-2 tw-py-0.5 tw-text-[10px] tw-font-bold tw-text-white" v-if="!isLicenseActive">PRO</span>
                <div class="tw-flex tw-h-14 tw-w-14 tw-items-center tw-justify-center tw-rounded-xl tw-bg-green-50">
                  <RectangleStackIcon class="tw-h-7 tw-w-7 tw-text-green-500" />
                </div>
                <span class="tw-font-semibold tw-text-gray-800">{{ __("Bulk Discount", "giantwp-discount-rules") }}</span>
                <span class="tw-text-xs tw-text-gray-500">{{ __("Tiered pricing by quantity", "giantwp-discount-rules") }}</span>
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
            class="tw-bg-brand-600 tw-text-white tw-px-4 tw-py-2 tw-rounded hover:tw-bg-brand-700"
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
