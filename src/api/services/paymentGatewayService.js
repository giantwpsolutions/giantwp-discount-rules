import apiFetch from "@wordpress/api-fetch";


//Fetch the payment gateway
export const fetchPaymentGateways = async () => {
    try {
        const response = await apiFetch({
            path: `${gwpdrPluginData.restUrl}payment-gateways`,
            method: "GET",
        });
        return response;
    } catch (error) {
        console.error("Error Fetching Payment Gateways: ", error);
        throw error;
    }
};