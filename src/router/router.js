// src/router/index.js
import { createRouter, createWebHashHistory } from 'vue-router';

import Discounts    from '../pages/Discounts.vue';
import Settings     from '../pages/Settings.vue';
import Analytics    from '../pages/Analytics.vue';
import SmartEngine  from '../pages/SmartEngine.vue';
import NotFound     from '../components/NotFound.vue';

const routes = [
    {
        path: '/',
        name: 'Discounts',
        component: Discounts,
    },
    {
        path: '/settings',
        name: 'Settings',
        component: Settings,
    },
    {
        path: '/analytics',
        name: 'Analytics',
        component: Analytics,
    },
    {
        path: '/smart-engine',
        name: 'SmartEngine',
        component: SmartEngine,
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: NotFound,
    },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

export default router;
