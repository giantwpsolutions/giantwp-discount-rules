// src/data/productsFetch.js
import { ref } from "vue";
import { fetchProducts } from "@/api/services/productService";
const { __ } = wp.i18n; // Localization function for translations

// Reactive states
export const productOptions = ref([]);    // For products
export const variationOptions = ref([]);  // For variations
export const isLoadingProducts = ref(true);
export const productError = ref(null);

// Function to fetch products and variations
export const loadProducts = async () => {
    try {
        const products = await fetchProducts(); // Using wp.apiFetch from productService.js

        // Clear existing options
        productOptions.value = [];
        variationOptions.value = [];

        // Populate product and variation options
        products.forEach((product) => {
            // Add product to productOptions
            productOptions.value.push({
                label: product.name,
                value: product.id,
            });

            // Add variations to variationOptions
            if (product.variations && product.variations.length > 0) {
                product.variations.forEach((variation) => {
                    const attributes = variation.attributes || {};

                    // Build the attribute string (e.g., "Color: Black")
                    const attributeString = Object.entries(attributes)
                        .map(([key, value]) => `${value}`)
                        .join(", ");

                    // Push variation with attributes to options
                    variationOptions.value.push({
                        label: `${product.name} - ${attributeString || "N/A"}`,
                        value: variation.id,
                    });
                });
            }
        });

    } catch (error) {
        console.error("Error loading products:", error);
        productError.value = __("Failed to load products.", "giantwp-discount-rules");
    } finally {
        isLoadingProducts.value = false;
    }
};
