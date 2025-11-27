/**
 * Vuetify Plugin Configuration
 */
import 'vuetify/styles';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';

/**
 * Create and export Vuetify instance
 */
export function createVuetifyInstance() {
    return createVuetify({
        components,
        directives,
    });
}

export default createVuetifyInstance();

