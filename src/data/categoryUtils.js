// Build a hierarchical structure for categories
export const buildCategoryHierarchy = (categories) => {
    const categoryMap = {};

    // Create a map of categories
    categories.forEach((category) => {
        categoryMap[category.id] = { ...category, children: [] };
    });

    const hierarchicalCategories = [];

    // Assign child categories to their parent
    categories.forEach((category) => {
        if (category.parent && categoryMap[category.parent]) {
            categoryMap[category.parent].children.push(categoryMap[category.id]);
        } else {
            hierarchicalCategories.push(categoryMap[category.id]); // Top-level category
        }
    });

    return hierarchicalCategories;
};

// Flatten hierarchical categories and format label as "Parent ⇒ Child"
export const flattenCategories = (categories, parentLabel = "") => {
    const flattened = [];

    categories.forEach((category) => {
        const label = parentLabel ? `${parentLabel} ⇒ ${category.name}` : category.name;
        flattened.push({ label, value: category.id });

        // Recurse for children
        if (category.children && category.children.length > 0) {
            flattened.push(...flattenCategories(category.children, label));
        }
    });

    return flattened;
};
