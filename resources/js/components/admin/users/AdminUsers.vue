<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">User Management</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                Add New User
            </v-btn>
        </div>

        <!-- Search and Filter -->
        <v-card class="mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="4">
                        <v-select v-model="perPage" :items="perPageOptions" label="Items per page"
                            prepend-inner-icon="mdi-format-list-numbered" variant="outlined" density="compact"
                            @update:model-value="onPerPageChange"></v-select>
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-select v-model="roleFilter" :items="roleOptions" label="Filter by Role" variant="outlined"
                            density="compact" clearable @update:model-value="loadUsers"></v-select>
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-text-field v-model="search" label="Search by name, email, phone, city, or country"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @input="loadUsers"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Users Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Users</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ pagination.total || 0 }}</strong>
                    <span v-if="users.length > 0">
                        | Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage,
                            pagination.total) }} of {{ pagination.total }}
                    </span>
                </span>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th class="sortable" @click="onSort('name')">
                                <div class="d-flex align-center">
                                    Name
                                    <v-icon :icon="getSortIcon('name')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th class="sortable" @click="onSort('email')">
                                <div class="d-flex align-center">
                                    Email
                                    <v-icon :icon="getSortIcon('email')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th>Phone</th>
                            <th>Location</th>
                            <th>Role</th>
                            <th class="sortable" @click="onSort('created_at')">
                                <div class="d-flex align-center">
                                    Created
                                    <v-icon :icon="getSortIcon('created_at')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Skeleton Loaders -->
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td>
                                <div class="d-flex align-center gap-2">
                                    <v-skeleton-loader type="avatar" width="32" height="32"></v-skeleton-loader>
                                    <v-skeleton-loader type="text" width="150"></v-skeleton-loader>
                                </div>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="200"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="120"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="150"></v-skeleton-loader>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <v-skeleton-loader type="chip" width="80" height="24"
                                        class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="chip" width="70" height="24"></v-skeleton-loader>
                                </div>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="120"></v-skeleton-loader>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <v-skeleton-loader type="button" width="32" height="32"
                                        class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader>
                                </div>
                            </td>
                        </tr>
                        <!-- Actual User Data -->
                        <template v-else>
                            <tr v-for="user in users" :key="user.id">
                                <td>
                                    <div class="d-flex align-center gap-2">
                                        <v-avatar size="32" color="primary">
                                            <v-img v-if="user.avatar" :src="user.avatar" :alt="user.name"></v-img>
                                            <span v-else class="text-white">{{ user.name.charAt(0).toUpperCase()
                                                }}</span>
                                        </v-avatar>
                                        {{ user.name }}
                                    </div>
                                </td>
                                <td>{{ user.email }}</td>
                                <td>
                                    <span v-if="user.phone" class="d-flex align-center">
                                        <v-icon size="small" class="mr-1">mdi-phone</v-icon>
                                        {{ user.phone }}
                                    </span>
                                    <span v-else class="text-caption text-grey">-</span>
                                </td>
                                <td>
                                    <div v-if="user.city || user.country" class="d-flex flex-column">
                                        <span v-if="user.city" class="text-body-2">{{ user.city }}</span>
                                        <span v-if="user.country" class="text-caption text-grey">{{ user.country
                                        }}</span>
                                        <span v-if="!user.city && !user.country" class="text-caption text-grey">-</span>
                                    </div>
                                    <span v-else class="text-caption text-grey">-</span>
                                </td>
                                <td>
                                    <div v-if="user.roles && user.roles.length > 0" class="d-flex flex-wrap gap-1">
                                        <v-chip v-for="role in user.roles" :key="role.id"
                                            :color="getRoleColor(role.slug)" size="small">
                                            {{ role.name }}
                                        </v-chip>
                                    </div>
                                    <span v-else class="text-caption text-grey">No roles</span>
                                </td>
                                <td>{{ formatDate(user.created_at) }}</td>
                                <td>
                                    <v-btn size="small" icon="mdi-eye" @click="viewUserProfile(user)" variant="text"
                                        color="info" :title="'View Profile'"></v-btn>
                                    <v-btn size="small" icon="mdi-pencil" @click="openDialog(user)" variant="text"
                                        :title="'Edit User'"></v-btn>
                                    <v-btn size="small" icon="mdi-delete" @click="deleteUser(user)" variant="text"
                                        color="error" :disabled="user.id === currentUserId"
                                        :title="'Delete User'"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="users.length === 0">
                                <td colspan="7" class="text-center py-4">No users found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination and Records Info -->
                <div
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <div class="text-caption text-grey">
                        <span v-if="users.length > 0 && pagination.total > 0">
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
                        <v-pagination v-model="currentPage" :length="pagination.last_page" :total-visible="7"
                            density="comfortable" @update:model-value="loadUsers">
                        </v-pagination>
                    </div>
                </div>
            </v-card-text>
        </v-card>

        <!-- User Dialog -->
        <v-dialog v-model="dialog" max-width="900" scrollable persistent>
            <v-card>
                <v-card-title>
                    {{ editingUser ? 'Edit User' : 'Add New User' }}
                </v-card-title>
                <v-card-text class="pa-0">
                    <v-form ref="form" @submit.prevent="saveUser">
                        <v-tabs v-model="activeTab" bg-color="grey-lighten-4">
                            <v-tab value="basic">Basic Information</v-tab>
                            <v-tab value="profile">Profile Information</v-tab>
                            <v-tab value="security">Security</v-tab>
                        </v-tabs>

                        <v-window v-model="activeTab">
                            <!-- Basic Information Tab -->
                            <v-window-item value="basic">
                                <div class="pa-6">
                                    <v-text-field v-model="form.name" label="Full Name" :rules="[rules.required]"
                                        required class="mb-4"></v-text-field>

                                    <v-text-field v-model="form.email" label="Email" type="email"
                                        :rules="[rules.required, rules.email]" required class="mb-4"></v-text-field>

                                    <v-select v-model="form.role_ids" :items="roles" item-title="label"
                                        item-value="value" label="Roles" :rules="[rules.required]" required multiple
                                        chips class="mb-4">
                                        <template v-slot:item="{ props, item }">
                                            <v-list-item v-bind="props">
                                                <template v-slot:title>
                                                    {{ item.raw.label }}
                                                    <v-chip v-if="item.raw.is_system" size="x-small" color="warning"
                                                        class="ml-2">
                                                        System
                                                    </v-chip>
                                                </template>
                                                <template v-slot:subtitle>
                                                    {{ item.raw.description }}
                                                </template>
                                            </v-list-item>
                                        </template>
                                        <template v-slot:selection="{ item, index }">
                                            <v-chip v-if="index < 2" size="small" class="mr-1">
                                                {{ item.raw.label }}
                                            </v-chip>
                                            <span v-if="index === 2" class="text-grey text-caption align-self-center">
                                                (+{{ form.role_ids.length - 2 }} others)
                                            </span>
                                        </template>
                                    </v-select>

                                    <!-- Avatar Upload Section -->
                                    <div class="mb-4">
                                        <div class="text-subtitle-2 font-weight-medium mb-2">Avatar</div>

                                        <!-- Avatar Preview -->
                                        <div v-if="form.avatar" class="mb-3 text-center">
                                            <v-avatar size="80" class="mb-2">
                                                <v-img :src="form.avatar ? resolveImageUrl(form.avatar) : ''"
                                                    alt="Avatar Preview"></v-img>
                                            </v-avatar>
                                            <div>
                                                <v-btn size="small" variant="text" color="error"
                                                    prepend-icon="mdi-delete" @click="clearAvatar">Remove Avatar</v-btn>
                                            </div>
                                        </div>

                                        <!-- File Upload -->
                                        <v-file-input v-model="avatarFile" label="Upload Avatar" variant="outlined"
                                            density="comfortable" color="primary" accept="image/*"
                                            prepend-icon="mdi-image"
                                            hint="Upload an avatar image (JPG, PNG, GIF, WebP - Max 5MB). Recommended size: 200x200px"
                                            persistent-hint show-size @update:model-value="handleAvatarUpload"
                                            class="mb-3">
                                            <template v-slot:append-inner v-if="uploadingAvatar">
                                                <v-progress-circular indeterminate size="20"
                                                    color="primary"></v-progress-circular>
                                            </template>
                                        </v-file-input>

                                        <!-- Or Enter URL -->
                                        <v-text-field v-model="form.avatar" label="Or Enter Avatar URL"
                                            variant="outlined" density="comfortable" color="primary"
                                            hint="Enter a direct URL to the avatar image" persistent-hint
                                            prepend-inner-icon="mdi-link">
                                            <template v-slot:append-inner v-if="form.avatar && !avatarFile">
                                                <v-btn icon="mdi-open-in-new" variant="text" size="small"
                                                    @click="window.open(resolveImageUrl(form.avatar), '_blank')"></v-btn>
                                            </template>
                                        </v-text-field>
                                    </div>
                                </div>
                            </v-window-item>

                            <!-- Profile Information Tab -->
                            <v-window-item value="profile">
                                <div class="pa-6">
                                    <v-row>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model="form.phone" label="Phone Number" variant="outlined"
                                                prepend-inner-icon="mdi-phone" hint="e.g., +8801707080401"
                                                persistent-hint></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model="form.date_of_birth" label="Date of Birth" type="date"
                                                variant="outlined" prepend-inner-icon="mdi-calendar"></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-select v-model="form.gender" :items="genderOptions" label="Gender"
                                                variant="outlined" prepend-inner-icon="mdi-gender-male-female"
                                                clearable></v-select>
                                        </v-col>
                                        <v-col cols="12">
                                            <v-textarea v-model="form.address" label="Address" variant="outlined"
                                                rows="2" prepend-inner-icon="mdi-map-marker" hint="Street address"
                                                persistent-hint></v-textarea>
                                        </v-col>
                                        <v-col cols="12" md="4">
                                            <v-text-field v-model="form.city" label="City" variant="outlined"
                                                prepend-inner-icon="mdi-city"></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="4">
                                            <v-text-field v-model="form.state" label="State/Province" variant="outlined"
                                                prepend-inner-icon="mdi-map"></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="4">
                                            <v-text-field v-model="form.country" label="Country" variant="outlined"
                                                prepend-inner-icon="mdi-earth"></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model="form.postal_code" label="Postal Code"
                                                variant="outlined" prepend-inner-icon="mdi-mailbox"></v-text-field>
                                        </v-col>
                                        <v-col cols="12">
                                            <v-textarea v-model="form.bio" label="Bio/Description" variant="outlined"
                                                rows="4" prepend-inner-icon="mdi-text"
                                                hint="Brief description about the user" persistent-hint
                                                :counter="1000"></v-textarea>
                                        </v-col>
                                    </v-row>
                                </div>
                            </v-window-item>

                            <!-- Security Tab -->
                            <v-window-item value="security">
                                <div class="pa-6">
                                    <v-text-field v-model="form.password" label="Password"
                                        :type="showPassword ? 'text' : 'password'"
                                        :rules="editingUser ? [] : [rules.required, rules.minLength]"
                                        :required="!editingUser" hint="Leave blank to keep current password"
                                        persistent-hint class="mb-4" prepend-inner-icon="mdi-lock">
                                        <template v-slot:append-inner>
                                            <v-btn icon variant="text" size="small"
                                                @click="showPassword = !showPassword">
                                                <v-icon>{{ showPassword ? 'mdi-eye-off' : 'mdi-eye' }}</v-icon>
                                            </v-btn>
                                        </template>
                                    </v-text-field>

                                    <v-text-field v-if="!editingUser || form.password"
                                        v-model="form.password_confirmation" label="Confirm Password"
                                        :type="showPasswordConfirmation ? 'text' : 'password'" :rules="form.password ? [
                                            () => !!form.password_confirmation || 'Please confirm your password',
                                            () => form.password_confirmation === form.password || 'Passwords do not match'
                                        ] : []" :required="!!form.password"
                                        prepend-inner-icon="mdi-lock-check"></v-text-field>
                                </div>
                            </v-window-item>
                        </v-window>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="closeDialog" variant="text">Cancel</v-btn>
                    <v-btn @click="saveUser" color="primary" :loading="saving">
                        {{ editingUser ? 'Update' : 'Create' }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <!-- User Profile View Dialog -->
        <UserProfileDialog v-model="profileDialogVisible" :user="selectedUser" />
    </div>
</template>

<script>
import adminPaginationMixin from '../../../mixins/adminPaginationMixin';
import { normalizeUploadPath, resolveUploadUrl } from '../../../utils/uploads';
import UserProfileDialog from './UserProfileDialog.vue';

export default {
    components: {
        UserProfileDialog
    },
    mixins: [adminPaginationMixin],
    data() {
        return {
            users: [],
            roles: [],
            roleFilter: null,
            roleOptions: [],
            dialog: false,
            editingUser: null,
            saving: false,
            form: {
                name: '',
                email: '',
                role_ids: [],
                password: '',
                password_confirmation: '',
                avatar: '',
                phone: '',
                date_of_birth: '',
                gender: null,
                address: '',
                city: '',
                state: '',
                country: '',
                postal_code: '',
                bio: ''
            },
            activeTab: 'basic',
            genderOptions: [
                { title: 'Male', value: 'male' },
                { title: 'Female', value: 'female' },
                { title: 'Other', value: 'other' }
            ],
            rules: {
                required: value => {
                    if (Array.isArray(value)) {
                        return value.length > 0 || 'At least one role is required';
                    }
                    return !!value || 'This field is required';
                },
                email: value => {
                    const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
                    return pattern.test(value) || 'Invalid email'
                },
                minLength: value => {
                    if (!value) return true; // Skip if empty (handled by required rule)
                    return value.length >= 8 || 'Password must be at least 8 characters'
                },
            },
            currentUserId: null,
            showPassword: false,
            showPasswordConfirmation: false,
            avatarFile: null,
            uploadingAvatar: false,
            profileDialogVisible: false, // User profile dialog visibility
            selectedUser: null // User selected for profile view
        };
    },
    async mounted() {
        await this.loadRoles();
        await this.loadUsers();
        this.loadCurrentUser();
    },
    methods: {
        async loadUsers() {
            try {
                this.loading = true;
                const params = this.buildPaginationParams();

                if (this.search) {
                    params.search = this.search;
                }
                if (this.roleFilter) {
                    params.role = this.roleFilter;
                }

                const response = await this.$axios.get('/api/v1/users', {
                    params,
                    headers: this.getAuthHeaders()
                });

                const users = response.data.data || [];
                // Backend already returns full URLs via transformUserAvatar, so we keep them as-is
                this.users = users;
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load users');
            } finally {
                this.loading = false;
            }
        },
        async loadRoles() {
            try {
                const response = await this.$axios.get('/api/v1/users/roles', {
                    headers: this.getAuthHeaders()
                });
                this.roles = response.data.roles;

                // Populate roleOptions for filter
                this.roleOptions = this.roles.map(role => ({
                    title: role.label,
                    value: role.value // This is the role ID
                }));
            } catch (error) {
                console.error('Error loading roles:', error);
            }
        },
        loadCurrentUser() {
            // Get current user ID from token or API
            // For now, we'll get it from the auth user endpoint
            const token = localStorage.getItem('admin_token');
            this.$axios.get('/api/v1/auth/user', {
                headers: { Authorization: `Bearer ${token}` }
            }).then(response => {
                this.currentUserId = response.data.id;
            }).catch(() => {
                // Ignore if can't load current user
            });
        },
        openDialog(user) {
            this.avatarFile = null; // Reset avatar file when opening dialog
            this.activeTab = 'basic'; // Reset to first tab
            if (user) {
                this.editingUser = user;
                // Extract role IDs from user.roles array
                const roleIds = user.roles ? user.roles.map(role => role.id) : [];
                // Normalize the avatar URL back to a path for editing
                // Backend returns full URLs, but we need normalized paths for storage
                const avatarPath = this.normalizeImageInput(user.avatar || '');
                this.form = {
                    name: user.name || '',
                    email: user.email || '',
                    role_ids: roleIds,
                    password: '',
                    password_confirmation: '',
                    avatar: avatarPath,
                    phone: user.phone || '',
                    date_of_birth: user.date_of_birth || '',
                    gender: user.gender || null,
                    address: user.address || '',
                    city: user.city || '',
                    state: user.state || '',
                    country: user.country || '',
                    postal_code: user.postal_code || '',
                    bio: user.bio || ''
                };
            } else {
                this.editingUser = null;
                // Set default role (Administrator) if available
                const defaultRoleId = this.roles.length > 0 ? this.roles[0].value : null;
                this.form = {
                    name: '',
                    email: '',
                    role_ids: defaultRoleId ? [defaultRoleId] : [],
                    password: '',
                    password_confirmation: '',
                    avatar: '',
                    phone: '',
                    date_of_birth: '',
                    gender: null,
                    address: '',
                    city: '',
                    state: '',
                    country: '',
                    postal_code: '',
                    bio: ''
                };
            }
            this.dialog = true;
        },
        closeDialog() {
            this.dialog = false;
            this.editingUser = null;
            this.activeTab = 'basic';
            const defaultRoleId = this.roles.length > 0 ? this.roles[0].value : null;
            this.form = {
                name: '',
                email: '',
                role_ids: defaultRoleId ? [defaultRoleId] : [],
                password: '',
                password_confirmation: '',
                avatar: '',
                phone: '',
                date_of_birth: '',
                gender: null,
                address: '',
                city: '',
                state: '',
                country: '',
                postal_code: '',
                bio: ''
            };
            this.showPassword = false;
            this.showPasswordConfirmation = false;
            this.avatarFile = null;
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
            }
        },
        async handleAvatarUpload() {
            if (!this.avatarFile) {
                return;
            }

            const fileToUpload = Array.isArray(this.avatarFile) ? this.avatarFile[0] : this.avatarFile;
            if (!fileToUpload) {
                return;
            }

            // Validate file type
            if (!fileToUpload.type.startsWith('image/')) {
                this.showError('Please select a valid image file');
                this.avatarFile = null;
                return;
            }

            // Validate file size (5MB max)
            const maxSize = 5 * 1024 * 1024; // 5MB
            if (fileToUpload.size > maxSize) {
                this.showError('File size must be less than 5MB');
                this.avatarFile = null;
                return;
            }

            this.uploadingAvatar = true;
            try {
                const formData = new FormData();
                formData.append('image', fileToUpload);
                formData.append('folder', 'users');
                // Add user name as prefix if available
                if (this.form.name) {
                    formData.append('prefix', this.form.name);
                }

                const token = localStorage.getItem('admin_token');
                const response = await this.$axios.post('/api/v1/upload/image', formData, {
                    headers: {
                        Authorization: `Bearer ${token}`,
                        'Content-Type': 'multipart/form-data'
                    }
                });

                if (response.data.success) {
                    const uploadedPath = this.normalizeImageInput(response.data.path || response.data.url);
                    this.form.avatar = uploadedPath;
                    this.avatarFile = null;
                    this.showSuccess('Avatar uploaded successfully');
                } else {
                    throw new Error(response.data.message || 'Failed to upload avatar');
                }
            } catch (error) {
                console.error('Error uploading avatar:', error);
                let errorMessage = 'Failed to upload avatar';
                if (error.response) {
                    errorMessage = error.response.data?.message || error.response.statusText || errorMessage;
                } else if (error.message) {
                    errorMessage = error.message;
                }
                this.showError(errorMessage);
                this.avatarFile = null;
            } finally {
                this.uploadingAvatar = false;
            }
        },
        clearAvatar() {
            this.form.avatar = '';
            this.avatarFile = null;
        },
        async saveUser() {
            // Manual validation for password confirmation
            if (this.form.password && this.form.password !== this.form.password_confirmation) {
                this.showError('Passwords do not match');
                return;
            }

            // If creating new user, password is required
            if (!this.editingUser && !this.form.password) {
                this.showError('Password is required for new users');
                return;
            }

            // If editing and password is provided, confirmation is required
            if (this.editingUser && this.form.password && !this.form.password_confirmation) {
                this.showError('Please confirm the password');
                return;
            }

            if (!this.$refs.form.validate()) {
                return;
            }

            this.saving = true;
            try {
                const token = localStorage.getItem('admin_token');
                const url = this.editingUser
                    ? `/api/v1/users/${this.editingUser.id}`
                    : '/api/v1/users';

                const data = { ...this.form };

                // Convert role_ids to ensure it's an array
                if (this.form.role_ids) {
                    data.role_ids = Array.isArray(this.form.role_ids)
                        ? this.form.role_ids
                        : [this.form.role_ids];
                }

                // Include password_confirmation for backend validation
                // Backend uses Laravel's 'confirmed' rule
                if (this.form.password) {
                    data.password_confirmation = this.form.password_confirmation;
                } else {
                    // Remove password fields if password is not being changed
                    delete data.password;
                    delete data.password_confirmation;
                }

                data.avatar = this.normalizeImageInput(data.avatar);

                // Remove legacy role field if it exists
                delete data.role;

                const method = this.editingUser ? 'put' : 'post';

                await axios[method](url, data, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess(
                    this.editingUser ? 'User updated successfully' : 'User created successfully'
                );
                this.closeDialog();
                await this.loadUsers();
            } catch (error) {
                console.error('Error saving user:', error);
                let message = 'Error saving user';

                if (error.response?.data?.errors) {
                    // Handle validation errors
                    const errors = error.response.data.errors;
                    const errorMessages = [];
                    Object.keys(errors).forEach(key => {
                        errorMessages.push(errors[key][0]);
                    });
                    message = errorMessages.join(', ');
                } else if (error.response?.data?.message) {
                    message = error.response.data.message;
                }

                this.showError(message);
            } finally {
                this.saving = false;
            }
        },
        async deleteUser(user) {
            if (user.id === this.currentUserId) {
                this.showError('You cannot delete your own account');
                return;
            }

            if (!confirm(`Are you sure you want to delete ${user.name}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.delete(`/api/v1/users/${user.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('User deleted successfully');
                await this.loadUsers();
            } catch (error) {
                this.handleApiError(error, 'Error deleting user');
            }
        },
        getRoleLabel(roleSlug) {
            const roleObj = this.roles.find(r => r.slug === roleSlug);
            return roleObj ? roleObj.label : roleSlug;
        },
        getRoleColor(roleSlug) {
            // Map role slugs to colors
            const colors = {
                administrator: 'error',
                editor: 'primary',
                'hr-user': 'success',
                staff: 'info'
            };
            return colors[roleSlug] || 'primary';
        },
        onPerPageChange() {
            this.resetPagination();
            this.loadUsers();
        },
        onSort(field) {
            this.handleSort(field);
            this.loadUsers();
        },
        normalizeImageInput(imageValue) {
            return normalizeUploadPath(imageValue);
        },
        resolveImageUrl(value) {
            return resolveUploadUrl(value);
        },
        /**
         * View user profile
         */
        async viewUserProfile(user) {
            try {
                // Fetch full user details with roles and permissions
                const token = localStorage.getItem('admin_token');
                const response = await this.$axios.get(`/api/v1/users/${user.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                // Use the full user data with permissions loaded
                this.selectedUser = response.data;
                this.profileDialogVisible = true;
            } catch (error) {
                console.error('Error loading user profile:', error);
                // Fallback to the user data from the list if API call fails
                this.selectedUser = user;
                this.profileDialogVisible = true;
                this.handleApiError(error, 'Failed to load user profile details');
            }
        }
    }
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>
