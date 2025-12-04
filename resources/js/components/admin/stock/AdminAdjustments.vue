<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Stock Adjustments</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                New Adjustment
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
                        <v-select v-model="statusFilter" :items="statusOptions" label="Filter by Status"
                            variant="outlined" density="compact" clearable @update:model-value="loadAdjustments"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="typeFilter" :items="typeOptions" label="Filter by Type"
                            variant="outlined" density="compact" clearable @update:model-value="loadAdjustments"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="search" label="Search by adjustment number"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @input="loadAdjustments"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Adjustments Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Adjustments</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ pagination.total || 0 }}</strong>
                </span>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th>Adjustment #</th>
                            <th>Warehouse</th>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Reason</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="60"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="150"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader></td>
                        </tr>
                        <template v-else>
                            <tr v-for="adjustment in adjustments" :key="adjustment.id">
                                <td>{{ adjustment.adjustment_number }}</td>
                                <td>{{ adjustment.warehouse?.name }}</td>
                                <td>{{ formatDate(adjustment.adjustment_date) }}</td>
                                <td>
                                    <v-chip :color="adjustment.type === 'increase' ? 'success' : 'error'" size="small">
                                        {{ adjustment.type === 'increase' ? 'Increase' : 'Decrease' }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-chip :color="getStatusColor(adjustment.status)" size="small">
                                        {{ adjustment.status }}
                                    </v-chip>
                                </td>
                                <td>{{ adjustment.items?.length || 0 }}</td>
                                <td>{{ adjustment.reason || '-' }}</td>
                                <td>{{ adjustment.created_by?.name || '-' }}</td>
                                <td>
                                    <v-btn size="small" icon="mdi-eye" @click="viewAdjustment(adjustment)" variant="text"
                                        title="View"></v-btn>
                                    <v-btn v-if="adjustment.status === 'draft'" size="small" icon="mdi-pencil"
                                        @click="openDialog(adjustment)" variant="text" title="Edit"></v-btn>
                                    <v-btn v-if="adjustment.status === 'draft'" size="small" icon="mdi-check"
                                        @click="approveAdjustment(adjustment)" variant="text" color="success"
                                        title="Approve"></v-btn>
                                    <v-btn v-if="adjustment.status === 'approved'" size="small" icon="mdi-check-circle"
                                        @click="completeAdjustment(adjustment)" variant="text" color="primary"
                                        title="Complete"></v-btn>
                                    <v-btn v-if="adjustment.status === 'draft'" size="small" icon="mdi-delete"
                                        @click="deleteAdjustment(adjustment)" variant="text" color="error"
                                        title="Delete"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="adjustments.length === 0">
                                <td colspan="9" class="text-center py-4">No adjustments found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <div class="text-caption text-grey">
                        <span v-if="adjustments.length > 0 && pagination.total > 0">
                            Showing <strong>{{ ((currentPage - 1) * perPage) + 1 }}</strong> to
                            <strong>{{ Math.min(currentPage * perPage, pagination.total) }}</strong> of
                            <strong>{{ pagination.total.toLocaleString() }}</strong> records
                        </span>
                        <span v-else>No records found</span>
                    </div>
                    <div v-if="pagination.last_page > 1" class="d-flex align-center gap-2">
                        <v-pagination v-model="currentPage" :length="pagination.last_page" :total-visible="7"
                            density="comfortable" @update:model-value="loadAdjustments">
                        </v-pagination>
                    </div>
                </div>
            </v-card-text>
        </v-card>

        <!-- Adjustment Dialog -->
        <v-dialog v-model="dialog" max-width="900" scrollable persistent>
            <v-card>
                <v-card-title>
                    {{ editingAdjustment ? 'Edit Adjustment' : 'New Stock Adjustment' }}
                </v-card-title>
                <v-card-text class="pa-0">
                    <v-form ref="form" @submit.prevent="saveAdjustment">
                        <div class="pa-6">
                            <v-row>
                                <v-col cols="12" md="6">
                                    <v-select v-model="form.warehouse_id" :items="warehouseOptions"
                                        item-title="label" item-value="value" label="Warehouse"
                                        :rules="[rules.required]" required></v-select>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field v-model="form.adjustment_date" label="Adjustment Date" type="date"
                                        :rules="[rules.required]" required></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-select v-model="form.type" :items="typeOptions" item-title="title"
                                        item-value="value" label="Adjustment Type" :rules="[rules.required]"
                                        required></v-select>
                                </v-col>
                                <v-col cols="12">
                                    <v-text-field v-model="form.reason" label="Reason" variant="outlined"></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-textarea v-model="form.notes" label="Notes" variant="outlined" rows="2"></v-textarea>
                                </v-col>
                            </v-row>

                            <!-- Adjustment Items -->
                            <div class="mt-4">
                                <div class="d-flex justify-space-between align-center mb-2">
                                    <h3 class="text-h6">Adjustment Items</h3>
                                    <v-btn size="small" color="primary" prepend-icon="mdi-plus"
                                        @click="addAdjustmentItem">Add Item</v-btn>
                                </div>
                                <v-table>
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Unit Cost</th>
                                            <th>Available Stock</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in form.items" :key="index">
                                            <td>
                                                <v-select v-model="item.product_id" :items="productOptions"
                                                    item-title="label" item-value="value" density="compact"
                                                    @update:model-value="loadProductStock(item, index)"
                                                    :rules="[rules.required]"></v-select>
                                            </td>
                                            <td>
                                                <v-text-field v-model.number="item.quantity" type="number" min="1"
                                                    density="compact" :rules="[rules.required, rules.minValue]"></v-text-field>
                                            </td>
                                            <td>
                                                <v-text-field v-model.number="item.unit_cost" type="number" min="0"
                                                    step="0.01" density="compact" prefix="$"></v-text-field>
                                            </td>
                                            <td>
                                                <span v-if="item.available_stock !== null"
                                                    :class="form.type === 'decrease' && item.available_stock < item.quantity ? 'text-error' : ''">
                                                    {{ item.available_stock }}
                                                </span>
                                                <span v-else class="text-grey">-</span>
                                            </td>
                                            <td>
                                                <v-btn size="small" icon="mdi-delete" variant="text" color="error"
                                                    @click="removeAdjustmentItem(index)"></v-btn>
                                            </td>
                                        </tr>
                                        <tr v-if="form.items.length === 0">
                                            <td colspan="5" class="text-center py-4 text-grey">No items added</td>
                                        </tr>
                                    </tbody>
                                </v-table>
                            </div>
                        </div>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="closeDialog" variant="text">Cancel</v-btn>
                    <v-btn @click="saveAdjustment" color="primary" :loading="saving">
                        {{ editingAdjustment ? 'Update' : 'Create' }}
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
            adjustments: [],
            warehouses: [],
            warehouseOptions: [],
            products: [],
            productOptions: [],
            statusFilter: null,
            statusOptions: [
                { title: 'Draft', value: 'draft' },
                { title: 'Approved', value: 'approved' },
                { title: 'Completed', value: 'completed' },
                { title: 'Cancelled', value: 'cancelled' }
            ],
            typeFilter: null,
            typeOptions: [
                { title: 'Increase', value: 'increase' },
                { title: 'Decrease', value: 'decrease' }
            ],
            dialog: false,
            editingAdjustment: null,
            saving: false,
            form: {
                warehouse_id: null,
                adjustment_date: new Date().toISOString().split('T')[0],
                type: 'increase',
                reason: '',
                notes: '',
                items: []
            },
            rules: {
                required: value => !!value || 'This field is required',
                minValue: value => value > 0 || 'Quantity must be greater than 0'
            }
        };
    },
    async mounted() {
        await this.loadWarehouses();
        await this.loadProducts();
        await this.loadAdjustments();
    },
    methods: {
        async loadAdjustments() {
            try {
                this.loading = true;
                const params = this.buildPaginationParams();

                if (this.search) {
                    params.search = this.search;
                }
                if (this.statusFilter) {
                    params.status = this.statusFilter;
                }
                if (this.typeFilter) {
                    params.type = this.typeFilter;
                }

                const response = await this.$axios.get('/api/v1/adjustments', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.adjustments = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load adjustments');
            } finally {
                this.loading = false;
            }
        },
        async loadWarehouses() {
            try {
                const response = await this.$axios.get('/api/v1/adjustments/warehouses', {
                    headers: this.getAuthHeaders()
                });
                this.warehouses = response.data.warehouses || [];
                this.warehouseOptions = this.warehouses;
            } catch (error) {
                console.error('Error loading warehouses:', error);
            }
        },
        async loadProducts() {
            try {
                const response = await this.$axios.get('/api/v1/products', {
                    params: { per_page: 1000, is_active: true },
                    headers: this.getAuthHeaders()
                });
                this.products = response.data.data || [];
                this.productOptions = this.products.map(p => ({
                    label: `${p.name} (${p.sku})`,
                    value: p.id
                }));
            } catch (error) {
                console.error('Error loading products:', error);
            }
        },
        async loadProductStock(item, index) {
            if (!item.product_id || !this.form.warehouse_id) return;

            try {
                const response = await this.$axios.get(`/api/v1/stocks`, {
                    params: {
                        product_id: item.product_id,
                        warehouse_id: this.form.warehouse_id
                    },
                    headers: this.getAuthHeaders()
                });
                const stock = response.data.data?.[0] || response.data?.[0];
                this.$set(this.form.items[index], 'available_stock', stock?.quantity || 0);
            } catch (error) {
                this.$set(this.form.items[index], 'available_stock', 0);
            }
        },
        addAdjustmentItem() {
            this.form.items.push({
                product_id: null,
                quantity: 1,
                unit_cost: 0,
                available_stock: null
            });
        },
        removeAdjustmentItem(index) {
            this.form.items.splice(index, 1);
        },
        openDialog(adjustment) {
            if (adjustment) {
                this.editingAdjustment = adjustment;
                this.form = {
                    warehouse_id: adjustment.warehouse_id,
                    adjustment_date: adjustment.adjustment_date,
                    type: adjustment.type,
                    reason: adjustment.reason || '',
                    notes: adjustment.notes || '',
                    items: adjustment.items?.map(item => ({
                        product_id: item.product_id,
                        quantity: item.quantity,
                        unit_cost: item.unit_cost || 0,
                        available_stock: null
                    })) || []
                };
            } else {
                this.editingAdjustment = null;
                this.form = {
                    warehouse_id: null,
                    adjustment_date: new Date().toISOString().split('T')[0],
                    type: 'increase',
                    reason: '',
                    notes: '',
                    items: []
                };
            }
            this.dialog = true;
        },
        closeDialog() {
            this.dialog = false;
            this.editingAdjustment = null;
            this.form = {
                warehouse_id: null,
                adjustment_date: new Date().toISOString().split('T')[0],
                type: 'increase',
                reason: '',
                notes: '',
                items: []
            };
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
            }
        },
        async saveAdjustment() {
            if (!this.$refs.form.validate()) {
                return;
            }

            if (this.form.items.length === 0) {
                this.showError('Please add at least one item');
                return;
            }

            this.saving = true;
            try {
                const token = localStorage.getItem('admin_token');
                const url = this.editingAdjustment
                    ? `/api/v1/adjustments/${this.editingAdjustment.id}`
                    : '/api/v1/adjustments';

                const method = this.editingAdjustment ? 'put' : 'post';

                await axios[method](url, this.form, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess(
                    this.editingAdjustment ? 'Adjustment updated successfully' : 'Adjustment created successfully'
                );
                this.closeDialog();
                await this.loadAdjustments();
            } catch (error) {
                this.handleApiError(error, 'Error saving adjustment');
            } finally {
                this.saving = false;
            }
        },
        async approveAdjustment(adjustment) {
            if (!confirm(`Approve adjustment ${adjustment.adjustment_number}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.post(`/api/v1/adjustments/${adjustment.id}/approve`, {}, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Adjustment approved successfully');
                await this.loadAdjustments();
            } catch (error) {
                this.handleApiError(error, 'Error approving adjustment');
            }
        },
        async completeAdjustment(adjustment) {
            if (!confirm(`Complete adjustment ${adjustment.adjustment_number}? This will update stock levels.`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.post(`/api/v1/adjustments/${adjustment.id}/complete`, {}, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Adjustment completed successfully');
                await this.loadAdjustments();
            } catch (error) {
                this.handleApiError(error, 'Error completing adjustment');
            }
        },
        async deleteAdjustment(adjustment) {
            if (!confirm(`Delete adjustment ${adjustment.adjustment_number}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.delete(`/api/v1/adjustments/${adjustment.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Adjustment deleted successfully');
                await this.loadAdjustments();
            } catch (error) {
                this.handleApiError(error, 'Error deleting adjustment');
            }
        },
        viewAdjustment(adjustment) {
            // TODO: Implement view dialog
            alert(`Adjustment: ${adjustment.adjustment_number}\nType: ${adjustment.type}\nWarehouse: ${adjustment.warehouse?.name}`);
        },
        getStatusColor(status) {
            const colors = {
                'draft': 'warning',
                'approved': 'info',
                'completed': 'success',
                'cancelled': 'error'
            };
            return colors[status] || 'default';
        },
        onPerPageChange() {
            this.resetPagination();
            this.loadAdjustments();
        }
    }
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>

