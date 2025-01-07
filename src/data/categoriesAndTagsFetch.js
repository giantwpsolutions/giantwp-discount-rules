import { ref } from "vue";
import { fetchProductCategories, fetchProductTags } from "@/api/services/productCategoryTagService";



// Reactive state for categories and tags
export const categoryOptions = ref([]);
export const tagOptions = ref([]);
export const isLoadingCategoriesTags = ref(true);
export const categoriesTagsError = ref(null);

// Helper to format categories (Parent ⇒ Child)
const formatCategoryLabel = (category, categories) => {
    const parent = categories.find((cat) => cat.id === category.parent);
    if (parent) {
        return `${parent.name} ⇒ ${category.name}`;
    }
    return category.name;
};

// Load categories and tags
export const loadCategoriesAndTags = async () => {
    try {
        // Fetch categories
        const categories = await fetchProductCategories();
        categoryOptions.value = categories.map((category) => ({
            label: formatCategoryLabel(category, categories),
            value: category.id,
        }));

        // Fetch tags
        const tags = await fetchProductTags();
        tagOptions.value = tags.map((tag) => ({
            label: tag.name,
            value: tag.id,
        }));
    } catch (error) {
        console.error("Error loading categories and tags:", error);
        categoriesTagsError.value = "Failed to load categories or tags.";
    } finally {
        isLoadingCategoriesTags.value = false;
    }
};