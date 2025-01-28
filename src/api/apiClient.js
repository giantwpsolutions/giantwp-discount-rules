import { apiFetch } from '@wordpress/api-fetch';

const apiClient = {
    get: (path, options = {}) => {
        return apiFetch({
            path: `${pluginData.restUrl}${path}`,
            method: 'GET',
            ...options,
        });
    },

    post: (path, data = {}, options = {}) => {
        return apiFetch({
            path: `${pluginData.restUrl}${path}`,
            method: 'POST',
            data,
            ...options,
        });
    },

    put: (path, data = {}, options = {}) => {
        return apiFetch({
            path: `${pluginData.restUrl}${path}`,
            method: 'PUT',
            data,
            ...options,
        });
    },

    delete: (path, options = {}) => {
        return apiFetch({
            path: `${pluginData.restUrl}${path}`,
            method: 'DELETE',
            ...options,
        });
    },
};

export default apiClient;