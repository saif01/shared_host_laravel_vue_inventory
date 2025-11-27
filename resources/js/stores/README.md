# Auth Store Usage Guide

The auth store manages the current logged-in user's authentication state, roles, and permissions using Pinia.

## Setup

The auth store is automatically initialized when the app starts. It will check for an existing token and fetch user data if available.

## Usage in Components

### Option 1: Using the Composable (Recommended)

```vue
<template>
    <div>
        <div v-if="isAuthenticated">
            <p>Welcome, {{ user.name }}!</p>
            <p>Your roles: {{ roleNames.join(', ') }}</p>
        </div>
        
        <v-btn v-if="hasPermission('create-users')" @click="createUser">
            Create User
        </v-btn>
        
        <v-btn v-if="hasRole('admin')" @click="adminAction">
            Admin Action
        </v-btn>
    </div>
</template>

<script>
import { useAuth } from '@/composables/useAuth';

export default {
    setup() {
        const { 
            user, 
            isAuthenticated, 
            hasRole, 
            hasPermission,
            roleNames,
            logout 
        } = useAuth();

        return {
            user,
            isAuthenticated,
            hasRole,
            hasPermission,
            roleNames,
            logout
        };
    }
};
</script>
```

### Option 2: Using mapState and mapActions (Options API)

```vue
<script>
import { useAuthStore } from '@/stores/auth';
import { mapState, mapActions, mapGetters } from 'pinia';

export default {
    computed: {
        ...mapState(useAuthStore, ['user', 'isAuthenticated', 'roles', 'permissions']),
        ...mapGetters(useAuthStore, ['hasRole', 'hasPermission'])
    },
    methods: {
        ...mapActions(useAuthStore, ['login', 'logout', 'fetchUser']),
        
        checkAccess() {
            if (this.hasRole('admin')) {
                // Do admin stuff
            }
            
            if (this.hasPermission('edit-posts')) {
                // Allow editing
            }
        }
    }
};
</script>
```

## Available Getters

### `hasRole(roleName)`
Check if user has a specific role. Accepts string or array.

```javascript
// Single role
if (authStore.hasRole('admin')) { }

// Multiple roles (returns true if user has ANY of the roles)
if (authStore.hasRole(['admin', 'moderator'])) { }
```

### `hasPermission(permissionName)`
Check if user has a specific permission. Accepts string or array.

```javascript
// Single permission
if (authStore.hasPermission('create-users')) { }

// Multiple permissions (returns true if user has ANY)
if (authStore.hasPermission(['create-users', 'edit-users'])) { }
```

### `hasAnyPermission(permissionNames)`
Check if user has any of the given permissions.

```javascript
if (authStore.hasAnyPermission(['create-users', 'edit-users', 'delete-users'])) {
    // User can perform at least one of these actions
}
```

### `hasAllPermissions(permissionNames)`
Check if user has all of the given permissions.

```javascript
if (authStore.hasAllPermissions(['create-users', 'edit-users'])) {
    // User can perform both actions
}
```

### `roleNames`
Get array of user's role names.

```javascript
const roles = authStore.roleNames; // ['admin', 'editor']
```

### `permissionNames`
Get array of user's permission names.

```javascript
const permissions = authStore.permissionNames; // ['create-users', 'edit-posts', ...]
```

## Available Actions

### `login(credentials)`
Login user with email and password.

```javascript
try {
    await authStore.login({
        email: 'user@example.com',
        password: 'password'
    });
    // Redirect to dashboard
} catch (error) {
    // Handle error
}
```

### `logout()`
Logout current user and clear all auth data.

```javascript
authStore.logout();
router.push({ name: 'AdminLogin' });
```

### `fetchUser()`
Fetch current user data from API (useful for refreshing user data).

```javascript
try {
    await authStore.fetchUser();
} catch (error) {
    // Handle error (e.g., token expired)
}
```

### `updateUser(userData)`
Update user data in the store.

```javascript
authStore.updateUser({
    name: 'New Name',
    email: 'newemail@example.com'
});
```

### `init()`
Initialize auth state (called automatically on app start).

## Route Guards

The router automatically checks authentication for routes with `meta: { requiresAuth: true }`.

## Example: Conditional Rendering

```vue
<template>
    <!-- Show only if user has admin role -->
    <v-btn v-if="hasRole('admin')" @click="adminAction">
        Admin Panel
    </v-btn>
    
    <!-- Show only if user has specific permission -->
    <v-btn v-if="hasPermission('delete-users')" @click="deleteUser">
        Delete User
    </v-btn>
    
    <!-- Show if user has any of these permissions -->
    <div v-if="hasAnyPermission(['create-posts', 'edit-posts', 'delete-posts'])">
        Post Management
    </div>
</template>
```

## Example: Programmatic Checks

```javascript
methods: {
    async performAction() {
        if (!this.hasPermission('perform-action')) {
            this.showError('You do not have permission to perform this action');
            return;
        }
        
        // Proceed with action
    },
    
    checkAccess() {
        const requiredRoles = ['admin', 'super-admin'];
        if (!this.hasRole(requiredRoles)) {
            this.$router.push({ name: 'Unauthorized' });
            return;
        }
    }
}
```

