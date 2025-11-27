/**
 * Debounce function to limit the rate at which a function can fire
 * @param {Function} fn - Function to debounce
 * @param {number} delay - Delay in milliseconds
 * @returns {Function} Debounced function
 */
export function debounce(fn, delay = 300) {
    let timeoutId = null;
    
    return function (...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => {
            fn.apply(this, args);
        }, delay);
    };
}

/**
 * Vue composable for debounced ref
 * @param {any} initialValue - Initial value
 * @param {number} delay - Delay in milliseconds
 * @returns {Object} { value, debouncedValue, setDebouncedValue }
 */
export function useDebouncedRef(initialValue, delay = 300) {
    const { ref, customRef } = require('vue');
    
    return customRef((track, trigger) => {
        let timeout;
        let value = initialValue;
        
        return {
            get() {
                track();
                return value;
            },
            set(newValue) {
                clearTimeout(timeout);
                timeout = setTimeout(() => {
                    value = newValue;
                    trigger();
                }, delay);
            }
        };
    });
}



