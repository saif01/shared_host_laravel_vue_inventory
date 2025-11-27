import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';

/**
 * Composable for easy access to auth store
 * Usage in components:
 * 
 * import { useAuth } from '@/composables/useAuth';
 * 
 * const { user, isAuthenticated, hasRole, hasPermission } = useAuth();
 */
export function useAuth() {
    const authStore = useAuthStore();

    return {
        // State
        user: computed(() => authStore.user),
        token: computed(() => authStore.token),
        roles: computed(() => authStore.roles),
        permissions: computed(() => authStore.permissions),
        isAuthenticated: computed(() => authStore.isAuthenticated),
        loading: computed(() => authStore.loading),

        // Getters
        hasRole: (roleName) => authStore.hasRole(roleName),
        hasPermission: (permissionName) => authStore.hasPermission(permissionName),
        hasAnyPermission: (permissionNames) => authStore.hasAnyPermission(permissionNames),
        hasAllPermissions: (permissionNames) => authStore.hasAllPermissions(permissionNames),
        roleNames: computed(() => authStore.roleNames),
        permissionNames: computed(() => authStore.permissionNames),

        // Actions
        login: (credentials) => authStore.login(credentials),
        logout: () => authStore.logout(),
        fetchUser: () => authStore.fetchUser(),
        updateUser: (userData) => authStore.updateUser(userData)
    };
}

