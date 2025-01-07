import { ref } from "vue";
import { fetchPaymentGateways } from "../api/services/paymentGatewayService";

export const paymentGatewayOptions = ref([]);
export const isLoadingPaymentGateways = ref(true);
export const paymentGatewayError = ref(null);

export const loadPaymentGateways = async () => {
    try {
        const gateways = await fetchPaymentGateways();
        paymentGatewayOptions.value = gateways.map((gateway) => ({
            label: gateway.title,
            value: gateway.id,
        }));
    } catch (error) {
        console.error("Error loading payment gateways:", error);
        paymentGatewayError.value = "Failed to load payment gateways.";
    } finally {
        isLoadingPaymentGateways.value = false;
    }
};