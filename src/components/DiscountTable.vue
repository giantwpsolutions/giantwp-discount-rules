<script setup>
import { ref } from "vue";
import { Edit, Delete, InfoFilled } from "@element-plus/icons-vue";
import { __ } from "@wordpress/i18n";

// Define props (No need to import defineProps)
defineProps({
  discountRules: {
    type: Array,
    required: true,
  },
  onEdit: {
    type: Function,
    required: true,
  },
  onDelete: {
    type: Function,
    required: true,
  },
  onToggleStatus: {
    type: Function,
    required: true,
  },
});

// Function to format the date (YYYY-MM-DD)
const formatDate = (dateString) => {
  if (!dateString || dateString === "") return "-"; // Handle empty or null dates

  const date = new Date(dateString);
  if (isNaN(date.getTime())) return "-"; // Handle invalid dates

  return date.toISOString().split("T")[0]; // Extracts "YYYY-MM-DD"
};

const formatUsage = (rule) => {
  const enabled = rule.usageLimits?.enableUsage;
  const total = Number(rule.usageLimits?.usageLimitsCount ?? 0);
  const used = Number(rule.usedCount ?? 0);

  return enabled
    ? `${used} / ${total}`
    : __("Unlimited", "giantwp-discount-rules");
};

// Track clicked state for confirmation
const clicked = ref(false);

const handleCancel = () => {
  clicked.value = true;
};
</script>

<template>
  <div class="tw-overflow-x-auto">
    <table class="tw-min-w-full tw-table-auto tw-border-collapse tw-border tw-border-gray-200">
      <thead>
        <tr class="tw-bg-gray-100 tw-text-left">
          <th class="tw-px-4 tw-py-2 tw-border-b tw-w-12">
            <input type="checkbox" class="h-5 w-5" />
          </th>
          <th class="tw-px-4 tw-py-2 tw-border-b">
            {{ __("Discount Name", "giantwp-discount-rules") }}
          </th>
          <th class="tw-px-4 tw-py-2 tw-border-b">
            {{ __("Type", "giantwp-discount-rules") }}
          </th>
          <th class="tw-px-4 tw-py-2 tw-border-b">
            {{ __("Start Date", "giantwp-discount-rules") }}
          </th>
          <th class="tw-px-4 tw-py-2 tw-border-b">
            {{ __("End Date", "giantwp-discount-rules") }}
          </th>
          <th class="tw-px-4 tw-py-2 tw-border-b">
            {{ __("Usage Limits", "giantwp-discount-rules") }}
          </th>
          <th class="tw-px-4 tw-py-2 tw-border-b">
            {{ __("Status", "giantwp-discount-rules") }}
          </th>
          <th class="tw-px-4 tw-py-2 tw-border-b">
            {{ __("Actions", "giantwp-discount-rules") }}
          </th>
        </tr>
      </thead>
      <tbody>
        <!-- No Data Row -->
        <tr v-if="discountRules.length === 0">
          <td colspan="7" class="tw-text-center tw-px-4 tw-py-6 tw-text-gray-500">
            {{ __("No discount rules created", "giantwp-discount-rules") }}
          </td>
        </tr>

        <!-- Discount Rule Rows -->
        <tr v-for="rule in discountRules" :key="rule.id">
          <td class="tw-px-4 tw-py-2 tw-border-b">
            <input type="checkbox" class="tw-h-5 tw-w-5" :value="rule.id" />
          </td>
          <td class="tw-px-4 tw-py-2 tw-border-b">{{ rule.couponName }}</td>
          <td class="tw-px-4 tw-py-2 tw-border-b tw-capitalize">{{ rule.discountType }}</td>
          <td class="tw-px-4 tw-py-2 tw-border-b">
            {{ formatDate(rule.schedule?.startDate) || "--" }}
          </td>
          <td class="tw-px-4 tw-py-2 tw-border-b">
            {{ formatDate(rule.schedule?.endDate) || "--" }}
          </td>

          <td class="tw-px-4 tw-py-2 tw-border-b">
            {{ formatUsage(rule) }}
          </td>
          <td class="tw-px-4 tw-py-2 tw-border-b">
            <label class="tw-inline-flex tw-relative tw-items-center tw-cursor-pointer">
              <el-switch
                v-model="rule.status"
                :active-value="'on'"
                :inactive-value="'off'"
                @update:model-value="
                  (val) => onToggleStatus({ ...rule, status: val })
                " />
            </label>
          </td>
          <td class="tw-px-4 tw-py-2 tw-border-b">
            <!-- Edit Button -->
            <el-tooltip
              class="box-item"
              effect="dark"
              :content="__('Edit Rule', 'giantwp-discount-rules')"
              placement="top">
              <el-icon
                @click="onEdit(rule)"
                class="tw-text-blue-600 tw-hover:text-blue-800 tw-mr-2 tw-hover:cursor-pointer"
                :size="20"
                ><Edit
              /></el-icon>
            </el-tooltip>

            <!-- Delete Button with Confirmation -->

            <el-popconfirm
              width="220"
              icon-color="#626AEF"
              :title="
                __(
                  'Are you sure you want to delete this discount?',
                  'giantwp-discount-rules'
                )
              "
              @confirm="onDelete(rule)">
              <template #reference>
                <span>
                  <el-tooltip
                    effect="dark"
                    :content="__('Delete Rule', 'giantwp-discount-rules')"
                    placement="top">
                    <el-icon
                      class="tw-text-red-600 tw-hover:text-red-800 tw-hover:cursor-pointer"
                      :size="20">
                      <Delete />
                    </el-icon>
                  </el-tooltip>
                </span>
              </template>

              <template #actions="{ confirm, cancel }">
                <el-button size="small" @click="cancel">No</el-button>
                <el-button type="danger" size="small" @click="confirm"
                  >Yes</el-button
                >
              </template>
            </el-popconfirm>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
