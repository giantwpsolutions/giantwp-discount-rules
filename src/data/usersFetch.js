//src/data/usersFetch.js

import { ref } from "vue";
import { fetchUsers, fetchRoles } from "@/api/services/userService";

export const userOptions = ref([]);
export const roleOptions = ref([]);
export const isLoadingUsersAndRoles = ref(true);
export const usersAndRolesError = ref(null);

export const loadUsersAndRoles = async () => {
    try {
        // Fetch users
        const users = await fetchUsers();
        userOptions.value = users.map((user) => ({
            label: `${user.display_name} (#${user.id} ${user.email})`,
            value: user.id,
        }));

        // Fetch roles
        const roles = await fetchRoles();
        roleOptions.value = roles.map((role) => ({
            label: `${role.name}`,
            value: role.key,
        }));
    } catch (error) {
        console.error("Error loading users or roles:", error);
        usersAndRolesError.value = "Failed to load users or roles.";
    } finally {
        isLoadingUsersAndRoles.value = false;
    }
};
