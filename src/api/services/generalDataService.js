import axios from "axios";

// Fetch general data from the WooCommerce API
export const fetchGeneralData = async () => {
    try {
        const response = await axios.get(`${pluginData.restUrl}general`);
        return response.data; // Return the general data (currency code, symbol, etc.)
    } catch (error) {
        console.error("Error fetching general data:", error);
        throw error; // Rethrow the error for higher-level handling
    }
};
