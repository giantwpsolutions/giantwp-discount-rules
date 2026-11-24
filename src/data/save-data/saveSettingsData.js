import apiFetch from "@wordpress/api-fetch";
import { ref } from "vue";

export const saveSettingsData = ref({
    discountBasedOn: "regular_price",
    orderPageLabel: true,
    upsellNotificationWidget: false,
    showProductBadge: false,
    badgeBgColor: "#1c4a96",
    badgeTextColor: "#ffffff",
    countdownTimerEnabled: false,
    countdownLabel: "Sale ends in",
});

export const isLoadingSettings = ref(false);
export const settingsError = ref(null);

// 🔄 Load saved settings from WordPress
export const loadSettings = async () => {
    isLoadingSettings.value = true;
    try {
        const response = await apiFetch({
            path: `${gwpdrPluginData.restUrl}settings`,
            method: "GET",
            headers: {
                "X-WP-Nonce": gwpdrPluginData.nonce,
            },
        });

        saveSettingsData.value = {
            discountBasedOn: response.discountBasedOn || "regular_price",
            orderPageLabel: response.orderPageLabel ?? true,
            upsellNotificationWidget: response.upsellNotificationWidget ?? false,
            showProductBadge: response.showProductBadge ?? false,
            badgeBgColor: response.badgeBgColor ?? "#1c4a96",
            badgeTextColor: response.badgeTextColor ?? "#ffffff",
            countdownTimerEnabled: response.countdownTimerEnabled ?? false,
            countdownLabel: response.countdownLabel ?? "Sale ends in",
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
            path: `${gwpdrPluginData.restUrl}settings`,
            method: "POST",
            headers: {
                "X-WP-Nonce": gwpdrPluginData.nonce,
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
