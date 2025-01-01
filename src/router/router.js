// src/router/index.js
import { createRouter, createWebHashHistory } from 'vue-router';

// Import components for the routes
import Discounts from '../pages/Discounts.vue';
import Settings from '../pages/Settings.vue';
import NotFound from '../components/NotFound.vue';

const routes = [
    {
        path: '/',
        name: 'Discounts',
        component: Discounts, // Default route for Discounts page
    },
    {
        path: '/settings',
        name: 'Settings',
        component: Settings, // Route for Settings page
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: NotFound, // Fallback route for 404 errors
    },
];


const router = createRouter({
    history: createWebHashHistory(), // Use hash mode
    routes,
});



export default router;
