import axios from "axios";

// Fetch product categories
export const fetchProductCategories = async () => {
    try {
        const response = await axios.get(`${pluginData.restUrl}categories`);
        return response.data; // Return raw categories data
    } catch (error) {
        console.error("Error fetching product categories:", error);
        throw error;
    }
};

// Fetch product tags
export const fetchProductTags = async () => {
    try {
        const response = await axios.get(`${pluginData.restUrl}tags`);
        return response.data; // Return raw tags data
    } catch (error) {
        console.error("Error fetching product tags:", error);
        throw error;
    }
};