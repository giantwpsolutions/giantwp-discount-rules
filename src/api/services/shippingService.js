import apiFetch from "@wordpress/api-fetch";

// Fetch countries, states, and continents
export const fetchCountriesAndStates = async () => {
    try {
        const response = await apiFetch({
            path: `${gwpdrPluginData.restUrl}shipping-zones`,
            method: "GET",
        });

        return response; // Return the API response
    } catch (error) {
        console.error("Error fetching countries, states, and continents:", error);
        throw error;
    }
};
