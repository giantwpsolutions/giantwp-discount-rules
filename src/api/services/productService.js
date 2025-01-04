import apiClient from '../apiClient';

export const fetchProducts = async () => {
    try {
        const response = await apiClient.get('aio-woodiscount/v1/products');
        return response.data;
    } catch (error) {
        console.error('Error fetching products:', error);
        throw error;
    }
};