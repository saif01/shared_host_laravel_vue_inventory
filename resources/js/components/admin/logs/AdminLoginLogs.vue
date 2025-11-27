<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Login Log Management</h1>
        </div>

        <!-- Statistics Cards -->
        <v-row class="mb-4">
            <v-col cols="12" md="3">
                <v-card>
                    <v-card-text>
                        <div class="d-flex align-center">
                            <v-icon color="primary" size="40" class="mr-3">mdi-login</v-icon>
                            <div>
                                <div class="text-h6">{{ statistics.total || 0 }}</div>
                                <div class="text-caption text-grey">Total Logins</div>
                            </div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" md="3">
                <v-card>
                    <v-card-text>
                        <div class="d-flex align-center">
                            <v-icon color="success" size="40" class="mr-3">mdi-check-circle</v-icon>
                            <div>
                                <div class="text-h6">{{ statistics.successful || 0 }}</div>
                                <div class="text-caption text-grey">Successful</div>
                            </div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" md="3">
                <v-card>
                    <v-card-text>
                        <div class="d-flex align-center">
                            <v-icon color="error" size="40" class="mr-3">mdi-close-circle</v-icon>
                            <div>
                                <div class="text-h6">{{ statistics.failed || 0 }}</div>
                                <div class="text-caption text-grey">Failed</div>
                            </div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" md="3">
                <v-card>
                    <v-card-text>
                        <div class="d-flex align-center">
                            <v-icon color="info" size="40" class="mr-3">mdi-account-group</v-icon>
                            <div>
                                <div class="text-h6">{{ statistics.unique_users || 0 }}</div>
                                <div class="text-caption text-grey">Unique Users</div>
                            </div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" md="3">
                <v-card>
                    <v-card-text>
                        <div class="d-flex align-center">
                            <v-icon color="purple" size="40" class="mr-3">mdi-database</v-icon>
                            <div>
                                <div class="text-h6">{{ pagination.total || 0 }}</div>
                                <div class="text-caption text-grey">Total Records</div>
                            </div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

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
                            prepend-inner-icon="mdi-filter" variant="outlined" density="compact" clearable
                            @update:model-value="loadLogs"></v-select>
                    </v-col>
                    <v-col cols="12" md="6">
                        <v-text-field v-model="search" label="Search (email, IP, user agent)" prepend-inner-icon="mdi-magnify"
                            variant="outlined" density="compact" clearable
                            @update:model-value="loadLogs"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Login Logs Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Login Logs</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ pagination.total || 0 }}</strong>
                    <span v-if="logs.length > 0">
                        | Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage, pagination.total) }} of {{ pagination.total }}
                    </span>
                </span>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th class="sortable" @click="onSort('email')">
                                <div class="d-flex align-center">
                                    Email
                                    <v-icon :icon="getSortIcon('email')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th>User</th>
                            <th class="sortable" @click="onSort('ip_address')">
                                <div class="d-flex align-center">
                                    IP Address
                                    <v-icon :icon="getSortIcon('ip_address')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th>User Agent</th>
                            <th class="sortable" @click="onSort('status')">
                                <div class="d-flex align-center">
                                    Status
                                    <v-icon :icon="getSortIcon('status')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th>Failure Reason</th>
                            <th class="sortable" @click="onSort('created_at')">
                                <div class="d-flex align-center">
                                    Date
                                    <v-icon :icon="getSortIcon('created_at')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Skeleton Loaders -->
                        <tr v-if="loading" v-for="n in 5" :key="`skeleton-${n}`">
                            <td>
                                <v-skeleton-loader type="text" width="180"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="chip" width="100" height="24"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="chip" width="120" height="24"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="200"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="chip" width="80" height="24"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="120"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="140"></v-skeleton-loader>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <v-skeleton-loader type="button" width="32" height="32" class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader>
                                </div>
                            </td>
                        </tr>
                        <!-- Actual Log Data -->
                        <template v-else>
                            <tr v-for="log in logs" :key="log.id">
                                <td>{{ log.email }}</td>
                                <td>
                                    <v-chip v-if="log.user" size="small" color="primary" variant="text">
                                        {{ log.user.name }}
                                    </v-chip>
                                    <span v-else class="text-grey">-</span>
                                </td>
                                <td>
                                    <v-chip size="small" variant="outlined">{{ log.ip_address || '-' }}</v-chip>
                                </td>
                                <td>
                                    <span class="text-caption" :title="log.user_agent">
                                        {{ truncateText(log.user_agent, 50) }}
                                    </span>
                                </td>
                                <td>
                                    <v-chip :color="log.status === 'success' ? 'success' : 'error'" size="small">
                                        {{ log.status }}
                                    </v-chip>
                                </td>
                                <td>
                                    <span v-if="log.failure_reason" class="text-caption text-grey">
                                        {{ log.failure_reason }}
                                    </span>
                                    <span v-else>-</span>
                                </td>
                                <td>{{ formatDate(log.created_at) }}</td>
                                <td>
                                    <v-btn size="small" icon="mdi-eye" @click="viewLog(log)" variant="text"></v-btn>
                                    <v-btn size="small" icon="mdi-delete" @click="deleteLog(log)" variant="text" color="error"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="logs.length === 0">
                                <td colspan="8" class="text-center py-4">No login logs found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination and Records Info -->
                <div class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
                    <div class="text-caption text-grey">
                        <span v-if="logs.length > 0 && pagination.total > 0">
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
                        <v-pagination 
                            v-model="currentPage" 
                            :length="pagination.last_page"
                            :total-visible="7"
                            density="comfortable"
                            @update:model-value="loadLogs">
                        </v-pagination>
                    </div>
                </div>
            </v-card-text>
        </v-card>

        <!-- View Log Dialog -->
        <v-dialog v-model="viewDialog" max-width="700">
            <v-card v-if="selectedLog">
                <v-card-title>Login Log Details</v-card-title>
                <v-card-text>
                    <v-row>
                        <v-col cols="12" md="6">
                            <div class="mb-3">
                                <strong>Email:</strong> {{ selectedLog.email }}
                            </div>
                            <div class="mb-3">
                                <strong>User:</strong>
                                <v-chip v-if="selectedLog.user" size="small" color="primary" variant="text" class="ml-2">
                                    {{ selectedLog.user.name }} ({{ selectedLog.user.email }})
                                </v-chip>
                                <span v-else class="text-grey ml-2">-</span>
                            </div>
                            <div class="mb-3">
                                <strong>Status:</strong>
                                <v-chip :color="selectedLog.status === 'success' ? 'success' : 'error'" size="small" class="ml-2">
                                    {{ selectedLog.status }}
                                </v-chip>
                            </div>
                        </v-col>
                        <v-col cols="12" md="6">
                            <div class="mb-3">
                                <strong>IP Address:</strong> {{ selectedLog.ip_address || '-' }}
                            </div>
                            <div class="mb-3">
                                <strong>Failure Reason:</strong>
                                <span v-if="selectedLog.failure_reason" class="text-grey ml-2">{{ selectedLog.failure_reason }}</span>
                                <span v-else class="text-grey ml-2">-</span>
                            </div>
                            <div class="mb-3">
                                <strong>Logged In At:</strong>
                                <span v-if="selectedLog.logged_in_at" class="ml-2">{{ formatDate(selectedLog.logged_in_at) }}</span>
                                <span v-else class="text-grey ml-2">-</span>
                            </div>
                        </v-col>
                        <v-col cols="12">
                            <div class="mb-2">
                                <strong>User Agent:</strong>
                            </div>
                            <v-textarea :value="selectedLog.user_agent || '-'" readonly variant="outlined" density="compact" rows="3"></v-textarea>
                        </v-col>
                        <v-col cols="12">
                            <div class="mb-2">
                                <strong>Created At:</strong> {{ formatDate(selectedLog.created_at) }}
                            </div>
                        </v-col>
                    </v-row>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn @click="viewDialog = false" variant="text">Close</v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </div>
</template>

<script>
import adminPaginationMixin from '../../../mixins/adminPaginationMixin';
import moment from 'moment';

export default {
    mixins: [adminPaginationMixin],
    data() {
        return {
            logs: [],
            statistics: {
                total: 0,
                successful: 0,
                failed: 0,
                unique_users: 0,
                unique_ips: 0,
            },
            statusFilter: null,
            statusOptions: [
                { title: 'Success', value: 'success' },
                { title: 'Failed', value: 'failed' }
            ],
            viewDialog: false,
            selectedLog: null,
        };
    },
    async mounted() {
        await this.loadStatistics();
        await this.loadLogs();
    },
    methods: {
        async loadStatistics() {
            try {
                const response = await this.$axios.get('/api/v1/login-logs/statistics', {
                    headers: this.getAuthHeaders()
                });
                this.statistics = response.data;
            } catch (error) {
                console.error('Error loading statistics:', error);
            }
        },
        async loadLogs() {
            try {
                this.loading = true;
                const params = this.buildPaginationParams();

                if (this.search) {
                    params.search = this.search;
                }

                if (this.statusFilter) {
                    params.status = this.statusFilter;
                }

                const response = await this.$axios.get('/api/v1/login-logs', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.logs = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load login logs');
            } finally {
                this.loading = false;
            }
        },
        viewLog(log) {
            this.selectedLog = log;
            this.viewDialog = true;
        },
        async deleteLog(log) {
            if (!confirm(`Are you sure you want to delete this login log?`)) {
                return;
            }

            try {
                await this.$axios.delete(`/api/v1/login-logs/${log.id}`, {
                    headers: this.getAuthHeaders()
                });

                this.showSuccess('Login log deleted successfully');
                await this.loadStatistics();
                await this.loadLogs();
            } catch (error) {
                this.handleApiError(error, 'Error deleting login log');
            }
        },
        formatDate(date) {
            if (!date) return '-';
            return moment(date).format('YYYY-MM-DD HH:mm:ss');
        },
        truncateText(text, length) {
            if (!text) return '-';
            return text.length > length ? text.substring(0, length) + '...' : text;
        },
        onPerPageChange() {
            this.resetPagination();
            this.loadLogs();
        },
        onSort(field) {
            this.handleSort(field);
            this.loadLogs();
        }
    }
};
</script>

<style scoped>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.page-title {
    margin: 0;
}
</style>

