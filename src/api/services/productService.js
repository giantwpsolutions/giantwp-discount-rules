import apiFetch from "@wordpress/api-fetch";

// Fetch Products
export const fetchProducts = async () => {
    try {
        // Use `apiFetch` for WordPress API requests
        const response = await apiFetch({
            path: `${gwpdrPluginData.restUrl}products`,
            method: "GET",
        });

        // Return the product data
        return response;
    } catch (error) {
        // Log error for debugging
        console.error("Error fetching products:", error);

        // Re-throw error for further handling
        throw error;
    }
};