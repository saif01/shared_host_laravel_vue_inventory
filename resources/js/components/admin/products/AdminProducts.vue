<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Product Management</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                Add New Product
            </v-btn>
        </div>

        <!-- Search and Filter -->
        <v-card class="mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12" md="3">
                        <v-select v-model="perPage" :items="perPageOptions" label="Items per page"
                            prepend-inner-icon="mdi-format-list-numbered" variant="outlined" density="compact"
                            @update:model-value="onPerPageChange"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="categoryFilter" :items="categoryOptions" label="Filter by Category"
                            variant="outlined" density="compact" clearable @update:model-value="loadProducts"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="activeFilter" :items="activeOptions" label="Filter by Status"
                            variant="outlined" density="compact" clearable @update:model-value="loadProducts"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="search" label="Search by name, SKU, barcode"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @input="loadProducts"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Products Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Products</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ pagination.total || 0 }}</strong>
                    <span v-if="products.length > 0">
                        | Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage,
                            pagination.total) }} of {{ pagination.total }}
                    </span>
                </span>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th class="sortable" @click="onSort('name')">
                                <div class="d-flex align-center">
                                    Name
                                    <v-icon :icon="getSortIcon('name')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th class="sortable" @click="onSort('sku')">
                                <div class="d-flex align-center">
                                    SKU
                                    <v-icon :icon="getSortIcon('sku')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th>Barcode</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th class="sortable" @click="onSort('cost_price')">
                                <div class="d-flex align-center">
                                    Cost Price
                                    <v-icon :icon="getSortIcon('cost_price')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th class="sortable" @click="onSort('selling_price')">
                                <div class="d-flex align-center">
                                    Selling Price
                                    <v-icon :icon="getSortIcon('selling_price')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th>Min Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Skeleton Loaders -->
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td>
                                <v-skeleton-loader type="image" width="50" height="50"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="150"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="100"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="120"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="100"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="80"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="80"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="80"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="60"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <v-skeleton-loader type="button" width="32" height="32"
                                        class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader>
                                </div>
                            </td>
                        </tr>
                        <!-- Actual Product Data -->
                        <template v-else>
                            <tr v-for="product in products" :key="product.id">
                                <td>
                                    <v-avatar size="50" v-if="product.image">
                                        <v-img :src="product.image" :alt="product.name"></v-img>
                                    </v-avatar>
                                    <v-avatar size="50" color="grey-lighten-2" v-else>
                                        <v-icon>mdi-image-off</v-icon>
                                    </v-avatar>
                                </td>
                                <td>{{ product.name }}</td>
                                <td>
                                    <v-chip size="small" variant="outlined">{{ product.sku }}</v-chip>
                                </td>
                                <td>
                                    <span v-if="product.barcode" class="text-body-2">{{ product.barcode }}</span>
                                    <span v-else class="text-caption text-grey">-</span>
                                </td>
                                <td>
                                    <v-chip size="small" v-if="product.category">
                                        {{ product.category.name }}
                                    </v-chip>
                                    <span v-else class="text-caption text-grey">-</span>
                                </td>
                                <td>
                                    <span v-if="product.unit" class="text-body-2">
                                        {{ product.unit.name }} ({{ product.unit.code }})
                                    </span>
                                    <span v-else class="text-caption text-grey">-</span>
                                </td>
                                <td>{{ formatCurrency(product.cost_price) }}</td>
                                <td>{{ formatCurrency(product.selling_price) }}</td>
                                <td>
                                    <v-chip size="small" :color="product.minimum_stock_level > 0 ? 'info' : 'default'">
                                        {{ product.minimum_stock_level }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-chip :color="product.is_active ? 'success' : 'error'" size="small">
                                        {{ product.is_active ? 'Active' : 'Inactive' }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-btn size="small" icon="mdi-pencil" @click="openDialog(product)" variant="text"
                                        :title="'Edit Product'"></v-btn>
                                    <v-btn size="small" icon="mdi-delete" @click="deleteProduct(product)" variant="text"
                                        color="error" :title="'Delete Product'"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="products.length === 0">
                                <td colspan="11" class="text-center py-4">No products found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination and Records Info -->
                <div
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <div class="text-caption text-grey">
                        <span v-if="products.length > 0 && pagination.total > 0">
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
                            density="comfortable" @update:model-value="loadProducts">
                        </v-pagination>
                    </div>
                </div>
            </v-card-text>
        </v-card>

        <!-- Product Dialog -->
        <v-dialog v-model="dialog" max-width="900" scrollable persistent>
            <v-card>
                <v-card-title>
                    {{ editingProduct ? 'Edit Product' : 'Add New Product' }}
                </v-card-title>
                <v-card-text class="pa-0">
                    <v-form ref="form" @submit.prevent="saveProduct">
                        <v-tabs v-model="activeTab" bg-color="grey-lighten-4">
                            <v-tab value="basic">Basic Information</v-tab>
                            <v-tab value="pricing">Pricing & Stock</v-tab>
                        </v-tabs>

                        <v-window v-model="activeTab">
                            <!-- Basic Information Tab -->
                            <v-window-item value="basic">
                                <div class="pa-6">
                                    <v-text-field v-model="form.name" label="Product Name" :rules="[rules.required]"
                                        required class="mb-4"></v-text-field>

                                    <v-row>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model="form.sku" label="SKU" :rules="[rules.required]"
                                                required hint="Stock Keeping Unit" persistent-hint></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model="form.barcode" label="Barcode"
                                                hint="Optional barcode/QR code" persistent-hint></v-text-field>
                                        </v-col>
                                    </v-row>

                                    <v-row>
                                        <v-col cols="12" md="6">
                                            <v-select v-model="form.category_id" :items="categories" item-title="label"
                                                item-value="value" label="Category" :rules="[rules.required]" required
                                                class="mb-4"></v-select>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-select v-model="form.unit_id" :items="units" item-title="label"
                                                item-value="value" label="Unit of Measure" :rules="[rules.required]"
                                                required class="mb-4"></v-select>
                                        </v-col>
                                    </v-row>

                                    <v-textarea v-model="form.description" label="Description" variant="outlined" rows="3"
                                        hint="Product description" persistent-hint class="mb-4"></v-textarea>

                                    <!-- Image Upload Section -->
                                    <div class="mb-4">
                                        <div class="text-subtitle-2 font-weight-medium mb-2">Product Image</div>

                                        <!-- Image Preview -->
                                        <div v-if="form.image" class="mb-3 text-center">
                                            <v-avatar size="120" class="mb-2">
                                                <v-img :src="form.image ? resolveImageUrl(form.image) : ''"
                                                    alt="Product Preview"></v-img>
                                            </v-avatar>
                                            <div>
                                                <v-btn size="small" variant="text" color="error"
                                                    prepend-icon="mdi-delete" @click="clearImage">Remove Image</v-btn>
                                            </div>
                                        </div>

                                        <!-- File Upload -->
                                        <v-file-input v-model="imageFile" label="Upload Image" variant="outlined"
                                            density="comfortable" color="primary" accept="image/*"
                                            prepend-icon="mdi-image"
                                            hint="Upload a product image (JPG, PNG, GIF, WebP - Max 5MB). Recommended size: 400x400px"
                                            persistent-hint show-size @update:model-value="handleImageUpload"
                                            class="mb-3">
                                            <template v-slot:append-inner v-if="uploadingImage">
                                                <v-progress-circular indeterminate size="20"
                                                    color="primary"></v-progress-circular>
                                            </template>
                                        </v-file-input>

                                        <!-- Or Enter URL -->
                                        <v-text-field v-model="form.image" label="Or Enter Image URL"
                                            variant="outlined" density="comfortable" color="primary"
                                            hint="Enter a direct URL to the image" persistent-hint
                                            prepend-inner-icon="mdi-link">
                                            <template v-slot:append-inner v-if="form.image && !imageFile">
                                                <v-btn icon="mdi-open-in-new" variant="text" size="small"
                                                    @click="window.open(resolveImageUrl(form.image), '_blank')"></v-btn>
                                            </template>
                                        </v-text-field>
                                    </div>

                                    <v-switch v-model="form.track_serial" label="Track Serial Numbers"
                                        color="primary" class="mb-4"></v-switch>

                                    <v-switch v-model="form.is_active" label="Active" color="success"></v-switch>
                                </div>
                            </v-window-item>

                            <!-- Pricing & Stock Tab -->
                            <v-window-item value="pricing">
                                <div class="pa-6">
                                    <v-row>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model.number="form.cost_price" label="Cost Price"
                                                type="number" step="0.01" min="0" :rules="[rules.required, rules.minValue]"
                                                required prefix="$" class="mb-4"></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model.number="form.selling_price" label="Selling Price"
                                                type="number" step="0.01" min="0"
                                                :rules="[rules.required, rules.minValue]" required prefix="$"
                                                class="mb-4"></v-text-field>
                                        </v-col>
                                    </v-row>

                                    <v-row>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model.number="form.minimum_stock_level"
                                                label="Minimum Stock Level" type="number" min="0"
                                                hint="Alert when stock falls below this level" persistent-hint
                                                class="mb-4"></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model.number="form.opening_stock" label="Opening Stock"
                                                type="number" min="0"
                                                hint="Initial stock quantity (for new products)" persistent-hint
                                                class="mb-4"></v-text-field>
                                        </v-col>
                                    </v-row>
                                </div>
                            </v-window-item>
                        </v-window>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="closeDialog" variant="text">Cancel</v-btn>
                    <v-btn @click="saveProduct" color="primary" :loading="saving">
                        {{ editingProduct ? 'Update' : 'Create' }}
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
            products: [],
            categories: [],
            units: [],
            categoryFilter: null,
            categoryOptions: [],
            activeFilter: null,
            activeOptions: [
                { title: 'Active', value: true },
                { title: 'Inactive', value: false }
            ],
            dialog: false,
            editingProduct: null,
            saving: false,
            activeTab: 'basic',
            form: {
                name: '',
                sku: '',
                barcode: '',
                category_id: null,
                unit_id: null,
                description: '',
                image: '',
                cost_price: 0,
                selling_price: 0,
                minimum_stock_level: 0,
                opening_stock: 0,
                track_serial: false,
                is_active: true,
            },
            rules: {
                required: value => {
                    if (typeof value === 'number') {
                        return value >= 0 || 'This field is required';
                    }
                    return !!value || 'This field is required';
                },
                minValue: value => {
                    if (value === null || value === undefined || value === '') {
                        return true;
                    }
                    return value >= 0 || 'Value must be greater than or equal to 0';
                },
            },
            imageFile: null,
            uploadingImage: false,
        };
    },
    async mounted() {
        await this.loadCategories();
        await this.loadUnits();
        await this.loadProducts();
    },
    methods: {
        async loadProducts() {
            try {
                this.loading = true;
                const params = this.buildPaginationParams();

                if (this.search) {
                    params.search = this.search;
                }
                if (this.categoryFilter) {
                    params.category_id = this.categoryFilter;
                }
                if (this.activeFilter !== null) {
                    params.is_active = this.activeFilter;
                }

                const response = await this.$axios.get('/api/v1/products', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.products = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load products');
            } finally {
                this.loading = false;
            }
        },
        async loadCategories() {
            try {
                const response = await this.$axios.get('/api/v1/products/categories', {
                    headers: this.getAuthHeaders()
                });
                this.categories = response.data.categories || [];

                // Populate categoryOptions for filter
                this.categoryOptions = this.categories.map(cat => ({
                    title: cat.label,
                    value: cat.value
                }));
            } catch (error) {
                console.error('Error loading categories:', error);
            }
        },
        async loadUnits() {
            try {
                const response = await this.$axios.get('/api/v1/products/units', {
                    headers: this.getAuthHeaders()
                });
                this.units = response.data.units || [];
            } catch (error) {
                console.error('Error loading units:', error);
            }
        },
        openDialog(product) {
            this.imageFile = null;
            this.activeTab = 'basic';
            if (product) {
                this.editingProduct = product;
                const imagePath = this.normalizeImageInput(product.image || '');
                this.form = {
                    name: product.name || '',
                    sku: product.sku || '',
                    barcode: product.barcode || '',
                    category_id: product.category_id || null,
                    unit_id: product.unit_id || null,
                    description: product.description || '',
                    image: imagePath,
                    cost_price: product.cost_price || 0,
                    selling_price: product.selling_price || 0,
                    minimum_stock_level: product.minimum_stock_level || 0,
                    opening_stock: product.opening_stock || 0,
                    track_serial: product.track_serial || false,
                    is_active: product.is_active !== undefined ? product.is_active : true,
                };
            } else {
                this.editingProduct = null;
                this.form = {
                    name: '',
                    sku: '',
                    barcode: '',
                    category_id: this.categories.length > 0 ? this.categories[0].value : null,
                    unit_id: this.units.length > 0 ? this.units[0].value : null,
                    description: '',
                    image: '',
                    cost_price: 0,
                    selling_price: 0,
                    minimum_stock_level: 0,
                    opening_stock: 0,
                    track_serial: false,
                    is_active: true,
                };
            }
            this.dialog = true;
        },
        closeDialog() {
            this.dialog = false;
            this.editingProduct = null;
            this.activeTab = 'basic';
            this.form = {
                name: '',
                sku: '',
                barcode: '',
                category_id: this.categories.length > 0 ? this.categories[0].value : null,
                unit_id: this.units.length > 0 ? this.units[0].value : null,
                description: '',
                image: '',
                cost_price: 0,
                selling_price: 0,
                minimum_stock_level: 0,
                opening_stock: 0,
                track_serial: false,
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
                formData.append('folder', 'products');
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
        async saveProduct() {
            if (!this.$refs.form.validate()) {
                return;
            }

            this.saving = true;
            try {
                const token = localStorage.getItem('admin_token');
                const url = this.editingProduct
                    ? `/api/v1/products/${this.editingProduct.id}`
                    : '/api/v1/products';

                const data = { ...this.form };
                data.image = this.normalizeImageInput(data.image);

                const method = this.editingProduct ? 'put' : 'post';

                await axios[method](url, data, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess(
                    this.editingProduct ? 'Product updated successfully' : 'Product created successfully'
                );
                this.closeDialog();
                await this.loadProducts();
            } catch (error) {
                console.error('Error saving product:', error);
                let message = 'Error saving product';

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
        async deleteProduct(product) {
            if (!confirm(`Are you sure you want to delete ${product.name}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.delete(`/api/v1/products/${product.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Product deleted successfully');
                await this.loadProducts();
            } catch (error) {
                this.handleApiError(error, 'Error deleting product');
            }
        },
        formatCurrency(value) {
            if (value === null || value === undefined) return '$0.00';
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(value);
        },
        onPerPageChange() {
            this.resetPagination();
            this.loadProducts();
        },
        onSort(field) {
            this.handleSort(field);
            this.loadProducts();
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

