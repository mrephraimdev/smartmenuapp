import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/menu-client.js',
                'resources/js/kds.js',
                'resources/js/admin.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        // Code splitting for better performance
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['alpinejs', '@alpinejs/persist'],
                },
            },
        },
    },
});
