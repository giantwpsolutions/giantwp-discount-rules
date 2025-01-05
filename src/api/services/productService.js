import axios from "axios";

// Fetch Products
export const fetchProducts = async () => {
    try {
        // Use full URL if Vue is on a different domain/port
        const response = await axios.get(`${pluginData.restUrl}products`);

        // Log API response for debugging
        console.log("API Response:", response.data);

        // Return the product data
        return response.data;
    } catch (error) {
        // Log error for debugging
        console.error("Error fetching products:", error);

        // Re-throw error for further handling
        throw error;
    }
};
