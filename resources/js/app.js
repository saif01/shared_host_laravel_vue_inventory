import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './routes';

// Utilities and Plugins
import { setupAxios } from './utils/axios.config';
import axios from './utils/axios.config';
import { createVuetifyInstance } from './plugins/vuetify';
import VueProgressBar, { progressBarOptions, setupProgressBarHelper } from './plugins/progressBar';
import VueSweetalert2, { setupSweetAlert } from './plugins/sweetalert';

// Stores
import { useAuthStore } from './stores/auth';

// Components
import App from './components/app.vue';

// Initialize axios configuration
setupAxios();

// Initialize SweetAlert
setupSweetAlert();

// Create Pinia instance
const pinia = createPinia();

// Create Vue app instance
const app = createApp(App);

// Make axios globally available as $axios
app.config.globalProperties.$axios = axios;

// Register plugins
app.use(pinia);
app.use(router);
app.use(createVuetifyInstance());
app.use(VueProgressBar, progressBarOptions);
app.use(VueSweetalert2);

// Setup progress bar helper for router hooks
setupProgressBarHelper(app, router);

// Initialize auth store
const authStore = useAuthStore();
authStore.init().catch(err => {
    console.error('Auth initialization error:', err);
});

// Mount the app
app.mount('#app');
