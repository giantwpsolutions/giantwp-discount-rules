import apiFetch from "@wordpress/api-fetch";

// Fetch general data from the WooCommerce API
export const fetchGeneralData = async () => {
    try {
        const response = await apiFetch({
            path: `${pluginData.restUrl}general`,
            method: "GET",
        });
        return response; // Return the general data (currency code, symbol, etc.)
    } catch (error) {
        console.error("Error fetching general data:", error);
        throw error; // Rethrow the error for higher-level handling
    }
};
