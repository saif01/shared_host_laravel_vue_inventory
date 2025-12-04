<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Supplier Invoices (Purchases)</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                New Purchase Invoice
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
                            variant="outlined" density="compact" clearable @update:model-value="loadPurchases"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="supplierFilter" :items="supplierOptions" label="Filter by Supplier"
                            variant="outlined" density="compact" clearable @update:model-value="loadPurchases"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="search" label="Search by invoice number"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @input="loadPurchases"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Purchases Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Supplier Invoices</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ pagination.total || 0 }}</strong>
                </span>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Supplier</th>
                            <th>Warehouse</th>
                            <th>Invoice Date</th>
                            <th>Status</th>
                            <th>Total Amount</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="150"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader></td>
                        </tr>
                        <template v-else>
                            <tr v-for="purchase in purchases" :key="purchase.id">
                                <td>{{ purchase.invoice_number }}</td>
                                <td>{{ purchase.supplier?.name }}</td>
                                <td>{{ purchase.warehouse?.name }}</td>
                                <td>{{ formatDate(purchase.invoice_date) }}</td>
                                <td>
                                    <v-chip :color="getStatusColor(purchase.status)" size="small">
                                        {{ purchase.status }}
                                    </v-chip>
                                </td>
                                <td>{{ formatCurrency(purchase.total_amount) }}</td>
                                <td>{{ formatCurrency(purchase.paid_amount) }}</td>
                                <td>
                                    <v-chip :color="purchase.balance_amount > 0 ? 'warning' : 'success'" size="small">
                                        {{ formatCurrency(purchase.balance_amount) }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-btn size="small" icon="mdi-eye" @click="viewPurchase(purchase)" variant="text"
                                        title="View"></v-btn>
                                    <v-btn v-if="purchase.status === 'draft'" size="small" icon="mdi-pencil"
                                        @click="openDialog(purchase)" variant="text" title="Edit"></v-btn>
                                    <v-btn v-if="purchase.status === 'draft'" size="small" icon="mdi-package-variant"
                                        @click="receivePurchase(purchase)" variant="text" color="success"
                                        title="Receive Stock"></v-btn>
                                    <v-btn v-if="purchase.status === 'draft'" size="small" icon="mdi-delete"
                                        @click="deletePurchase(purchase)" variant="text" color="error"
                                        title="Delete"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="purchases.length === 0">
                                <td colspan="9" class="text-center py-4">No purchases found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <div class="text-caption text-grey">
                        <span v-if="purchases.length > 0 && pagination.total > 0">
                            Showing <strong>{{ ((currentPage - 1) * perPage) + 1 }}</strong> to
                            <strong>{{ Math.min(currentPage * perPage, pagination.total) }}</strong> of
                            <strong>{{ pagination.total.toLocaleString() }}</strong> records
                        </span>
                        <span v-else>No records found</span>
                    </div>
                    <div v-if="pagination.last_page > 1" class="d-flex align-center gap-2">
                        <v-pagination v-model="currentPage" :length="pagination.last_page" :total-visible="7"
                            density="comfortable" @update:model-value="loadPurchases">
                        </v-pagination>
                    </div>
                </div>
            </v-card-text>
        </v-card>

        <!-- Purchase Dialog -->
        <v-dialog v-model="dialog" max-width="1000" scrollable persistent>
            <v-card>
                <v-card-title>
                    {{ editingPurchase ? 'Edit Purchase Invoice' : 'New Purchase Invoice' }}
                </v-card-title>
                <v-card-text class="pa-0">
                    <v-form ref="form" @submit.prevent="savePurchase">
                        <v-tabs v-model="activeTab" bg-color="grey-lighten-4">
                            <v-tab value="basic">Basic Information</v-tab>
                            <v-tab value="items">Items</v-tab>
                        </v-tabs>

                        <v-window v-model="activeTab">
                            <!-- Basic Information Tab -->
                            <v-window-item value="basic">
                                <div class="pa-6">
                                    <v-row>
                                        <v-col cols="12" md="6">
                                            <v-select v-model="form.supplier_id" :items="supplierOptions"
                                                item-title="label" item-value="value" label="Supplier"
                                                :rules="[rules.required]" required></v-select>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-select v-model="form.warehouse_id" :items="warehouseOptions"
                                                item-title="label" item-value="value" label="Warehouse"
                                                :rules="[rules.required]" required></v-select>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-select v-model="form.grn_id" :items="grnOptions" item-title="label"
                                                item-value="value" label="GRN (Optional)" clearable></v-select>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model="form.invoice_date" label="Invoice Date" type="date"
                                                :rules="[rules.required]" required></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model="form.due_date" label="Due Date" type="date"></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model.number="form.shipping_cost" label="Shipping Cost"
                                                type="number" min="0" step="0.01" prefix="৳" @input="calculateTotals"></v-text-field>
                                        </v-col>
                                        <v-col cols="12">
                                            <v-textarea v-model="form.notes" label="Notes" variant="outlined"
                                                rows="2"></v-textarea>
                                        </v-col>
                                    </v-row>
                                </div>
                            </v-window-item>

                            <!-- Items Tab -->
                            <v-window-item value="items">
                                <div class="pa-6">
                                    <div class="d-flex justify-space-between align-center mb-2">
                                        <h3 class="text-h6">Invoice Items</h3>
                                        <v-btn size="small" color="primary" prepend-icon="mdi-plus"
                                            @click="addPurchaseItem">Add Item</v-btn>
                                    </div>
                                    <v-table>
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Quantity</th>
                                                <th>Unit Price</th>
                                                <th>Discount</th>
                                                <th>Tax</th>
                                                <th>Total</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, index) in form.items" :key="index">
                                                <td>
                                                    <v-select v-model="item.product_id" :items="productOptions"
                                                        item-title="label" item-value="value" density="compact"
                                                        @update:model-value="calculateItemTotal(index)"
                                                        :rules="[rules.required]"></v-select>
                                                </td>
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
                                                <td>
                                                    <v-text-field v-model.number="item.discount" type="number" min="0"
                                                        step="0.01" density="compact" @input="calculateItemTotal(index)"></v-text-field>
                                                </td>
                                                <td>
                                                    <v-text-field v-model.number="item.tax" type="number" min="0"
                                                        step="0.01" density="compact" @input="calculateItemTotal(index)"></v-text-field>
                                                </td>
                                                <td>{{ formatCurrency(item.total || 0) }}</td>
                                                <td>
                                                    <v-btn size="small" icon="mdi-delete" variant="text" color="error"
                                                        @click="removePurchaseItem(index)"></v-btn>
                                                </td>
                                            </tr>
                                            <tr v-if="form.items.length === 0">
                                                <td colspan="7" class="text-center py-4 text-grey">No items added</td>
                                            </tr>
                                            <tr v-if="form.items.length > 0">
                                                <td colspan="5" class="text-right font-weight-bold">Subtotal:</td>
                                                <td class="font-weight-bold">{{ formatCurrency(calculatedSubtotal) }}</td>
                                                <td></td>
                                            </tr>
                                            <tr v-if="form.items.length > 0">
                                                <td colspan="5" class="text-right">Tax:</td>
                                                <td>{{ formatCurrency(calculatedTax) }}</td>
                                                <td></td>
                                            </tr>
                                            <tr v-if="form.items.length > 0">
                                                <td colspan="5" class="text-right">Shipping:</td>
                                                <td>{{ formatCurrency(form.shipping_cost || 0) }}</td>
                                                <td></td>
                                            </tr>
                                            <tr v-if="form.items.length > 0">
                                                <td colspan="5" class="text-right">Discount:</td>
                                                <td>{{ formatCurrency(calculatedDiscount) }}</td>
                                                <td></td>
                                            </tr>
                                            <tr v-if="form.items.length > 0">
                                                <td colspan="5" class="text-right font-weight-bold">Total:</td>
                                                <td class="font-weight-bold">{{ formatCurrency(calculatedTotal) }}</td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </v-table>
                                </div>
                            </v-window-item>
                        </v-window>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="closeDialog" variant="text">Cancel</v-btn>
                    <v-btn @click="savePurchase" color="primary" :loading="saving">
                        {{ editingPurchase ? 'Update' : 'Create' }}
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
            purchases: [],
            suppliers: [],
            supplierOptions: [],
            warehouses: [],
            warehouseOptions: [],
            grns: [],
            grnOptions: [],
            products: [],
            productOptions: [],
            statusFilter: null,
            statusOptions: [
                { title: 'Draft', value: 'draft' },
                { title: 'Pending', value: 'pending' },
                { title: 'Partial', value: 'partial' },
                { title: 'Paid', value: 'paid' },
                { title: 'Cancelled', value: 'cancelled' }
            ],
            supplierFilter: null,
            dialog: false,
            editingPurchase: null,
            saving: false,
            activeTab: 'basic',
            form: {
                supplier_id: null,
                warehouse_id: null,
                grn_id: null,
                invoice_date: new Date().toISOString().split('T')[0],
                due_date: null,
                shipping_cost: 0,
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
        calculatedSubtotal() {
            return this.form.items.reduce((sum, item) => {
                const itemTotal = (item.quantity || 0) * (item.unit_price || 0) - (item.discount || 0);
                return sum + itemTotal;
            }, 0);
        },
        calculatedTax() {
            return this.form.items.reduce((sum, item) => sum + (item.tax || 0), 0);
        },
        calculatedDiscount() {
            return this.form.items.reduce((sum, item) => sum + (item.discount || 0), 0);
        },
        calculatedTotal() {
            return this.calculatedSubtotal + this.calculatedTax + (this.form.shipping_cost || 0) - this.calculatedDiscount;
        }
    },
    async mounted() {
        await this.loadSuppliers();
        await this.loadWarehouses();
        await this.loadGrns();
        await this.loadProducts();
        await this.loadPurchases();
    },
    methods: {
        async loadPurchases() {
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

                const response = await this.$axios.get('/api/v1/purchases', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.purchases = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load purchases');
            } finally {
                this.loading = false;
            }
        },
        async loadSuppliers() {
            try {
                const response = await this.$axios.get('/api/v1/purchase-orders/suppliers', {
                    headers: this.getAuthHeaders()
                });
                this.suppliers = response.data.suppliers || [];
                this.supplierOptions = this.suppliers;
            } catch (error) {
                console.error('Error loading suppliers:', error);
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
        async loadGrns() {
            try {
                const response = await this.$axios.get('/api/v1/grns', {
                    params: { per_page: 1000, status: 'verified' },
                    headers: this.getAuthHeaders()
                });
                this.grns = response.data.data || [];
                this.grnOptions = this.grns.map(grn => ({
                    label: `${grn.grn_number} - ${grn.purchase_order?.po_number || ''}`,
                    value: grn.id
                }));
            } catch (error) {
                console.error('Error loading GRNs:', error);
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
        calculateItemTotal(index) {
            const item = this.form.items[index];
            if (item.quantity && item.unit_price) {
                const itemTotal = (item.quantity * item.unit_price) - (item.discount || 0) + (item.tax || 0);
                this.$set(this.form.items[index], 'total', itemTotal);
            }
        },
        calculateTotals() {
            // Trigger recalculation if needed
        },
        addPurchaseItem() {
            this.form.items.push({
                product_id: null,
                quantity: 1,
                unit_price: 0,
                discount: 0,
                tax: 0,
                total: 0
            });
        },
        removePurchaseItem(index) {
            this.form.items.splice(index, 1);
        },
        openDialog(purchase) {
            if (purchase) {
                this.editingPurchase = purchase;
                this.activeTab = 'basic';
                this.form = {
                    supplier_id: purchase.supplier_id,
                    warehouse_id: purchase.warehouse_id,
                    grn_id: purchase.grn_id,
                    invoice_date: purchase.invoice_date,
                    due_date: purchase.due_date,
                    shipping_cost: purchase.shipping_cost || 0,
                    notes: purchase.notes || '',
                    items: purchase.items?.map(item => ({
                        product_id: item.product_id,
                        quantity: item.quantity,
                        unit_price: item.unit_price,
                        discount: item.discount || 0,
                        tax: item.tax || 0,
                        total: item.total || 0
                    })) || []
                };
            } else {
                this.editingPurchase = null;
                this.activeTab = 'basic';
                this.form = {
                    supplier_id: null,
                    warehouse_id: null,
                    grn_id: null,
                    invoice_date: new Date().toISOString().split('T')[0],
                    due_date: null,
                    shipping_cost: 0,
                    notes: '',
                    items: []
                };
            }
            this.dialog = true;
        },
        closeDialog() {
            this.dialog = false;
            this.editingPurchase = null;
            this.activeTab = 'basic';
            this.form = {
                supplier_id: null,
                warehouse_id: null,
                grn_id: null,
                invoice_date: new Date().toISOString().split('T')[0],
                due_date: null,
                shipping_cost: 0,
                notes: '',
                items: []
            };
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
            }
        },
        async savePurchase() {
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
                const url = this.editingPurchase
                    ? `/api/v1/purchases/${this.editingPurchase.id}`
                    : '/api/v1/purchases';

                const method = this.editingPurchase ? 'put' : 'post';

                await axios[method](url, this.form, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess(
                    this.editingPurchase ? 'Purchase updated successfully' : 'Purchase created successfully'
                );
                this.closeDialog();
                await this.loadPurchases();
            } catch (error) {
                this.handleApiError(error, 'Error saving purchase');
            } finally {
                this.saving = false;
            }
        },
        async receivePurchase(purchase) {
            if (!confirm(`Receive stock for purchase ${purchase.invoice_number}? This will update stock levels.`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.post(`/api/v1/purchases/${purchase.id}/receive`, {}, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Purchase received and stock updated successfully');
                await this.loadPurchases();
            } catch (error) {
                this.handleApiError(error, 'Error receiving purchase');
            }
        },
        async deletePurchase(purchase) {
            if (!confirm(`Delete purchase ${purchase.invoice_number}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.delete(`/api/v1/purchases/${purchase.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Purchase deleted successfully');
                await this.loadPurchases();
            } catch (error) {
                this.handleApiError(error, 'Error deleting purchase');
            }
        },
        viewPurchase(purchase) {
            alert(`Invoice: ${purchase.invoice_number}\nSupplier: ${purchase.supplier?.name}\nTotal: ${this.formatCurrency(purchase.total_amount)}`);
        },
        formatCurrency(value) {
            if (value === null || value === undefined) return '৳0.00';
            return '৳' + new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        },
        getStatusColor(status) {
            const colors = {
                'draft': 'grey',
                'pending': 'warning',
                'partial': 'info',
                'paid': 'success',
                'cancelled': 'error'
            };
            return colors[status] || 'default';
        },
        onPerPageChange() {
            this.resetPagination();
            this.loadPurchases();
        }
    }
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>

