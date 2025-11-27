/**
 * Utilities to normalize stored upload paths and resolve them to URLs
 * so images stay valid across environments/hosts.
 */

/**
 * Get the application base URL, honoring the api-base-url meta tag so
 * subdirectory deployments work.
 */
export function getAppBaseUrl() {
    const metaApiBase = document.querySelector('meta[name="api-base-url"]')?.getAttribute('content');
    if (metaApiBase) {
        try {
            const metaUrl = new URL(metaApiBase, window.location.origin);
            const basePath = metaUrl.pathname.replace(/\/api(\/v\d+)?\/?$/, '');
            return `${metaUrl.origin}${basePath}`;
        } catch (err) {
            console.warn('Invalid api-base-url meta tag, falling back to origin.', err);
        }
    }

    const origin = window.location.origin;
    const path = window.location.pathname || '/';
    const publicIndex = path.indexOf('/public');
    const basePath = publicIndex !== -1 ? path.slice(0, publicIndex + '/public'.length) : '';

    return `${origin}${basePath}`;
}

/**
 * Normalize an upload path for storage (strip host/leading slash).
 */
export function normalizeUploadPath(value) {
    if (!value) return '';

    const raw = String(value).trim();
    if (raw === '') return '';

    const uploadsPathPattern = /^\/?(uploads|storage)\/?/i;

    if (/^https?:\/\//i.test(raw)) {
        try {
            const url = new URL(raw);
            const path = (url.pathname || '').replace(/^\/+/, '');
            if (uploadsPathPattern.test(path)) {
                return stripStoragePrefix(path);
            }
            return raw;
        } catch {
            return raw;
        }
    }

    if (uploadsPathPattern.test(raw)) {
        return stripStoragePrefix(raw);
    }

    return raw.replace(/^\/+/, '');
}

/**
 * Resolve a stored upload path to a full URL on the current host.
 */
export function resolveUploadUrl(value, baseUrl = null) {
    if (!value) return '';
    if (typeof value !== 'string') return '';

    const raw = value.trim();
    if (raw === '') return '';

    if (/^(data:|\/\/)/i.test(raw)) {
        return raw;
    }

    const uploadsPathPattern = /^\/?(uploads|storage)\/?/i;
    const base = (baseUrl || getAppBaseUrl()).replace(/\/$/, '');

    if (/^https?:\/\//i.test(raw)) {
        try {
            const url = new URL(raw);
            const path = (url.pathname || '').replace(/^\/+/, '');
            if (uploadsPathPattern.test(path)) {
                const normalizedPath = stripStoragePrefix(path);
                return `${base}/${normalizedPath}`;
            }
            return raw;
        } catch {
            return raw;
        }
    }

    const normalizedPath = stripStoragePrefix(raw);
    const prefixed = normalizedPath.startsWith('/') ? normalizedPath : `/${normalizedPath}`;
    return `${base}${prefixed}`;
}

function stripStoragePrefix(path) {
    const trimmed = path.replace(/^\/+/, '');
    return trimmed.replace(/^storage\//i, '');
}
