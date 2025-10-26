import apiFetch from "@wordpress/api-fetch";
import { ref } from "vue";

export const saveSettingsData = ref({
    discountBasedOn: "regular_price",
    orderPageLabel: true,
    upsellNotificationWidget: false,
});

export const isLoadingSettings = ref(false);
export const settingsError = ref(null);

// 🔄 Load saved settings from WordPress
export const loadSettings = async () => {
    isLoadingSettings.value = true;
    try {
        const response = await apiFetch({
            path: `${pluginData.restUrl}settings`,
            method: "GET",
            headers: {
                "X-WP-Nonce": pluginData.nonce,
            },
        });

        saveSettingsData.value = {
            discountBasedOn: response.discountBasedOn || "sale_price",
            orderPageLabel: response.orderPageLabel ?? true,
            upsellNotificationWidget: response.upsellNotificationWidget ?? false,
        };
    } catch (err) {
        console.error("❌ Failed to load settings:", err);
        settingsError.value = "Unable to load settings.";
    } finally {
        isLoadingSettings.value = false;
    }
};

// 💾 Save updated settings
export const saveSettings = async () => {
    try {
        const response = await apiFetch({
            path: `${pluginData.restUrl}settings`,
            method: "POST",
            headers: {
                "X-WP-Nonce": pluginData.nonce,
                "Content-Type": "application/json",
            },
            body: JSON.stringify(saveSettingsData.value),
        });

        return response;
    } catch (err) {
        console.error("❌ Failed to save settings:", err);
        throw err;
    }
};
