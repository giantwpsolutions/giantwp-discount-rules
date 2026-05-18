import { ref } from "vue";
import apiFetch from "@wordpress/api-fetch";

export const marginSettings = ref({
    enabled: false,
    globalMaxDiscount: 0,
    globalMinMargin: 0,
});

export const isLoadingMargin = ref(false);
export const marginError = ref(null);

export const loadMarginSettings = async () => {
    isLoadingMargin.value = true;
    try {
        const response = await apiFetch({
            path: `${gwpdrPluginData.restUrl}margin-settings`,
            method: "GET",
            headers: { "X-WP-Nonce": gwpdrPluginData.nonce },
        });
        marginSettings.value = {
            enabled:            response.enabled            ?? false,
            globalMaxDiscount:  response.globalMaxDiscount  ?? 0,
            globalMinMargin:    response.globalMinMargin    ?? 0,
        };
    } catch (err) {
        console.error("Failed to load margin settings:", err);
        marginError.value = "Unable to load margin settings.";
    } finally {
        isLoadingMargin.value = false;
    }
};

export const saveMarginSettings = async () => {
    try {
        await apiFetch({
            path: `${gwpdrPluginData.restUrl}margin-settings`,
            method: "POST",
            headers: {
                "X-WP-Nonce": gwpdrPluginData.nonce,
                "Content-Type": "application/json",
            },
            data: marginSettings.value,
        });
    } catch (err) {
        console.error("Failed to save margin settings:", err);
        throw err;
    }
};
