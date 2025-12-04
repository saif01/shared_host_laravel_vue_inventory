<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Purchase Requests</h1>
            <v-btn color="primary" prepend-icon="mdi-plus" @click="openDialog(null)" class="add-button">
                New Purchase Request
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
                            variant="outlined" density="compact" clearable @update:model-value="loadRequests"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-select v-model="warehouseFilter" :items="warehouseOptions" label="Filter by Warehouse"
                            variant="outlined" density="compact" clearable @update:model-value="loadRequests"></v-select>
                    </v-col>
                    <v-col cols="12" md="3">
                        <v-text-field v-model="search" label="Search by PR number"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @input="loadRequests"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Purchase Requests Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Purchase Requests</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ pagination.total || 0 }}</strong>
                </span>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th>PR Number</th>
                            <th>Warehouse</th>
                            <th>Request Date</th>
                            <th>Status</th>
                            <th>Requested By</th>
                            <th>Approved By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td><v-skeleton-loader type="text" width="120"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="text" width="100"></v-skeleton-loader></td>
                            <td><v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader></td>
                        </tr>
                        <template v-else>
                            <tr v-for="request in requests" :key="request.id">
                                <td>{{ request.pr_number }}</td>
                                <td>{{ request.warehouse?.name }}</td>
                                <td>{{ formatDate(request.request_date) }}</td>
                                <td>
                                    <v-chip :color="getStatusColor(request.status)" size="small">
                                        {{ request.status }}
                                    </v-chip>
                                </td>
                                <td>{{ request.requested_by?.name || '-' }}</td>
                                <td>{{ request.approved_by?.name || '-' }}</td>
                                <td>
                                    <v-btn size="small" icon="mdi-eye" @click="viewRequest(request)" variant="text"
                                        title="View"></v-btn>
                                    <v-btn v-if="request.status === 'pending'" size="small" icon="mdi-pencil"
                                        @click="openDialog(request)" variant="text" title="Edit"></v-btn>
                                    <v-btn v-if="request.status === 'pending'" size="small" icon="mdi-check"
                                        @click="approveRequest(request)" variant="text" color="success"
                                        title="Approve"></v-btn>
                                    <v-btn v-if="request.status === 'pending'" size="small" icon="mdi-close"
                                        @click="rejectRequest(request)" variant="text" color="error"
                                        title="Reject"></v-btn>
                                    <v-btn v-if="request.status === 'pending'" size="small" icon="mdi-delete"
                                        @click="deleteRequest(request)" variant="text" color="error"
                                        title="Delete"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="requests.length === 0">
                                <td colspan="7" class="text-center py-4">No purchase requests found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination -->
                <div class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <div class="text-caption text-grey">
                        <span v-if="requests.length > 0 && pagination.total > 0">
                            Showing <strong>{{ ((currentPage - 1) * perPage) + 1 }}</strong> to
                            <strong>{{ Math.min(currentPage * perPage, pagination.total) }}</strong> of
                            <strong>{{ pagination.total.toLocaleString() }}</strong> records
                        </span>
                        <span v-else>No records found</span>
                    </div>
                    <div v-if="pagination.last_page > 1" class="d-flex align-center gap-2">
                        <v-pagination v-model="currentPage" :length="pagination.last_page" :total-visible="7"
                            density="comfortable" @update:model-value="loadRequests">
                        </v-pagination>
                    </div>
                </div>
            </v-card-text>
        </v-card>

        <!-- Purchase Request Dialog -->
        <v-dialog v-model="dialog" max-width="600" scrollable persistent>
            <v-card>
                <v-card-title>
                    {{ editingRequest ? 'Edit Purchase Request' : 'New Purchase Request' }}
                </v-card-title>
                <v-card-text>
                    <v-form ref="form" @submit.prevent="saveRequest">
                        <v-select v-model="form.warehouse_id" :items="warehouseOptions" item-title="label"
                            item-value="value" label="Warehouse" :rules="[rules.required]" required class="mb-4"></v-select>

                        <v-text-field v-model="form.request_date" label="Request Date" type="date"
                            :rules="[rules.required]" required class="mb-4"></v-text-field>

                        <v-textarea v-model="form.notes" label="Notes" variant="outlined" rows="3"
                            class="mb-4"></v-textarea>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="closeDialog" variant="text">Cancel</v-btn>
                    <v-btn @click="saveRequest" color="primary" :loading="saving">
                        {{ editingRequest ? 'Update' : 'Create' }}
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
            requests: [],
            warehouses: [],
            warehouseOptions: [],
            statusFilter: null,
            statusOptions: [
                { title: 'Pending', value: 'pending' },
                { title: 'Approved', value: 'approved' },
                { title: 'Rejected', value: 'rejected' },
                { title: 'Converted', value: 'converted' }
            ],
            warehouseFilter: null,
            dialog: false,
            editingRequest: null,
            saving: false,
            form: {
                warehouse_id: null,
                request_date: new Date().toISOString().split('T')[0],
                notes: ''
            },
            rules: {
                required: value => !!value || 'This field is required'
            }
        };
    },
    async mounted() {
        await this.loadWarehouses();
        await this.loadRequests();
    },
    methods: {
        async loadRequests() {
            try {
                this.loading = true;
                const params = this.buildPaginationParams();

                if (this.search) {
                    params.search = this.search;
                }
                if (this.statusFilter) {
                    params.status = this.statusFilter;
                }
                if (this.warehouseFilter) {
                    params.warehouse_id = this.warehouseFilter;
                }

                const response = await this.$axios.get('/api/v1/purchase-requests', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.requests = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load purchase requests');
            } finally {
                this.loading = false;
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
        openDialog(request) {
            if (request) {
                this.editingRequest = request;
                this.form = {
                    warehouse_id: request.warehouse_id,
                    request_date: request.request_date,
                    notes: request.notes || ''
                };
            } else {
                this.editingRequest = null;
                this.form = {
                    warehouse_id: null,
                    request_date: new Date().toISOString().split('T')[0],
                    notes: ''
                };
            }
            this.dialog = true;
        },
        closeDialog() {
            this.dialog = false;
            this.editingRequest = null;
            this.form = {
                warehouse_id: null,
                request_date: new Date().toISOString().split('T')[0],
                notes: ''
            };
            if (this.$refs.form) {
                this.$refs.form.resetValidation();
            }
        },
        async saveRequest() {
            if (!this.$refs.form.validate()) {
                return;
            }

            this.saving = true;
            try {
                const token = localStorage.getItem('admin_token');
                const url = this.editingRequest
                    ? `/api/v1/purchase-requests/${this.editingRequest.id}`
                    : '/api/v1/purchase-requests';

                const method = this.editingRequest ? 'put' : 'post';

                await axios[method](url, this.form, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess(
                    this.editingRequest ? 'Purchase request updated successfully' : 'Purchase request created successfully'
                );
                this.closeDialog();
                await this.loadRequests();
            } catch (error) {
                this.handleApiError(error, 'Error saving purchase request');
            } finally {
                this.saving = false;
            }
        },
        async approveRequest(request) {
            if (!confirm(`Approve purchase request ${request.pr_number}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.post(`/api/v1/purchase-requests/${request.id}/approve`, {}, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Purchase request approved successfully');
                await this.loadRequests();
            } catch (error) {
                this.handleApiError(error, 'Error approving purchase request');
            }
        },
        async rejectRequest(request) {
            if (!confirm(`Reject purchase request ${request.pr_number}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.post(`/api/v1/purchase-requests/${request.id}/reject`, {}, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Purchase request rejected successfully');
                await this.loadRequests();
            } catch (error) {
                this.handleApiError(error, 'Error rejecting purchase request');
            }
        },
        async deleteRequest(request) {
            if (!confirm(`Delete purchase request ${request.pr_number}?`)) {
                return;
            }

            try {
                const token = localStorage.getItem('admin_token');
                await this.$axios.delete(`/api/v1/purchase-requests/${request.id}`, {
                    headers: { Authorization: `Bearer ${token}` }
                });

                this.showSuccess('Purchase request deleted successfully');
                await this.loadRequests();
            } catch (error) {
                this.handleApiError(error, 'Error deleting purchase request');
            }
        },
        viewRequest(request) {
            alert(`PR: ${request.pr_number}\nWarehouse: ${request.warehouse?.name}\nStatus: ${request.status}`);
        },
        getStatusColor(status) {
            const colors = {
                'pending': 'warning',
                'approved': 'success',
                'rejected': 'error',
                'converted': 'info'
            };
            return colors[status] || 'default';
        },
        onPerPageChange() {
            this.resetPagination();
            this.loadRequests();
        }
    }
};
</script>

<style scoped>
.gap-2 {
    gap: 8px;
}
</style>

