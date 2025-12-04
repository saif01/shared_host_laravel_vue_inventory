<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Goods Received Notes (GRN)</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                New GRN
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
                            variant="outlined" density="compact" clearable @update:model-value="loadGrns"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="poFilter" :items="poOptions" label="Filter by Purchase Order"
                            variant="outlined" density="compact" clearable @update:model-value="loadGrns"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="search" label="Search by GRN number"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @input="loadGrns"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- GRNs Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>GRNs</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ pagination.total || 0 }}</strong>
                </span>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th>GRN Number</th>
                            <th>Purchase Order</th>
                            <th>Warehouse</th>
                            <th>GRN Date</th>
                            <th>Status</th>
                            <th>Items</th>
                            <th>Received By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="60"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader></td>
                        </tr>
                        <template v-else>
                            <tr v-for="grn in grns" :key="grn.id">
                                <td>{{ grn.grn_number }}</td>
                                <td>{{ grn.purchase_order?.po_number || '-' }}</td>
                                <td>{{ grn.warehouse?.name }}</td>
                                <td>{{ formatDate(grn.grn_date) }}</td>
                                <td>
                                    <v-chip :color="getStatusColor(grn.status)" size="small">
                                        {{ grn.status }}
                                    </v-chip>
                                </td>
                                <td>{{ grn.items?.length || 0 }}</td>
                                <td>{{ grn.received_by?.name || '-' }}</td>
                                <td>
                                    <v-btn size="small" icon="mdi-eye" @click="viewGrn(grn)" variant="text"
                                        title="View"></v-btn>
                                    <v-btn v-if="grn.status === 'draft'" size="small" icon="mdi-pencil"
                                        @click="openDialog(grn)" variant="text" title="Edit"></v-btn>
                                    <v-btn v-if="grn.status === 'draft'" size="small" icon="mdi-check"
                                        @click="verifyGrn(grn)" variant="text" color="success"
                                        title="Verify"></v-btn>
                                    <v-btn v-if="grn.status === 'draft'" size="small" icon="mdi-delete"
                                        @click="deleteGrn(grn)" variant="text" color="error"
                                        title="Delete"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="grns.length === 0">
                                <td colspan="8" class="text-center py-4">No GRNs found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <div class="text-caption text-grey">
                        <span v-if="grns.length > 0 && pagination.total > 0">
                            Showing <strong>{{ ((currentPage - 1) * perPage) + 1 }}</strong> to
                            <strong>{{ Math.min(currentPage * perPage, pagination.total) }}</strong> of
                            <strong>{{ pagination.total.toLocaleString() }}</strong> records
                        </span>
                        <span v-else>No records found</span>
                    </div>
                    <div v-if="pagination.last_page > 1" class="d-flex align-center gap-2">
                        <v-pagination v-model="currentPage" :length="pagination.last_page" :total-visible="7"
                            density="comfortable" @update:model-value="loadGrns">
                        </v-pagination>
                    </div>
                </div>
            </v-card-text>
        </v-card>

        <!-- GRN Dialog -->
        <v-dialog v-model="dialog" max-width="1000" scrollable persistent>
            <v-card>
                <v-card-title>
                    {{ editingGrn ? 'Edit GRN' : 'New GRN' }}
                </v-card-title>
                <v-card-text class="pa-0">
                    <v-form ref="form" @submit.prevent="saveGrn">
                        <div class="pa-6">
                            <v-row>
                                <v-col cols="12" md="6">
                                    <v-select v-model="form.purchase_order_id" :items="poOptions" item-title="label"
                                        item-value="value" label="Purchase Order" :rules="[rules.required]" required
                                        @update:model-value="loadPOItems"></v-select>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-select v-model="form.warehouse_id" :items="warehouseOptions" item-title="label"
                                        item-value="value" label="Warehouse" :rules="[rules.required]" required></v-select>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field v-model="form.grn_date" label="GRN Date" type="date"
                                        :rules="[rules.required]" required></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-textarea v-model="form.notes" label="Notes" variant="outlined" rows="2"></v-textarea>
                                </v-col>
                            </v-row>

                            <!-- GRN Items -->
                            <div class="mt-4">
                                <div class="d-flex justify-space-between align-center mb-2">
                                    <h3 class="text-h6">Received Items</h3>
                                </div>
                                <v-table>
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Ordered Qty</th>
                                            <th>Received Qty</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in form.items" :key="index">
                                            <td>{{ item.product?.name || 'Loading...' }}</td>
                                            <td>{{ item.ordered_quantity }}</td>
                                            <td>
                                                <v-text-field v-model.number="item.received_quantity" type="number"
                                                    min="1" density="compact" @input="calculateItemTotal(index)"
                                                    :rules="[rules.required, rules.minValue]"></v-text-field>
                                            </td>
                                            <td>
                                                <v-text-field v-model.number="item.unit_price" type="number" min="0"
                                                    step="0.01" density="compact" @input="calculateItemTotal(index)"
                                                    :rules="[rules.required]"></v-text-field>
                                            </td>
                                            <td>{{ formatCurrency(item.total || 0) }}</td>
                                        </tr>
                                        <tr v-if="form.items.length === 0">
                                            <td colspan="5" class="text-center py-4 text-grey">Select a purchase order to load items</td>
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
                    <v-btn @click="saveGrn" color="primary" :loading="saving">
                        {{ editingGrn ? 'Update' : 'Create' }}
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
            grns: [],
            purchaseOrders: [],
            poOptions: [],
            warehouses: [],
            warehouseOptions: [],
            statusFilter: null,
            statusOptions: [
                { title: 'Draft', value: 'draft' },
                { title: 'Received', value: 'received' },
                { title: 'Verified', value: 'verified' },
                { title: 'Cancelled', value: 'cancelled' }
            ],
            poFilter: null,
            dialog: false,
            editingGrn: null,
            saving: false,
            form: {
                purchase_order_id: null,
                warehouse_id: null,
                grn_date: new Date().toISOString().split('T')[0],
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
        await this.loadPurchaseOrders();
        await this.loadWarehouses();
        await this.loadGrns();
    },
    methods: {
        async loadGrns() {
            try {
                this.loading = true;
                const params = this.buildPaginationParams();

                if (this.search) {
                    params.search = this.search;
                }
                if (this.statusFilter) {
                    params.status = this.statusFilter;
                }
                if (this.poFilter) {
                    params.purchase_order_id = this.poFilter;
                }

                const response = await this.$axios.get('/api/v1/grns', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.grns = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load GRNs');
            } finally {
                this.loading = false;
            }
        },
        async loadPurchaseOrders() {
            try {
                const response = await this.$axios.get('/api/v1/purchase-orders', {
                    params: { per_page: 1000, status: 'confirmed' },
                    headers: this.getAuthHeaders()
                });
                this.purchaseOrders = response.data.data || [];
                this.poOptions = this.purchaseOrders.map(po => ({
                    label: `${po.po_number} - ${po.supplier?.name}`,
                    value: po.id
                }));
            } catch (error) {
                console.error('Error loading purchase orders:', error);
            }
        },
        async loadWarehouses() {
            try {
                const response = await this.$axios.get('/api/v1/stock-ledger/warehouses', {
                    headers: this.getAuthHeaders()
                });
                this.warehouses = response.data.warehouses || [];
                this.warehouseOptions = this.warehouses;
            } catch (error) {
                console.error('Error loading warehouses:', error);
            }
        },
        async loadPOItems() {
            if (!this.form.purchase_order_id) {
                this.form.items = [];
                return;
            }

            try {
                const response = await this.$axios.get(`/api/v1/purchase-orders/${this.form.purchase_order_id}`, {
                    headers: this.getAuthHeaders()
                });
                const po = response.data;
                this.form.warehouse_id = po.warehouse_id;
                this.form.items = po.items?.map(item => ({
                    product_id: item.product_id,
                    product: item.product,
                    ordered_quantity: item.quantity,
                    received_quantity: item.quantity,
                    unit_price: item.unit_price,
                    total: item.quantity * item.unit_price
                })) || [];
            } catch (error) {
                this.handleApiError(error, 'Error loading purchase order items');
            }
        },
        calculateItemTotal(index) {
            const item = this.form.items[index];
            if (item.received_quantity && item.unit_price) {
                const itemTotal = item.received_quantity * item.unit_price;
                this.$set(this.form.items[index], 'total', itemTotal);
            }
        },
        openDialog(grn) {
            if (grn) {
                this.editingGrn = grn;
                this.form = {
                    purchase_order_id: grn.purchase_order_id,
                    warehouse_id: grn.warehouse_id,
                    grn_date: grn.grn_date,
                    notes: grn.notes || '',
                    items: grn.items?.map(item => ({
                        product_id: item.product_id,
                        product: item.product,
                        ordered_quantity: item.ordered_quantity,
                        received_quantity: item.received_quantity,
                        unit_price: item.unit_price,
                        total: item.total || 0
                    })) || []
                };
            } else {
                this.editingGrn = null;
                this.form = {
                    purchase_order_id: null,
                    warehouse_id: null,
                    grn_date: new Date().toISOString().split('T')[0],
                    notes: '',
                    items: []
                };
            }
            this.dialog = true;
        },
        closeDialog() {
            this.dialog = false;
            this.editingGrn = null;
            this.form = {
                purchase_order_id: null,
                warehouse_id: null,
                grn_date: new Date().toISOString().split('T')[0],
                notes: '',
                items: []
            };
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
            }
        },
        async saveGrn() {
            if (!this.$refs.form.validate()) {
                return;
            }

            if (this.form.items.length === 0) {
                this.showError('Please select a purchase order with items');
                return;
            }

            this.saving = true;
            try {
                const token = localStorage.getItem('admin_token');
                const url = this.editingGrn
                    ? `/api/v1/grns/${this.editingGrn.id}`
                    : '/api/v1/grns';

                const method = this.editingGrn ? 'put' : 'post';

                await axios[method](url, this.form, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess(
                    this.editingGrn ? 'GRN updated successfully' : 'GRN created successfully'
                );
                this.closeDialog();
                await this.loadGrns();
            } catch (error) {
                this.handleApiError(error, 'Error saving GRN');
            } finally {
                this.saving = false;
            }
        },
        async verifyGrn(grn) {
            if (!confirm(`Verify GRN ${grn.grn_number}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.post(`/api/v1/grns/${grn.id}/verify`, {}, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('GRN verified successfully');
                await this.loadGrns();
            } catch (error) {
                this.handleApiError(error, 'Error verifying GRN');
            }
        },
        async deleteGrn(grn) {
            if (!confirm(`Delete GRN ${grn.grn_number}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.delete(`/api/v1/grns/${grn.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('GRN deleted successfully');
                await this.loadGrns();
            } catch (error) {
                this.handleApiError(error, 'Error deleting GRN');
            }
        },
        viewGrn(grn) {
            alert(`GRN: ${grn.grn_number}\nPO: ${grn.purchase_order?.po_number}\nStatus: ${grn.status}`);
        },
        formatCurrency(value) {
            if (value === null || value === undefined) return '$0.00';
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD'
            }).format(value);
        },
        getStatusColor(status) {
            const colors = {
                'draft': 'grey',
                'received': 'info',
                'verified': 'success',
                'cancelled': 'error'
            };
            return colors[status] || 'default';
        },
        onPerPageChange() {
            this.resetPagination();
            this.loadGrns();
        }
    }
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>

