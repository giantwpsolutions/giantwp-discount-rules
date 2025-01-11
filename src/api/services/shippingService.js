import axios from "axios";

// Fetch countries, states, and continents
export const fetchCountriesAndStates = async () => {
    try {
        const response = await axios.get(`${pluginData.restUrl}shipping-zones`);
        return response.data; // Return the API response
    } catch (error) {
        console.error("Error fetching countries, states, and continents:", error);
        throw error;
    }
};
