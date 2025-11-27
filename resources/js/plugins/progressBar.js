/**
 * Vue Progress Bar Plugin Configuration
 */
import VueProgressBar from "@aacassandra/vue3-progressbar";

/**
 * Progress bar configuration options
 */
export const progressBarOptions = {
    color: '#66FE5E',
    failedColor: '#f44336',
    thickness: "4px",
    transition: {
        speed: "0.2s",
        opacity: "0.6s",
        termination: 300,
    },
    autoRevert: true,
    location: "top", // left, right, top, bottom
    inverse: false,
};

/**
 * Setup progress bar plugin helper for router
 */
export function setupProgressBarHelper(app, router) {
    // Export router with app instance for progress bar access
    router.app = app;

    // Helper function to access progress bar from router
    router.getProgressBar = function () {
        if (this.app && this.app.config && this.app.config.globalProperties) {
            const progressBar = this.app.config.globalProperties.$Progress;
            return progressBar;
        }
        return null;
    };

    // Store progress bar access on window for easy access
    window.getProgressBar = () => {
        if (router.getProgressBar) {
            return router.getProgressBar();
        }
        return null;
    };

    // Debug: Check if progress bar is available after mount
    if (process.env.NODE_ENV === 'development') {
        setTimeout(() => {
            const progressBar = router.getProgressBar();
            if (progressBar) {
                console.log('✓ Progress bar initialized:', Object.keys(progressBar));
            } else {
                console.warn('✗ Progress bar not available');
            }
        }, 500);
    }
}

export default VueProgressBar;

