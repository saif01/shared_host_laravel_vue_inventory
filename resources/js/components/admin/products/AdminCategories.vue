<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Category Management</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                Add New Category
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
                        <v-select v-model="activeFilter" :items="activeOptions" label="Filter by Status"
                            variant="outlined" density="compact" clearable
                            @update:model-value="loadCategories"></v-select>
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-text-field v-model="search" label="Search by name, slug, or description"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @input="loadCategories"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Categories Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Categories</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ pagination.total || 0 }}</strong>
                    <span v-if="categories.length > 0">
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
                            <th class="sortable" @click="onSort('slug')">
                                <div class="d-flex align-center">
                                    Slug
                                    <v-icon :icon="getSortIcon('slug')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th>Image</th>
                            <th>Description</th>
                            <th>Status</th>
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
                                <v-skeleton-loader type="text" width="150"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="120"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="image" width="50" height="50"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="200"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader>
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
                        <!-- Actual Category Data -->
                        <template v-else>
                            <tr v-for="category in categories" :key="category.id">
                                <td>{{ category.name }}</td>
                                <td>
                                    <v-chip size="small" variant="outlined">{{ category.slug }}</v-chip>
                                </td>
                                <td>
                                    <v-avatar size="50" v-if="category.image">
                                        <v-img :src="category.image" :alt="category.name"></v-img>
                                    </v-avatar>
                                    <span v-else class="text-caption text-grey">No image</span>
                                </td>
                                <td>
                                    <span v-if="category.description" class="text-body-2">
                                        {{ category.description.length > 50 ? category.description.substring(0, 50) +
                                            '...' : category.description }}
                                    </span>
                                    <span v-else class="text-caption text-grey">-</span>
                                </td>
                                <td>
                                    <v-chip :color="category.is_active ? 'success' : 'error'" size="small">
                                        {{ category.is_active ? 'Active' : 'Inactive' }}
                                    </v-chip>
                                </td>
                                <td>{{ formatDate(category.created_at) }}</td>
                                <td>
                                    <v-btn size="small" icon="mdi-pencil" @click="openDialog(category)" variant="text"
                                        :title="'Edit Category'"></v-btn>
                                    <v-btn size="small" icon="mdi-delete" @click="deleteCategory(category)"
                                        variant="text" color="error" :title="'Delete Category'"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="categories.length === 0">
                                <td colspan="7" class="text-center py-4">No categories found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination and Records Info -->
                <div
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <div class="text-caption text-grey">
                        <span v-if="categories.length > 0 && pagination.total > 0">
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
                            density="comfortable" @update:model-value="loadCategories">
                        </v-pagination>
                    </div>
                </div>
            </v-card-text>
        </v-card>

        <!-- Category Dialog -->
        <v-dialog v-model="dialog" max-width="700" scrollable persistent>
            <v-card>
                <v-card-title>
                    {{ editingCategory ? 'Edit Category' : 'Add New Category' }}
                </v-card-title>
                <v-card-text class="pa-0">
                    <v-form ref="form" @submit.prevent="saveCategory">
                        <div class="pa-6">
                            <v-text-field v-model="form.name" label="Category Name" :rules="[rules.required]" required
                                class="mb-4"></v-text-field>

                            <v-text-field v-model="form.slug" label="Slug" hint="Auto-generated from category name"
                                persistent-hint class="mb-4" readonly></v-text-field>

                            <v-textarea v-model="form.description" label="Description" variant="outlined" rows="3"
                                hint="Brief description about the category" persistent-hint class="mb-4"></v-textarea>

                            <!-- Image Upload Section -->
                            <div class="mb-4">
                                <div class="text-subtitle-2 font-weight-medium mb-2">Category Image</div>

                                <!-- Image Preview -->
                                <div v-if="form.image" class="mb-3 text-center">
                                    <v-avatar size="120" class="mb-2">
                                        <v-img :src="form.image ? resolveImageUrl(form.image) : ''"
                                            alt="Category Preview"></v-img>
                                    </v-avatar>
                                    <div>
                                        <v-btn size="small" variant="text" color="error" prepend-icon="mdi-delete"
                                            @click="clearImage">Remove Image</v-btn>
                                    </div>
                                </div>

                                <!-- File Upload -->
                                <v-file-input v-model="imageFile" label="Upload Image" variant="outlined"
                                    density="comfortable" color="primary" accept="image/*" prepend-icon="mdi-image"
                                    hint="Upload a category image (JPG, PNG, GIF, WebP - Max 5MB). Recommended size: 400x400px"
                                    persistent-hint show-size @update:model-value="handleImageUpload" class="mb-3">
                                    <template v-slot:append-inner v-if="uploadingImage">
                                        <v-progress-circular indeterminate size="20"
                                            color="primary"></v-progress-circular>
                                    </template>
                                </v-file-input>

                                <!-- Or Enter URL -->
                                <v-text-field v-model="form.image" label="Or Enter Image URL" variant="outlined"
                                    density="comfortable" color="primary" hint="Enter a direct URL to the image"
                                    persistent-hint prepend-inner-icon="mdi-link">
                                    <template v-slot:append-inner v-if="form.image && !imageFile">
                                        <v-btn icon="mdi-open-in-new" variant="text" size="small"
                                            @click="window.open(resolveImageUrl(form.image), '_blank')"></v-btn>
                                    </template>
                                </v-text-field>
                            </div>

                            <v-switch v-model="form.is_active" label="Active" color="success" class="mb-4"></v-switch>
                        </div>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="closeDialog" variant="text">Cancel</v-btn>
                    <v-btn @click="saveCategory" color="primary" :loading="saving">
                        {{ editingCategory ? 'Update' : 'Create' }}
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import adminPaginationMixin from '../../../mixins/adminPaginationMixin';
import { normalizeUploadPath, resolveUploadUrl } from '../../../utils/uploads';

export default {
    mixins: [adminPaginationMixin],
    data() {
        return {
            categories: [],
            activeFilter: null,
            activeOptions: [
                { title: 'Active', value: true },
                { title: 'Inactive', value: false }
            ],
            dialog: false,
            editingCategory: null,
            saving: false,
            form: {
                name: '',
                slug: '',
                description: '',
                image: '',
                is_active: true,
            },
            rules: {
                required: value => !!value || 'This field is required',
            },
            imageFile: null,
            uploadingImage: false,
        };
    },
    async mounted() {
        await this.loadCategories();
    },
    watch: {
        'form.name'(newName) {
            // Auto-generate slug from name in real-time
            if (newName) {
                this.form.slug = this.generateSlug(newName);
            } else {
                this.form.slug = '';
            }
        }
    },
    methods: {
        generateSlug(text) {
            return text
                .toString()
                .toLowerCase()
                .trim()
                .replace(/\s+/g, '-')           // Replace spaces with -
                .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
                .replace(/\-\-+/g, '-')         // Replace multiple - with single -
                .replace(/^-+/, '')             // Trim - from start of text
                .replace(/-+$/, '');            // Trim - from end of text
        },
        async loadCategories() {
            try {
                this.loading = true;
                const params = this.buildPaginationParams();

                if (this.search) {
                    params.search = this.search;
                }
                if (this.activeFilter !== null) {
                    params.is_active = this.activeFilter;
                }

                const response = await this.$axios.get('/api/v1/categories', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.categories = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load categories');
            } finally {
                this.loading = false;
            }
        },
        openDialog(category) {
            this.imageFile = null;
            if (category) {
                this.editingCategory = category;
                const imagePath = this.normalizeImageInput(category.image || '');
                this.form = {
                    name: category.name || '',
                    slug: category.slug || '',
                    description: category.description || '',
                    image: imagePath,
                    is_active: category.is_active !== undefined ? category.is_active : true,
                };
            } else {
                this.editingCategory = null;
                this.form = {
                    name: '',
                    slug: '',
                    description: '',
                    image: '',
                    is_active: true,
                };
            }
            this.dialog = true;
        },
        closeDialog() {
            this.dialog = false;
            this.editingCategory = null;
            this.form = {
                name: '',
                slug: '',
                description: '',
                image: '',
                is_active: true,
            };
            this.imageFile = null;
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
            }
        },
        async handleImageUpload() {
            if (!this.imageFile) {
                return;
            }

            const fileToUpload = Array.isArray(this.imageFile) ? this.imageFile[0] : this.imageFile;
            if (!fileToUpload) {
                return;
            }

            if (!fileToUpload.type.startsWith('image/')) {
                this.showError('Please select a valid image file');
                this.imageFile = null;
                return;
            }

            const maxSize = 5 * 1024 * 1024;
            if (fileToUpload.size > maxSize) {
                this.showError('File size must be less than 5MB');
                this.imageFile = null;
                return;
            }

            this.uploadingImage = true;
            try {
                const formData = new FormData();
                formData.append('image', fileToUpload);
                formData.append('folder', 'products/categories');
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
                    this.form.image = uploadedPath;
                    this.imageFile = null;
                    this.showSuccess('Image uploaded successfully');
                } else {
                    throw new Error(response.data.message || 'Failed to upload image');
                }
            } catch (error) {
                console.error('Error uploading image:', error);
                let errorMessage = 'Failed to upload image';
                if (error.response) {
                    errorMessage = error.response.data?.message || error.response.statusText || errorMessage;
                } else if (error.message) {
                    errorMessage = error.message;
                }
                this.showError(errorMessage);
                this.imageFile = null;
            } finally {
                this.uploadingImage = false;
            }
        },
        clearImage() {
            this.form.image = '';
            this.imageFile = null;
        },
        async saveCategory() {
            if (!this.$refs.form.validate()) {
                return;
            }

            this.saving = true;
            try {
                const token = localStorage.getItem('admin_token');
                const url = this.editingCategory
                    ? `/api/v1/categories/${this.editingCategory.id}`
                    : '/api/v1/categories';

                const data = { ...this.form };
                data.image = this.normalizeImageInput(data.image);

                const method = this.editingCategory ? 'put' : 'post';

                await axios[method](url, data, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess(
                    this.editingCategory ? 'Category updated successfully' : 'Category created successfully'
                );
                this.closeDialog();
                await this.loadCategories();
            } catch (error) {
                console.error('Error saving category:', error);
                let message = 'Error saving category';

                if (error.response?.data?.errors) {
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
        async deleteCategory(category) {
            if (!confirm(`Are you sure you want to delete ${category.name}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.delete(`/api/v1/categories/${category.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Category deleted successfully');
                await this.loadCategories();
            } catch (error) {
                this.handleApiError(error, 'Error deleting category');
            }
        },
        onPerPageChange() {
            this.resetPagination();
            this.loadCategories();
        },
        onSort(field) {
            this.handleSort(field);
            this.loadCategories();
        },
        normalizeImageInput(imageValue) {
            return normalizeUploadPath(imageValue);
        },
        resolveImageUrl(value) {
            return resolveUploadUrl(value);
        },
    }
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>
