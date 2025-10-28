import apiFetch from "@wordpress/api-fetch";

// Fetch product categories
export const fetchProductCategories = async () => {
    try {
        const response = await apiFetch({
            path: `${gwpdrPluginData.restUrl}categories`,
            method: "GET",
        });
        return response; // Return raw categories data
    } catch (error) {
        console.error("Error fetching product categories:", error);
        throw error;
    }
};

// Fetch product tags
export const fetchProductTags = async () => {
    try {
        const response = await apiFetch({
            path: `${gwpdrPluginData.restUrl}tags`,
            method: "GET",
        });
        return response; // Return raw tags data
    } catch (error) {
        console.error("Error fetching product tags:", error);
        throw error;
    }
};