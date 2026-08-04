import {defineConfig} from 'vite';
import {createHtmlPlugin} from 'vite-plugin-html'
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    server: {
        host: '127.0.0.1',
        cors: true,
        hmr: {
            host: '127.0.0.1',
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/front.js'],
            refresh: true,
        }),
        createHtmlPlugin({})
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
});
