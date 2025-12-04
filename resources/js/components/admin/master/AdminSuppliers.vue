<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Supplier Management</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                Add New Supplier
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
                        <v-select v-model="activeFilter" :items="activeOptions" label="Filter by Status"
                            variant="outlined" density="compact" clearable @update:model-value="loadSuppliers"></v-select>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field v-model="search" label="Search by name, code, company, email, phone"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @input="loadSuppliers"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Suppliers Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Suppliers</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ pagination.total || 0 }}</strong>
                </span>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="150"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="150"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="150"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader></td>
                        </tr>
                        <template v-else>
                            <tr v-for="supplier in suppliers" :key="supplier.id">
                                <td>{{ supplier.code || '-' }}</td>
                                <td>{{ supplier.name }}</td>
                                <td>{{ supplier.company_name || '-' }}</td>
                                <td>{{ supplier.email || '-' }}</td>
                                <td>{{ supplier.phone || supplier.mobile || '-' }}</td>
                                <td>{{ supplier.city || '-' }}</td>
                                <td>
                                    <v-chip :color="supplier.current_balance >= 0 ? 'success' : 'error'" size="small">
                                        {{ formatCurrency(supplier.current_balance) }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-chip :color="supplier.is_active ? 'success' : 'error'" size="small">
                                        {{ supplier.is_active ? 'Active' : 'Inactive' }}
                                    </v-chip>
                                </td>
                                <td>
                                    <v-btn size="small" icon="mdi-eye" @click="viewSupplier(supplier)" variant="text"
                                        title="View"></v-btn>
                                    <v-btn size="small" icon="mdi-pencil" @click="openDialog(supplier)" variant="text"
                                        title="Edit"></v-btn>
                                    <v-btn size="small" icon="mdi-delete" @click="deleteSupplier(supplier)" variant="text"
                                        color="error" title="Delete"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="suppliers.length === 0">
                                <td colspan="9" class="text-center py-4">No suppliers found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <div class="text-caption text-grey">
                        <span v-if="suppliers.length > 0 && pagination.total > 0">
                            Showing <strong>{{ ((currentPage - 1) * perPage) + 1 }}</strong> to
                            <strong>{{ Math.min(currentPage * perPage, pagination.total) }}</strong> of
                            <strong>{{ pagination.total.toLocaleString() }}</strong> records
                        </span>
                        <span v-else>No records found</span>
                    </div>
                    <div v-if="pagination.last_page > 1" class="d-flex align-center gap-2">
                        <v-pagination v-model="currentPage" :length="pagination.last_page" :total-visible="7"
                            density="comfortable" @update:model-value="loadSuppliers">
                        </v-pagination>
                    </div>
                </div>
            </v-card-text>
        </v-card>

        <!-- Supplier Dialog -->
        <v-dialog v-model="dialog" max-width="800" scrollable persistent>
            <v-card>
                <v-card-title>
                    {{ editingSupplier ? 'Edit Supplier' : 'New Supplier' }}
                </v-card-title>
                <v-card-text>
                    <v-form ref="form" @submit.prevent="saveSupplier">
                        <v-tabs v-model="activeTab" bg-color="grey-lighten-4">
                            <v-tab value="basic">Basic Information</v-tab>
                            <v-tab value="contact">Contact Details</v-tab>
                            <v-tab value="financial">Financial</v-tab>
                        </v-tabs>

                        <v-window v-model="activeTab">
                            <!-- Basic Information Tab -->
                            <v-window-item value="basic">
                                <div class="pa-4">
                                    <v-text-field v-model="form.name" label="Name *" variant="outlined"
                                        :rules="[rules.required]" required class="mb-4"></v-text-field>

                                    <v-text-field v-model="form.code" label="Code" variant="outlined"
                                        hint="Auto-generated if left empty" persistent-hint class="mb-4"></v-text-field>

                                    <v-text-field v-model="form.company_name" label="Company Name" variant="outlined"
                                        class="mb-4"></v-text-field>

                                    <v-text-field v-model="form.tax_id" label="Tax ID" variant="outlined"
                                        class="mb-4"></v-text-field>

                                    <v-switch v-model="form.is_active" label="Active" color="success"
                                        class="mb-4"></v-switch>
                                </div>
                            </v-window-item>

                            <!-- Contact Details Tab -->
                            <v-window-item value="contact">
                                <div class="pa-4">
                                    <v-text-field v-model="form.email" label="Email" type="email" variant="outlined"
                                        :rules="[rules.email]" class="mb-4"></v-text-field>

                                    <v-text-field v-model="form.phone" label="Phone" variant="outlined"
                                        class="mb-4"></v-text-field>

                                    <v-text-field v-model="form.mobile" label="Mobile" variant="outlined"
                                        class="mb-4"></v-text-field>

                                    <v-textarea v-model="form.address" label="Address" variant="outlined" rows="2"
                                        class="mb-4"></v-textarea>

                                    <v-row>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model="form.city" label="City" variant="outlined"></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model="form.state" label="State" variant="outlined"></v-text-field>
                                        </v-col>
                                    </v-row>

                                    <v-row>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model="form.country" label="Country" variant="outlined"></v-text-field>
                                        </v-col>
                                        <v-col cols="12" md="6">
                                            <v-text-field v-model="form.postal_code" label="Postal Code"
                                                variant="outlined"></v-text-field>
                                        </v-col>
                                    </v-row>
                                </div>
                            </v-window-item>

                            <!-- Financial Tab -->
                            <v-window-item value="financial">
                                <div class="pa-4">
                                    <v-text-field v-model.number="form.opening_balance" label="Opening Balance"
                                        type="number" step="0.01" prefix="৳" variant="outlined" class="mb-4"></v-text-field>

                                    <v-textarea v-model="form.notes" label="Notes" variant="outlined" rows="4"
                                        class="mb-4"></v-textarea>
                                </div>
                            </v-window-item>
                        </v-window>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="closeDialog" variant="text">Cancel</v-btn>
                    <v-btn @click="saveSupplier" color="primary" :loading="saving">
                        {{ editingSupplier ? 'Update' : 'Create' }}
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
            suppliers: [],
            activeFilter: null,
            activeOptions: [
                { title: 'Active', value: true },
                { title: 'Inactive', value: false }
            ],
            dialog: false,
            editingSupplier: null,
            saving: false,
            activeTab: 'basic',
            form: {
                name: '',
                code: '',
                company_name: '',
                email: '',
                phone: '',
                mobile: '',
                address: '',
                city: '',
                state: '',
                country: '',
                postal_code: '',
                tax_id: '',
                opening_balance: 0,
                notes: '',
                is_active: true
            },
            rules: {
                required: value => !!value || 'This field is required',
                email: value => !value || /.+@.+\..+/.test(value) || 'Email must be valid'
            }
        };
    },
    async mounted() {
        await this.loadSuppliers();
    },
    methods: {
        async loadSuppliers() {
            try {
                this.loading = true;
                const params = this.buildPaginationParams();

                if (this.search) {
                    params.search = this.search;
                }
                if (this.activeFilter !== null) {
                    params.is_active = this.activeFilter;
                }

                const response = await this.$axios.get('/api/v1/suppliers', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.suppliers = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load suppliers');
            } finally {
                this.loading = false;
            }
        },
        openDialog(supplier) {
            if (supplier) {
                this.editingSupplier = supplier;
                this.activeTab = 'basic';
                this.form = {
                    name: supplier.name,
                    code: supplier.code || '',
                    company_name: supplier.company_name || '',
                    email: supplier.email || '',
                    phone: supplier.phone || '',
                    mobile: supplier.mobile || '',
                    address: supplier.address || '',
                    city: supplier.city || '',
                    state: supplier.state || '',
                    country: supplier.country || '',
                    postal_code: supplier.postal_code || '',
                    tax_id: supplier.tax_id || '',
                    opening_balance: supplier.opening_balance || 0,
                    notes: supplier.notes || '',
                    is_active: supplier.is_active !== undefined ? supplier.is_active : true
                };
            } else {
                this.editingSupplier = null;
                this.activeTab = 'basic';
                this.form = {
                    name: '',
                    code: '',
                    company_name: '',
                    email: '',
                    phone: '',
                    mobile: '',
                    address: '',
                    city: '',
                    state: '',
                    country: '',
                    postal_code: '',
                    tax_id: '',
                    opening_balance: 0,
                    notes: '',
                    is_active: true
                };
            }
            this.dialog = true;
        },
        closeDialog() {
            this.dialog = false;
            this.editingSupplier = null;
            this.activeTab = 'basic';
            this.form = {
                name: '',
                code: '',
                company_name: '',
                email: '',
                phone: '',
                mobile: '',
                address: '',
                city: '',
                state: '',
                country: '',
                postal_code: '',
                tax_id: '',
                opening_balance: 0,
                notes: '',
                is_active: true
            };
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
            }
        },
        async saveSupplier() {
            if (!this.$refs.form.validate()) {
                return;
            }

            this.saving = true;
            try {
                const token = localStorage.getItem('admin_token');
                const url = this.editingSupplier
                    ? `/api/v1/suppliers/${this.editingSupplier.id}`
                    : '/api/v1/suppliers';

                const method = this.editingSupplier ? 'put' : 'post';

                await axios[method](url, this.form, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess(
                    this.editingSupplier ? 'Supplier updated successfully' : 'Supplier created successfully'
                );
                this.closeDialog();
                await this.loadSuppliers();
            } catch (error) {
                this.handleApiError(error, 'Error saving supplier');
            } finally {
                this.saving = false;
            }
        },
        async deleteSupplier(supplier) {
            if (!confirm(`Delete supplier "${supplier.name}"?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.delete(`/api/v1/suppliers/${supplier.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Supplier deleted successfully');
                await this.loadSuppliers();
            } catch (error) {
                this.handleApiError(error, 'Error deleting supplier');
            }
        },
        viewSupplier(supplier) {
            alert(`Supplier: ${supplier.name}\nCompany: ${supplier.company_name || 'N/A'}\nEmail: ${supplier.email || 'N/A'}\nBalance: ${this.formatCurrency(supplier.current_balance)}`);
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
            this.loadSuppliers();
        }
    }
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>



