<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Stock Transfers</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                New Transfer
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
                            variant="outlined" density="compact" clearable @update:model-value="loadTransfers"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="fromWarehouseFilter" :items="warehouseOptions" label="From Warehouse"
                            variant="outlined" density="compact" clearable @update:model-value="loadTransfers"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="search" label="Search by transfer number"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @input="loadTransfers"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Transfers Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Transfers</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ pagination.total || 0 }}</strong>
                </span>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th>Transfer #</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Requested By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="60"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader></td>
                        </tr>
                        <template v-else>
                            <tr v-for="transfer in transfers" :key="transfer.id">
                                <td>{{ transfer.transfer_number }}</td>
                                <td>{{ transfer.from_warehouse?.name }}</td>
                                <td>{{ transfer.to_warehouse?.name }}</td>
                                <td>{{ formatDate(transfer.transfer_date) }}</td>
                                <td>
                                    <v-chip :color="getStatusColor(transfer.status)" size="small">
                                        {{ transfer.status }}
                                    </v-chip>
                                </td>
                                <td>{{ transfer.items?.length || 0 }}</td>
                                <td>{{ transfer.requested_by?.name || '-' }}</td>
                                <td>
                                    <v-btn size="small" icon="mdi-eye" @click="viewTransfer(transfer)" variant="text"
                                        title="View"></v-btn>
                                    <v-btn v-if="transfer.status === 'pending'" size="small" icon="mdi-pencil"
                                        @click="openDialog(transfer)" variant="text" title="Edit"></v-btn>
                                    <v-btn v-if="transfer.status === 'pending'" size="small" icon="mdi-check"
                                        @click="approveTransfer(transfer)" variant="text" color="success"
                                        title="Approve"></v-btn>
                                    <v-btn v-if="transfer.status === 'approved'" size="small" icon="mdi-package-variant"
                                        @click="receiveTransfer(transfer)" variant="text" color="info"
                                        title="Receive"></v-btn>
                                    <v-btn v-if="transfer.status === 'pending'" size="small" icon="mdi-delete"
                                        @click="deleteTransfer(transfer)" variant="text" color="error"
                                        title="Delete"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="transfers.length === 0">
                                <td colspan="8" class="text-center py-4">No transfers found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <div class="text-caption text-grey">
                        <span v-if="transfers.length > 0 && pagination.total > 0">
                            Showing <strong>{{ ((currentPage - 1) * perPage) + 1 }}</strong> to
                            <strong>{{ Math.min(currentPage * perPage, pagination.total) }}</strong> of
                            <strong>{{ pagination.total.toLocaleString() }}</strong> records
                        </span>
                        <span v-else>No records found</span>
                    </div>
                    <div v-if="pagination.last_page > 1" class="d-flex align-center gap-2">
                        <v-pagination v-model="currentPage" :length="pagination.last_page" :total-visible="7"
                            density="comfortable" @update:model-value="loadTransfers">
                        </v-pagination>
                    </div>
                </div>
            </v-card-text>
        </v-card>

        <!-- Transfer Dialog -->
        <v-dialog v-model="dialog" max-width="900" scrollable persistent>
            <v-card>
                <v-card-title>
                    {{ editingTransfer ? 'Edit Transfer' : 'New Stock Transfer' }}
                </v-card-title>
                <v-card-text class="pa-0">
                    <v-form ref="form" @submit.prevent="saveTransfer">
                        <div class="pa-6">
                            <v-row>
                                <v-col cols="12" md="6">
                                    <v-select v-model="form.from_warehouse_id" :items="warehouseOptions"
                                        item-title="label" item-value="value" label="From Warehouse"
                                        :rules="[rules.required]" required></v-select>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-select v-model="form.to_warehouse_id" :items="warehouseOptions"
                                        item-title="label" item-value="value" label="To Warehouse"
                                        :rules="[rules.required, rules.differentWarehouse]" required></v-select>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field v-model="form.transfer_date" label="Transfer Date" type="date"
                                        :rules="[rules.required]" required></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-textarea v-model="form.notes" label="Notes" variant="outlined" rows="2"></v-textarea>
                                </v-col>
                            </v-row>

                            <!-- Transfer Items -->
                            <div class="mt-4">
                                <div class="d-flex justify-space-between align-center mb-2">
                                    <h3 class="text-h6">Transfer Items</h3>
                                    <v-btn size="small" color="primary" prepend-icon="mdi-plus"
                                        @click="addTransferItem">Add Item</v-btn>
                                </div>
                                <v-table>
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
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
                                                <span v-if="item.available_stock !== null"
                                                    :class="item.available_stock < item.quantity ? 'text-error' : ''">
                                                    {{ item.available_stock }}
                                                </span>
                                                <span v-else class="text-grey">-</span>
                                            </td>
                                            <td>
                                                <v-btn size="small" icon="mdi-delete" variant="text" color="error"
                                                    @click="removeTransferItem(index)"></v-btn>
                                            </td>
                                        </tr>
                                        <tr v-if="form.items.length === 0">
                                            <td colspan="4" class="text-center py-4 text-grey">No items added</td>
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
                    <v-btn @click="saveTransfer" color="primary" :loading="saving">
                        {{ editingTransfer ? 'Update' : 'Create' }}
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
            transfers: [],
            warehouses: [],
            warehouseOptions: [],
            products: [],
            productOptions: [],
            stocks: {},
            statusFilter: null,
            statusOptions: [
                { title: 'Pending', value: 'pending' },
                { title: 'Approved', value: 'approved' },
                { title: 'In Transit', value: 'in_transit' },
                { title: 'Completed', value: 'completed' },
                { title: 'Cancelled', value: 'cancelled' }
            ],
            fromWarehouseFilter: null,
            dialog: false,
            editingTransfer: null,
            saving: false,
            form: {
                from_warehouse_id: null,
                to_warehouse_id: null,
                transfer_date: new Date().toISOString().split('T')[0],
                notes: '',
                items: []
            },
            rules: {
                required: value => !!value || 'This field is required',
                minValue: value => value > 0 || 'Quantity must be greater than 0',
                differentWarehouse: () => {
                    if (this.form.from_warehouse_id && this.form.to_warehouse_id &&
                        this.form.from_warehouse_id === this.form.to_warehouse_id) {
                        return 'To warehouse must be different from from warehouse';
                    }
                    return true;
                }
            }
        };
    },
    async mounted() {
        await this.loadWarehouses();
        await this.loadProducts();
        await this.loadTransfers();
    },
    methods: {
        async loadTransfers() {
            try {
                this.loading = true;
                const params = this.buildPaginationParams();

                if (this.search) {
                    params.search = this.search;
                }
                if (this.statusFilter) {
                    params.status = this.statusFilter;
                }
                if (this.fromWarehouseFilter) {
                    params.from_warehouse_id = this.fromWarehouseFilter;
                }

                const response = await this.$axios.get('/api/v1/transfers', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.transfers = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load transfers');
            } finally {
                this.loading = false;
            }
        },
        async loadWarehouses() {
            try {
                const response = await this.$axios.get('/api/v1/transfers/warehouses', {
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
            if (!item.product_id || !this.form.from_warehouse_id) return;

            try {
                const response = await this.$axios.get(`/api/v1/stocks`, {
                    params: {
                        product_id: item.product_id,
                        warehouse_id: this.form.from_warehouse_id
                    },
                    headers: this.getAuthHeaders()
                });
                const stock = response.data.data?.[0] || response.data?.[0];
                this.$set(this.form.items[index], 'available_stock', stock?.quantity || 0);
            } catch (error) {
                this.$set(this.form.items[index], 'available_stock', 0);
            }
        },
        addTransferItem() {
            this.form.items.push({
                product_id: null,
                quantity: 1,
                available_stock: null
            });
        },
        removeTransferItem(index) {
            this.form.items.splice(index, 1);
        },
        openDialog(transfer) {
            if (transfer) {
                this.editingTransfer = transfer;
                this.form = {
                    from_warehouse_id: transfer.from_warehouse_id,
                    to_warehouse_id: transfer.to_warehouse_id,
                    transfer_date: transfer.transfer_date,
                    notes: transfer.notes || '',
                    items: transfer.items?.map(item => ({
                        product_id: item.product_id,
                        quantity: item.quantity,
                        available_stock: null
                    })) || []
                };
            } else {
                this.editingTransfer = null;
                this.form = {
                    from_warehouse_id: null,
                    to_warehouse_id: null,
                    transfer_date: new Date().toISOString().split('T')[0],
                    notes: '',
                    items: []
                };
            }
            this.dialog = true;
        },
        closeDialog() {
            this.dialog = false;
            this.editingTransfer = null;
            this.form = {
                from_warehouse_id: null,
                to_warehouse_id: null,
                transfer_date: new Date().toISOString().split('T')[0],
                notes: '',
                items: []
            };
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
            }
        },
        async saveTransfer() {
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
                const url = this.editingTransfer
                    ? `/api/v1/transfers/${this.editingTransfer.id}`
                    : '/api/v1/transfers';

                const method = this.editingTransfer ? 'put' : 'post';

                await axios[method](url, this.form, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess(
                    this.editingTransfer ? 'Transfer updated successfully' : 'Transfer created successfully'
                );
                this.closeDialog();
                await this.loadTransfers();
            } catch (error) {
                this.handleApiError(error, 'Error saving transfer');
            } finally {
                this.saving = false;
            }
        },
        async approveTransfer(transfer) {
            if (!confirm(`Approve transfer ${transfer.transfer_number}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.post(`/api/v1/transfers/${transfer.id}/approve`, {}, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Transfer approved successfully');
                await this.loadTransfers();
            } catch (error) {
                this.handleApiError(error, 'Error approving transfer');
            }
        },
        async receiveTransfer(transfer) {
            if (!confirm(`Receive transfer ${transfer.transfer_number}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.post(`/api/v1/transfers/${transfer.id}/receive`, {}, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Transfer received successfully');
                await this.loadTransfers();
            } catch (error) {
                this.handleApiError(error, 'Error receiving transfer');
            }
        },
        async deleteTransfer(transfer) {
            if (!confirm(`Delete transfer ${transfer.transfer_number}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.delete(`/api/v1/transfers/${transfer.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Transfer deleted successfully');
                await this.loadTransfers();
            } catch (error) {
                this.handleApiError(error, 'Error deleting transfer');
            }
        },
        viewTransfer(transfer) {
            // TODO: Implement view dialog
            alert(`Transfer: ${transfer.transfer_number}\nFrom: ${transfer.from_warehouse?.name}\nTo: ${transfer.to_warehouse?.name}`);
        },
        getStatusColor(status) {
            const colors = {
                'pending': 'warning',
                'approved': 'info',
                'in_transit': 'primary',
                'completed': 'success',
                'cancelled': 'error'
            };
            return colors[status] || 'default';
        },
        onPerPageChange() {
            this.resetPagination();
            this.loadTransfers();
        }
    }
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>

