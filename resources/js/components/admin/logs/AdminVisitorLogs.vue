<template>
    <div>
        <div class="page-header">
            <h1 class="text-h4 page-title">Visitor Log Management</h1>
            <v-btn color="error" prepend-icon="mdi-delete" @click="deleteSelected"
                :disabled="selectedLogs.length === 0">
                Delete Selected ({{ selectedLogs.length }})
            </v-btn>
        </div>

        <!-- Statistics Cards -->
        <v-row class="mb-4">
            <v-col cols="12" md="3">
                <v-card>
                    <v-card-text>
                        <div class="d-flex align-center">
                            <v-icon color="primary" size="40" class="mr-3">mdi-account-group</v-icon>
                            <div>
                                <div class="text-h6">{{ statistics.total || 0 }}</div>
                                <div class="text-caption text-grey">Total Visits</div>
                            </div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" md="3">
                <v-card>
                    <v-card-text>
                        <div class="d-flex align-center">
                            <v-icon color="success" size="40" class="mr-3">mdi-account</v-icon>
                            <div>
                                <div class="text-h6">{{ statistics.human_visits || 0 }}</div>
                                <div class="text-caption text-grey">Human Visits</div>
                            </div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" md="3">
                <v-card>
                    <v-card-text>
                        <div class="d-flex align-center">
                            <v-icon color="warning" size="40" class="mr-3">mdi-robot</v-icon>
                            <div>
                                <div class="text-h6">{{ statistics.bot_visits || 0 }}</div>
                                <div class="text-caption text-grey">Bot Visits</div>
                            </div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
            <v-col cols="12" md="3">
                <v-card>
                    <v-card-text>
                        <div class="d-flex align-center">
                            <v-icon color="info" size="40" class="mr-3">mdi-web</v-icon>
                            <div>
                                <div class="text-h6">{{ statistics.unique_ips || 0 }}</div>
                                <div class="text-caption text-grey">Unique IPs</div>
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
                    <v-col cols="12" md="2">
                        <v-select v-model="perPage" :items="perPageOptions" label="Items per page"
                            prepend-inner-icon="mdi-format-list-numbered" variant="outlined" density="compact"
                            @update:model-value="onPerPageChange"></v-select>
                    </v-col>
                    <v-col cols="12" md="2">
                        <v-select v-model="deviceFilter" :items="deviceOptions" label="Device Type"
                            prepend-inner-icon="mdi-devices" variant="outlined" density="compact" clearable
                            @update:model-value="loadLogs"></v-select>
                    </v-col>
                    <v-col cols="12" md="2">
                        <v-select v-model="browserFilter" :items="browserOptions" label="Browser"
                            prepend-inner-icon="mdi-web" variant="outlined" density="compact" clearable
                            @update:model-value="loadLogs"></v-select>
                    </v-col>
                    <v-col cols="12" md="2">
                        <v-select v-model="botFilter" :items="botOptions" label="Bot Filter"
                            prepend-inner-icon="mdi-robot" variant="outlined" density="compact" clearable
                            @update:model-value="loadLogs"></v-select>
                    </v-col>
                    <v-col cols="12" md="4">
                        <v-text-field v-model="search" label="Search (IP, URL, user agent)"
                            prepend-inner-icon="mdi-magnify" variant="outlined" density="compact" clearable
                            @update:model-value="loadLogs"></v-text-field>
                    </v-col>
                </v-row>
            </v-card-text>
        </v-card>

        <!-- Visitor Logs Table -->
        <v-card>
            <v-card-title class="d-flex justify-space-between align-center">
                <span>Visitor Logs</span>
                <span class="text-caption text-grey">
                    Total Records: <strong>{{ (pagination.total || 0).toLocaleString() }}</strong>
                </span>
            </v-card-title>
            <v-card-text>
                <v-table>
                    <thead>
                        <tr>
                            <th>
                                <v-checkbox v-model="selectAll" @change="toggleSelectAll"
                                    density="compact"></v-checkbox>
                            </th>
                            <th class="sortable" @click="onSort('ip_address')">
                                <div class="d-flex align-center">
                                    IP Address
                                    <v-icon :icon="getSortIcon('ip_address')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th class="sortable" @click="onSort('url')">
                                <div class="d-flex align-center">
                                    URL
                                    <v-icon :icon="getSortIcon('url')" size="small" class="ml-1"></v-icon>
                                </div>
                            </th>
                            <th>Device</th>
                            <th>Browser</th>
                            <th>OS</th>
                            <th>Referer</th>
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
                                <v-skeleton-loader type="text" width="24" height="24"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="chip" width="120" height="24"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="200"></v-skeleton-loader>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <v-skeleton-loader type="chip" width="80" height="24"
                                        class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="chip" width="40" height="20"></v-skeleton-loader>
                                </div>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="100"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="100"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="150"></v-skeleton-loader>
                            </td>
                            <td>
                                <v-skeleton-loader type="text" width="140"></v-skeleton-loader>
                            </td>
                            <td>
                                <div class="d-flex">
                                    <v-skeleton-loader type="button" width="32" height="32"
                                        class="mr-1"></v-skeleton-loader>
                                    <v-skeleton-loader type="button" width="32" height="32"></v-skeleton-loader>
                                </div>
                            </td>
                        </tr>
                        <!-- Actual Log Data -->
                        <template v-else>
                            <tr v-for="log in logs" :key="log.id">
                                <td>
                                    <v-checkbox :value="log.id" v-model="selectedLogs" density="compact"></v-checkbox>
                                </td>
                                <td>
                                    <v-chip size="small" variant="outlined">{{ log.ip_address || '-' }}</v-chip>
                                </td>
                                <td>
                                    <span class="text-caption" :title="log.url">
                                        {{ truncateText(log.url, 40) }}
                                    </span>
                                </td>
                                <td>
                                    <v-chip size="small" :color="getDeviceColor(log.device_type)">
                                        {{ log.device_type || '-' }}
                                    </v-chip>
                                    <v-chip v-if="log.is_bot" size="x-small" color="warning" class="ml-1">Bot</v-chip>
                                </td>
                                <td>{{ log.browser || '-' }}</td>
                                <td>{{ log.os || '-' }}</td>
                                <td>
                                    <span v-if="log.referer" class="text-caption" :title="log.referer">
                                        {{ truncateText(log.referer, 30) }}
                                    </span>
                                    <span v-else class="text-grey">-</span>
                                </td>
                                <td>{{ formatDate(log.created_at) }}</td>
                                <td>
                                    <v-btn size="small" icon="mdi-eye" @click="viewLog(log)" variant="text"></v-btn>
                                    <v-btn size="small" icon="mdi-delete" @click="deleteLog(log)" variant="text"
                                        color="error"></v-btn>
                                </td>
                            </tr>
                            <tr v-if="logs.length === 0">
                                <td colspan="9" class="text-center py-4">No visitor logs found</td>
                            </tr>
                        </template>
                    </tbody>
                </v-table>

                <!-- Pagination and Records Info -->
                <div
                    class="d-flex flex-column flex-md-row justify-space-between align-center align-md-start gap-3 mt-4">
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
                        <v-pagination v-model="currentPage" :length="pagination.last_page" :total-visible="7"
                            density="comfortable" @update:model-value="loadLogs">
                        </v-pagination>
                    </div>
                </div>
            </v-card-text>
        </v-card>

        <!-- View Log Dialog -->
        <v-dialog v-model="viewDialog" max-width="800">
            <v-card v-if="selectedLog">
                <v-card-title>Visitor Log Details</v-card-title>
                <v-card-text>
                    <v-row>
                        <v-col cols="12" md="6">
                            <div class="mb-3">
                                <strong>IP Address:</strong> {{ selectedLog.ip_address || '-' }}
                            </div>
                            <div class="mb-3">
                                <strong>URL:</strong>
                                <div class="mt-1">
                                    <a :href="selectedLog.url" target="_blank" class="text-primary">{{ selectedLog.url
                                    }}</a>
                                </div>
                            </div>
                            <div class="mb-3">
                                <strong>Referer:</strong>
                                <div class="mt-1">
                                    <a v-if="selectedLog.referer" :href="selectedLog.referer" target="_blank"
                                        class="text-primary">{{ selectedLog.referer }}</a>
                                    <span v-else class="text-grey">-</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <strong>Method:</strong> {{ selectedLog.method || 'GET' }}
                            </div>
                        </v-col>
                        <v-col cols="12" md="6">
                            <div class="mb-3">
                                <strong>Device Type:</strong>
                                <v-chip size="small" :color="getDeviceColor(selectedLog.device_type)" class="ml-2">
                                    {{ selectedLog.device_type || '-' }}
                                </v-chip>
                                <v-chip v-if="selectedLog.is_bot" size="small" color="warning" class="ml-2">Bot</v-chip>
                            </div>
                            <div class="mb-3">
                                <strong>Browser:</strong> {{ selectedLog.browser || '-' }}
                            </div>
                            <div class="mb-3">
                                <strong>OS:</strong> {{ selectedLog.os || '-' }}
                            </div>
                            <div class="mb-3">
                                <strong>Page Views:</strong> {{ selectedLog.page_views || 1 }}
                            </div>
                            <div class="mb-3">
                                <strong>Created At:</strong> {{ formatDate(selectedLog.created_at) }}
                            </div>
                        </v-col>
                        <v-col cols="12">
                            <div class="mb-2">
                                <strong>User Agent:</strong>
                            </div>
                            <v-textarea :value="selectedLog.user_agent || '-'" readonly variant="outlined"
                                density="compact" rows="3"></v-textarea>
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
                human_visits: 0,
                bot_visits: 0,
                unique_ips: 0,
            },
            deviceFilter: null,
            browserFilter: null,
            botFilter: null,
            deviceOptions: [
                { title: 'Desktop', value: 'desktop' },
                { title: 'Mobile', value: 'mobile' },
                { title: 'Tablet', value: 'tablet' },
            ],
            browserOptions: [],
            botOptions: [
                { title: 'Human Only', value: 'false' },
                { title: 'Bots Only', value: 'true' },
            ],
            viewDialog: false,
            selectedLog: null,
            selectedLogs: [],
            selectAll: false,
        };
    },
    async mounted() {
        await this.loadStatistics();
        await this.loadLogs();
    },
    methods: {
        async loadStatistics() {
            try {
                const response = await this.$axios.get('/api/v1/visitor-logs/statistics', {
                    headers: this.getAuthHeaders()
                });
                this.statistics = response.data;

                // Populate browser options from statistics
                if (response.data.browser_stats) {
                    this.browserOptions = Object.keys(response.data.browser_stats).map(browser => ({
                        title: browser,
                        value: browser
                    }));
                }
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

                if (this.deviceFilter) {
                    params.device_type = this.deviceFilter;
                }

                if (this.browserFilter) {
                    params.browser = this.browserFilter;
                }

                if (this.botFilter !== null) {
                    params.is_bot = this.botFilter;
                }

                const response = await this.$axios.get('/api/v1/visitor-logs', {
                    params,
                    headers: this.getAuthHeaders()
                });

                this.logs = response.data.data || [];
                this.updatePagination(response.data);
            } catch (error) {
                this.handleApiError(error, 'Failed to load visitor logs');
            } finally {
                this.loading = false;
            }
        },
        viewLog(log) {
            this.selectedLog = log;
            this.viewDialog = true;
        },
        async deleteLog(log) {
            if (!confirm(`Are you sure you want to delete this visitor log?`)) {
                return;
            }

            try {
                await this.$axios.delete(`/api/v1/visitor-logs/${log.id}`, {
                    headers: this.getAuthHeaders()
                });

                this.showSuccess('Visitor log deleted successfully');
                await this.loadStatistics();
                await this.loadLogs();
            } catch (error) {
                this.handleApiError(error, 'Error deleting visitor log');
            }
        },
        async deleteSelected() {
            if (this.selectedLogs.length === 0) {
                return;
            }

            if (!confirm(`Are you sure you want to delete ${this.selectedLogs.length} visitor log(s)?`)) {
                return;
            }

            try {
                await this.$axios.post('/api/v1/visitor-logs/delete-multiple', {
                    ids: this.selectedLogs
                }, {
                    headers: this.getAuthHeaders()
                });

                this.showSuccess(`${this.selectedLogs.length} visitor log(s) deleted successfully`);
                this.selectedLogs = [];
                this.selectAll = false;
                await this.loadStatistics();
                await this.loadLogs();
            } catch (error) {
                this.handleApiError(error, 'Error deleting visitor logs');
            }
        },
        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedLogs = this.logs.map(log => log.id);
            } else {
                this.selectedLogs = [];
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
        getDeviceColor(deviceType) {
            const colors = {
                'desktop': 'primary',
                'mobile': 'success',
                'tablet': 'info',
            };
            return colors[deviceType] || 'grey';
        },
        onPerPageChange() {
            this.resetPagination();
            this.loadLogs();
        },
        onSort(field) {
            this.handleSort(field);
            this.loadLogs();
        }
    },
    watch: {
        selectedLogs(newVal) {
            this.selectAll = newVal.length === this.logs.length && this.logs.length > 0;
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
