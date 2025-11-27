/**
 * SweetAlert2 Plugin Configuration
 */
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';
import Swal from 'sweetalert2';

/**
 * Toast configuration for notifications
 */
export const toastConfig = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
});

/**
 * Setup SweetAlert2 and expose globally
 */
export function setupSweetAlert() {
    // Expose Swal and Toast globally for easy access
    window.Swal = Swal;
    window.Toast = toastConfig;
}

export { VueSweetalert2, Swal };
export default VueSweetalert2;

