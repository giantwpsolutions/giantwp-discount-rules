import { apiFetch } from '@wordpress/api-fetch';

const apiClient = {
    get: (path, options = {}) => {
        return apiFetch({
            path: `${gwpdrPluginData.restUrl}${path}`,
            method: 'GET',
            ...options,
        });
    },

    post: (path, data = {}, options = {}) => {
        return apiFetch({
            path: `${gwpdrPluginData.restUrl}${path}`,
            method: 'POST',
            data,
            ...options,
        });
    },

    put: (path, data = {}, options = {}) => {
        return apiFetch({
            path: `${gwpdrPluginData.restUrl}${path}`,
            method: 'PUT',
            data,
            ...options,
        });
    },

    delete: (path, options = {}) => {
        return apiFetch({
            path: `${gwpdrPluginData.restUrl}${path}`,
            method: 'DELETE',
            ...options,
        });
    },
};

export default apiClient;