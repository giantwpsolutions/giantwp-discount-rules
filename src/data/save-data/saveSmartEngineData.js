import { ref } from 'vue';

const BASE  = gwpdrPluginData.restUrl + 'smart-engine';
const NONCE = gwpdrPluginData.nonce;

export const smartEngineData = ref({
    enabled:     false,
    provider:    'anthropic',
    api_key:     '',
    api_key_set: '',
    max_discount: 15,
    store_notes:  '',
});

export const smartEngineStats = ref({
    customers_analysed:     0,
    discounts_personalised: 0,
    extra_revenue:          0,
});

export const isLoadingSmartEngine = ref(false);
export const isConnecting         = ref(false);
export const connectStatus        = ref(null);
export const connectMessage       = ref('');

export const loadSmartEngine = async () => {
    isLoadingSmartEngine.value = true;
    try {
        const [settingsRes, statsRes] = await Promise.all([
            fetch(BASE + '/settings', { headers: { 'X-WP-Nonce': NONCE } }),
            fetch(BASE + '/stats',    { headers: { 'X-WP-Nonce': NONCE } }),
        ]);

        if (settingsRes.ok) {
            const s = await settingsRes.json();
            Object.assign(smartEngineData.value, s);
            smartEngineData.value.api_key = '';
        }

        if (statsRes.ok) {
            Object.assign(smartEngineStats.value, await statsRes.json());
        }
    } catch (e) {
        console.error('[SmartEngine] load failed', e);
    } finally {
        isLoadingSmartEngine.value = false;
    }
};

export const saveSmartEngine = async () => {
    isLoadingSmartEngine.value = true;
    try {
        const res  = await fetch(BASE + '/settings', {
            method:  'POST',
            headers: { 'X-WP-Nonce': NONCE, 'Content-Type': 'application/json' },
            body:    JSON.stringify(smartEngineData.value),
        });
        const json = await res.json();
        if (json.data) {
            Object.assign(smartEngineData.value, json.data);
            smartEngineData.value.api_key = '';
        }
        return json;
    } finally {
        isLoadingSmartEngine.value = false;
    }
};

export const testConnection = async () => {
    isConnecting.value   = true;
    connectStatus.value  = null;
    connectMessage.value = '';
    try {
        const res  = await fetch(BASE + '/connect', {
            method:  'POST',
            headers: { 'X-WP-Nonce': NONCE, 'Content-Type': 'application/json' },
            body:    JSON.stringify({
                provider: smartEngineData.value.provider,
                api_key:  smartEngineData.value.api_key,
            }),
        });
        const data = await res.json();
        connectStatus.value  = data.success ? 'success' : 'error';
        connectMessage.value = data.message ?? (data.success ? 'Connected!' : 'Failed');

        // Auto-save the key on successful connection
        if (data.success && smartEngineData.value.api_key) {
            const saveRes  = await fetch(BASE + '/settings', {
                method:  'POST',
                headers: { 'X-WP-Nonce': NONCE, 'Content-Type': 'application/json' },
                body:    JSON.stringify(smartEngineData.value),
            });
            const saveJson = await saveRes.json();
            if (saveJson.data) {
                Object.assign(smartEngineData.value, saveJson.data);
                smartEngineData.value.api_key = '';
            }
        }
    } catch {
        connectStatus.value  = 'error';
        connectMessage.value = 'Request failed — check your network.';
    } finally {
        isConnecting.value = false;
    }
};
