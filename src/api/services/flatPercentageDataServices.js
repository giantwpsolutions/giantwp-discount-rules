import { ref } from "vue";
import apiFetch from "@wordpress/api-fetch";

export const discountRules = ref([]);

export const fetchFlatPercentageRule = async () => {
    try {
        const response = await apiFetch({
            path: `${pluginData.restUrl}get-flatpercentage-discount`,
            method: "GET",
            headers: {
                "X-WP-Nonce": pluginData.nonce,
            },
        });

        // Ensure response is properly processed
        let parsedResponse = response;
        if (typeof response === "string") {
            try {
                parsedResponse = JSON.parse(response);
            } catch (error) {
                console.error("Error parsing JSON response:", error);
                return [];
            }
        }

        // Store data in `discountRules`
        discountRules.value = Array.isArray(parsedResponse) ? parsedResponse : [];

        // ✅ Return the fetched data
        return discountRules.value;

    } catch (error) {
        console.error("Error fetching discounts:", error);
        discountRules.value = [];
        return [];
    }
};
