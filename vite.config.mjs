import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/modern.js'],
            refresh: [
                'app/Livewire/**',
                'resources/views/components/layouts/modern.blade.php',
                'resources/views/components/modern/**',
                'resources/views/livewire/**',
            ],
            hotFile: 'storage/vite.hot',
            buildDirectory: 'build',
        }),
        tailwindcss(),
    ],
    server: {
        port: 5173,
        strictPort: true,
    },
});
