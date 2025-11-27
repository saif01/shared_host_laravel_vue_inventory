/**
 * Axios Configuration
 * Handles base URL setup, headers, interceptors, and error handling
 */
import axios from 'axios';
import router from '../routes';

/**
 * Configure axios base URL based on environment
 */
function configureBaseURL() {
    // Prefer meta tag (set in Blade) so subdirectory installs use the correct base
    const metaApiBase = document.querySelector('meta[name="api-base-url"]')?.getAttribute('content');
    if (metaApiBase) {
        try {
            const metaUrl = new URL(metaApiBase, window.location.origin);
            // Strip trailing /api or /api/vX so we get the actual app base path
            const basePath = metaUrl.pathname.replace(/\/api(\/v\d+)?\/?$/, '');
            axios.defaults.baseURL = `${metaUrl.origin}${basePath || ''}/`;
            return;
        } catch (err) {
            console.warn('Invalid api-base-url meta tag, falling back to origin.', err);
        }
    }

    // Fallback: include "/public" if the app is served from a subdirectory
    const origin = window.location.origin;
    const path = window.location.pathname || '/';
    const publicIndex = path.indexOf('/public');
    const basePath = publicIndex !== -1 ? path.slice(0, publicIndex + '/public'.length) : '';

    axios.defaults.baseURL = `${origin}${basePath}/`;
}

/**
 * Configure axios default headers
 */
function configureHeaders() {
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    axios.defaults.headers.common['Accept'] = 'application/json';
    axios.defaults.headers.common['Content-Type'] = 'application/json';
    axios.defaults.withCredentials = false;

    // Add CSRF token handling for Laravel
    const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
    if (csrfToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
    }
}

/**
 * Setup request interceptors
 */
function setupRequestInterceptors() {
    axios.interceptors.request.use(
        config => {
            // Add authentication token to requests
            const token = localStorage.getItem('admin_token');
            if (token) {
                config.headers.Authorization = `Bearer ${token}`;
            }
            return config;
        },
        error => {
            return Promise.reject(error);
        }
    );
}

/**
 * Setup response interceptors
 */
function setupResponseInterceptors() {
    axios.interceptors.response.use(
        response => response,
        error => {
            // Handle CORS errors
            if (!error.response) {
                if (error.code === 'ERR_NETWORK' ||
                    error.message.includes('CORS') ||
                    error.message.includes('Network Error')) {

                    console.error('CORS Error: Cross-Origin Request Blocked');
                    console.error('Solution: Access the application via HTTP server (e.g., http://localhost or your domain)');
                    console.error('Current URL:', window.location.href);

                    // Show user-friendly error
                    if (window.Swal) {
                        window.Swal.fire({
                            icon: 'error',
                            title: 'Connection Error',
                            text: 'Cannot connect to server. Please ensure you are accessing the application via HTTP (not file://) and the server is running.',
                            footer: 'If using XAMPP, access via: http://localhost/s_h_micro_control'
                        });
                    }
                }
            }

            // Handle 401 errors (Unauthorized)
            if (error.response && error.response.status === 401) {
                // Use auth store if available
                import('../stores/auth').then(({ useAuthStore }) => {
                    const authStore = useAuthStore();
                    authStore.logout();
                }).catch(() => {
                    // Fallback if store not available
                    localStorage.removeItem('admin_token');
                });
                router.push({ name: 'AdminLogin' });
            }

            return Promise.reject(error);
        }
    );
}

/**
 * Initialize axios configuration
 */
export function setupAxios() {
    configureBaseURL();
    configureHeaders();
    setupRequestInterceptors();
    setupResponseInterceptors();
    
    // Make axios globally available on window
    window.axios = axios;
}

export default axios;

