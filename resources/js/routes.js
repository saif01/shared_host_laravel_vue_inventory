import {
    createRouter,
    createWebHistory
} from 'vue-router'


// All Routes define
const routes = [
    // Routes
    {
        path: '/',
        component: () => import('./components/admin/AdminLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                redirect: { name: 'AdminDashboard' }
            },
            {
                path: 'dashboard',
                component: () => import('./components/admin/AdminDashboard.vue'),
                name: 'AdminDashboard',
                meta: { title: 'Admin Dashboard', permissions: ['access-dashboard'] }
            },
            {
                path: 'users',
                component: () => import('./components/admin/users/AdminUsers.vue'),
                name: 'AdminUsers',
                meta: { title: 'User Management', permissions: ['manage-users'] }
            },
            {
                path: 'roles',
                component: () => import('./components/admin/users/AdminRoles.vue'),
                name: 'AdminRoles',
                meta: { title: 'Role Management', permissions: ['manage-roles'] }
            },
            {
                path: 'permissions',
                component: () => import('./components/admin/users/AdminPermissions.vue'),
                name: 'AdminPermissions',
                meta: { title: 'Permission Management', permissions: ['manage-roles'] }
            },
            {
                path: 'settings',
                component: () => import('./components/admin/settings/AdminSettings.vue'),
                name: 'AdminSettings',
                meta: { title: 'Settings', permissions: ['manage-settings'] }
            },
            {
                path: 'login-logs',
                component: () => import('./components/admin/logs/AdminLoginLogs.vue'),
                name: 'AdminLoginLogs',
                meta: { title: 'Login Logs Management', permissions: ['view-login-logs'] }
            },

        ]
    },

    // Admin Login
    {
        path: '/login',
        component: () => import('./components/admin/auth/AdminLogin.vue'),
        name: 'AdminLogin',
        meta: { title: 'Admin Login' }
    },














    {
        path: '/:pathMatch(.*)*',
        component: () => import('./components/admin/auth/AdminLogin.vue'),
        name: 'NotFound',
        meta: {
            title: 'Page Not Found',
        },
    }



];

const router = createRouter({
    history: createWebHistory(),
    //history: createWebHistory(import.meta.env.BASE_URL),
    routes,
});


// Helper to handle progress bar
const progressBar = {
    start: () => {
        const bar = router.getProgressBar ? router.getProgressBar() : null;
        if (bar && typeof bar.start === 'function') bar.start();
    },
    finish: () => {
        const bar = router.getProgressBar ? router.getProgressBar() : null;
        if (bar && typeof bar.finish === 'function') bar.finish();
    },
    fail: () => {
        const bar = router.getProgressBar ? router.getProgressBar() : null;
        if (bar && typeof bar.fail === 'function') bar.fail();
    }
};

// Run before every route request
router.beforeEach(async (to, from, next) => {
    // Start progress bar on route change
    progressBar.start();

    const appName = import.meta.env.VITE_APP_NAME || 'Micro Control Technology';
    const title = to.meta && to.meta.title ? to.meta.title : '';
    document.title = `${title ? title + ' - ' : ''}${appName}`;

    // Import auth store
    const { useAuthStore } = await import('./stores/auth');
    const authStore = useAuthStore();

    // Check authentication for admin routes
    if (to.meta.requiresAuth) {
        if (!authStore.isAuthenticated || !authStore.token) {
            // Try to fetch user if token exists
            if (authStore.token) {
                try {
                    await authStore.fetchUser();
                    if (!authStore.isAuthenticated) {
                        progressBar.finish();
                        next({ name: 'AdminLogin' });
                        return;
                    }
                } catch (error) {
                    progressBar.finish();
                    next({ name: 'AdminLogin' });
                    return;
                }
            } else {
                progressBar.finish();
                next({ name: 'AdminLogin' });
                return;
            }
        }

        // Permission check for admin routes
        if (to.meta.permissions && authStore.isAuthenticated) {
            const requiredPermissions = Array.isArray(to.meta.permissions) ? to.meta.permissions : [to.meta.permissions];

            const hasPermission = requiredPermissions.some((permission) =>
                authStore.hasPermission(permission)
            );

            const isAdministrator = authStore.hasRole && authStore.hasRole(['admin', 'administrator']);

            if (!hasPermission && !isAdministrator) {
                progressBar.finish();
                next({ name: 'AdminDashboard' });
                return;
            }
        }
    }

    // Redirect logged-in admin away from login page
    if (to.name === 'AdminLogin') {
        if (authStore.isAuthenticated && authStore.token) {
            progressBar.finish();
            next({ name: 'AdminDashboard' });
            return;
        }
    }

    next();
});

// Run after route is resolved
router.afterEach((to, from) => {
    // Finish progress bar after route change
    setTimeout(() => {
        progressBar.finish();
    }, 100);
});

// Handle route errors
router.onError((error) => {
    progressBar.fail();
    console.error('Router error:', error);
});



export default router;
