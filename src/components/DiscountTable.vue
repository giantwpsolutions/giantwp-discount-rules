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
    : __("Unlimited", "dealbuilder-discount-rules");
};

// Track clicked state for confirmation
const clicked = ref(false);

const handleCancel = () => {
  clicked.value = true;
};
</script>

<template>
  <div class="overflow-x-auto">
    <table class="min-w-full table-auto border-collapse border border-gray-200">
      <thead>
        <tr class="bg-gray-100 text-left">
          <th class="px-4 py-2 border-b w-12">
            <input type="checkbox" class="h-5 w-5" />
          </th>
          <th class="px-4 py-2 border-b">
            {{ __("Discount Name", "dealbuilder-discount-rules") }}
          </th>
          <th class="px-4 py-2 border-b">
            {{ __("Type", "dealbuilder-discount-rules") }}
          </th>
          <th class="px-4 py-2 border-b">
            {{ __("Start Date", "dealbuilder-discount-rules") }}
          </th>
          <th class="px-4 py-2 border-b">
            {{ __("End Date", "dealbuilder-discount-rules") }}
          </th>
          <th class="px-4 py-2 border-b">
            {{ __("Usage Limits", "dealbuilder-discount-rules") }}
          </th>
          <th class="px-4 py-2 border-b">
            {{ __("Status", "dealbuilder-discount-rules") }}
          </th>
          <th class="px-4 py-2 border-b">
            {{ __("Actions", "dealbuilder-discount-rules") }}
          </th>
        </tr>
      </thead>
      <tbody>
        <!-- No Data Row -->
        <tr v-if="discountRules.length === 0">
          <td colspan="7" class="text-center px-4 py-6 text-gray-500">
            {{ __("No discount rules created", "dealbuilder-discount-rules") }}
          </td>
        </tr>

        <!-- Discount Rule Rows -->
        <tr v-for="rule in discountRules" :key="rule.id">
          <td class="px-4 py-2 border-b">
            <input type="checkbox" class="h-5 w-5" :value="rule.id" />
          </td>
          <td class="px-4 py-2 border-b">{{ rule.couponName }}</td>
          <td class="px-4 py-2 border-b capitalize">{{ rule.discountType }}</td>
          <td class="px-4 py-2 border-b">
            {{ formatDate(rule.schedule?.startDate) || "--" }}
          </td>
          <td class="px-4 py-2 border-b">
            {{ formatDate(rule.schedule?.endDate) || "--" }}
          </td>

          <td class="px-4 py-2 border-b">
            {{ formatUsage(rule) }}
          </td>
          <td class="px-4 py-2 border-b">
            <label class="inline-flex relative items-center cursor-pointer">
              <el-switch
                v-model="rule.status"
                :active-value="'on'"
                :inactive-value="'off'"
                @update:model-value="
                  (val) => onToggleStatus({ ...rule, status: val })
                " />
            </label>
          </td>
          <td class="px-4 py-2 border-b">
            <!-- Edit Button -->
            <el-tooltip
              class="box-item"
              effect="dark"
              :content="__('Edit Rule', 'dealbuilder-discount-rules')"
              placement="top">
              <el-icon
                @click="onEdit(rule)"
                class="text-blue-600 hover:text-blue-800 mr-2 hover:cursor-pointer"
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
                  'dealbuilder-discount-rules'
                )
              "
              @confirm="onDelete(rule)">
              <template #reference>
                <span>
                  <el-tooltip
                    effect="dark"
                    :content="__('Delete Rule', 'dealbuilder-discount-rules')"
                    placement="top">
                    <el-icon
                      class="text-red-600 hover:text-red-800 hover:cursor-pointer"
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
