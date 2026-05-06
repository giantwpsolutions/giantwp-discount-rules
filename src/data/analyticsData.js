import apiFetch from "@wordpress/api-fetch";
import { ref } from "vue";

export const analyticsRows     = ref([]);
export const analyticsTotals   = ref({ total_revenue: 0, total_discount: 0, total_applied: 0 });
export const analyticsCurrency = ref("$");
export const isLoadingAnalytics = ref(false);
export const analyticsError    = ref(null);

export const loadAnalytics = async () => {
    isLoadingAnalytics.value = true;
    analyticsError.value     = null;
    try {
        const res = await apiFetch({
            path: `${gwpdrPluginData.restUrl}analytics`,
            method: "GET",
            headers: { "X-WP-Nonce": gwpdrPluginData.nonce },
        });
        analyticsRows.value     = res.rows     || [];
        analyticsTotals.value   = res.totals   || { total_revenue: 0, total_discount: 0, total_applied: 0 };
        analyticsCurrency.value = res.currency || "$";
    } catch (err) {
        analyticsError.value = err?.message || "Failed to load analytics.";
    } finally {
        isLoadingAnalytics.value = false;
    }
};

export const resetAnalytics = async () => {
    await apiFetch({
        path: `${gwpdrPluginData.restUrl}analytics/reset`,
        method: "DELETE",
        headers: { "X-WP-Nonce": gwpdrPluginData.nonce },
    });
    await loadAnalytics();
};
