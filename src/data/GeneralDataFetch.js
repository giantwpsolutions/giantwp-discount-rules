import { ref, computed } from "vue";
import { fetchGeneralData } from "@/api/services/generalDataService";

// Reactive variables
export const generalData = ref([]);
export const isLoadingGeneralData = ref(true);
export const generalDataError = ref(null);

// Load general data once
export const loadGeneralData = async () => {
    try {
        const data = await fetchGeneralData();
        generalData.value = data;
    } catch (error) {
        console.error("Error loading general data:", error);
        generalDataError.value = "Failed to load general data.";
    } finally {
        isLoadingGeneralData.value = false;
    }
};

