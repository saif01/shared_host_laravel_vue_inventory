import { defineStore } from 'pinia';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('admin_token') || null,
        roles: [],
        permissions: [],
        isAuthenticated: false,
        loading: false
    }),

    getters: {
        /**
         * Check if user has a specific role
         * @param {string|array} roleName - Role name(s) to check
         * @returns {boolean}
         */
        hasRole: (state) => (roleName) => {
            if (!state.user || !state.roles.length) return false;

            if (Array.isArray(roleName)) {
                return roleName.some(role =>
                    state.roles.some(userRole =>
                        userRole.name === role || userRole.slug === role
                    )
                );
            }

            return state.roles.some(role =>
                role.name === roleName || role.slug === roleName
            );
        },

        /**
         * Check if user has a specific permission
         * @param {string|array} permissionName - Permission name(s) to check
         * @returns {boolean}
         */
        hasPermission: (state) => (permissionName) => {
            if (!state.user || !state.permissions.length) return false;

            if (Array.isArray(permissionName)) {
                return permissionName.some(perm =>
                    state.permissions.some(userPerm =>
                        userPerm.name === perm || userPerm.slug === perm
                    )
                );
            }

            return state.permissions.some(permission =>
                permission.name === permissionName || permission.slug === permissionName
            );
        },

        /**
         * Check if user has any of the given permissions
         * @param {array} permissionNames - Array of permission names
         * @returns {boolean}
         */
        hasAnyPermission: (state) => (permissionNames) => {
            if (!Array.isArray(permissionNames)) return false;
            return state.permissions.some(permission =>
                permissionNames.includes(permission.name) || permissionNames.includes(permission.slug)
            );
        },

        /**
         * Check if user has all of the given permissions
         * @param {array} permissionNames - Array of permission names
         * @returns {boolean}
         */
        hasAllPermissions: (state) => (permissionNames) => {
            if (!Array.isArray(permissionNames)) return false;
            return permissionNames.every(permName =>
                state.permissions.some(permission =>
                    permission.name === permName || permission.slug === permName
                )
            );
        },

        /**
         * Get user's role names
         * @returns {array}
         */
        roleNames: (state) => {
            return state.roles.map(role => role.name || role.slug);
        },

        /**
         * Get user's permission names
         * @returns {array}
         */
        permissionNames: (state) => {
            return state.permissions.map(perm => perm.name || perm.slug);
        }
    },

    actions: {
        /**
         * Set authentication data
         * @param {object} data - { user, token }
         */
        setAuth(data) {
            this.token = data.token;
            this.user = data.user;
            this.isAuthenticated = true;

            // Store token in localStorage
            if (data.token) {
                localStorage.setItem('admin_token', data.token);
            }

            // Extract roles and permissions from user
            this.extractRolesAndPermissions(data.user);
        },

        /**
         * Extract roles and permissions from user object
         * @param {object} user - User object with roles and permissions
         */
        extractRolesAndPermissions(user) {
            this.roles = [];
            this.permissions = [];

            if (!user) return;

            // Extract roles
            if (user.roles && Array.isArray(user.roles)) {
                this.roles = user.roles;
            }

            // Extract permissions from roles
            const permissionsMap = new Map();

            if (user.roles && Array.isArray(user.roles)) {
                user.roles.forEach(role => {
                    if (role.permissions && Array.isArray(role.permissions)) {
                        role.permissions.forEach(permission => {
                            // Use Map to avoid duplicates
                            const key = permission.id || permission.name || permission.slug;
                            if (key && !permissionsMap.has(key)) {
                                permissionsMap.set(key, permission);
                            }
                        });
                    }
                });
            }

            // Convert Map to Array
            this.permissions = Array.from(permissionsMap.values());
        },

        /**
         * Login user
         * @param {object} credentials - { email, password }
         * @returns {Promise}
         */
        async login(credentials) {
            this.loading = true;
            try {
                const response = await window.axios.post('/api/v1/auth/login', credentials);

                if (response.data.token && response.data.user) {
                    this.setAuth({
                        token: response.data.token,
                        user: response.data.user
                    });

                    return { success: true, data: response.data };
                }

                throw new Error('Invalid response from server');
            } catch (error) {
                this.logout();
                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Logout user
         */
        /**
         * Logout user
         */
        async logout() {
            try {
                // Call logout endpoint to revoke token on server
                if (this.token) {
                    await window.axios.post('/api/v1/auth/logout', {}, {
                        headers: {
                            Authorization: `Bearer ${this.token}`
                        }
                    });
                }
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                // Clear state
                this.user = null;
                this.token = null;
                this.roles = [];
                this.permissions = [];
                this.isAuthenticated = false;

                // Remove token from localStorage
                localStorage.removeItem('admin_token');
            }
        },

        /**
         * Fetch current user from API
         * @returns {Promise}
         */
        async fetchUser() {
            if (!this.token) {
                this.logout();
                return;
            }

            this.loading = true;
            try {
                const response = await window.axios.get('/api/v1/auth/user', {
                    headers: {
                        'Authorization': `Bearer ${this.token}`
                    }
                });

                if (response.data) {
                    this.user = response.data;
                    this.isAuthenticated = true;
                    this.extractRolesAndPermissions(response.data);
                }
            } catch (error) {
                // If 401, user is not authenticated
                if (error.response?.status === 401) {
                    this.logout();
                }
                throw error;
            } finally {
                this.loading = false;
            }
        },

        /**
         * Initialize auth state (check if user is logged in)
         * @returns {Promise}
         */
        async init() {
            if (this.token) {
                try {
                    await this.fetchUser();
                } catch (error) {
                    console.error('Failed to initialize auth:', error);
                    this.logout();
                }
            }
        },

        /**
         * Update user data
         * @param {object} userData - Updated user data
         */
        updateUser(userData) {
            if (this.user) {
                this.user = { ...this.user, ...userData };
                this.extractRolesAndPermissions(this.user);
            }
        }
    }
});

