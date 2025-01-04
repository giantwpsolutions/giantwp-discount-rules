import axios from 'axios';

const apiClient = axios.create({
    baseURL: pluginData.restUrl, // Defined in your PHP script via `wp_localize_script`
    headers: {
        'Content-Type': 'application/json',
    },
});

export default apiClient;