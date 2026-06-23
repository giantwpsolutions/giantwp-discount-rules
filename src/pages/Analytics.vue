<script setup>
import { ref, computed, onMounted } from "vue";
import { ArrowPathIcon, TrophyIcon, CurrencyDollarIcon, TagIcon, ChartBarIcon } from "@heroicons/vue/24/outline";
import VueApexCharts from "vue3-apexcharts";
import Sidebar from "../components/Sidebar.vue";
import {
  analyticsRows,
  analyticsTotals,
  isLoadingAnalytics,
  loadAnalytics,
  resetAnalytics,
} from "@/data/analyticsData.js";
import { generalData, loadGeneralData } from "@/data/GeneralDataFetch.js";

const { __ } = wp.i18n;

const confirmReset = ref(false);
const isResetting  = ref(false);

onMounted(() => {
  loadGeneralData();
  loadAnalytics();
});

// Returns formatted number only — currency symbol rendered separately via v-html
const fmtNum = (val) =>
  Number(val || 0).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 });

const currencySymbol = computed(() => generalData.value?.currency_symbol || "$");

const bestRule = computed(() =>
  analyticsRows.value.length ? analyticsRows.value[0]?.name || "—" : "—"
);

const typeLabel = (type) => {
  const map = {
    "bogo":            __("BOGO", "giantwp-discount-rules"),
    "flat/percentage": __("Flat/Percentage", "giantwp-discount-rules"),
    "bulk discount":   __("Bulk", "giantwp-discount-rules"),
    "buy x get y":     __("Buy X Get Y", "giantwp-discount-rules"),
    "shipping":        __("Shipping", "giantwp-discount-rules"),
  };
  return map[type?.toLowerCase()] || type;
};

const typeColor = (type) => {
  const map = {
    "bogo":            "tw-bg-purple-100 tw-text-purple-700",
    "flat/percentage": "tw-bg-brand-100 tw-text-brand-700",
    "bulk discount":   "tw-bg-orange-100 tw-text-orange-700",
    "buy x get y":     "tw-bg-green-100 tw-text-green-700",
    "shipping":        "tw-bg-teal-100 tw-text-teal-700",
  };
  return map[type?.toLowerCase()] || "tw-bg-gray-100 tw-text-gray-600";
};

// Chart config
const chartOptions = computed(() => ({
  chart:   { type: "bar", toolbar: { show: false }, fontFamily: "Inter, sans-serif" },
  colors:  ["#1c4a96"],
  plotOptions: { bar: { borderRadius: 4, horizontal: false, columnWidth: "50%" } },
  dataLabels: { enabled: false },
  xaxis: {
    categories: analyticsRows.value.slice(0, 8).map((r) => r.name || r.id),
    labels: { style: { fontSize: "11px" }, rotate: -30 },
  },
  yaxis: {
    labels: {
      formatter: (v) => Number(v).toLocaleString("en-US", { maximumFractionDigits: 0 }),
    },
  },
  tooltip: {
    y: { formatter: (v) => fmtNum(v) },
  },
  grid: { borderColor: "#f0f0f0" },
}));

const chartSeries = computed(() => [
  {
    name: __("Revenue", "giantwp-discount-rules"),
    data: analyticsRows.value.slice(0, 8).map((r) => parseFloat(r.revenue_total || 0).toFixed(2)),
  },
]);

const handleReset = async () => {
  if (!confirmReset.value) {
    confirmReset.value = true;
    return;
  }
  isResetting.value  = true;
  confirmReset.value = false;
  await resetAnalytics();
  isResetting.value = false;
};
</script>

<template>
  <div class="tw-flex tw-gap-4 tw-m-4 tw-items-start">

    <!-- Main Content -->
    <div class="tw-flex-1 tw-min-w-0">

      <div>

          <!-- Summary Cards -->
          <div class="tw-grid tw-grid-cols-2 lg:tw-grid-cols-4 tw-gap-3 tw-mb-4">

            <!-- Total Revenue -->
            <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-p-4">
              <div class="tw-flex tw-items-center tw-gap-2 tw-mb-2">
                <div class="tw-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-lg tw-bg-green-50">
                  <CurrencyDollarIcon class="tw-h-4 tw-w-4 tw-text-green-600" />
                </div>
                <span class="tw-text-xs tw-text-gray-500 tw-font-medium">{{ __("Total Revenue", "giantwp-discount-rules") }}</span>
              </div>
              <p class="tw-text-xl tw-font-bold tw-text-gray-800"><span v-html="currencySymbol" />{{ fmtNum(analyticsTotals.total_revenue) }}</p>
            </div>

            <!-- Total Discount -->
            <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-p-4">
              <div class="tw-flex tw-items-center tw-gap-2 tw-mb-2">
                <div class="tw-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-lg tw-bg-red-50">
                  <TagIcon class="tw-h-4 tw-w-4 tw-text-red-500" />
                </div>
                <span class="tw-text-xs tw-text-gray-500 tw-font-medium">{{ __("Total Discount Given", "giantwp-discount-rules") }}</span>
              </div>
              <p class="tw-text-xl tw-font-bold tw-text-gray-800"><span v-html="currencySymbol" />{{ fmtNum(analyticsTotals.total_discount) }}</p>
            </div>

            <!-- Total Applied -->
            <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-p-4">
              <div class="tw-flex tw-items-center tw-gap-2 tw-mb-2">
                <div class="tw-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-lg tw-bg-brand-50">
                  <ChartBarIcon class="tw-h-4 tw-w-4 tw-text-brand-600" />
                </div>
                <span class="tw-text-xs tw-text-gray-500 tw-font-medium">{{ __("Orders Impacted", "giantwp-discount-rules") }}</span>
              </div>
              <p class="tw-text-xl tw-font-bold tw-text-gray-800">{{ analyticsTotals.total_applied }}</p>
            </div>

            <!-- Best Rule -->
            <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-p-4">
              <div class="tw-flex tw-items-center tw-gap-2 tw-mb-2">
                <div class="tw-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-lg tw-bg-amber-50">
                  <TrophyIcon class="tw-h-4 tw-w-4 tw-text-amber-500" />
                </div>
                <span class="tw-text-xs tw-text-gray-500 tw-font-medium">{{ __("Best Performing Rule", "giantwp-discount-rules") }}</span>
              </div>
              <p class="tw-text-sm tw-font-bold tw-text-gray-800 tw-truncate" :title="bestRule">{{ bestRule }}</p>
            </div>
          </div>

          <!-- Chart -->
          <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-mb-4 tw-overflow-hidden">
            <div class="tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
              <p class="tw-text-sm tw-font-bold tw-text-gray-800">{{ __("Revenue by Rule (Top 8)", "giantwp-discount-rules") }}</p>
              <p class="tw-text-xs tw-text-gray-400">{{ __("Completed orders attributed to each discount rule", "giantwp-discount-rules") }}</p>
            </div>
            <div class="tw-p-4">
              <div v-if="isLoadingAnalytics" class="tw-flex tw-justify-center tw-py-10">
                <ArrowPathIcon class="tw-h-6 tw-w-6 tw-animate-spin tw-text-gray-400" />
              </div>
              <div v-else-if="analyticsRows.length === 0" class="tw-text-center tw-py-10 tw-text-sm tw-text-gray-400">
                {{ __("No data yet. Analytics will populate as orders complete.", "giantwp-discount-rules") }}
              </div>
              <VueApexCharts
                v-else
                type="bar"
                height="280"
                :options="chartOptions"
                :series="chartSeries"
              />
            </div>
          </div>

          <!-- Table -->
          <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-overflow-x-auto">
            <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
              <div>
                <p class="tw-text-sm tw-font-bold tw-text-gray-800">{{ __("Rule Performance", "giantwp-discount-rules") }}</p>
                <p class="tw-text-xs tw-text-gray-400">{{ __("All active rules sorted by revenue generated", "giantwp-discount-rules") }}</p>
              </div>
              <div class="tw-flex tw-items-center tw-gap-2">
                <el-button
                  size="small"
                  :loading="isLoadingAnalytics"
                  @click="loadAnalytics"
                  :icon="ArrowPathIcon"
                >
                  {{ __("Refresh", "giantwp-discount-rules") }}
                </el-button>
                <el-button
                  size="small"
                  :type="confirmReset ? 'danger' : 'default'"
                  :loading="isResetting"
                  @click="handleReset"
                >
                  {{ confirmReset ? __("Confirm Reset?", "giantwp-discount-rules") : __("Reset Data", "giantwp-discount-rules") }}
                </el-button>
              </div>
            </div>

            <div v-if="isLoadingAnalytics" class="tw-flex tw-justify-center tw-py-10">
              <ArrowPathIcon class="tw-h-6 tw-w-6 tw-animate-spin tw-text-gray-400" />
            </div>

            <div v-else-if="analyticsRows.length === 0" class="tw-text-center tw-py-12 tw-text-sm tw-text-gray-400">
              {{ __("No analytics data yet.", "giantwp-discount-rules") }}
            </div>

            <table v-else class="tw-w-full tw-text-sm">
              <thead class="tw-bg-gray-50 tw-text-xs tw-text-gray-500 tw-uppercase tw-tracking-wide">
                <tr>
                  <th class="tw-px-5 tw-py-3 tw-text-left tw-font-medium">{{ __("Rule Name", "giantwp-discount-rules") }}</th>
                  <th class="tw-px-4 tw-py-3 tw-text-left tw-font-medium">{{ __("Type", "giantwp-discount-rules") }}</th>
                  <th class="tw-px-4 tw-py-3 tw-text-right tw-font-medium">{{ __("Applied", "giantwp-discount-rules") }}</th>
                  <th class="tw-px-4 tw-py-3 tw-text-right tw-font-medium">{{ __("Revenue", "giantwp-discount-rules") }}</th>
                  <th class="tw-px-4 tw-py-3 tw-text-right tw-font-medium">{{ __("Discount Given", "giantwp-discount-rules") }}</th>
                  <th class="tw-px-4 tw-py-3 tw-text-right tw-font-medium">{{ __("Last Applied", "giantwp-discount-rules") }}</th>
                </tr>
              </thead>
              <tbody class="tw-divide-y tw-divide-gray-100">
                <tr v-for="row in analyticsRows" :key="row.id" class="hover:tw-bg-gray-50 tw-transition-colors">
                  <td class="tw-px-5 tw-py-3 tw-font-medium tw-text-gray-800 tw-max-w-xs tw-truncate">{{ row.name || row.id }}</td>
                  <td class="tw-px-4 tw-py-3">
                    <span :class="['tw-inline-block tw-rounded tw-px-2 tw-py-0.5 tw-text-xs tw-font-semibold', typeColor(row.type)]">
                      {{ typeLabel(row.type) }}
                    </span>
                  </td>
                  <td class="tw-px-4 tw-py-3 tw-text-right tw-text-gray-700">{{ row.applied }}</td>
                  <td class="tw-px-4 tw-py-3 tw-text-right tw-font-semibold tw-text-green-700"><span v-html="currencySymbol" />{{ fmtNum(row.revenue_total) }}</td>
                  <td class="tw-px-4 tw-py-3 tw-text-right tw-text-red-600"><span v-html="currencySymbol" />{{ fmtNum(row.discount_total) }}</td>
                  <td class="tw-px-4 tw-py-3 tw-text-right tw-text-gray-400 tw-text-xs">{{ row.last_applied || "—" }}</td>
                </tr>
              </tbody>
            </table>
          </div>
      </div>
    </div>

    <!-- Sidebar -->
    <div class="tw-w-64 tw-shrink-0 tw-hidden lg:tw-block">
      <Sidebar />
    </div>

  </div>
</template>
