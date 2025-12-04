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
                    <v-col cols="12" md="2">
                        <v-select v-model="perPage" :items="perPageOptions" label="Items per page"
                            prepend-inner-icon="mdi-format-list-numbered" variant="outlined" density="compact"
                            @update:model-value="onPerPageChange"></v-select>
                    </v-col>
                    <v-col cols="12" md="2">
                        <v-select v-model="categoryFilter" :items="categoryOptions" label="Filter by Category"
                            variant="outlined" density="compact" clearable
                            @update:model-value="onCategoryFilterChange"></v-select>
                    </v-col>
                    <v-col cols="12" md="2">
                        <v-select v-model="subCategoryFilter" :items="filteredSubCategoryOptions"
                            label="Filter by Subcategory" variant="outlined" density="compact" clearable
                            :disabled="!categoryFilter" @update:model-value="loadProducts"></v-select>
                    </v-col>
                    <v-col cols="12" md="2">
                        <v-select v-model="activeFilter" :items="activeOptions" label="Filter by Status"
                            variant="outlined" density="compact" clearable
                            @update:model-value="loadProducts"></v-select>
                    </v-col>
                    <v-col cols="12" md="4">
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
                            <th>Subcategory</th>
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
                                    <v-chip size="small" color="primary" variant="tonal" v-if="product.category">
                                        {{ product.category.name }}
                                    </v-chip>
                                    <span v-else class="text-caption text-grey">-</span>
                                </td>
                                <td>
                                    <v-chip size="small" color="info" variant="tonal" v-if="product.sub_category">
                                        {{ product.sub_category.name }}
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
                                <td colspan="12" class="text-center py-4">No products found</td>
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
        <ProductDialog v-model="dialog" :product="editingProduct" :categories="categories" :units="units"
            :sub-categories="subCategories" @save="saveProduct" @cancel="closeDialog" />
    </div>
</template>

<script>
import adminPaginationMixin from '../../../mixins/adminPaginationMixin';
import ProductDialog from './ProductDialog.vue';

export default {
    components: {
        ProductDialog
    },
    mixins: [adminPaginationMixin],
    data() {
        return {
            products: [],
            categories: [],
            subCategories: [],
            units: [],
            categoryFilter: null,
            categoryOptions: [],
            subCategoryFilter: null,
            subCategoryOptions: [],
            activeFilter: null,
            activeOptions: [
                { title: 'Active', value: true },
                { title: 'Inactive', value: false }
            ],
            dialog: false,
            editingProduct: null,
        };
    },
    computed: {
        filteredSubCategoryOptions() {
            if (!this.categoryFilter) {
                return [];
            }
            return this.subCategoryOptions.filter(sub => sub.category_id === this.categoryFilter);
        }
    },
    async mounted() {
        await this.loadCategories();
        await this.loadSubCategories();
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
                if (this.subCategoryFilter) {
                    params.sub_category_id = this.subCategoryFilter;
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
        async loadSubCategories() {
            try {
                const response = await this.$axios.get('/api/v1/products/sub-categories', {
                    headers: this.getAuthHeaders()
                });
                this.subCategories = response.data.sub_categories || [];

                // Populate subCategoryOptions for filter
                this.subCategoryOptions = this.subCategories.map(sub => ({
                    title: sub.label,
                    value: sub.value,
                    category_id: sub.category_id
                }));
            } catch (error) {
                console.error('Error loading subcategories:', error);
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
            this.editingProduct = product;
            this.dialog = true;
        },
        closeDialog() {
            this.dialog = false;
            this.editingProduct = null;
        },
        async saveProduct({ data, isEditing }) {
            try {
                const token = localStorage.getItem('admin_token');
                const url = isEditing
                    ? `/api/v1/products/${this.editingProduct.id}`
                    : '/api/v1/products';

                const method = isEditing ? 'put' : 'post';

                await axios[method](url, data, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess(
                    isEditing ? 'Product updated successfully' : 'Product created successfully'
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
                throw error; // Re-throw so the dialog can handle the error state
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
            if (value === null || value === undefined) return '৳0.00';
            return '৳' + new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
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
        onCategoryFilterChange() {
            // Reset subcategory filter when category changes
            this.subCategoryFilter = null;
            this.loadProducts();
        },
    }
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>
