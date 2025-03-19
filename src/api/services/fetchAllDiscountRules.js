import { ref } from "vue";
import apiFetch from "@wordpress/api-fetch";

export const discountRules = ref([]);

export const fetchAllDiscountRules = async () => {
    try {
        // console.log("📡 Fetching fresh discount rules...");

        const response = await apiFetch({
            path: `${pluginData.restUrl}get-all-discounts`,  // ✅ Ensure API is correct
            method: "GET",
            headers: {
                "X-WP-Nonce": pluginData.nonce,
            },
        });

        // console.log("✅ API Response for Discounts:", response);

        if (!Array.isArray(response)) {
            console.error("❌ Unexpected API response format:", response);
            return [];
        }

        // ✅ Ensure fresh response is stored in Vue state
        discountRules.value = [...response];
        return discountRules.value;

    } catch (error) {
        console.error("❌ Error fetching discounts:", error);
        return [];
    }
};

