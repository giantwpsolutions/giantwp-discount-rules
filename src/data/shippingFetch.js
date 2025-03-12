import { ref } from "vue";
import { fetchCountriesAndStates } from "@/api/services/shippingService";

// Reactive variables
export const countriesOptions = ref([]);
export const isLoadingCountries = ref(true);
export const countriesError = ref(null);

// Function to load countries, states, and continents
export const loadCountriesAndStates = async () => {
    try {
        const continents = await fetchCountriesAndStates();

        // Transform data for cascader
        countriesOptions.value = continents.map((continent) => ({
            value: continent.code,
            label: continent.name,
            children: continent.countries.map((country) => ({
                value: country.code,
                label: country.name,
                children: Object.entries(country.states).map(([state_code, state_name]) => ({
                    value: state_code,
                    label: state_name,
                })),
            })),
        }));
    } catch (error) {
        console.error("Error loading countries, states, and continents:", error);
        countriesError.value = "Failed to load countries, states, and continents.";
    } finally {
        isLoadingCountries.value = false;
    }
};



