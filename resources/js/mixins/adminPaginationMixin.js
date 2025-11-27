/**
 * Admin Pagination Mixin
 * 
 * Provides common pagination functionality, utilities, and methods
 * for all admin pages to reduce code duplication.
 * 
 * This mixin includes:
 * - Pagination state management (currentPage, perPage, pagination object)
 * - Search functionality
 * - Loading/saving states
 * - Success/Error notification methods
 * - Date formatting utilities
 * - Authentication helpers
 * - API error handling
 * - Common helper methods
 */
export default {
    data() {
        return {
            // Pagination state
            currentPage: 1,
            perPage: 10,
            perPageOptions: [10, 25, 50, 100, 500],
            pagination: {
                current_page: 1,
                last_page: 1,
                per_page: 10,
                total: 0
            },

            // Common search state
            search: '',

            // Sorting state
            sortBy: null,
            sortDirection: 'asc', // 'asc' or 'desc'

            // Loading states
            loading: false,
            saving: false
        };
    },

    methods: {
        /**
         * Handle per page change - resets to page 1 and reloads data
         * Override loadData method in component to use this
         */
        onPerPageChange() {
            this.currentPage = 1;
            if (this.loadData) {
                this.loadData();
            }
        },

        /**
         * Show success notification
         * @param {string} message - Success message to display
         */
        showSuccess(message) {
            if (window.Toast) {
                window.Toast.fire({
                    icon: 'success',
                    title: message
                });
            } else {
                alert(message);
            }
        },

        /**
         * Show error notification
         * @param {string} message - Error message to display
         */
        showError(message) {
            if (window.Toast) {
                window.Toast.fire({
                    icon: 'error',
                    title: message
                });
            } else {
                alert(message);
            }
        },

        /**
         * Format date to locale string
         * @param {string|Date} date - Date to format
         * @returns {string} Formatted date string
         */
        formatDate(date) {
            if (!date) return '-';
            return new Date(date).toLocaleDateString();
        },

        /**
         * Format date with time
         * @param {string|Date} date - Date to format
         * @returns {string} Formatted date and time string
         */
        formatDateTime(date) {
            if (!date) return '-';
            return new Date(date).toLocaleString();
        },

        /**
         * Get authentication token from localStorage
         * @returns {string|null} Authentication token
         */
        getAuthToken() {
            return localStorage.getItem('admin_token');
        },

        /**
         * Get axios headers with authentication
         * @returns {Object} Headers object with Authorization
         */
        getAuthHeaders() {
            return {
                Authorization: `Bearer ${this.getAuthToken()}`
            };
        },

        /**
         * Build pagination params for API requests
         * @param {Object} additionalParams - Additional parameters to include
         * @returns {Object} Parameters object for API request
         */
        buildPaginationParams(additionalParams = {}) {
            const params = {
                page: this.currentPage,
                per_page: this.perPage,
                ...additionalParams
            };

            // Add sorting parameters if sortBy is set
            if (this.sortBy) {
                params.sort_by = this.sortBy;
                params.sort_direction = this.sortDirection;
            }

            return params;
        },

        /**
         * Update pagination state from API response
         * @param {Object} responseData - Response data from API
         */
        updatePagination(responseData) {
            if (responseData) {
                this.pagination = {
                    current_page: responseData.current_page || 1,
                    last_page: responseData.last_page || 1,
                    per_page: responseData.per_page || this.perPage,
                    total: responseData.total || 0
                };
            }
        },

        /**
         * Reset pagination to first page
         */
        resetPagination() {
            this.currentPage = 1;
        },

        /**
         * Handle API error with user-friendly messages
         * @param {Error} error - Error object from axios
         * @param {string} defaultMessage - Default error message
         */
        handleApiError(error, defaultMessage = 'An error occurred') {
            console.error('API Error:', error);

            let message = defaultMessage;

            if (error.response?.data?.errors) {
                // Handle validation errors
                const errors = error.response.data.errors;
                const errorMessages = [];
                Object.keys(errors).forEach(key => {
                    if (Array.isArray(errors[key])) {
                        errorMessages.push(`${key}: ${errors[key][0]}`);
                    } else {
                        errorMessages.push(`${key}: ${errors[key]}`);
                    }
                });
                message = errorMessages.join('\n');
            } else if (error.response?.data?.message) {
                message = error.response.data.message;
            } else if (error.message) {
                message = error.message;
            }

            this.showError(message);
        },

        /**
         * Handle table header sort click
         * @param {string} field - Field name to sort by
         */
        handleSort(field) {
            if (this.sortBy === field) {
                // Toggle direction if same field
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                // New field, default to ascending
                this.sortBy = field;
                this.sortDirection = 'asc';
            }
            // Reset to first page when sorting changes
            this.currentPage = 1;
        },

        /**
         * Get sort icon for table header
         * @param {string} field - Field name
         * @returns {string} Icon name
         */
        getSortIcon(field) {
            if (this.sortBy !== field) {
                return 'mdi-sort';
            }
            return this.sortDirection === 'asc' ? 'mdi-sort-ascending' : 'mdi-sort-descending';
        },

        /**
         * Reset sorting
         */
        resetSorting() {
            this.sortBy = null;
            this.sortDirection = 'asc';
        }
    }
};

