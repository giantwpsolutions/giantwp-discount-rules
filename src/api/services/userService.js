import apiFetch from "@wordpress/api-fetch";

// Fetch all users with their roles
export const fetchUsers = async (roles = []) => {
    try {
        // Construct the query parameters for roles
        const queryParams = roles.length > 0 ? `?roles=${roles.join(",")}` : "";
        const response = await apiFetch({
            path: `${gwpdrPluginData.restUrl}users${queryParams}`,
            method: "GET",
        });

        // Return user data
        return response;
    } catch (error) {
        console.error("Error fetching users:", error);
        throw error;
    }
};

// Fetch available roles
export const fetchRoles = async () => {
    try {
        const response = await apiFetch({
            path: `${gwpdrPluginData.restUrl}roles`,
            method: "GET",
        });

        // Return roles data
        return response;
    } catch (error) {
        console.error("Error fetching roles:", error);
        throw error;
    }
};
