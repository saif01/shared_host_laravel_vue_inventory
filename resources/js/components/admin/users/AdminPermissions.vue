<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Permission Management</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                Add New Permission
            </v-btn>
        </div>

        <!-- Search and Filter -->
        <v-card class="mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="4" v-if="viewMode === 'flat'">
                        <v-select v-model="perPage" :items="perPageOptions" label="Items per page"
                            prepend-inner-icon="mdi-format-list-numbered" variant="outlined" density="compact"
                            @update:model-value="onPerPageChange"></v-select>
                    </v-col>
                    <v-col cols="12" :md="viewMode === 'flat' ? 4 : 6">
                        <v-select v-model="selectedGroup" :items="groups"
                            :item-title="item => typeof item === 'string' ? item : item.name"
                            :item-value="item => typeof item === 'string' ? item : item.name" label="Filter by Group"
                            prepend-inner-icon="mdi-filter" variant="outlined" density="compact" clearable
                            @update:model-value="loadPermissions"></v-select>
                    </v-col>
                    <v-col cols="12" :md="viewMode === 'flat' ? 4 : 6">
                        <v-text-field v-model="searchQuery" label="Search permissions" prepend-inner-icon="mdi-magnify"
                            variant="outlined" density="compact" clearable
                            @update:model-value="loadPermissions"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Permission Groups - Compact Clickable Chips -->
        <v-card class="mb-4">
            <v-card-text class="py-3">
                <div class="d-flex align-center flex-wrap" style="gap: 8px;">
                    <v-btn-toggle v-model="viewMode" mandatory density="compact" class="mr-4">
                        <v-btn value="flat" size="small">
                            <v-icon start>mdi-format-list-bulleted</v-icon>
                            Flat View
                        </v-btn>
                        <v-btn value="grouped" size="small">
                            <v-icon start>mdi-folder</v-icon>
                            Grouped View
                        </v-btn>
                    </v-btn-toggle>
                    <span class="text-caption text-grey mr-2">Filter by Group:</span>
                    <v-chip v-for="group in groups" :key="group.name" :color="getGroupColor(group.name)" size="small"
                        :variant="selectedGroup === group.name ? 'flat' : 'outlined'"
                        :class="{ 'font-weight-bold': selectedGroup === group.name }" @click="filterByGroup(group.name)"
                        style="cursor: pointer;" class="group-filter-chip">
                        {{ group.name }}
                        <v-chip size="x-small"
                            :color="selectedGroup === group.name ? 'white' : getGroupColor(group.name)" variant="flat"
                            class="ml-2">
                            {{ group.permissions_count || 0 }}
                        </v-chip>
                    </v-chip>
                    <v-chip v-if="selectedGroup" size="small" color="grey" variant="text" prepend-icon="mdi-close"
                        @click="clearGroupFilter" style="cursor: pointer;" class="ml-2">
                        Clear Filter
                    </v-chip>
                </div>
            </v-card-text>
        </v-card>

        <!-- Permissions List -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Permissions</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ pagination.total || 0 }}</strong>
                    <span v-if="permissions.length > 0 && viewMode === 'flat'">
                        | Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage, pagination.total) }} of {{ pagination.total }}
                    </span>
                </span>
            </v-card-title>
            <v-card-text>
                <!-- Grouped View -->
                <div v-if="viewMode === 'grouped'">
                    <div v-for="(permissions, group) in groupedPermissions" :key="group" class="mb-6">
                        <h3 class="text-h6 mb-3">
                            {{ group.charAt(0).toUpperCase() + group.slice(1) }}
                            <v-chip size="small" color="primary" variant="text" class="ml-2">
                                {{ permissions.length }} permission{{ permissions.length !== 1 ? 's' : '' }}
                            </v-chip>
                        </h3>
                        <v-table>
                            <thead>
                                <tr>
                                    <th class="sortable" @click="onSort('name')">
                                        <div class="d-flex align-center">
                                            Name
                                            <v-icon :icon="getSortIcon('name')" size="small" class="ml-1"></v-icon>
                                        </div>
                                    </th>
                                    <th class="sortable" @click="onSort('slug')">
                                        <div class="d-flex align-center">
                                            Slug
                                            <v-icon :icon="getSortIcon('slug')" size="small" class="ml-1"></v-icon>
                                        </div>
                                    </th>
                                    <th>Description</th>
                                    <th>Roles</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Skeleton Loaders for Grouped View -->
                                <template v-if="loading">
                                    <tr v-for="n in 5" :key="`skeleton-grouped-${n}`">
                                        <td>
                                            <v-skeleton-loader type="text" width="150"></v-skeleton-loader>
                                        </td>
                                        <td>
                                            <v-skeleton-loader type="chip" width="120" height="24"></v-skeleton-loader>
                                        </td>
                                        <td>
                                            <v-skeleton-loader type="text" width="200"></v-skeleton-loader>
                                        </td>
                                        <td>
                                            <v-skeleton-loader type="chip" width="60" height="24"></v-skeleton-loader>
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <v-skeleton-loader type="button" width="32" height="32" class="mr-1"></v-skeleton-loader>
                                                <v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                                <!-- Actual Permission Data for Grouped View -->
                                <template v-else>
                                    <tr v-for="permission in permissions" :key="permission.id">
                                        <td>
                                            <div class="font-weight-medium">{{ permission.name }}</div>
                                        </td>
                                        <td>
                                            <v-chip size="small" variant="outlined">{{ permission.slug }}</v-chip>
                                        </td>
                                        <td>
                                            <span class="text-caption text-grey">
                                                {{ permission.description || 'No description' }}
                                            </span>
                                        </td>
                                        <td>
                                            <v-chip size="small" color="info" variant="text">
                                                {{ permission.roles_count || 0 }} role{{ (permission.roles_count || 0) !== 1
                                                    ? 's' : '' }}
                                            </v-chip>
                                        </td>
                                        <td>
                                            <v-btn size="small" icon="mdi-pencil" @click="openDialog(permission)"
                                                variant="text"></v-btn>
                                            <v-btn size="small" icon="mdi-delete" @click="deletePermission(permission)"
                                                variant="text" color="error"
                                                :disabled="(permission.roles_count || 0) > 0"></v-btn>
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </v-table>
                    </div>
                </div>

                <!-- Flat View -->
                <v-table v-else>
                    <thead>
                        <tr>
                            <th class="sortable" @click="onSort('name')">
                                <div class="d-flex align-center">
                                    Name
                                    <v-icon :icon="getSortIcon('name')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th class="sortable" @click="onSort('slug')">
                                <div class="d-flex align-center">
                                    Slug
                                    <v-icon :icon="getSortIcon('slug')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th class="sortable" @click="onSort('group')">
                                <div class="d-flex align-center">
                                    Group
                                    <v-icon :icon="getSortIcon('group')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th>Description</th>
                            <th>Roles</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Skeleton Loaders for Flat View -->
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-flat-${n}`">
                            <td>
                                <v-skeleton-loader type="text" width="150"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="chip" width="120" height="24"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="chip" width="100" height="24"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="200"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="chip" width="60" height="24"></v-skeleton-loader>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <v-skeleton-loader type="button" width="32" height="32" class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader>
                                </div>
                            </td>
                        </tr>
                        <!-- Actual Permission Data for Flat View -->
                        <template v-else>
                            <tr v-for="permission in permissions" :key="permission.id">
                                <td>
                                    <div class="font-weight-medium">{{ permission.name }}</div>
                                </td>
                                <td>
                                    <v-chip size="small" variant="outlined">{{ permission.slug }}</v-chip>
                                </td>
                                <td>
                                    <v-chip size="small" :color="getGroupColor(permission.group)">
                                        {{ permission.group }}
                                    </v-chip>
                                </td>
                                <td>
                                    <span class="text-caption text-grey">
                                        {{ permission.description || 'No description' }}
                                    </span>
                                </td>
                                <td>
                                    <v-chip size="small" color="info" variant="text">
                                        {{ permission.roles_count || 0 }} role{{ (permission.roles_count || 0) !== 1 ? 's' :
                                            '' }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-btn size="small" icon="mdi-pencil" @click="openDialog(permission)"
                                        variant="text"></v-btn>
                                    <v-btn size="small" icon="mdi-delete" @click="deletePermission(permission)"
                                        variant="text" color="error" :disabled="(permission.roles_count || 0) > 0"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="permissions.length === 0">
                                <td colspan="6" class="text-center py-4">No permissions found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination and Records Info (only for flat view) -->
                <div v-if="viewMode === 'flat'" class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <div class="text-caption text-grey">
                        <span v-if="permissions.length > 0 && pagination.total > 0">
                            Showing <strong>{{ ((currentPage - 1) * perPage) + 1 }}</strong> to 
                            <strong>{{ Math.min(currentPage * perPage, pagination.total) }}</strong> of 
                            <strong>{{ pagination.total.toLocaleString() }}</strong> records
                            <span v-if="pagination.last_page > 1" class="ml-2">
                                (Page {{ currentPage }} of {{ pagination.last_page }})
                            </span>
                        </span>
                        <span v-else>
                            No records found
                        </span>
                    </div>
                    <div v-if="pagination.last_page > 1" class="d-flex align-center gap-2">
                        <v-pagination 
                            v-model="currentPage"
                            :length="pagination.last_page" 
                            :total-visible="7"
                            density="comfortable"
                            @update:model-value="loadPermissions">
                        </v-pagination>
                    </div>
                </div>
            </v-card-text>
        </v-card>

        <!-- Permission Dialog -->
        <v-dialog v-model="dialog" max-width="600" persistent>
            <v-card>
                <v-card-title>
                    {{ editingPermission ? 'Edit Permission' : 'Add New Permission' }}
                </v-card-title>
                <v-card-text>
                    <v-form ref="permissionForm" @submit.prevent="savePermission">
                        <v-text-field v-model="form.name" label="Permission Name" :rules="[rules.required]" required
                            hint="Display name for the permission (e.g., 'Manage Pages')" persistent-hint class="mb-4"
                            @blur="autoGenerateSlugFromName"></v-text-field>

                        <v-text-field v-model="form.slug" label="Slug"
                            hint="URL-friendly identifier (auto-generated if empty)" persistent-hint
                            class="mb-4"></v-text-field>

                        <v-select v-model="form.group" :items="groups"
                            :item-title="item => typeof item === 'string' ? item : item.name"
                            :item-value="item => typeof item === 'string' ? item : item.name" label="Group"
                            :rules="[rules.required]" required hint="Category/group for organizing permissions"
                            persistent-hint class="mb-4">
                            <template v-slot:append-item>
                                <v-list-item>
                                    <v-text-field v-model="newGroup" label="Add New Group" prepend-inner-icon="mdi-plus"
                                        variant="outlined" density="compact" @keyup.enter="addNewGroup"></v-text-field>
                                </v-list-item>
                            </template>
                        </v-select>

                        <v-textarea v-model="form.description" label="Description"
                            hint="Brief description of what this permission allows" persistent-hint rows="2"
                            class="mb-4"></v-textarea>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="closeDialog" variant="text">Cancel</v-btn>
                    <v-btn @click="savePermission" color="primary" :loading="saving">
                        {{ editingPermission ? 'Update' : 'Create' }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

    </div>
</template>

<script>
import adminPaginationMixin from '../../../mixins/adminPaginationMixin';

export default {
    mixins: [adminPaginationMixin],
    data() {
        return {
            permissions: [],
            groupedPermissions: {},
            groups: [],
            dialog: false,
            editingPermission: null,
            viewMode: 'flat',
            searchQuery: '', // Note: Using searchQuery instead of search for this component
            selectedGroup: null,
            newGroup: '',
            form: {
                name: '',
                slug: '',
                group: '',
                description: '',
            },
            rules: {
                required: value => !!value || 'This field is required'
            }
        };
    },
    async mounted() {
        await this.loadGroups();
        await this.loadPermissions();
    },
    methods: {
        /**
         * Load all permissions from the API
         * Supports filtering by group and search query
         */
        async loadPermissions() {
            try {
                this.loading = true;
                const params = {};

                if (this.searchQuery) {
                    params.search = this.searchQuery;
                }

                if (this.selectedGroup) {
                    params.group = this.selectedGroup;
                }

                // Load grouped permissions for grouped view (no pagination)
                if (this.viewMode === 'grouped') {
                    params.grouped = true;
                } else {
                    // Add pagination for flat view
                    params.page = this.currentPage;
                    params.per_page = this.perPage;
                }

                const response = await this.$axios.get('/api/v1/permissions', {
                    params,
                    headers: this.getAuthHeaders()
                });

                if (this.viewMode === 'grouped') {
                    this.groupedPermissions = response.data;
                    // Flatten for easier access
                    this.permissions = [];
                    Object.values(response.data).forEach(group => {
                        this.permissions.push(...group);
                    });
                    // Reset pagination for grouped view
                    this.pagination = {
                        current_page: 1,
                        last_page: 1,
                        per_page: this.permissions.length,
                        total: this.permissions.length
                    };
                } else {
                    // Handle paginated response
                    if (response.data.data) {
                        this.permissions = response.data.data || [];
                        this.pagination = {
                            current_page: response.data.current_page,
                            last_page: response.data.last_page,
                            per_page: response.data.per_page,
                            total: response.data.total
                        };
                    } else {
                        // Fallback for non-paginated response
                        this.permissions = response.data || [];
                        this.pagination = {
                            current_page: 1,
                            last_page: 1,
                            per_page: this.permissions.length,
                            total: this.permissions.length
                        };
                    }
                    // Group them for reference
                    this.groupedPermissions = {};
                    this.permissions.forEach(permission => {
                        if (!this.groupedPermissions[permission.group]) {
                            this.groupedPermissions[permission.group] = [];
                        }
                        this.groupedPermissions[permission.group].push(permission);
                    });
                }
            } catch (error) {
                this.handleApiError(error, 'Failed to load permissions');
            } finally {
                this.loading = false;
            }
        },

        /**
         * Load available permission groups with permission counts
         */
        async loadGroups() {
            try {
                const response = await this.$axios.get('/api/v1/permissions/groups', {
                    headers: this.getAuthHeaders()
                });
                // Handle both old format (array of strings) and new format (array of objects)
                if (response.data && response.data.length > 0) {
                    if (typeof response.data[0] === 'string') {
                        // Old format - convert to new format
                        this.groups = response.data.map(name => ({
                            name: name,
                            permissions_count: 0
                        }));
                    } else {
                        // New format
                        this.groups = response.data;
                    }
                } else {
                    this.groups = [];
                }
            } catch (error) {
                console.error('Error loading groups:', error);
                this.groups = [];
            }
        },

        /**
         * Open dialog for creating or editing a permission
         */
        openDialog(permission) {
            if (permission) {
                this.editingPermission = permission;
                this.form = {
                    name: permission.name,
                    slug: permission.slug,
                    group: permission.group,
                    description: permission.description || '',
                };
            } else {
                this.editingPermission = null;
                this.form = {
                    name: '',
                    slug: '',
                    group: '',
                    description: '',
                };
            }
            this.dialog = true;
        },

        /**
         * Close dialog and reset form
         */
        closeDialog() {
            this.dialog = false;
            this.editingPermission = null;
            this.form = {
                name: '',
                slug: '',
                group: '',
                description: '',
            };
            if (this.$refs.permissionForm) {
                this.$refs.permissionForm.resetValidation();
            }
        },

        /**
         * Auto-generate slug from permission name
         */
        autoGenerateSlugFromName() {
            if (!this.form.slug && this.form.name && !this.editingPermission) {
                this.form.slug = this.form.name
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        },

        /**
         * Add a new group to the groups list (for permission form)
         */
        addNewGroup() {
            if (this.newGroup && !this.groups.find(g => g.name === this.newGroup || (typeof g === 'string' && g === this.newGroup))) {
                const groupName = this.newGroup.trim();
                this.groups.push({
                    name: groupName,
                    permissions_count: 0
                });
                this.form.group = groupName;
                this.newGroup = '';
            }
        },


        /**
         * Save permission (create or update)
         */
        async savePermission() {
            // Validate form
            if (!this.$refs.permissionForm) {
                this.showError('Form reference not found');
                return;
            }

            if (!this.$refs.permissionForm.validate()) {
                return;
            }

            // Auto-generate slug if empty
            if (!this.form.slug && this.form.name) {
                this.form.slug = this.form.name
                    .toLowerCase()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }

            this.saving = true;
            try {
                const url = this.editingPermission
                    ? `/api/v1/permissions/${this.editingPermission.id}`
                    : '/api/v1/permissions';

                const method = this.editingPermission ? 'put' : 'post';

                const data = {
                    name: this.form.name.trim(),
                    slug: this.form.slug ? this.form.slug.trim() : null,
                    group: this.form.group.trim(),
                    description: this.form.description ? this.form.description.trim() : null,
                };

                await this.$axios[method](url, data, {
                    headers: this.getAuthHeaders()
                });

                this.showSuccess(
                    this.editingPermission ? 'Permission updated successfully' : 'Permission created successfully'
                );
                this.closeDialog();
                await this.loadGroups();
                await this.loadPermissions();
            } catch (error) {
                this.handleApiError(error, 'Error saving permission');
            } finally {
                this.saving = false;
            }
        },

        /**
         * Delete a permission
         */
        async deletePermission(permission) {
            if ((permission.roles_count || 0) > 0) {
                this.showError('Cannot delete permission that is assigned to roles. Please remove it from all roles first.');
                return;
            }

            if (!confirm(`Are you sure you want to delete the permission "${permission.name}"?`)) {
                return;
            }

            try {
                await this.$axios.delete(`/api/v1/permissions/${permission.id}`, {
                    headers: this.getAuthHeaders()
                });

                this.showSuccess('Permission deleted successfully');
                await this.loadPermissions();
            } catch (error) {
                this.handleApiError(error, 'Error deleting permission');
            }
        },

        /**
         * Get color for group chip
         */
        getGroupColor(group) {
            const colors = {
                'general': 'primary',
                'content': 'success',
                'users': 'info',
                'leads': 'warning',
                'careers': 'purple',
                'settings': 'orange',
                'system': 'error',
            };
            return colors[group] || 'grey';
        },

        onPerPageChange() {
            this.resetPagination();
            this.loadPermissions();
        },
        onSort(field) {
            this.handleSort(field);
            this.loadPermissions();
        },

        /**
         * Filter permissions by group
         */
        filterByGroup(groupName) {
            if (this.selectedGroup === groupName) {
                // If clicking the same group, clear the filter
                this.clearGroupFilter();
            } else {
                // Set the selected group and reload permissions
                this.selectedGroup = groupName;
                this.resetPagination();
                this.loadPermissions();
            }
        },

        /**
         * Clear group filter
         */
        clearGroupFilter() {
            this.selectedGroup = null;
            this.resetPagination();
            this.loadPermissions();
        }
    },
    watch: {
        viewMode() {
            // Reset to page 1 when switching views
            this.currentPage = 1;
            this.loadPermissions();
        }
    }
};
</script>
