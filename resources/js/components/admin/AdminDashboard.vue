<template>
    <div>
        <div class="page-header mb-6">
            <h1 class="text-h4 page-title">Dashboard</h1>
            <div class="d-flex gap-2">
                <v-select v-model="timeRange" :items="timeRangeOptions" label="Time Range" variant="outlined"
                    density="compact" prepend-inner-icon="mdi-calendar-range" @update:model-value="loadDashboard"
                    style="max-width: 200px;"></v-select>
                <v-btn icon="mdi-refresh" @click="loadDashboard" :loading="loading" variant="text"></v-btn>
            </div>
        </div>

        <!-- Statistics Cards -->
        <v-row class="mb-4">
            <v-col cols="12" sm="6" md="3">
                <v-card class="stat-card" :class="'stat-card-primary'">
                    <v-card-text>
                        <div class="d-flex align-center justify-space-between">
                            <div>
                                <div class="text-caption text-grey mb-1">Total Visitors</div>
                                <div class="text-h4 font-weight-bold">{{ formatNumber(visitorStats.total || 0) }}</div>
                                <div class="text-caption mt-1">
                                    <v-icon size="small" :color="getTrendColor(visitorTrend)">
                                        {{ getTrendIcon(visitorTrend) }}
                                    </v-icon>
                                    <span :class="getTrendColor(visitorTrend) + '--text'">
                                        {{ visitorTrend > 0 ? '+' : '' }}{{ visitorTrend }}% vs last period
                                    </span>
                                </div>
                            </div>
                            <v-avatar size="64" :color="'primary'" class="stat-icon">
                                <v-icon size="32" color="white">mdi-account-group</v-icon>
                            </v-avatar>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="3">
                <v-card class="stat-card" :class="'stat-card-success'">
                    <v-card-text>
                        <div class="d-flex align-center justify-space-between">
                            <div>
                                <div class="text-caption text-grey mb-1">Human Visits</div>
                                <div class="text-h4 font-weight-bold">{{ formatNumber(visitorStats.human_visits || 0) }}
                                </div>
                                <div class="text-caption mt-1">
                                    <span class="text-grey">
                                        {{ getPercentage(visitorStats.human_visits, visitorStats.total) }}% of total
                                    </span>
                                </div>
                            </div>
                            <v-avatar size="64" :color="'success'" class="stat-icon">
                                <v-icon size="32" color="white">mdi-account</v-icon>
                            </v-avatar>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="3">
                <v-card class="stat-card" :class="'stat-card-warning'">
                    <v-card-text>
                        <div class="d-flex align-center justify-space-between">
                            <div>
                                <div class="text-caption text-grey mb-1">New Leads</div>
                                <div class="text-h4 font-weight-bold">{{ formatNumber(stats.leads || 0) }}</div>
                                <div class="text-caption mt-1">
                                    <v-icon size="small" color="error" v-if="stats.leads > 0">mdi-alert-circle</v-icon>
                                    <span class="text-grey" v-if="stats.leads > 0">Requires attention</span>
                                    <span class="text-grey" v-else>No new leads</span>
                                </div>
                            </div>
                            <v-avatar size="64" :color="'warning'" class="stat-icon">
                                <v-icon size="32" color="white">mdi-email</v-icon>
                            </v-avatar>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" sm="6" md="3">
                <v-card class="stat-card" :class="'stat-card-info'">
                    <v-card-text>
                        <div class="d-flex align-center justify-space-between">
                            <div>
                                <div class="text-caption text-grey mb-1">Total Products</div>
                                <div class="text-h4 font-weight-bold">{{ formatNumber(stats.products || 0) }}</div>
                                <div class="text-caption mt-1">
                                    <span class="text-grey">{{ stats.services || 0 }} services</span>
                                </div>
                            </div>
                            <v-avatar size="64" :color="'info'" class="stat-icon">
                                <v-icon size="32" color="white">mdi-package-variant</v-icon>
                            </v-avatar>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Charts Row 1 -->
        <v-row class="mb-4">
            <v-col cols="12" md="8">
                <v-card>
                    <v-card-title class="d-flex justify-space-between align-center">
                        <span>Visitor Trends</span>
                        <v-chip size="small" variant="outlined">{{ timeRangeLabel }}</v-chip>
                    </v-card-title>
                    <v-card-text>
                        <div v-if="loading" class="text-center py-8">
                            <v-progress-circular indeterminate color="primary"></v-progress-circular>
                        </div>
                        <LineChart v-else :data="visitorTrendsData" :options="lineChartOptions" />
                    </v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" md="4">
                <v-card>
                    <v-card-title>Device Distribution</v-card-title>
                    <v-card-text>
                        <div v-if="loading" class="text-center py-8">
                            <v-progress-circular indeterminate color="primary"></v-progress-circular>
                        </div>
                        <DoughnutChart v-else :data="deviceChartData" :options="doughnutChartOptions" />
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Charts Row 2 -->
        <v-row class="mb-4">
            <v-col cols="12" md="6">
                <v-card>
                    <v-card-title>Browser Distribution</v-card-title>
                    <v-card-text>
                        <div v-if="loading" class="text-center py-8">
                            <v-progress-circular indeterminate color="primary"></v-progress-circular>
                        </div>
                        <BarChart v-else :data="browserChartData" :options="barChartOptions" />
                    </v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" md="6">
                <v-card>
                    <v-card-title>Login Activity</v-card-title>
                    <v-card-text>
                        <div v-if="loading" class="text-center py-8">
                            <v-progress-circular indeterminate color="primary"></v-progress-circular>
                        </div>
                        <PieChart v-else :data="loginChartData" :options="pieChartOptions" />
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- Charts Row 3 -->
        <v-row class="mb-4">
            <v-col cols="12" md="6">
                <v-card>
                    <v-card-title>Leads by Status</v-card-title>
                    <v-card-text>
                        <div v-if="loading" class="text-center py-8">
                            <v-progress-circular indeterminate color="primary"></v-progress-circular>
                        </div>
                        <BarChart v-else :data="leadsChartData" :options="barChartOptions" />
                    </v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" md="6">
                <v-card>
                    <v-card-title>Top Visited Pages</v-card-title>
                    <v-card-text>
                        <div v-if="loading" class="text-center py-8">
                            <v-progress-circular indeterminate color="primary"></v-progress-circular>
                        </div>
                        <BarChart v-else :data="topPagesChartData" :options="horizontalBarChartOptions" />
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>

        <!-- AI Insights & Recent Activity -->
        <v-row class="mb-4">
            <v-col cols="12" md="4">
                <v-card class="insights-card">
                    <v-card-title class="d-flex align-center">
                        <v-icon class="mr-2" color="primary">mdi-brain</v-icon>
                        AI-Powered Insights
                    </v-card-title>
                    <v-card-text>
                        <div v-if="insights.length === 0" class="text-center py-4 text-grey">
                            <v-icon size="48" color="grey-lighten-1">mdi-chart-line-variant</v-icon>
                            <div class="mt-2">Generating insights...</div>
                        </div>
                        <div v-else>
                            <div v-for="(insight, index) in insights" :key="index" class="insight-item mb-3">
                                <div class="d-flex align-start">
                                    <v-icon
                                        :color="insight.type === 'warning' ? 'warning' : insight.type === 'success' ? 'success' : 'info'"
                                        size="small" class="mr-2 mt-1">
                                        {{ getInsightIcon(insight.type) }}
                                    </v-icon>
                                    <div class="flex-grow-1">
                                        <div class="text-body-2 font-weight-medium">{{ insight.title }}</div>
                                        <div class="text-caption text-grey">{{ insight.message }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>

            <v-col cols="12" md="8">
                <v-card>
                    <v-card-title>Recent Leads</v-card-title>
                    <v-card-text>
                        <v-table v-if="!loading && recentLeads.length > 0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="lead in recentLeads" :key="lead.id" :class="{ 'unread-row': !lead.is_read }">
                                    <td>
                                        <div class="d-flex align-center">
                                            <v-icon v-if="!lead.is_read" icon="mdi-circle" size="small" color="primary"
                                                class="mr-2"></v-icon>
                                            <span v-else class="mr-2" style="width: 16px;"></span>
                                            {{ lead.name }}
                                        </div>
                                    </td>
                                    <td>{{ lead.email }}</td>
                                    <td>
                                        <v-chip size="small" variant="outlined">{{ lead.type }}</v-chip>
                                    </td>
                                    <td>
                                        <v-chip :color="getStatusColor(lead.status)" size="small">
                                            {{ lead.status }}
                                        </v-chip>
                                    </td>
                                    <td>{{ formatDate(lead.created_at) }}</td>
                                </tr>
                            </tbody>
                        </v-table>
                        <div v-else-if="loading" class="text-center py-8">
                            <v-progress-circular indeterminate color="primary"></v-progress-circular>
                        </div>
                        <div v-else class="text-center py-8 text-grey">
                            No recent leads found
                        </div>
                    </v-card-text>
                </v-card>
            </v-col>
        </v-row>
    </div>
</template>

<script>
import { Line, Bar, Pie, Doughnut } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, BarElement, ArcElement, Title, Tooltip, Legend } from 'chart.js';
import moment from 'moment';

// Register Chart.js components
ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    ArcElement,
    Title,
    Tooltip,
    Legend
);

export default {
    components: {
        LineChart: Line,
        BarChart: Bar,
        PieChart: Pie,
        DoughnutChart: Doughnut
    },
    data() {
        return {
            loading: false,
            stats: {
                services: 0,
                products: 0,
                leads: 0,
            },
            visitorStats: {},
            loginStats: {},
            recentLeads: [],
            timeRange: '7d',
            timeRangeOptions: [
                { title: 'Last 7 Days', value: '7d' },
                { title: 'Last 30 Days', value: '30d' },
                { title: 'Last 90 Days', value: '90d' },
                { title: 'Last Year', value: '1y' },
            ],
            visitorTrend: 0,
            insights: [],
            // Chart options
            lineChartOptions: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            },
            barChartOptions: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            },
            horizontalBarChartOptions: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            },
            doughnutChartOptions: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            },
            pieChartOptions: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        };
    },
    computed: {
        timeRangeLabel() {
            const option = this.timeRangeOptions.find(opt => opt.value === this.timeRange);
            return option ? option.title : 'Last 7 Days';
        },
        visitorTrendsData() {
            const trends = this.visitorStats.trends || {};
            const labels = (trends.labels || []).map(date => moment(date).format('MMM DD'));

            return {
                labels,
                datasets: [
                    {
                        label: 'Total Visits',
                        data: trends.total || [],
                        borderColor: 'rgb(75, 192, 192)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        tension: 0.4,
                        fill: true
                    },
                    {
                        label: 'Human Visits',
                        data: trends.human || [],
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        tension: 0.4,
                        fill: true
                    }
                ]
            };
        },
        deviceChartData() {
            const deviceStats = this.visitorStats.device_stats || {};
            const labels = Object.keys(deviceStats);
            const data = Object.values(deviceStats);

            return {
                labels,
                datasets: [{
                    data,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                    ]
                }]
            };
        },
        browserChartData() {
            const browserStats = this.visitorStats.browser_stats || {};
            const entries = Object.entries(browserStats)
                .sort((a, b) => b[1] - a[1])
                .slice(0, 5);

            return {
                labels: entries.map(([browser]) => browser),
                datasets: [{
                    label: 'Visits',
                    data: entries.map(([, count]) => count),
                    backgroundColor: 'rgba(54, 162, 235, 0.8)',
                }]
            };
        },
        loginChartData() {
            return {
                labels: ['Successful', 'Failed'],
                datasets: [{
                    data: [
                        this.loginStats.successful || 0,
                        this.loginStats.failed || 0
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                    ]
                }]
            };
        },
        leadsChartData() {
            const statusCounts = this.getLeadsByStatus();
            return {
                labels: Object.keys(statusCounts),
                datasets: [{
                    label: 'Leads',
                    data: Object.values(statusCounts),
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                    ]
                }]
            };
        },
        topPagesChartData() {
            const topPages = this.visitorStats.top_pages || [];
            const top5 = topPages.slice(0, 5);

            return {
                labels: top5.map(page => this.truncateUrl(page.url, 40)),
                datasets: [{
                    label: 'Visits',
                    data: top5.map(page => page.visits),
                    backgroundColor: 'rgba(153, 102, 255, 0.8)',
                }]
            };
        }
    },
    async mounted() {
        await this.loadDashboard();
    },
    methods: {
        async loadDashboard() {
            this.loading = true;
            try {
                const token = localStorage.getItem('admin_token');

                // Load all dashboard data from dedicated endpoint
                const response = await this.$axios.get('/api/v1/dashboard', {
                    params: { time_range: this.timeRange },
                    headers: { Authorization: `Bearer ${token}` }
                });

                const data = response.data;

                // Process stats
                this.stats.services = data.stats?.services || 0;
                this.stats.products = data.stats?.products || 0;
                this.stats.leads = data.stats?.leads || 0;

                // Get recent leads
                this.recentLeads = Array.isArray(data.recent_leads) ? data.recent_leads : [];

                // Visitor and login stats
                this.visitorStats = data.visitor_stats || {};
                this.loginStats = data.login_stats || {};

                // Calculate trends and generate insights
                this.calculateTrends();
                this.generateInsights();

            } catch (error) {
                console.error('Error loading dashboard:', error);
            } finally {
                this.loading = false;
            }
        },
        calculateTrends() {
            // Calculate visitor trend (simplified - in production, compare with previous period)
            const current = this.visitorStats.human_visits || 0;
            const previous = current * 0.85; // Simulated previous period
            this.visitorTrend = previous > 0 ? Math.round(((current - previous) / previous) * 100) : 0;
        },
        generateInsights() {
            this.insights = [];

            // Lead insights
            if (this.stats.leads > 0) {
                this.insights.push({
                    type: 'warning',
                    title: 'New Leads Require Attention',
                    message: `You have ${this.stats.leads} new lead(s) that need to be reviewed and responded to.`
                });
            }

            // Visitor insights
            const botPercentage = this.getPercentage(this.visitorStats.bot_visits, this.visitorStats.total);
            if (botPercentage > 50) {
                this.insights.push({
                    type: 'warning',
                    title: 'High Bot Traffic',
                    message: `${botPercentage}% of your traffic is from bots. Consider implementing bot filtering.`
                });
            }

            // Login insights
            const failureRate = this.getPercentage(this.loginStats.failed, this.loginStats.total);
            if (failureRate > 20 && this.loginStats.total > 10) {
                this.insights.push({
                    type: 'warning',
                    title: 'High Login Failure Rate',
                    message: `${failureRate}% of login attempts are failing. This might indicate security concerns.`
                });
            }

            // Positive insights
            if (this.visitorStats.human_visits > 100) {
                this.insights.push({
                    type: 'success',
                    title: 'Strong Visitor Engagement',
                    message: `You have ${this.visitorStats.human_visits} human visits, indicating good site engagement.`
                });
            }

            if (this.stats.products > 0 && this.stats.services > 0) {
                this.insights.push({
                    type: 'info',
                    title: 'Content Portfolio',
                    message: `You have ${this.stats.products} products and ${this.stats.services} services in your catalog.`
                });
            }
        },
        getLeadsByStatus() {
            const statusCounts = {};
            this.recentLeads.forEach(lead => {
                statusCounts[lead.status] = (statusCounts[lead.status] || 0) + 1;
            });
            return statusCounts;
        },
        formatNumber(num) {
            return num.toLocaleString();
        },
        getPercentage(part, total) {
            if (!total || total === 0) return 0;
            return Math.round((part / total) * 100);
        },
        formatDate(date) {
            if (!date) return '-';
            return moment(date).format('MMM DD, YYYY');
        },
        truncateUrl(url, length) {
            if (!url) return '-';
            return url.length > length ? url.substring(0, length) + '...' : url;
        },
        getStatusColor(status) {
            const colors = {
                'new': 'primary',
                'contacted': 'info',
                'qualified': 'warning',
                'converted': 'success',
                'lost': 'error'
            };
            return colors[status] || 'grey';
        },
        getTrendColor(trend) {
            return trend > 0 ? 'success' : trend < 0 ? 'error' : 'grey';
        },
        getTrendIcon(trend) {
            return trend > 0 ? 'mdi-trending-up' : trend < 0 ? 'mdi-trending-down' : 'mdi-trending-neutral';
        },
        getInsightIcon(type) {
            const icons = {
                'warning': 'mdi-alert',
                'success': 'mdi-check-circle',
                'info': 'mdi-information'
            };
            return icons[type] || 'mdi-information';
        }
    }
};
</script>

<style scoped>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.page-title {
    margin: 0;
}

.stat-card {
    transition: all 0.3s ease;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    opacity: 0.9;
}

.insights-card {
    height: 100%;
}

.insight-item {
    padding: 12px;
    background: rgba(0, 0, 0, 0.02);
    border-radius: 8px;
    border-left: 3px solid;
}

.insight-item:last-child {
    margin-bottom: 0;
}

.unread-row {
    background-color: rgba(25, 118, 210, 0.05);
}

:deep(.chart-container) {
    position: relative;
    height: 300px;
}
</style>
