import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        // Force base path to /public/build/ for shared hosting
        // This ensures assets are always loaded from the public directory
        base: '/public/build/',

        server: {
            // Enable CORS for development
            cors: true,
            // Ensure the dev server is accessible
            host: '0.0.0.0',
            hmr: {
                host: 'localhost',
                protocol: 'ws',
            },
            // Allowed hosts for development
            allowedHosts: [
                'sh.test',
                'test-2.cpbfivestar.com',
                'localhost',
                '127.0.0.1',
            ],
            // CORS configuration
            headers: {
                'Access-Control-Allow-Origin': '*',
                'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, OPTIONS',
                'Access-Control-Allow-Headers': 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN',
            },
        },

        plugins: [
            laravel({
                input: [
                    'resources/sass/app.scss',
                    'resources/js/app.js',
                ],
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],

        resolve: {
            alias: {
                vue: 'vue/dist/vue.esm-bundler.js',
            },
        },

        css: {
            preprocessorOptions: {
                scss: {
                    quietDeps: true,
                    sassOptions: {
                        quiet: true,
                    },
                },
            },
        },

        build: {
            cssCodeSplit: true,
            rollupOptions: {
                output: {
                    // Organize assets neatly
                    assetFileNames: (assetInfo) => {
                        if (assetInfo.name && /\.(woff|woff2|eot|ttf|otf)$/.test(assetInfo.name)) {
                            return 'assets/fonts/[name]-[hash][extname]';
                        }
                        if (assetInfo.name && /\.(png|jpe?g|gif|svg|webp)$/.test(assetInfo.name)) {
                            return 'assets/images/[name]-[hash][extname]';
                        }
                        return 'assets/[name]-[hash][extname]';
                    },
                },
            },
        },
    };
});
