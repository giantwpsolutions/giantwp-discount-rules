import { ref } from "vue";
import apiFetch from "@wordpress/api-fetch";

// State
export const licenseKey = ref("");
export const licenseStatus = ref("unknown");
export const isLoadingLicense = ref(true);
export const licenseError = ref(null);

// Fetch current license status from DB (via REST)
export const fetchLicenseStatus = async () => {
    isLoadingLicense.value = true;
    try {
        const response = await apiFetch({
            path: `${gwpdrPluginData.restUrl}license/status`,
            method: "GET",
            headers: {
                "X-WP-Nonce": gwpdrPluginData.nonce,
            },
        });

        licenseKey.value = response.license_key || "";
        licenseStatus.value = response.license_status || "unknown";
    } catch (error) {
        console.error("Failed to load license status:", error);
        licenseError.value = "Unable to fetch license status.";
    } finally {
        isLoadingLicense.value = false;
    }
};

// Activate license
export const activateLicense = async (key) => {
    isLoadingLicense.value = true;
    try {
        const response = await apiFetch({
            path: `${gwpdrPluginData.restUrl}license/activate`,
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-WP-Nonce": gwpdrPluginData.nonce,
            },
            data: {
                license_key: key,
            },
        });

        licenseStatus.value = response.status || "invalid";
        licenseKey.value = key;
        return response;
    } catch (err) {
        console.error("License activation failed:", err);
        licenseStatus.value = "invalid";
        throw err;
    } finally {
        isLoadingLicense.value = false;
    }
};

// Deactivate license
export const deactivateLicense = async () => {
    isLoadingLicense.value = true;
    try {
        const response = await apiFetch({
            path: `${gwpdrPluginData.restUrl}license/deactivate`,
            method: "POST",
            headers: {
                "X-WP-Nonce": gwpdrPluginData.nonce,
            },
        });

        if (response.success) {
            licenseKey.value = "";
            licenseStatus.value = "unknown";
        }

        return response;
    } catch (err) {
        console.error("License deactivation failed:", err);
        throw err;
    } finally {
        isLoadingLicense.value = false;
    }
};
