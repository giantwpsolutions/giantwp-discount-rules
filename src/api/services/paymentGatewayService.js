import axios from "axios";


//Fetch the payment gateway
export const fetchPaymentGateways = async () => {
    try {
        const response = await axios.get(`${pluginData.restUrl}payment-gateways`);
        return response.data;
    } catch (error) {
        console.error("Error Fetching Payment Gateways: ", error);
        throw error;
    }
};