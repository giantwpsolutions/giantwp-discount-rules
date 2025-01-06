import axios from "axios";

// Fetch all users with their roles
export const fetchUsers = async (roles = []) => {
    try {
        // Construct the query parameters for roles
        const queryParams = roles.length > 0 ? `?roles=${roles.join(",")}` : "";
        const response = await axios.get(`${pluginData.restUrl}users${queryParams}`);

        // Return user data
        return response.data;
    } catch (error) {
        console.error("Error fetching users:", error);
        throw error;
    }
};

// Fetch available roles
export const fetchRoles = async () => {
    try {
        const response = await axios.get(`${pluginData.restUrl}roles`);

        // Return roles data
        return response.data;
    } catch (error) {
        console.error("Error fetching roles:", error);
        throw error;
    }
};
