<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Stock Ledger</h1>
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
                        <v-select v-model="productFilter" :items="productOptions" label="Filter by Product"
                            variant="outlined" density="compact" clearable @update:model-value="loadLedger"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="warehouseFilter" :items="warehouseOptions" label="Filter by Warehouse"
                            variant="outlined" density="compact" clearable @update:model-value="loadLedger"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="typeFilter" :items="typeOptions" label="Filter by Type"
                            variant="outlined" density="compact" clearable @update:model-value="loadLedger"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="referenceTypeFilter" :items="referenceTypeOptions" label="Filter by Reference"
                            variant="outlined" density="compact" clearable @update:model-value="loadLedger"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="dateFrom" label="Date From" type="date" variant="outlined"
                            density="compact" @update:model-value="loadLedger"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="dateTo" label="Date To" type="date" variant="outlined"
                            density="compact" @update:model-value="loadLedger"></v-text-field>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="search" label="Search by reference number or product"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @input="loadLedger"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Stock Ledger Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Stock Movements</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ pagination.total || 0 }}</strong>
                    <span v-if="ledgers.length > 0">
                        | Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage,
                            pagination.total) }} of {{ pagination.total }}
                    </span>
                </span>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th class="sortable" @click="onSort('transaction_date')">
                                <div class="d-flex align-center">
                                    Date
                                    <v-icon :icon="getSortIcon('transaction_date')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th>Product</th>
                            <th>Warehouse</th>
                            <th>Type</th>
                            <th>Reference</th>
                            <th class="sortable" @click="onSort('quantity')">
                                <div class="d-flex align-center">
                                    Quantity
                                    <v-icon :icon="getSortIcon('quantity')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                            <th>Balance After</th>
                            <th>Created By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Skeleton Loaders -->
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="150"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="80"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                        </tr>
                        <!-- Actual Ledger Data -->
                        <template v-else>
                            <tr v-for="ledger in ledgers" :key="ledger.id">
                                <td>{{ formatDate(ledger.transaction_date) }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="font-weight-medium">{{ ledger.product?.name }}</span>
                                        <span class="text-caption text-grey">{{ ledger.product?.sku }}</span>
                                    </div>
                                </td>
                                <td>{{ ledger.warehouse?.name }}</td>
                                <td>
                                    <v-chip :color="ledger.type === 'in' ? 'success' : 'error'" size="small">
                                        {{ ledger.type === 'in' ? 'IN' : 'OUT' }}
                                    </v-chip>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-body-2">{{ formatReferenceType(ledger.reference_type) }}</span>
                                        <span class="text-caption text-grey">{{ ledger.reference_number || '-' }}</span>
                                    </div>
                                </td>
                                <td class="text-right">{{ ledger.quantity }}</td>
                                <td class="text-right">{{ formatCurrency(ledger.unit_cost) }}</td>
                                <td class="text-right">{{ formatCurrency(ledger.total_cost) }}</td>
                                <td class="text-right">
                                    <v-chip size="small" :color="ledger.balance_after < 0 ? 'error' : 'default'">
                                        {{ ledger.balance_after }}
                                    </v-chip>
                                </td>
                                <td>{{ ledger.creator?.name || '-' }}</td>
                            </tr>
                            <tr v-if="ledgers.length === 0">
                                <td colspan="10" class="text-center py-4">No stock movements found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <div class="text-caption text-grey">
                        <span v-if="ledgers.length > 0 && pagination.total > 0">
                            Showing <strong>{{ ((currentPage - 1) * perPage) + 1 }}</strong> to
                            <strong>{{ Math.min(currentPage * perPage, pagination.total) }}</strong> of
                            <strong>{{ pagination.total.toLocaleString() }}</strong> records
                        </span>
                        <span v-else>No records found</span>
                    </div>
                    <div v-if="pagination.last_page > 1" class="d-flex align-center gap-2">
                        <v-pagination v-model="currentPage" :length="pagination.last_page" :total-visible="7"
                            density="comfortable" @update:model-value="loadLedger">
                        </v-pagination>
                    </div>
                </div>
            </v-card-text>
        </v-card>
    </div>
</template>

<script>
import adminPaginationMixin from '../../../mixins/adminPaginationMixin';

export default {
    mixins: [adminPaginationMixin],
    data() {
        return {
            ledgers: [],
            products: [],
            warehouses: [],
            productFilter: null,
            productOptions: [],
            warehouseFilter: null,
            warehouseOptions: [],
            typeFilter: null,
            typeOptions: [
                { title: 'Stock In', value: 'in' },
                { title: 'Stock Out', value: 'out' }
            ],
            referenceTypeFilter: null,
            referenceTypeOptions: [
                { title: 'Purchase', value: 'purchase' },
                { title: 'Opening Stock', value: 'opening_stock' },
                { title: 'Adjustment', value: 'adjustment' },
                { title: 'Transfer In', value: 'transfer_in' },
                { title: 'Transfer Out', value: 'transfer_out' },
                { title: 'Sales', value: 'sales' },
                { title: 'Return', value: 'return' },
                { title: 'Damage', value: 'damage' },
                { title: 'Lost', value: 'lost' },
                { title: 'Purchase Return', value: 'purchase_return' }
            ],
            dateFrom: null,
            dateTo: null,
        };
    },
    async mounted() {
        await this.loadProducts();
        await this.loadWarehouses();
        await this.loadLedger();
    },
    methods: {
        async loadLedger() {
            try {
                this.loading = true;
                const params = this.buildPaginationParams();

                if (this.search) {
                    params.search = this.search;
                }
                if (this.productFilter) {
                    params.product_id = this.productFilter;
                }
                if (this.warehouseFilter) {
                    params.warehouse_id = this.warehouseFilter;
                }
                if (this.typeFilter) {
                    params.type = this.typeFilter;
                }
                if (this.referenceTypeFilter) {
                    params.reference_type = this.referenceTypeFilter;
                }
                if (this.dateFrom) {
                    params.date_from = this.dateFrom;
                }
                if (this.dateTo) {
                    params.date_to = this.dateTo;
                }

                const response = await this.$axios.get('/api/v1/stock-ledger', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.ledgers = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load stock ledger');
            } finally {
                this.loading = false;
            }
        },
        async loadProducts() {
            try {
                const response = await this.$axios.get('/api/v1/products', {
                    params: { per_page: 1000 },
                    headers: this.getAuthHeaders()
                });
                this.products = response.data.data || [];
                this.productOptions = this.products.map(p => ({
                    title: `${p.name} (${p.sku})`,
                    value: p.id
                }));
            } catch (error) {
                console.error('Error loading products:', error);
            }
        },
        async loadWarehouses() {
            try {
                const response = await this.$axios.get('/api/v1/stock-ledger/warehouses', {
                    headers: this.getAuthHeaders()
                });
                this.warehouses = response.data.warehouses || [];
                this.warehouseOptions = this.warehouses.map(w => ({
                    title: w.label,
                    value: w.value
                }));
            } catch (error) {
                console.error('Error loading warehouses:', error);
            }
        },
        formatReferenceType(type) {
            const types = {
                'purchase': 'Purchase',
                'opening_stock': 'Opening Stock',
                'adjustment': 'Adjustment',
                'transfer_in': 'Transfer In',
                'transfer_out': 'Transfer Out',
                'sales': 'Sales',
                'return': 'Return',
                'damage': 'Damage',
                'lost': 'Lost',
                'purchase_return': 'Purchase Return'
            };
            return types[type] || type;
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
            this.loadLedger();
        },
        onSort(field) {
            this.handleSort(field);
            this.loadLedger();
        },
    }
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>

