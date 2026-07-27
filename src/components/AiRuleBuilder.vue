<!-- AI Rule Builder / Editor Modal — Pro Feature -->
<script setup>
import { ref, computed, watch } from 'vue';
import {
  SparklesIcon,
  XMarkIcon,
  ArrowPathIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
  LightBulbIcon,
  BoltIcon,
  PencilSquareIcon,
} from '@heroicons/vue/24/outline';
import { SparklesIcon as SparklesSolid } from '@heroicons/vue/24/solid';
import { generateAiRule, modifyAiRule } from '@/data/save-data/aiRuleBuilderData.js';
import { saveFlatPercentageDiscount } from '@/data/save-data/saveFlatPercentageDiscount.js';
import { saveBogoData }          from '@/data/save-data/saveBogoData.js';
import { saveBulkDiscountData }  from '@/data/save-data/saveBulkDiscountData.js';
import { saveBuyXGetYData }      from '@/data/save-data/saveBuyXGetYData.js';
import { saveShippingData }      from '@/data/save-data/saveShippingData.js';
import { discountCreatedMessage, updatedDiscountMessage, errorMessage } from '@/data/message.js';

const { __ } = wp.i18n;

const isProActive = !!gwpdrPluginData?.proActive;

const props = defineProps({
  visible:     { type: Boolean, default: false },
  editingRule: { type: Object,  default: null  },
});

const emit = defineEmits(['close', 'ruleAdded', 'ruleUpdated']);

const isEditMode = computed(() => !!props.editingRule);

const prompt          = ref('');
const isGenerating    = ref(false);
const isSaving        = ref(false);
const generated       = ref(null);
const genError        = ref('');
const hasPlaceholders = ref(false);

// Reset state whenever the modal opens with a different rule
watch(() => props.editingRule, () => {
  prompt.value          = '';
  generated.value       = null;
  genError.value        = '';
  hasPlaceholders.value = false;
});

// ── Example chips ────────────────────────────────────────────────────────────

const CREATE_EXAMPLES = [
  __('Give 20% off to new customers who spend over $50', 'giantwp-discount-rules'),
  __('$10 off for everyone when cart total is above $100', 'giantwp-discount-rules'),
  __('15% off for wholesale customers buying 5 or more items', 'giantwp-discount-rules'),
  __('Give returning customers 10% off their next order', 'giantwp-discount-rules'),
  __('Free shipping nudge for logged-in members', 'giantwp-discount-rules'),
  __('25% off on orders paid via bank transfer', 'giantwp-discount-rules'),
];

const EDIT_EXAMPLES = [
  __('Change the discount to 25%', 'giantwp-discount-rules'),
  __('Add a condition: only for logged-in customers', 'giantwp-discount-rules'),
  __('Make it apply only when cart total is above $80', 'giantwp-discount-rules'),
  __('Add a new customer condition (first order only)', 'giantwp-discount-rules'),
  __('Change from percentage to $15 fixed discount', 'giantwp-discount-rules'),
  __('Set a usage limit of 100 uses total', 'giantwp-discount-rules'),
];

const examples = computed(() => isEditMode.value ? EDIT_EXAMPLES : CREATE_EXAMPLES);

// ── Label maps ────────────────────────────────────────────────────────────────

// ── Rule type config ─────────────────────────────────────────────────────────

const TYPE_CONFIG = {
  'flat/percentage':   { label: 'Flat / %',     color: '#0876CF', bg: '#eff6ff', border: '#bfdbfe' },
  'bogo':              { label: 'BOGO',          color: '#ea580c', bg: '#fff7ed', border: '#fed7aa' },
  'bulk discount':     { label: 'Bulk',          color: '#16a34a', bg: '#f0fdf4', border: '#bbf7d0' },
  'buy x get y':       { label: 'Buy X Get Y',  color: '#db2777', bg: '#fdf2f8', border: '#fbcfe8' },
  'shipping discount': { label: 'Shipping',      color: '#7c3aed', bg: '#f5f3ff', border: '#ddd6fe' },
};

const ruleTypeConfig = computed(() => {
  if (!generated.value) return TYPE_CONFIG['flat/percentage'];
  return TYPE_CONFIG[generated.value.discountType?.toLowerCase()] ?? TYPE_CONFIG['flat/percentage'];
});

const editingRuleTypeConfig = computed(() => {
  if (!props.editingRule) return TYPE_CONFIG['flat/percentage'];
  return TYPE_CONFIG[props.editingRule.discountType?.toLowerCase()] ?? TYPE_CONFIG['flat/percentage'];
});

// Route to the correct save service based on discountType
const getSaveService = (type) => {
  switch (type?.toLowerCase()) {
    case 'bogo':              return saveBogoData;
    case 'bulk discount':     return saveBulkDiscountData;
    case 'buy x get y':       return saveBuyXGetYData;
    case 'shipping discount': return saveShippingData;
    default:                  return saveFlatPercentageDiscount;
  }
};

const FIELD_LABELS = {
  cart_subtotal_price:             __('Cart Subtotal',       'giantwp-discount-rules'),
  cart_quantity:                   __('Cart Quantity',       'giantwp-discount-rules'),
  cart_total_weight:               __('Cart Weight',         'giantwp-discount-rules'),
  cart_item_regular_price:         __('Item Regular Price',  'giantwp-discount-rules'),
  cart_item_product:               __('Product',             'giantwp-discount-rules'),
  cart_item_variation:             __('Variation',           'giantwp-discount-rules'),
  cart_item_category:              __('Category',            'giantwp-discount-rules'),
  cart_item_tag:                   __('Tag',                 'giantwp-discount-rules'),
  customer_is_logged_in:           __('Login Status',        'giantwp-discount-rules'),
  customer_role:                   __('Customer Role',       'giantwp-discount-rules'),
  specific_customer:               __('Specific Customer',   'giantwp-discount-rules'),
  customer_order_count:            __('Order Count',         'giantwp-discount-rules'),
  customer_order_history_product:  __('Purchased Product',   'giantwp-discount-rules'),
  customer_order_history_category: __('Purchased Category',  'giantwp-discount-rules'),
  payment_method:                  __('Payment Method',      'giantwp-discount-rules'),
};

const OPERATOR_LABELS = {
  greater_than:       '>',
  less_than:          '<',
  equal_greater_than: '≥',
  equal_less_than:    '≤',
  contain_all:        __('includes all of',  'giantwp-discount-rules'),
  contain_in_list:    __('includes any of',  'giantwp-discount-rules'),
  not_contain_inlist: __('excludes',         'giantwp-discount-rules'),
  logged_in:          __('is logged in',     'giantwp-discount-rules'),
  not_logged_in:      __('is a guest',       'giantwp-discount-rules'),
  in_list:            __('is',               'giantwp-discount-rules'),
  not_in_list:        __('is not',           'giantwp-discount-rules'),
};

const fieldLabel    = (f) => FIELD_LABELS[f]   || f;
const operatorLabel = (o) => OPERATOR_LABELS[o] || o;
const condValLabel  = (c) => (!c.value || !c.value.length) ? '—' : c.value.join(', ');

// ── Core logic ────────────────────────────────────────────────────────────────

const detectPlaceholders = (rule) =>
  /_ID_HERE|_placeholder|PRODUCT_ID|CATEGORY_ID|VARIATION_ID|TAG_ID|USER_ID/i.test(JSON.stringify(rule));

const generate = async () => {
  if (!prompt.value.trim() || isGenerating.value) return;
  genError.value        = '';
  generated.value       = null;
  isGenerating.value    = true;
  try {
    const res = isEditMode.value
      ? await modifyAiRule(props.editingRule, prompt.value.trim())
      : await generateAiRule(prompt.value.trim());

    if (res.success) {
      generated.value       = res.rule;
      hasPlaceholders.value = detectPlaceholders(res.rule);
    } else {
      genError.value = res.message || __('Something went wrong. Please try again.', 'giantwp-discount-rules');
    }
  } catch {
    genError.value = __('Failed to reach the AI. Check your internet connection.', 'giantwp-discount-rules');
  } finally {
    isGenerating.value = false;
  }
};

const saveRule = async () => {
  if (!generated.value || isSaving.value) return;
  isSaving.value = true;
  const service = getSaveService(generated.value.discountType);
  try {
    if (isEditMode.value) {
      await service.updateDiscount(props.editingRule.id, { ...generated.value });
      updatedDiscountMessage();
      emit('ruleUpdated');
    } else {
      await service.saveCoupon({ ...generated.value, status: 'on' });
      discountCreatedMessage();
      emit('ruleAdded');
    }
    close();
  } catch {
    errorMessage();
  } finally {
    isSaving.value = false;
  }
};

const useExample = (ex) => { prompt.value = ex; };

const reset = () => {
  prompt.value          = '';
  generated.value       = null;
  genError.value        = '';
  hasPlaceholders.value = false;
};

const close = () => { reset(); emit('close'); };
</script>

<template>
  <Teleport to="body">
    <Transition name="aib-fade">
      <div v-if="visible" class="aib-overlay" @click.self="close">
        <Transition name="aib-rise">
          <div v-if="visible" class="aib-modal" role="dialog" aria-modal="true">

            <!-- ── Header ────────────────────────────────────────────────── -->
            <div class="aib-header" :class="{ 'aib-header--edit': isEditMode }">
              <div class="aib-header-left">
                <div class="aib-header-icon">
                  <component
                    :is="isEditMode ? PencilSquareIcon : SparklesSolid"
                    class="aib-icon-main"
                  />
                </div>
                <div>
                  <h2 class="aib-header-title">
                    {{ isEditMode
                        ? __('AI Rule Editor', 'giantwp-discount-rules')
                        : __('AI Rule Builder', 'giantwp-discount-rules') }}
                  </h2>
                  <p class="aib-header-sub">
                    {{ isEditMode
                        ? __('Describe what to change — AI will update the rule', 'giantwp-discount-rules')
                        : __('Describe your discount in plain English — AI builds the rule instantly', 'giantwp-discount-rules') }}
                  </p>
                </div>
              </div>
              <div class="aib-header-right">
                <span v-if="!isProActive" class="aib-pro-tag">PRO</span>
                <button class="aib-close" @click="close" :aria-label="__('Close', 'giantwp-discount-rules')">
                  <XMarkIcon class="aib-close-icon" />
                </button>
              </div>
            </div>

            <!-- ── Body ──────────────────────────────────────────────────── -->
            <div class="aib-body">

              <!-- LEFT: Prompt panel -->
              <div class="aib-panel aib-panel-left">

                <!-- Edit mode: current rule summary card -->
                <div v-if="isEditMode && editingRule" class="aib-current-summary">
                  <p class="aib-current-summary-label">
                    {{ __('Currently editing', 'giantwp-discount-rules') }}
                  </p>
                  <div class="aib-current-summary-name">
                    <BoltIcon class="aib-current-summary-bolt" />
                    <span>{{ editingRule.couponName }}</span>
                  </div>
                  <p class="aib-current-summary-meta">
                    <span
                      class="aib-current-summary-badge"
                      :style="{ background: editingRuleTypeConfig.color }"
                    >{{ editingRuleTypeConfig.label }}</span>
                    <span class="aib-current-summary-dot">·</span>
                    <span>
                      {{ editingRule.enableConditions && editingRule.conditions?.length
                          ? editingRule.conditions.length + ' ' + __('condition(s)', 'giantwp-discount-rules')
                          : __('No conditions', 'giantwp-discount-rules') }}
                    </span>
                  </p>
                </div>

                <label class="aib-label">
                  <LightBulbIcon class="aib-label-icon" />
                  {{ isEditMode
                      ? __('What would you like to change?', 'giantwp-discount-rules')
                      : __('Describe your discount rule', 'giantwp-discount-rules') }}
                </label>

                <textarea
                  v-model="prompt"
                  class="aib-textarea"
                  rows="5"
                  :placeholder="isEditMode
                    ? __('e.g. Change the discount to 25%, add a condition for logged-in users only…', 'giantwp-discount-rules')
                    : __('e.g. Give 20% off to new customers who spend over $50…', 'giantwp-discount-rules')"
                  @keydown.ctrl.enter="generate"
                  @keydown.meta.enter="generate"
                />
                <p class="aib-textarea-hint">
                  {{ prompt.length }}&thinsp;{{ __('chars', 'giantwp-discount-rules') }}
                  &nbsp;·&nbsp;Ctrl + Enter {{ __('to generate', 'giantwp-discount-rules') }}
                </p>

                <!-- Example chips -->
                <div class="aib-examples">
                  <p class="aib-examples-label">{{ __('Try an example:', 'giantwp-discount-rules') }}</p>
                  <div class="aib-chips">
                    <button
                      v-for="ex in examples"
                      :key="ex"
                      class="aib-chip"
                      @click="useExample(ex)"
                    >{{ ex }}</button>
                  </div>
                </div>

                <!-- Generate / Apply button -->
                <button
                  class="aib-generate-btn"
                  :disabled="!prompt.trim() || isGenerating"
                  @click="generate"
                >
                  <SparklesIcon class="aib-btn-icon" />
                  <span>{{ isGenerating
                    ? __('Processing…', 'giantwp-discount-rules')
                    : (isEditMode
                        ? __('Apply Changes', 'giantwp-discount-rules')
                        : __('Generate Rule', 'giantwp-discount-rules')) }}</span>
                  <span v-if="isGenerating" class="aib-spinner" />
                </button>

              </div><!-- /LEFT -->

              <!-- RIGHT: Result panel -->
              <div class="aib-panel aib-panel-right">

                <!-- Empty state -->
                <div v-if="!isGenerating && !generated && !genError" class="aib-empty">
                  <div class="aib-empty-icon-wrap">
                    <SparklesSolid class="aib-empty-icon" />
                  </div>
                  <p class="aib-empty-title">
                    {{ isEditMode
                        ? __('Modified rule will appear here', 'giantwp-discount-rules')
                        : __('Your rule will appear here', 'giantwp-discount-rules') }}
                  </p>
                  <p class="aib-empty-sub">
                    {{ isEditMode
                        ? __('Describe what to change on the left and click Apply Changes', 'giantwp-discount-rules')
                        : __('Describe your rule on the left and click Generate', 'giantwp-discount-rules') }}
                  </p>
                </div>

                <!-- Generating skeleton -->
                <div v-if="isGenerating" class="aib-skeleton-wrap">
                  <div class="aib-thinking">
                    <span class="aib-dot" style="--d:0ms"   />
                    <span class="aib-dot" style="--d:180ms" />
                    <span class="aib-dot" style="--d:360ms" />
                    <span class="aib-thinking-text">
                      {{ isEditMode
                          ? __('AI is modifying your rule…', 'giantwp-discount-rules')
                          : __('AI is building your rule…', 'giantwp-discount-rules') }}
                    </span>
                  </div>
                  <div class="aib-skels">
                    <div class="aib-skel" style="width:55%; height:18px;" />
                    <div class="aib-skel" style="width:100%; height:72px; border-radius:14px;" />
                    <div class="aib-skel" style="width:40%; height:14px;" />
                    <div class="aib-skel" style="width:100%; height:36px;" />
                    <div class="aib-skel" style="width:100%; height:36px;" />
                  </div>
                </div>

                <!-- Error state -->
                <div v-if="genError && !isGenerating" class="aib-error">
                  <ExclamationTriangleIcon class="aib-error-icon" />
                  <p class="aib-error-msg">{{ genError }}</p>
                  <p class="aib-error-sub">
                    {{ __('Make sure you have an AI provider connected in Smart Engine settings.', 'giantwp-discount-rules') }}
                  </p>
                </div>

                <!-- Generated / Modified rule card -->
                <div v-if="generated && !isGenerating" class="aib-rule-card">
                  <div class="aib-rule-accent" :class="{ 'aib-rule-accent--edit': isEditMode }" />

                  <!-- Rule name + type badge -->
                  <div class="aib-rule-name">
                    <BoltIcon class="aib-rule-name-icon" />
                    <span>{{ generated.couponName }}</span>
                    <span
                      class="aib-type-badge"
                      :style="{ background: ruleTypeConfig.bg, color: ruleTypeConfig.color, borderColor: ruleTypeConfig.border }"
                    >{{ ruleTypeConfig.label }}</span>
                  </div>

                  <!-- flat/percentage: big number -->
                  <div
                    v-if="generated.discountType === 'flat/percentage'"
                    class="aib-discount-badge"
                  >
                    <span class="aib-discount-value">
                      {{ generated.fpDiscountType === 'percentage'
                          ? generated.discountValue + '%'
                          : '$' + generated.discountValue }}
                    </span>
                    <span class="aib-discount-type">
                      {{ generated.fpDiscountType === 'percentage'
                          ? __('Percentage Off', 'giantwp-discount-rules')
                          : __('Fixed Discount', 'giantwp-discount-rules') }}
                    </span>
                  </div>

                  <!-- bogo summary -->
                  <div v-else-if="generated.discountType === 'bogo'" class="aib-type-summary">
                    <div class="aib-type-summary-row">
                      <span class="aib-type-summary-num">{{ generated.buyProductCount || 1 }}</span>
                      <span class="aib-type-summary-sep">→</span>
                      <span class="aib-type-summary-num">{{ generated.getProductCount || 1 }}</span>
                    </div>
                    <div class="aib-type-summary-label">
                      {{ __('Buy', 'giantwp-discount-rules') }} {{ generated.buyProductCount || 1 }}
                      {{ __('get', 'giantwp-discount-rules') }} {{ generated.getProductCount || 1 }}
                      {{ generated.freeOrDiscount === 'freeproduct'
                          ? __('FREE', 'giantwp-discount-rules')
                          : (generated.discountValue + (generated.discounttypeBogo === 'percentage' ? '% off' : '$ off')) }}
                    </div>
                  </div>

                  <!-- bulk discount: tier table -->
                  <div v-else-if="generated.discountType === 'bulk discount'" class="aib-tiers">
                    <div class="aib-tiers-heading">{{ __('Discount Tiers', 'giantwp-discount-rules') }}</div>
                    <div
                      v-for="(tier, i) in (generated.bulkDiscounts || [])"
                      :key="i"
                      class="aib-tier-row"
                    >
                      <span class="aib-tier-range">
                        {{ tier.fromcount }}{{ tier.toCount ? ' – ' + tier.toCount : '+ ' + __('items', 'giantwp-discount-rules') }}
                        {{ tier.toCount ? ' ' + __('items', 'giantwp-discount-rules') : '' }}
                      </span>
                      <span class="aib-tier-disc">
                        {{ tier.discountTypeBulk === 'percentage' ? tier.discountValue + '%' : '$' + tier.discountValue }}
                        {{ __('off', 'giantwp-discount-rules') }}
                      </span>
                    </div>
                  </div>

                  <!-- buy x get y summary -->
                  <div v-else-if="generated.discountType === 'buy x get y'" class="aib-type-summary">
                    <div class="aib-bxgy-row">
                      <div class="aib-bxgy-side">
                        <span class="aib-bxgy-label">{{ __('BUY', 'giantwp-discount-rules') }}</span>
                        <span class="aib-bxgy-count">{{ generated.buyProduct?.[0]?.buyProductCount || 1 }}</span>
                      </div>
                      <ArrowPathIcon class="aib-bxgy-arrow" />
                      <div class="aib-bxgy-side">
                        <span class="aib-bxgy-label">{{ __('GET', 'giantwp-discount-rules') }}</span>
                        <span class="aib-bxgy-count">{{ generated.getProduct?.[0]?.getProductCount || 1 }}</span>
                      </div>
                    </div>
                    <div class="aib-type-summary-label">
                      {{ generated.freeOrDiscount === 'free_product'
                          ? __('Get items FREE', 'giantwp-discount-rules')
                          : (generated.discountValue + (generated.discountTypeBxgy === 'percentage' ? '% off' : '$ off') + ' ' + __('on get items', 'giantwp-discount-rules')) }}
                    </div>
                  </div>

                  <!-- shipping discount summary -->
                  <div v-else-if="generated.discountType === 'shipping discount'" class="aib-type-summary">
                    <div class="aib-shipping-icon">🚚</div>
                    <div class="aib-type-summary-label aib-type-summary-label--big">
                      {{ generated.shippingDiscountType === 'customFee'
                          ? __('Custom shipping fee', 'giantwp-discount-rules') + ': $' + generated.discountValue
                          : (generated.discountValue >= 100 && generated.pDiscountType === 'percentage'
                              ? __('FREE SHIPPING', 'giantwp-discount-rules')
                              : __('Reduce shipping by', 'giantwp-discount-rules') + ' ' + (generated.pDiscountType === 'percentage' ? generated.discountValue + '%' : '$' + generated.discountValue)) }}
                    </div>
                  </div>

                  <!-- Conditions -->
                  <template v-if="generated.enableConditions && generated.conditions?.length">
                    <p class="aib-cond-heading">
                      {{ __('Applies when', 'giantwp-discount-rules') }}
                      <strong>{{ generated.conditionsApplies }}</strong>
                      {{ __('of these match:', 'giantwp-discount-rules') }}
                    </p>
                    <div class="aib-conds">
                      <div
                        v-for="(c, i) in generated.conditions"
                        :key="i"
                        class="aib-cond-row"
                      >
                        <span class="aib-cond-field">{{ fieldLabel(c.field) }}</span>
                        <span class="aib-cond-op">{{ operatorLabel(c.operator) }}</span>
                        <span class="aib-cond-val">{{ condValLabel(c) }}</span>
                      </div>
                    </div>
                  </template>
                  <p v-else class="aib-no-conditions">
                    {{ __('No conditions — applies to all customers', 'giantwp-discount-rules') }}
                  </p>

                  <!-- Placeholder warning -->
                  <div v-if="hasPlaceholders" class="aib-warn">
                    <ExclamationTriangleIcon class="aib-warn-icon" />
                    <span>{{ __('Some conditions use placeholder IDs (products/categories). Edit the rule after saving to set the real IDs.', 'giantwp-discount-rules') }}</span>
                  </div>

                  <!-- Actions -->
                  <div class="aib-actions">
                    <button class="aib-add-btn" :disabled="isSaving" @click="saveRule">
                      <CheckCircleIcon class="aib-btn-icon" />
                      {{ isSaving
                          ? __('Saving…', 'giantwp-discount-rules')
                          : (isEditMode
                              ? __('Update Rule', 'giantwp-discount-rules')
                              : __('Add This Rule', 'giantwp-discount-rules')) }}
                    </button>
                    <button class="aib-regen-btn" :disabled="isGenerating" @click="generate">
                      <ArrowPathIcon class="aib-btn-icon" />
                      {{ __('Retry', 'giantwp-discount-rules') }}
                    </button>
                  </div>
                </div>

              </div><!-- /RIGHT -->

            </div><!-- /body -->

          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
/* ── Overlay ──────────────────────────────────────────────────────────────── */
.aib-overlay {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.55);
  backdrop-filter: blur(6px);
  -webkit-backdrop-filter: blur(6px);
  z-index: 99999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}

/* ── Modal card ───────────────────────────────────────────────────────────── */
.aib-modal {
  background: #ffffff;
  border-radius: 22px;
  width: 100%;
  max-width: 920px;
  max-height: 88vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  box-shadow:
    0 0 0 1px rgba(0,0,0,0.06),
    0 32px 64px -16px rgba(0,0,0,0.28),
    0 8px 24px -4px rgba(0,0,0,0.12);
}

/* ── Header ───────────────────────────────────────────────────────────────── */
.aib-header {
  background: #0763AD;
  padding: 18px 24px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-shrink: 0;
}
.aib-header--edit {
  background: #0763AD;
}

.aib-header-left  { display: flex; align-items: center; gap: 14px; }
.aib-header-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

.aib-header-icon {
  width: 40px;
  height: 40px;
  background: rgba(255,255,255,0.18);
  border: 1px solid rgba(255,255,255,0.3);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}
.aib-icon-main { width: 20px; height: 20px; color: #fff; }

.aib-header-title {
  font-size: 18px;
  font-weight: 700;
  color: #fff;
  margin: 0;
  line-height: 1.2;
  letter-spacing: -0.01em;
}
.aib-header-sub {
  font-size: 12.5px;
  color: rgba(255,255,255,0.72);
  margin: 3px 0 0;
  line-height: 1.4;
}

.aib-pro-tag {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.1em;
  color: #fff;
  background: rgba(255,255,255,0.18);
  border: 1px solid rgba(255,255,255,0.35);
  border-radius: 100px;
  padding: 3px 9px;
  line-height: 1;
}

.aib-close {
  width: 34px;
  height: 34px;
  background: rgba(255,255,255,0.14);
  border: 1px solid rgba(255,255,255,0.25);
  border-radius: 9px;
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: background 0.18s;
}
.aib-close:hover { background: rgba(255,255,255,0.26); }
.aib-close-icon  { width: 18px; height: 18px; }

/* ── Two-panel body ───────────────────────────────────────────────────────── */
.aib-body {
  display: grid;
  grid-template-columns: 1fr 1fr;
  flex: 1;
  overflow: hidden;
}

.aib-panel {
  padding: 22px 24px;
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}
.aib-panel-left  { border-right: 1px solid #f1f5f9; }
.aib-panel-right { background: #f8fafc; }

/* ── Current rule summary (edit mode) ────────────────────────────────────── */
.aib-current-summary {
  background: #f0f9ff;
  border: 1px solid #bae6fd;
  border-radius: 12px;
  padding: 12px 14px;
  margin-bottom: 16px;
}
.aib-current-summary-label {
  font-size: 10.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #0369a1;
  margin-bottom: 6px;
}
.aib-current-summary-name {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 13.5px;
  font-weight: 700;
  color: #0c4a6e;
  margin-bottom: 5px;
}
.aib-current-summary-bolt { width: 14px; height: 14px; color: #0876CF; flex-shrink: 0; }
.aib-current-summary-meta {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: #0369a1;
  padding-left: 20px;
}
.aib-current-summary-badge {
  background: #0876CF;
  color: #fff;
  font-weight: 700;
  font-size: 11px;
  padding: 2px 8px;
  border-radius: 100px;
}
.aib-current-summary-dot { color: #94a3b8; }

/* ── Label ────────────────────────────────────────────────────────────────── */
.aib-label {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 13px;
  font-weight: 600;
  color: #1e293b;
  margin-bottom: 10px;
}
.aib-label-icon { width: 16px; height: 16px; flex-shrink: 0; color: #f59e0b; }

/* ── Textarea ─────────────────────────────────────────────────────────────── */
.aib-textarea {
  width: 100%;
  box-sizing: border-box;
  border: 2px solid #e2e8f0;
  border-radius: 14px;
  padding: 13px 15px;
  font-size: 14px;
  font-family: inherit;
  color: #1e293b;
  line-height: 1.65;
  resize: none;
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s;
  background: #fff;
}
.aib-textarea:focus {
  border-color: #0763AD;
  box-shadow: 0 0 0 3px rgba(7,99,173,0.12);
}
.aib-textarea::placeholder { color: #94a3b8; }

.aib-textarea-hint {
  font-size: 11.5px;
  color: #94a3b8;
  text-align: right;
  margin-top: 5px;
}

/* ── Example chips ────────────────────────────────────────────────────────── */
.aib-examples       { margin-top: 14px; }
.aib-examples-label { font-size: 11.5px; font-weight: 600; color: #64748b; margin: 0 0 7px; }
.aib-chips          { display: flex; flex-wrap: wrap; gap: 6px; }

.aib-chip {
  font-size: 11.5px;
  font-family: inherit;
  padding: 5px 11px;
  background: #eff6ff;
  color: #0763AD;
  border: 1px solid #bfdbfe;
  border-radius: 100px;
  cursor: pointer;
  line-height: 1.35;
  transition: background 0.15s, color 0.15s, border-color 0.15s;
  text-align: left;
}
.aib-chip:hover { background: #0763AD; color: #fff; border-color: #0763AD; }

/* ── Generate / Apply button ─────────────────────────────────────────────── */
.aib-generate-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  width: 100%;
  padding: 13px 20px;
  margin-top: 20px;
  background: #0763AD;
  color: #fff;
  border: none;
  border-radius: 13px;
  font-size: 14px;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
  box-shadow: 0 4px 14px rgba(7,99,173,0.3);
  flex-shrink: 0;
}
.aib-generate-btn:hover:not(:disabled) {
  opacity: 0.92;
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(7,99,173,0.38);
}
.aib-generate-btn:disabled { opacity: 0.48; cursor: not-allowed; transform: none; box-shadow: none; }

.aib-btn-icon { width: 16px; height: 16px; flex-shrink: 0; }

.aib-spinner {
  width: 15px;
  height: 15px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: #fff;
  border-radius: 50%;
  animation: aib-spin 0.65s linear infinite;
  flex-shrink: 0;
}
@keyframes aib-spin { to { transform: rotate(360deg); } }

/* ── Empty state ─────────────────────────────────────────────────────────── */
.aib-empty {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  padding: 32px 16px;
  min-height: 220px;
}
.aib-empty-icon-wrap {
  width: 80px;
  height: 80px;
  background: #dbeafe;
  border-radius: 22px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 18px;
  box-shadow: 0 4px 16px rgba(7,99,173,0.1);
}
.aib-empty-icon  { width: 36px; height: 36px; color: #93c5fd; }
.aib-empty-title { font-size: 14px; font-weight: 600; color: #475569; margin: 0; }
.aib-empty-sub   { font-size: 12.5px; color: #94a3b8; margin: 6px 0 0; }

/* ── Generating skeleton ─────────────────────────────────────────────────── */
.aib-skeleton-wrap { padding: 4px; }

.aib-thinking {
  display: flex;
  align-items: center;
  margin-bottom: 18px;
}
.aib-dot {
  width: 9px; height: 9px;
  border-radius: 50%;
  background: #0763AD;
  display: inline-block;
  margin-right: 5px;
  animation: aib-bounce 1.3s ease-in-out infinite;
  animation-delay: var(--d, 0ms);
}
@keyframes aib-bounce {
  0%, 80%, 100% { transform: scale(0.55); opacity: 0.4; }
  40%            { transform: scale(1);    opacity: 1;   }
}
.aib-thinking-text { font-size: 13px; font-weight: 600; color: #0763AD; margin-left: 6px; }

.aib-skels { display: flex; flex-direction: column; gap: 10px; }
.aib-skel  {
  border-radius: 7px;
  background: linear-gradient(90deg, #e2e8f0 25%, #f1f5f9 50%, #e2e8f0 75%);
  background-size: 200% 100%;
  animation: aib-shimmer 1.6s infinite;
}
@keyframes aib-shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ── Error state ─────────────────────────────────────────────────────────── */
.aib-error {
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 14px;
  padding: 28px 24px;
  text-align: center;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.aib-error-icon { width: 32px; height: 32px; color: #f87171; margin-bottom: 10px; }
.aib-error-msg  { font-size: 13.5px; font-weight: 600; color: #dc2626; margin: 0; }
.aib-error-sub  { font-size: 12px; color: #94a3b8; margin: 6px 0 0; }

/* ── Generated / modified rule card ─────────────────────────────────────── */
.aib-rule-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  padding: 22px 20px 20px;
  box-shadow: 0 4px 16px rgba(0,0,0,0.07);
  position: relative;
  overflow: hidden;
}
.aib-rule-accent {
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 4px;
  background: #0763AD;
}
.aib-rule-accent--edit {
  background: #0763AD;
}

.aib-rule-name {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 14px;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 14px;
}
.aib-rule-name-icon { width: 16px; height: 16px; color: #0876CF; flex-shrink: 0; }

.aib-discount-badge {
  display: flex;
  align-items: center;
  gap: 14px;
  background: #eff6ff;
  border-radius: 14px;
  padding: 16px 18px;
  margin-bottom: 18px;
  border: 1px solid #bfdbfe;
}
.aib-discount-value {
  font-size: 38px;
  font-weight: 900;
  line-height: 1;
  color: #0763AD;
  letter-spacing: -0.02em;
}
.aib-discount-type { font-size: 13px; color: #64748b; font-weight: 500; }

/* Conditions */
.aib-cond-heading { font-size: 12px; color: #64748b; margin: 0 0 8px; }
.aib-cond-heading strong { color: #1e293b; }

.aib-conds { display: flex; flex-direction: column; gap: 6px; margin-bottom: 12px; }
.aib-cond-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 5px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 9px;
  padding: 7px 10px;
  font-size: 12.5px;
}
.aib-cond-field { font-weight: 700; color: #334155; }
.aib-cond-op    { color: #94a3b8; font-size: 11.5px; }
.aib-cond-val   {
  color: #0876CF;
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 5px;
  padding: 1px 7px;
  font-weight: 600;
  font-size: 11.5px;
  word-break: break-all;
}

.aib-no-conditions { font-size: 12px; color: #94a3b8; font-style: italic; margin: 0 0 12px; }

/* Placeholder warning */
.aib-warn {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  background: #fffbeb;
  border: 1px solid #fde68a;
  border-radius: 10px;
  padding: 10px 12px;
  font-size: 12px;
  color: #78350f;
  margin-bottom: 14px;
  line-height: 1.45;
}
.aib-warn-icon { width: 16px; height: 16px; color: #f59e0b; flex-shrink: 0; margin-top: 1px; }

/* Actions */
.aib-actions { display: flex; gap: 8px; margin-top: 16px; }

.aib-add-btn {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  padding: 11px 16px;
  background: #0763AD;
  color: #fff;
  border: none;
  border-radius: 11px;
  font-size: 13px;
  font-weight: 600;
  font-family: inherit;
  cursor: pointer;
  transition: opacity 0.2s, box-shadow 0.2s;
  box-shadow: 0 3px 10px rgba(7,99,173,0.28);
}
.aib-add-btn:hover:not(:disabled)  { opacity: 0.9; box-shadow: 0 5px 14px rgba(7,99,173,0.38); }
.aib-add-btn:disabled               { opacity: 0.55; cursor: not-allowed; }

.aib-regen-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  padding: 11px 16px;
  background: #fff;
  color: #475569;
  border: 1px solid #e2e8f0;
  border-radius: 11px;
  font-size: 13px;
  font-weight: 500;
  font-family: inherit;
  cursor: pointer;
  transition: border-color 0.18s, color 0.18s, background 0.18s;
}
.aib-regen-btn:hover:not(:disabled) { border-color: #0763AD; color: #0763AD; background: #eff6ff; }
.aib-regen-btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* ── Type badge (rule name row) ──────────────────────────────────────────── */
.aib-type-badge {
  font-size: 10px;
  font-weight: 700;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  padding: 3px 9px;
  border-radius: 100px;
  border: 1px solid;
  line-height: 1;
  margin-left: auto;
  flex-shrink: 0;
}

/* ── Generic type summary wrapper ────────────────────────────────────────── */
.aib-type-summary {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
  background: linear-gradient(135deg, #f8fafc, #f1f5f9);
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 18px 18px 14px;
  margin-bottom: 18px;
  text-align: center;
}

/* ── BOGO: Buy N → Get M ─────────────────────────────────────────────────── */
.aib-type-summary-row {
  display: flex;
  align-items: center;
  gap: 12px;
}
.aib-type-summary-num {
  font-size: 40px;
  font-weight: 900;
  line-height: 1;
  letter-spacing: -0.02em;
  background: linear-gradient(135deg, #ea580c, #f97316);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.aib-type-summary-sep {
  font-size: 24px;
  font-weight: 700;
  color: #cbd5e1;
}
.aib-type-summary-label {
  font-size: 13px;
  color: #64748b;
  font-weight: 500;
}
.aib-type-summary-label--big {
  font-size: 20px;
  font-weight: 800;
  color: #7c3aed;
  letter-spacing: -0.01em;
}

/* ── Bulk discount tier table ────────────────────────────────────────────── */
.aib-tiers {
  background: linear-gradient(135deg, #f0fdf4, #f8fafc);
  border: 1px solid #bbf7d0;
  border-radius: 14px;
  padding: 14px 16px;
  margin-bottom: 18px;
}
.aib-tiers-heading {
  font-size: 11px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #16a34a;
  margin-bottom: 10px;
}
.aib-tier-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 0;
  border-bottom: 1px solid #dcfce7;
  font-size: 12.5px;
}
.aib-tier-row:last-child { border-bottom: none; }
.aib-tier-range { color: #374151; font-weight: 500; }
.aib-tier-disc {
  font-weight: 700;
  color: #16a34a;
  background: #dcfce7;
  padding: 2px 9px;
  border-radius: 100px;
  font-size: 12px;
}

/* ── Buy X Get Y ─────────────────────────────────────────────────────────── */
.aib-bxgy-row {
  display: flex;
  align-items: center;
  gap: 14px;
  justify-content: center;
  width: 100%;
}
.aib-bxgy-side {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
}
.aib-bxgy-label {
  font-size: 10px;
  font-weight: 800;
  letter-spacing: 0.1em;
  color: #db2777;
  text-transform: uppercase;
}
.aib-bxgy-count {
  font-size: 40px;
  font-weight: 900;
  line-height: 1;
  letter-spacing: -0.02em;
  background: linear-gradient(135deg, #db2777, #ec4899);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.aib-bxgy-arrow { width: 24px; height: 24px; color: #cbd5e1; flex-shrink: 0; }

/* ── Shipping icon ───────────────────────────────────────────────────────── */
.aib-shipping-icon { font-size: 36px; line-height: 1; }

/* ── Transitions ─────────────────────────────────────────────────────────── */
.aib-fade-enter-active,
.aib-fade-leave-active  { transition: opacity 0.22s; }
.aib-fade-enter-from,
.aib-fade-leave-to      { opacity: 0; }

.aib-rise-enter-active  { transition: transform 0.32s cubic-bezier(0.22,1,0.36,1), opacity 0.24s; }
.aib-rise-leave-active  { transition: transform 0.18s ease-in, opacity 0.18s; }
.aib-rise-enter-from    { transform: translateY(28px) scale(0.98); opacity: 0; }
.aib-rise-leave-to      { transform: translateY(16px) scale(0.98); opacity: 0; }
</style>
