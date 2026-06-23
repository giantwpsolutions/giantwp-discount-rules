<script setup>
import { ref, computed } from "vue";
import { __ } from "@wordpress/i18n";
import {
  ReceiptPercentIcon,
  GiftIcon,
  CubeIcon,
  TruckIcon,
  RectangleStackIcon,
  MagnifyingGlassIcon,
  PlusIcon,
  PencilSquareIcon,
  DocumentDuplicateIcon,
  TrashIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
  discountRules: { type: Array, required: true },
  onAdd:         { type: Function, required: true },
  onEdit:        { type: Function, required: true },
  onDelete:      { type: Function, required: true },
  onToggleStatus:{ type: Function, required: true },
  onDuplicate:   { type: Function, default: null },
});

const searchQuery    = ref("");
const confirmDeleteId = ref(null);

const filteredRules = computed(() => {
  const q = searchQuery.value.trim().toLowerCase();
  if (!q) return props.discountRules;
  return props.discountRules.filter(
    (r) =>
      r.couponName?.toLowerCase().includes(q) ||
      r.discountType?.toLowerCase().includes(q)
  );
});

const formatDate = (dateString) => {
  if (!dateString || dateString === "") return "—";
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return "—";
  return date.toLocaleDateString("en-US", {
    month: "short",
    day: "numeric",
    year: "numeric",
  });
};

const formatUsage = (rule) => {
  const enabled = rule.usageLimits?.enableUsage;
  const total   = Number(rule.usageLimits?.usageLimitsCount ?? 0);
  const used    = Number(rule.usedCount ?? 0);
  return enabled ? `${used} / ${total}` : `${used} / ∞`;
};

const typeConfig = {
  "flat/percentage":  { label: "Flat / %",      icon: ReceiptPercentIcon, bg: "tw-bg-brand-50",   text: "tw-text-brand-600"   },
  "bogo":             { label: "BOGO",           icon: GiftIcon,           bg: "tw-bg-orange-50", text: "tw-text-orange-600" },
  "buy x get y":      { label: "Buy X Get Y",   icon: CubeIcon,           bg: "tw-bg-pink-50",   text: "tw-text-pink-600"   },
  "shipping discount":{ label: "Shipping",       icon: TruckIcon,          bg: "tw-bg-purple-50", text: "tw-text-purple-600" },
  "bulk discount":    { label: "Bulk Discount",  icon: RectangleStackIcon, bg: "tw-bg-green-50",  text: "tw-text-green-600"  },
};

const getType = (discountType) =>
  typeConfig[discountType?.toLowerCase()] ?? {
    label: discountType,
    icon: null,
    bg: "tw-bg-gray-100",
    text: "tw-text-gray-600",
  };

const requestDelete = (id) => { confirmDeleteId.value = id; };
const cancelDelete  = ()  => { confirmDeleteId.value = null; };
const confirmDelete = (rule) => {
  confirmDeleteId.value = null;
  props.onDelete(rule);
};
</script>

<template>
  <div>
    <!-- Toolbar -->
    <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-2 tw-mb-4">
      <button
        @click="onAdd"
        class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-lg tw-bg-brand-600 tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-white tw-shadow-sm tw-transition hover:tw-bg-brand-700"
      >
        <PlusIcon class="tw-h-4 tw-w-4" />
        {{ __("Add New Rule", "giantwp-discount-rules") }}
      </button>

      <div class="tw-relative">
        <MagnifyingGlassIcon class="tw-absolute tw-left-3 tw-top-1/2 tw-h-4 tw-w-4 tw--translate-y-1/2 tw-text-gray-400" />
        <input
          v-model="searchQuery"
          type="text"
          :placeholder="__('Search rules...', 'giantwp-discount-rules')"
          class="tw-rounded-lg tw-border tw-border-gray-200 tw-bg-white tw-py-2 tw-pl-9 tw-pr-4 tw-text-sm tw-text-gray-700 tw-shadow-sm tw-outline-none focus:tw-border-brand-400 focus:tw-ring-1 focus:tw-ring-brand-400"
        />
      </div>
    </div>

    <!-- Table -->
    <div class="tw-rounded-xl tw-border tw-border-gray-200 tw-overflow-x-auto">
      <table class="tw-min-w-full tw-table-auto tw-border-collapse">
        <thead>
          <tr class="tw-border-b tw-border-gray-200 tw-bg-gray-50">
            <th class="tw-w-10 tw-px-4 tw-py-3">
              <input type="checkbox" class="tw-h-4 tw-w-4 tw-rounded tw-border-gray-300" />
            </th>
            <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-gray-500">
              {{ __("Discount Name", "giantwp-discount-rules") }}
            </th>
            <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-gray-500">
              {{ __("Type", "giantwp-discount-rules") }}
            </th>
            <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-gray-500">
              {{ __("Start Date", "giantwp-discount-rules") }}
            </th>
            <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-gray-500">
              {{ __("End Date", "giantwp-discount-rules") }}
            </th>
            <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-gray-500">
              {{ __("Usage", "giantwp-discount-rules") }}
            </th>
            <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-gray-500">
              {{ __("Status", "giantwp-discount-rules") }}
            </th>
            <th class="tw-px-4 tw-py-3 tw-text-left tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-gray-500">
              {{ __("Actions", "giantwp-discount-rules") }}
            </th>
          </tr>
        </thead>

        <tbody class="tw-divide-y tw-divide-gray-100 tw-bg-white">
          <!-- Empty state -->
          <tr v-if="filteredRules.length === 0">
            <td colspan="8" class="tw-py-10 tw-text-center tw-text-sm tw-text-gray-400">
              {{ __("No discount rules found", "giantwp-discount-rules") }}
            </td>
          </tr>

          <!-- Rows -->
          <tr
            v-for="rule in filteredRules"
            :key="rule.id"
            class="tw-transition hover:tw-bg-gray-50"
          >
            <!-- Checkbox -->
            <td class="tw-px-4 tw-py-3">
              <input type="checkbox" class="tw-h-4 tw-w-4 tw-rounded tw-border-gray-300" :value="rule.id" />
            </td>

            <!-- Name -->
            <td class="tw-px-4 tw-py-3 tw-text-sm tw-font-semibold tw-text-gray-800">
              {{ rule.couponName }}
            </td>

            <!-- Type badge -->
            <td class="tw-px-4 tw-py-3">
              <span
                :class="[
                  'tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-full tw-px-2.5 tw-py-1 tw-text-xs tw-font-medium',
                  getType(rule.discountType).bg,
                  getType(rule.discountType).text,
                ]"
              >
                <component
                  :is="getType(rule.discountType).icon"
                  v-if="getType(rule.discountType).icon"
                  class="tw-h-3.5 tw-w-3.5"
                />
                {{ getType(rule.discountType).label }}
              </span>
            </td>

            <!-- Start Date -->
            <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-600">
              {{ formatDate(rule.schedule?.startDate) }}
            </td>

            <!-- End Date -->
            <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-600">
              {{ formatDate(rule.schedule?.endDate) }}
            </td>

            <!-- Usage -->
            <td class="tw-px-4 tw-py-3 tw-text-sm tw-text-gray-600">
              {{ formatUsage(rule) }}
            </td>

            <!-- Status -->
            <td class="tw-px-4 tw-py-3">
              <button
                @click="onToggleStatus({ ...rule, status: rule.status === 'on' ? 'off' : 'on' })"
                :class="[
                  'tw-inline-flex tw-items-center tw-gap-1.5 tw-text-sm tw-font-medium tw-transition',
                  rule.status === 'on' ? 'tw-text-green-600' : 'tw-text-gray-400',
                ]"
              >
                <span
                  :class="[
                    'tw-inline-block tw-h-2 tw-w-2 tw-rounded-full',
                    rule.status === 'on' ? 'tw-bg-green-500' : 'tw-bg-gray-400',
                  ]"
                />
                {{ rule.status === 'on' ? __('Active', 'giantwp-discount-rules') : __('Inactive', 'giantwp-discount-rules') }}
              </button>
            </td>

            <!-- Actions -->
            <td class="tw-px-4 tw-py-3">
              <div class="tw-flex tw-items-center tw-gap-2">
                <!-- Edit -->
                <button
                  @click="onEdit(rule)"
                  class="tw-rounded tw-p-1 tw-text-gray-400 tw-transition hover:tw-bg-gray-100 hover:tw-text-brand-600"
                  :title="__('Edit', 'giantwp-discount-rules')"
                >
                  <PencilSquareIcon class="tw-h-4 tw-w-4" />
                </button>

                <!-- Duplicate -->
                <button
                  v-if="onDuplicate"
                  @click="onDuplicate(rule)"
                  class="tw-rounded tw-p-1 tw-text-gray-400 tw-transition hover:tw-bg-gray-100 hover:tw-text-indigo-600"
                  :title="__('Duplicate', 'giantwp-discount-rules')"
                >
                  <DocumentDuplicateIcon class="tw-h-4 tw-w-4" />
                </button>

                <!-- Delete -->
                <div class="tw-relative">
                  <button
                    @click="requestDelete(rule.id)"
                    class="tw-rounded tw-p-1 tw-text-gray-400 tw-transition hover:tw-bg-gray-100 hover:tw-text-red-600"
                    :title="__('Delete', 'giantwp-discount-rules')"
                  >
                    <TrashIcon class="tw-h-4 tw-w-4" />
                  </button>

                  <!-- Inline confirm popover -->
                  <div
                    v-if="confirmDeleteId === rule.id"
                    class="tw-absolute tw-right-0 tw-top-8 tw-z-10 tw-w-48 tw-rounded-lg tw-border tw-border-gray-200 tw-bg-white tw-p-3 tw-shadow-lg"
                  >
                    <p class="tw-mb-2 tw-text-xs tw-text-gray-700">
                      {{ __("Delete this rule?", "giantwp-discount-rules") }}
                    </p>
                    <div class="tw-flex tw-justify-end tw-gap-2">
                      <button
                        @click="cancelDelete"
                        class="tw-rounded tw-border tw-border-gray-200 tw-px-2 tw-py-1 tw-text-xs tw-text-gray-600 hover:tw-bg-gray-50"
                      >
                        {{ __("No", "giantwp-discount-rules") }}
                      </button>
                      <button
                        @click="confirmDelete(rule)"
                        class="tw-rounded tw-bg-red-600 tw-px-2 tw-py-1 tw-text-xs tw-text-white hover:tw-bg-red-700"
                      >
                        {{ __("Yes", "giantwp-discount-rules") }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
