import { ref } from "vue";
import { fetchGeneralData } from "@/api/services/generalDataService";

// Reactive variables
export const generalData = ref([]);
export const isLoadingGeneralData = ref(true);
export const generalDataError = ref(null);


export const loadGeneralData = async () => {
    try {
        // Fetch data using the service
        const data = await fetchGeneralData();
        generalData.value = data;
    } catch (error) {
        console.error("Error loading general data:", error);
        generalDataError.value = "Failed to load general data.";
    } finally {
        isLoadingGeneralData.value = false;
    }
}