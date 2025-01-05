// src/data/productsFetch.js
import { ref } from "vue";
import { fetchProducts } from "@/api/services/productService"; // Adjust the import path

// Reactive state
export const productOptions = ref([]);
export const isLoadingProducts = ref(true);
export const productError = ref(null);

// Function to fetch products
export const loadProducts = async () => {
    try {
        const products = await fetchProducts();
        productOptions.value = products.map((product) => ({
            label: product.name, // Product name
            value: product.id,   // Product ID
        }));
    } catch (error) {
        console.error("Error loading products:", error);
        productError.value = "Failed to load products.";
    } finally {
        isLoadingProducts.value = false;
    }
};
