<template>
    <v-app>
        <!-- Page Loader - Shows on initial page load/refresh -->
        <PageLoader />
        <!-- Router View - Layouts will be rendered here -->
        <router-view />
        <!-- Vue Progress Bar Component - Must be after router-view -->
        <vue-progress-bar></vue-progress-bar>
    </v-app>
</template>

<script>
import PageLoader from './PageLoader.vue';

export default {
    name: 'App',
    components: {
        PageLoader
    },
    mounted() {
        console.log('App component mounted.');

        // Check progress bar availability
        if (this.$Progress) {
            console.log('✓ Progress bar instance found:', this.$Progress);
            console.log('Progress bar methods:', Object.keys(this.$Progress));

            // Test progress bar
            setTimeout(() => {
                console.log('Testing progress bar...');
                this.$Progress.start();
                setTimeout(() => {
                    this.$Progress.finish();
                    console.log('✓ Progress bar test completed');
                }, 1000);
            }, 300);
        } else {
            console.error('✗ Progress bar instance NOT found on $Progress');
            console.log('Available on this:', Object.keys(this));
        }

        // Check if component is registered
        const app = this.$root;
        if (app && app.$options && app.$options.components) {
            console.log('Registered components:', Object.keys(app.$options.components));
        }
    }
}
</script>

<style>
/* Global styles for progress bar - not scoped */
vue-progress-bar,
.vue-progressbar {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    z-index: 9999 !important;
    height: 4px !important;
    pointer-events: none !important;
}

.vue-progressbar__bar {
    z-index: 10000 !important;
    height: 100% !important;
    background: #66FE5E !important;
    transition: width 0.2s ease, opacity 0.6s ease !important;
}

.vue-progressbar__bar--failed {
    background: #f44336 !important;
}
</style>
