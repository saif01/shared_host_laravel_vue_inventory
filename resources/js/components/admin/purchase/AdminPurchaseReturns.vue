<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Purchase Returns</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                New Purchase Return
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
                            variant="outlined" density="compact" clearable @update:model-value="loadReturns"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="supplierFilter" :items="supplierOptions" label="Filter by Supplier"
                            variant="outlined" density="compact" clearable @update:model-value="loadReturns"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="search" label="Search by return number"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @input="loadReturns"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Purchase Returns Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Purchase Returns</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ pagination.total || 0 }}</strong>
                </span>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th>Return #</th>
                            <th>Purchase Invoice</th>
                            <th>Supplier</th>
                            <th>Return Date</th>
                            <th>Status</th>
                            <th>Reason</th>
                            <th>Total Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="150"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader></td>
                        </tr>
                        <template v-else>
                            <tr v-for="returnItem in returns" :key="returnItem.id">
                                <td>{{ returnItem.return_number }}</td>
                                <td>{{ returnItem.purchase?.invoice_number || '-' }}</td>
                                <td>{{ returnItem.supplier?.name }}</td>
                                <td>{{ formatDate(returnItem.return_date) }}</td>
                                <td>
                                    <v-chip :color="getStatusColor(returnItem.status)" size="small">
                                        {{ returnItem.status }}
                                    </v-chip>
                                </td>
                                <td>{{ returnItem.reason || '-' }}</td>
                                <td>{{ formatCurrency(returnItem.total_amount) }}</td>
                                <td>
                                    <v-btn size="small" icon="mdi-eye" @click="viewReturn(returnItem)" variant="text"
                                        title="View"></v-btn>
                                    <v-btn v-if="returnItem.status === 'draft'" size="small" icon="mdi-pencil"
                                        @click="openDialog(returnItem)" variant="text" title="Edit"></v-btn>
                                    <v-btn v-if="returnItem.status === 'draft'" size="small" icon="mdi-check"
                                        @click="approveReturn(returnItem)" variant="text" color="success"
                                        title="Approve"></v-btn>
                                    <v-btn v-if="returnItem.status === 'approved'" size="small" icon="mdi-check-circle"
                                        @click="completeReturn(returnItem)" variant="text" color="primary"
                                        title="Complete"></v-btn>
                                    <v-btn v-if="returnItem.status === 'draft'" size="small" icon="mdi-delete"
                                        @click="deleteReturn(returnItem)" variant="text" color="error"
                                        title="Delete"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="returns.length === 0">
                                <td colspan="8" class="text-center py-4">No purchase returns found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <div class="text-caption text-grey">
                        <span v-if="returns.length > 0 && pagination.total > 0">
                            Showing <strong>{{ ((currentPage - 1) * perPage) + 1 }}</strong> to
                            <strong>{{ Math.min(currentPage * perPage, pagination.total) }}</strong> of
                            <strong>{{ pagination.total.toLocaleString() }}</strong> records
                        </span>
                        <span v-else>No records found</span>
                    </div>
                    <div v-if="pagination.last_page > 1" class="d-flex align-center gap-2">
                        <v-pagination v-model="currentPage" :length="pagination.last_page" :total-visible="7"
                            density="comfortable" @update:model-value="loadReturns">
                        </v-pagination>
                    </div>
                </div>
            </v-card-text>
        </v-card>

        <!-- Purchase Return Dialog -->
        <v-dialog v-model="dialog" max-width="900" scrollable persistent>
            <v-card>
                <v-card-title>
                    {{ editingReturn ? 'Edit Purchase Return' : 'New Purchase Return' }}
                </v-card-title>
                <v-card-text class="pa-0">
                    <v-form ref="form" @submit.prevent="saveReturn">
                        <div class="pa-6">
                            <v-row>
                                <v-col cols="12" md="6">
                                    <v-select v-model="form.purchase_id" :items="purchaseOptions" item-title="label"
                                        item-value="value" label="Purchase Invoice" :rules="[rules.required]" required
                                        @update:model-value="loadPurchaseItems"></v-select>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field v-model="form.return_date" label="Return Date" type="date"
                                        :rules="[rules.required]" required></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-select v-model="form.reason" :items="reasonOptions" item-title="title"
                                        item-value="value" label="Reason"></v-select>
                                </v-col>
                                <v-col cols="12">
                                    <v-textarea v-model="form.notes" label="Notes" variant="outlined" rows="2"></v-textarea>
                                </v-col>
                            </v-row>

                            <!-- Return Items -->
                            <div class="mt-4">
                                <div class="d-flex justify-space-between align-center mb-2">
                                    <h3 class="text-h6">Return Items</h3>
                                </div>
                                <v-table>
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Quantity</th>
                                            <th>Unit Price</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in form.items" :key="index">
                                            <td>{{ item.product?.name || 'Loading...' }}</td>
                                            <td>
                                                <v-text-field v-model.number="item.quantity" type="number" min="1"
                                                    density="compact" @input="calculateItemTotal(index)"
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
                                            <td colspan="4" class="text-center py-4 text-grey">Select a purchase invoice to load items</td>
                                        </tr>
                                        <tr v-if="form.items.length > 0">
                                            <td colspan="3" class="text-right font-weight-bold">Total:</td>
                                            <td class="font-weight-bold">{{ formatCurrency(calculatedTotal) }}</td>
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
                    <v-btn @click="saveReturn" color="primary" :loading="saving">
                        {{ editingReturn ? 'Update' : 'Create' }}
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
            returns: [],
            purchases: [],
            purchaseOptions: [],
            suppliers: [],
            supplierOptions: [],
            statusFilter: null,
            statusOptions: [
                { title: 'Draft', value: 'draft' },
                { title: 'Approved', value: 'approved' },
                { title: 'Completed', value: 'completed' },
                { title: 'Cancelled', value: 'cancelled' }
            ],
            reasonOptions: [
                { title: 'Defective', value: 'defective' },
                { title: 'Wrong Item', value: 'wrong_item' },
                { title: 'Damaged', value: 'damaged' },
                { title: 'Other', value: 'other' }
            ],
            supplierFilter: null,
            dialog: false,
            editingReturn: null,
            saving: false,
            form: {
                purchase_id: null,
                return_date: new Date().toISOString().split('T')[0],
                reason: 'other',
                notes: '',
                items: []
            },
            rules: {
                required: value => !!value || 'This field is required',
                minValue: value => value > 0 || 'Quantity must be greater than 0'
            }
        };
    },
    computed: {
        calculatedTotal() {
            return this.form.items.reduce((sum, item) => sum + (item.total || 0), 0);
        }
    },
    async mounted() {
        await this.loadPurchases();
        await this.loadReturns();
    },
    methods: {
        async loadReturns() {
            try {
                this.loading = true;
                const params = this.buildPaginationParams();

                if (this.search) {
                    params.search = this.search;
                }
                if (this.statusFilter) {
                    params.status = this.statusFilter;
                }
                if (this.supplierFilter) {
                    params.supplier_id = this.supplierFilter;
                }

                const response = await this.$axios.get('/api/v1/purchase-returns', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.returns = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load purchase returns');
            } finally {
                this.loading = false;
            }
        },
        async loadPurchases() {
            try {
                const response = await this.$axios.get('/api/v1/purchases', {
                    params: { per_page: 1000, status: 'pending' },
                    headers: this.getAuthHeaders()
                });
                this.purchases = response.data.data || [];
                this.purchaseOptions = this.purchases.map(p => ({
                    label: `${p.invoice_number} - ${p.supplier?.name}`,
                    value: p.id
                }));
            } catch (error) {
                console.error('Error loading purchases:', error);
            }
        },
        async loadPurchaseItems() {
            if (!this.form.purchase_id) {
                this.form.items = [];
                return;
            }

            try {
                const response = await this.$axios.get(`/api/v1/purchases/${this.form.purchase_id}`, {
                    headers: this.getAuthHeaders()
                });
                const purchase = response.data;
                this.form.items = purchase.items?.map(item => ({
                    product_id: item.product_id,
                    product: item.product,
                    quantity: 1,
                    unit_price: item.unit_price,
                    total: item.unit_price
                })) || [];
            } catch (error) {
                this.handleApiError(error, 'Error loading purchase items');
            }
        },
        calculateItemTotal(index) {
            const item = this.form.items[index];
            if (item.quantity && item.unit_price) {
                const itemTotal = item.quantity * item.unit_price;
                this.$set(this.form.items[index], 'total', itemTotal);
            }
        },
        openDialog(returnItem) {
            if (returnItem) {
                this.editingReturn = returnItem;
                this.form = {
                    purchase_id: returnItem.purchase_id,
                    return_date: returnItem.return_date,
                    reason: returnItem.reason || 'other',
                    notes: returnItem.notes || '',
                    items: returnItem.items?.map(item => ({
                        product_id: item.product_id,
                        product: item.product,
                        quantity: item.quantity,
                        unit_price: item.unit_price,
                        total: item.total || 0
                    })) || []
                };
            } else {
                this.editingReturn = null;
                this.form = {
                    purchase_id: null,
                    return_date: new Date().toISOString().split('T')[0],
                    reason: 'other',
                    notes: '',
                    items: []
                };
            }
            this.dialog = true;
        },
        closeDialog() {
            this.dialog = false;
            this.editingReturn = null;
            this.form = {
                purchase_id: null,
                return_date: new Date().toISOString().split('T')[0],
                reason: 'other',
                notes: '',
                items: []
            };
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
            }
        },
        async saveReturn() {
            if (!this.$refs.form.validate()) {
                return;
            }

            if (this.form.items.length === 0) {
                this.showError('Please select a purchase invoice with items');
                return;
            }

            this.saving = true;
            try {
                const token = localStorage.getItem('admin_token');
                const url = this.editingReturn
                    ? `/api/v1/purchase-returns/${this.editingReturn.id}`
                    : '/api/v1/purchase-returns';

                const method = this.editingReturn ? 'put' : 'post';

                await axios[method](url, this.form, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess(
                    this.editingReturn ? 'Purchase return updated successfully' : 'Purchase return created successfully'
                );
                this.closeDialog();
                await this.loadReturns();
            } catch (error) {
                this.handleApiError(error, 'Error saving purchase return');
            } finally {
                this.saving = false;
            }
        },
        async approveReturn(returnItem) {
            if (!confirm(`Approve purchase return ${returnItem.return_number}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.post(`/api/v1/purchase-returns/${returnItem.id}/approve`, {}, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Purchase return approved successfully');
                await this.loadReturns();
            } catch (error) {
                this.handleApiError(error, 'Error approving purchase return');
            }
        },
        async completeReturn(returnItem) {
            if (!confirm(`Complete purchase return ${returnItem.return_number}? This will update stock levels.`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.post(`/api/v1/purchase-returns/${returnItem.id}/complete`, {}, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Purchase return completed successfully');
                await this.loadReturns();
            } catch (error) {
                this.handleApiError(error, 'Error completing purchase return');
            }
        },
        async deleteReturn(returnItem) {
            if (!confirm(`Delete purchase return ${returnItem.return_number}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.delete(`/api/v1/purchase-returns/${returnItem.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Purchase return deleted successfully');
                await this.loadReturns();
            } catch (error) {
                this.handleApiError(error, 'Error deleting purchase return');
            }
        },
        viewReturn(returnItem) {
            alert(`Return: ${returnItem.return_number}\nPurchase: ${returnItem.purchase?.invoice_number}\nReason: ${returnItem.reason}`);
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
                'draft': 'warning',
                'approved': 'info',
                'completed': 'success',
                'cancelled': 'error'
            };
            return colors[status] || 'default';
        },
        onPerPageChange() {
            this.resetPagination();
            this.loadReturns();
        }
    }
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>

