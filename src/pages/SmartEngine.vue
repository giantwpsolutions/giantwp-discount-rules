<script setup>
import { onMounted, computed } from 'vue';
import {
  SparklesIcon,
  CheckIcon,
  UserGroupIcon,
  CurrencyDollarIcon,
  BoltIcon,
  LockClosedIcon,
  ChatBubbleLeftEllipsisIcon,
} from '@heroicons/vue/24/outline';
import Sidebar from '../components/Sidebar.vue';
import {
  smartEngineData,
  smartEngineStats,
  isLoadingSmartEngine,
  isConnecting,
  connectStatus,
  connectMessage,
  loadSmartEngine,
  saveSmartEngine,
  testConnection,
} from '@/data/save-data/saveSmartEngineData.js';
import { settingsUpdate, errorMessage } from '@/data/message.js';

const { __ } = wp.i18n;

const isProActive = !!gwpdrPluginData?.proActive;

const providers = [
  {
    id:          'anthropic',
    name:        'Anthropic Claude',
    model:       'claude-haiku-4-5',
    note:        __('Fast, cost-efficient, great reasoning', 'giantwp-discount-rules'),
    docsUrl:     'https://console.anthropic.com/',
    keyLabel:    __('Anthropic API Key', 'giantwp-discount-rules'),
    placeholder: 'sk-ant-...',
  },
  {
    id:          'openai',
    name:        'OpenAI GPT-4o',
    model:       'gpt-4o',
    note:        __('Industry standard, high accuracy', 'giantwp-discount-rules'),
    docsUrl:     'https://platform.openai.com/api-keys',
    keyLabel:    __('OpenAI API Key', 'giantwp-discount-rules'),
    placeholder: 'sk-...',
  },
  {
    id:          'gemini',
    name:        'Google Gemini',
    model:       'gemini-2.0-flash',
    note:        __('Google-powered, multimodal AI', 'giantwp-discount-rules'),
    docsUrl:     'https://aistudio.google.com/app/apikey',
    keyLabel:    __('Google AI API Key', 'giantwp-discount-rules'),
    placeholder: 'AIza...',
  },
];

const selectedProvider = computed(
  () => providers.find(p => p.id === smartEngineData.value.provider) ?? providers[0]
);

const formattedRevenue = computed(() => {
  const v = Number(smartEngineStats.value.extra_revenue ?? 0);
  return new Intl.NumberFormat(undefined, {
    style: 'currency', currency: 'USD', maximumFractionDigits: 0,
  }).format(v);
});

onMounted(() => {
  if (isProActive) loadSmartEngine();
});

const handleSave = async () => {
  try {
    await saveSmartEngine();
    settingsUpdate();
  } catch {
    errorMessage();
  }
};

const selectProvider = (id) => {
  smartEngineData.value.provider = id;
  connectStatus.value  = null;
  connectMessage.value = '';
};
</script>

<template>
  <div class="tw-flex tw-gap-4 tw-m-4 tw-items-start">

    <div class="tw-flex-1 tw-min-w-0">

      <!-- Pro gate -->
      <div v-if="!isProActive" class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-overflow-hidden">
        <div class="tw-bg-gradient-to-r tw-from-violet-600 tw-to-indigo-600 tw-px-6 tw-py-8 tw-text-center">
          <div class="tw-mx-auto tw-mb-3 tw-flex tw-h-14 tw-w-14 tw-items-center tw-justify-center tw-rounded-2xl tw-bg-white/20">
            <SparklesIcon class="tw-h-7 tw-w-7 tw-text-white" />
          </div>
          <h2 class="tw-text-xl tw-font-bold tw-text-white tw-mb-1">
            {{ __('Smart Engine', 'giantwp-discount-rules') }}
          </h2>
          <p class="tw-text-sm tw-text-white/80">
            {{ __('Your AI-powered Virtual Salesman — always pushing, never sleeping', 'giantwp-discount-rules') }}
          </p>
          <span class="tw-mt-3 tw-inline-block tw-rounded-full tw-bg-white/20 tw-px-3 tw-py-0.5 tw-text-xs tw-font-bold tw-text-white tw-uppercase tw-tracking-wide">
            {{ __('Pro Feature', 'giantwp-discount-rules') }}
          </span>
        </div>
        <div class="tw-px-6 tw-py-6">
          <p class="tw-text-sm tw-font-semibold tw-text-gray-700 tw-mb-4">
            {{ __('What Smart Engine does for every customer:', 'giantwp-discount-rules') }}
          </p>
          <ul class="tw-space-y-3 tw-mb-6">
            <li v-for="feat in [
              __('Threshold nudge — spend $100 more to unlock free shipping', 'giantwp-discount-rules'),
              __('Quantity upsell — buy 10+ of same item for 10% bulk discount', 'giantwp-discount-rules'),
              __('BOGO — add one more product and get cheapest free', 'giantwp-discount-rules'),
              __('Exit rescue — catches leaving customers with a last-second offer', 'giantwp-discount-rules'),
              __('AI writes a personalised message for every single customer', 'giantwp-discount-rules'),
            ]" :key="feat" class="tw-flex tw-items-start tw-gap-2.5">
              <CheckIcon class="tw-h-4 tw-w-4 tw-text-violet-500 tw-mt-0.5 tw-shrink-0" />
              <span class="tw-text-sm tw-text-gray-600">{{ feat }}</span>
            </li>
          </ul>
          <div class="tw-flex tw-items-center tw-gap-3 tw-rounded-lg tw-border tw-border-violet-200 tw-bg-violet-50 tw-px-4 tw-py-3 tw-mb-4">
            <LockClosedIcon class="tw-h-5 tw-w-5 tw-text-violet-400 tw-shrink-0" />
            <p class="tw-text-sm tw-text-violet-700">
              {{ __('Upgrade to Pro to unlock Smart Engine and all premium features.', 'giantwp-discount-rules') }}
            </p>
          </div>
          <a :href="gwpdrPluginData.proUrl" target="_blank" rel="noopener noreferrer"
            class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-lg tw-bg-violet-600 tw-px-5 tw-py-2.5 tw-text-sm tw-font-semibold tw-text-white hover:tw-bg-violet-700 tw-transition-colors tw-no-underline">
            <SparklesIcon class="tw-h-4 tw-w-4" />
            {{ __('Upgrade to Pro', 'giantwp-discount-rules') }}
          </a>
        </div>
      </div>

      <!-- Pro content -->
      <template v-if="isProActive">

        <!-- Master toggle -->
        <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-mb-4 tw-overflow-hidden">
          <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4">
            <div class="tw-flex tw-items-center tw-gap-3">
              <div class="tw-flex tw-h-10 tw-w-10 tw-items-center tw-justify-center tw-rounded-xl tw-bg-gradient-to-br tw-from-violet-500 tw-to-indigo-600">
                <SparklesIcon class="tw-h-5 tw-w-5 tw-text-white" />
              </div>
              <div>
                <p class="tw-text-sm tw-font-bold tw-text-gray-900 tw-leading-tight">
                  {{ __('Smart Engine', 'giantwp-discount-rules') }}
                </p>
                <p class="tw-text-xs tw-text-gray-400">
                  {{ __('Your AI Virtual Salesman — pushing every customer, automatically', 'giantwp-discount-rules') }}
                </p>
              </div>
            </div>
            <el-switch v-model="smartEngineData.enabled" inline-prompt
              :active-text="__('On', 'giantwp-discount-rules')"
              :inactive-text="__('Off', 'giantwp-discount-rules')" />
          </div>
        </div>

        <div :class="{ 'tw-opacity-50 tw-pointer-events-none': !smartEngineData.enabled }">

          <!-- Stats -->
          <div class="tw-grid tw-grid-cols-3 tw-gap-3 tw-mb-4">
            <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-p-4 tw-flex tw-items-center tw-gap-3">
              <div class="tw-flex tw-h-10 tw-w-10 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-lg tw-bg-brand-50">
                <UserGroupIcon class="tw-h-5 tw-w-5 tw-text-brand-500" />
              </div>
              <div>
                <p class="tw-text-xl tw-font-bold tw-text-gray-900 tw-leading-none">
                  {{ smartEngineStats.customers_analysed.toLocaleString() }}
                </p>
                <p class="tw-text-xs tw-text-gray-400 tw-mt-1">{{ __('Customers engaged', 'giantwp-discount-rules') }}</p>
              </div>
            </div>
            <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-p-4 tw-flex tw-items-center tw-gap-3">
              <div class="tw-flex tw-h-10 tw-w-10 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-lg tw-bg-violet-50">
                <BoltIcon class="tw-h-5 tw-w-5 tw-text-violet-500" />
              </div>
              <div>
                <p class="tw-text-xl tw-font-bold tw-text-gray-900 tw-leading-none">
                  {{ smartEngineStats.discounts_personalised.toLocaleString() }}
                </p>
                <p class="tw-text-xs tw-text-gray-400 tw-mt-1">{{ __('Offers applied', 'giantwp-discount-rules') }}</p>
              </div>
            </div>
            <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-p-4 tw-flex tw-items-center tw-gap-3">
              <div class="tw-flex tw-h-10 tw-w-10 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-lg tw-bg-green-50">
                <CurrencyDollarIcon class="tw-h-5 tw-w-5 tw-text-green-500" />
              </div>
              <div>
                <p class="tw-text-xl tw-font-bold tw-text-gray-900 tw-leading-none">{{ formattedRevenue }}</p>
                <p class="tw-text-xs tw-text-gray-400 tw-mt-1">{{ __('Extra revenue', 'giantwp-discount-rules') }}</p>
              </div>
            </div>
          </div>

          <!-- Settings -->
          <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-mb-4 tw-overflow-hidden">
            <div class="tw-flex tw-items-center tw-gap-3 tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
              <div class="tw-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-lg tw-bg-violet-50">
                <BoltIcon class="tw-h-5 tw-w-5 tw-text-violet-500" />
              </div>
              <div>
                <p class="tw-text-sm tw-font-bold tw-text-gray-800 tw-leading-tight">
                  {{ __('Salesman Settings', 'giantwp-discount-rules') }}
                </p>
                <p class="tw-text-xs tw-text-gray-400">
                  {{ __('Smart rules are automatic — just set the rescue discount limit', 'giantwp-discount-rules') }}
                </p>
              </div>
            </div>

            <!-- Exit rescue % -->
            <div class="tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
              <div class="tw-flex tw-items-start tw-gap-2.5">
                <BoltIcon class="tw-h-4 tw-w-4 tw-text-red-500 tw-mt-0.5 tw-shrink-0" />
                <div>
                  <p class="tw-text-sm tw-font-semibold tw-text-gray-800">
                    {{ __('Exit Rescue Discount', 'giantwp-discount-rules') }}
                  </p>
                  <p class="tw-text-xs tw-text-gray-400 tw-mt-0.5">
                    {{ __('Max discount offered when a customer is about to leave the cart page.', 'giantwp-discount-rules') }}
                  </p>
                </div>
              </div>
              <div class="tw-flex tw-items-center tw-gap-1.5">
                <el-input-number
                  v-model="smartEngineData.max_discount"
                  :min="5" :max="50" :step="5" :precision="0"
                  style="width:110px"
                  controls-position="right"
                />
                <span class="tw-text-xs tw-text-gray-500">%</span>
              </div>
            </div>

            <!-- Store notes -->
            <div class="tw-px-5 tw-py-4">
              <div class="tw-flex tw-items-start tw-gap-2.5 tw-mb-2">
                <ChatBubbleLeftEllipsisIcon class="tw-h-4 tw-w-4 tw-text-brand-500 tw-mt-0.5 tw-shrink-0" />
                <div>
                  <p class="tw-text-sm tw-font-semibold tw-text-gray-800">
                    {{ __('Store Notes for AI', 'giantwp-discount-rules') }}
                  </p>
                  <p class="tw-text-xs tw-text-gray-400 tw-mt-0.5">
                    {{ __('Optional. Tell the AI about your brand tone, promotions, or anything that helps personalise messages.', 'giantwp-discount-rules') }}
                  </p>
                </div>
              </div>
              <el-input
                v-model="smartEngineData.store_notes"
                type="textarea"
                :rows="3"
                :placeholder="__('e.g. We are a friendly eco-friendly store. Always mention our free returns policy.', 'giantwp-discount-rules')"
              />
            </div>
          </div>

          <!-- AI Provider -->
          <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-mb-4 tw-overflow-hidden">
            <div class="tw-flex tw-items-center tw-gap-3 tw-px-5 tw-py-4 tw-border-b tw-border-gray-100">
              <div class="tw-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-lg tw-bg-violet-50">
                <SparklesIcon class="tw-h-5 tw-w-5 tw-text-violet-500" />
              </div>
              <div>
                <p class="tw-text-sm tw-font-bold tw-text-gray-800 tw-leading-tight">
                  {{ __('AI Provider', 'giantwp-discount-rules') }}
                </p>
                <p class="tw-text-xs tw-text-gray-400">
                  {{ __('Writes personalised messages for every customer', 'giantwp-discount-rules') }}
                </p>
              </div>
            </div>

            <div class="tw-grid tw-grid-cols-3 tw-gap-3 tw-p-4">
              <button v-for="p in providers" :key="p.id" type="button" @click="selectProvider(p.id)"
                :class="[
                  'tw-relative tw-rounded-xl tw-border-2 tw-p-4 tw-text-left tw-transition-all tw-cursor-pointer',
                  smartEngineData.provider === p.id
                    ? 'tw-border-violet-500 tw-bg-violet-50'
                    : 'tw-border-gray-200 tw-bg-white hover:tw-border-gray-300',
                ]">
                <div v-if="smartEngineData.provider === p.id"
                  class="tw-absolute tw-top-2.5 tw-right-2.5 tw-h-5 tw-w-5 tw-rounded-full tw-bg-violet-500 tw-flex tw-items-center tw-justify-center">
                  <CheckIcon class="tw-h-3 tw-w-3 tw-text-white" />
                </div>
                <p class="tw-text-sm tw-font-bold tw-text-gray-800 tw-mb-0.5">{{ p.name }}</p>
                <p class="tw-text-xs tw-font-mono tw-text-violet-600 tw-mb-1.5">{{ p.model }}</p>
                <p class="tw-text-xs tw-text-gray-400 tw-leading-relaxed">{{ p.note }}</p>
              </button>
            </div>

            <div class="tw-border-t tw-border-gray-100 tw-px-5 tw-py-4">
              <div class="tw-flex tw-items-center tw-justify-between tw-mb-2">
                <div>
                  <p class="tw-text-sm tw-font-semibold tw-text-gray-800">{{ selectedProvider.keyLabel }}</p>
                  <a :href="selectedProvider.docsUrl" target="_blank" rel="noopener noreferrer"
                    class="tw-text-xs tw-text-brand-500 hover:tw-underline">
                    {{ __('Get API Key →', 'giantwp-discount-rules') }}
                  </a>
                </div>
                <transition name="fade">
                  <span v-if="connectStatus"
                    :class="[
                      'tw-inline-flex tw-items-center tw-gap-1 tw-rounded-full tw-px-2.5 tw-py-0.5 tw-text-xs tw-font-semibold',
                      connectStatus === 'success' ? 'tw-bg-green-100 tw-text-green-700' : 'tw-bg-red-100 tw-text-red-600',
                    ]">
                    {{ connectMessage }}
                  </span>
                </transition>
              </div>
              <div class="tw-flex tw-gap-2">
                <el-input v-model="smartEngineData.api_key" type="password" show-password
                  :placeholder="smartEngineData.api_key_set || selectedProvider.placeholder"
                  class="tw-flex-1" />
                <el-button type="primary" :loading="isConnecting" :disabled="!smartEngineData.api_key"
                  @click="testConnection">
                  {{ __('Connect & Save', 'giantwp-discount-rules') }}
                </el-button>
              </div>
              <div v-if="smartEngineData.api_key_set && !smartEngineData.api_key"
                class="tw-mt-2 tw-flex tw-items-center tw-gap-2 tw-rounded-lg tw-bg-green-50 tw-border tw-border-green-200 tw-px-3 tw-py-2">
                <CheckIcon class="tw-h-4 tw-w-4 tw-text-green-600 tw-shrink-0" />
                <span class="tw-text-sm tw-text-green-700 tw-font-medium">
                  {{ __('API key saved:', 'giantwp-discount-rules') }}
                  <span class="tw-font-mono tw-text-green-800">{{ smartEngineData.api_key_set }}</span>
                </span>
              </div>
            </div>
          </div>

          <!-- Save -->
          <div class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-px-5 tw-py-4">
            <button type="button" @click="handleSave" :disabled="isLoadingSmartEngine"
              class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-lg tw-bg-brand-600 tw-px-4 tw-py-2 tw-text-sm tw-font-medium tw-text-white tw-transition hover:tw-bg-brand-700 disabled:tw-opacity-60 disabled:tw-cursor-wait">
              <CheckIcon class="tw-h-4 tw-w-4" />
              {{ isLoadingSmartEngine
                  ? __('Saving…', 'giantwp-discount-rules')
                  : __('Save Settings', 'giantwp-discount-rules') }}
            </button>
          </div>

        </div>
      </template>

    </div>

    <div class="tw-w-64 tw-shrink-0 tw-hidden lg:tw-block">
      <Sidebar />
    </div>

  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; }
</style>
