<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Purchase Orders</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                New Purchase Order
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
                            variant="outlined" density="compact" clearable @update:model-value="loadOrders"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="supplierFilter" :items="supplierOptions" label="Filter by Supplier"
                            variant="outlined" density="compact" clearable @update:model-value="loadOrders"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="search" label="Search by PO number"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @input="loadOrders"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Purchase Orders Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Purchase Orders</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ pagination.total || 0 }}</strong>
                </span>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th>PO Number</th>
                            <th>Supplier</th>
                            <th>Warehouse</th>
                            <th>Order Date</th>
                            <th>Status</th>
                            <th>Total Amount</th>
                            <th>Items</th>
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
                            <td><v-skeleton-loader type="text" width="60"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader></td>
                        </tr>
                        <template v-else>
                            <tr v-for="order in orders" :key="order.id">
                                <td>{{ order.po_number }}</td>
                                <td>{{ order.supplier?.name }}</td>
                                <td>{{ order.warehouse?.name }}</td>
                                <td>{{ formatDate(order.order_date) }}</td>
                                <td>
                                    <v-chip :color="getStatusColor(order.status)" size="small">
                                        {{ order.status }}
                                    </v-chip>
                                </td>
                                <td>{{ formatCurrency(order.total_amount) }}</td>
                                <td>{{ order.items?.length || 0 }}</td>
                                <td>
                                    <v-btn size="small" icon="mdi-eye" @click="viewOrder(order)" variant="text"
                                        title="View"></v-btn>
                                    <v-btn v-if="order.status === 'draft'" size="small" icon="mdi-pencil"
                                        @click="openDialog(order)" variant="text" title="Edit"></v-btn>
                                    <v-btn v-if="order.status === 'draft'" size="small" icon="mdi-delete"
                                        @click="deleteOrder(order)" variant="text" color="error"
                                        title="Delete"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="orders.length === 0">
                                <td colspan="8" class="text-center py-4">No purchase orders found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <div class="text-caption text-grey">
                        <span v-if="orders.length > 0 && pagination.total > 0">
                            Showing <strong>{{ ((currentPage - 1) * perPage) + 1 }}</strong> to
                            <strong>{{ Math.min(currentPage * perPage, pagination.total) }}</strong> of
                            <strong>{{ pagination.total.toLocaleString() }}</strong> records
                        </span>
                        <span v-else>No records found</span>
                    </div>
                    <div v-if="pagination.last_page > 1" class="d-flex align-center gap-2">
                        <v-pagination v-model="currentPage" :length="pagination.last_page" :total-visible="7"
                            density="comfortable" @update:model-value="loadOrders">
                        </v-pagination>
                    </div>
                </div>
            </v-card-text>
        </v-card>

        <!-- Purchase Order Dialog -->
        <v-dialog v-model="dialog" max-width="1000" scrollable persistent>
            <v-card>
                <v-card-title>
                    {{ editingOrder ? 'Edit Purchase Order' : 'New Purchase Order' }}
                </v-card-title>
                <v-card-text class="pa-0">
                    <v-form ref="form" @submit.prevent="saveOrder">
                        <div class="pa-6">
                            <v-row>
                                <v-col cols="12" md="6">
                                    <v-select v-model="form.supplier_id" :items="supplierOptions" item-title="label"
                                        item-value="value" label="Supplier" :rules="[rules.required]" required></v-select>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-select v-model="form.warehouse_id" :items="warehouseOptions" item-title="label"
                                        item-value="value" label="Warehouse" :rules="[rules.required]" required></v-select>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field v-model="form.order_date" label="Order Date" type="date"
                                        :rules="[rules.required]" required></v-text-field>
                                </v-col>
                                <v-col cols="12" md="6">
                                    <v-text-field v-model="form.expected_delivery_date" label="Expected Delivery Date"
                                        type="date"></v-text-field>
                                </v-col>
                                <v-col cols="12">
                                    <v-textarea v-model="form.notes" label="Notes" variant="outlined" rows="2"></v-textarea>
                                </v-col>
                            </v-row>

                            <!-- Order Items -->
                            <div class="mt-4">
                                <div class="d-flex justify-space-between align-center mb-2">
                                    <h3 class="text-h6">Order Items</h3>
                                    <v-btn size="small" color="primary" prepend-icon="mdi-plus"
                                        @click="addOrderItem">Add Item</v-btn>
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
                                                    @click="removeOrderItem(index)"></v-btn>
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
                        </div>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="closeDialog" variant="text">Cancel</v-btn>
                    <v-btn @click="saveOrder" color="primary" :loading="saving">
                        {{ editingOrder ? 'Update' : 'Create' }}
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
            orders: [],
            suppliers: [],
            supplierOptions: [],
            warehouses: [],
            warehouseOptions: [],
            products: [],
            productOptions: [],
            statusFilter: null,
            statusOptions: [
                { title: 'Draft', value: 'draft' },
                { title: 'Sent', value: 'sent' },
                { title: 'Confirmed', value: 'confirmed' },
                { title: 'Partial', value: 'partial' },
                { title: 'Completed', value: 'completed' },
                { title: 'Cancelled', value: 'cancelled' }
            ],
            supplierFilter: null,
            dialog: false,
            editingOrder: null,
            saving: false,
            form: {
                purchase_request_id: null,
                supplier_id: null,
                warehouse_id: null,
                order_date: new Date().toISOString().split('T')[0],
                expected_delivery_date: null,
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
            return this.calculatedSubtotal + this.calculatedTax;
        }
    },
    async mounted() {
        await this.loadSuppliers();
        await this.loadWarehouses();
        await this.loadProducts();
        await this.loadOrders();
    },
    methods: {
        async loadOrders() {
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

                const response = await this.$axios.get('/api/v1/purchase-orders', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.orders = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load purchase orders');
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
        addOrderItem() {
            this.form.items.push({
                product_id: null,
                quantity: 1,
                unit_price: 0,
                discount: 0,
                tax: 0,
                total: 0
            });
        },
        removeOrderItem(index) {
            this.form.items.splice(index, 1);
        },
        openDialog(order) {
            if (order) {
                this.editingOrder = order;
                this.form = {
                    purchase_request_id: order.purchase_request_id,
                    supplier_id: order.supplier_id,
                    warehouse_id: order.warehouse_id,
                    order_date: order.order_date,
                    expected_delivery_date: order.expected_delivery_date,
                    notes: order.notes || '',
                    items: order.items?.map(item => ({
                        product_id: item.product_id,
                        quantity: item.quantity,
                        unit_price: item.unit_price,
                        discount: item.discount || 0,
                        tax: item.tax || 0,
                        total: item.total || 0
                    })) || []
                };
            } else {
                this.editingOrder = null;
                this.form = {
                    purchase_request_id: null,
                    supplier_id: null,
                    warehouse_id: null,
                    order_date: new Date().toISOString().split('T')[0],
                    expected_delivery_date: null,
                    notes: '',
                    items: []
                };
            }
            this.dialog = true;
        },
        closeDialog() {
            this.dialog = false;
            this.editingOrder = null;
            this.form = {
                purchase_request_id: null,
                supplier_id: null,
                warehouse_id: null,
                order_date: new Date().toISOString().split('T')[0],
                expected_delivery_date: null,
                notes: '',
                items: []
            };
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
            }
        },
        async saveOrder() {
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
                const url = this.editingOrder
                    ? `/api/v1/purchase-orders/${this.editingOrder.id}`
                    : '/api/v1/purchase-orders';

                const method = this.editingOrder ? 'put' : 'post';

                await axios[method](url, this.form, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess(
                    this.editingOrder ? 'Purchase order updated successfully' : 'Purchase order created successfully'
                );
                this.closeDialog();
                await this.loadOrders();
            } catch (error) {
                this.handleApiError(error, 'Error saving purchase order');
            } finally {
                this.saving = false;
            }
        },
        async deleteOrder(order) {
            if (!confirm(`Delete purchase order ${order.po_number}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.delete(`/api/v1/purchase-orders/${order.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Purchase order deleted successfully');
                await this.loadOrders();
            } catch (error) {
                this.handleApiError(error, 'Error deleting purchase order');
            }
        },
        viewOrder(order) {
            alert(`PO: ${order.po_number}\nSupplier: ${order.supplier?.name}\nTotal: ${this.formatCurrency(order.total_amount)}`);
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
                'sent': 'info',
                'confirmed': 'primary',
                'partial': 'warning',
                'completed': 'success',
                'cancelled': 'error'
            };
            return colors[status] || 'default';
        },
        onPerPageChange() {
            this.resetPagination();
            this.loadOrders();
        }
    }
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>

