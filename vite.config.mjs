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
                'resources/views/components/layouts/auth.blade.php',
                'resources/views/home-staff.blade.php',
                'resources/views/home-client.blade.php',
                'resources/views/minhaconta/edit.blade.php',
                'resources/views/notifications/**',
                'resources/views/relatorios/horas/index.blade.php',
                'resources/views/relatorios/analista/index.blade.php',
                'resources/views/tickets/index.blade.php',
                'resources/views/tickets/my.blade.php',
                'resources/views/tickets/pendentes.blade.php',
                'resources/views/tickets/create.blade.php',
                'resources/views/tickets/edit.blade.php',
                'resources/views/tickets/show.blade.php',
                'resources/views/tickets/cliente/**',
                'resources/views/auth/**',
                'resources/views/cadastros/categorias/**',
                'resources/views/cadastros/empresas/index.blade.php',
                'resources/views/cadastros/empresas/create.blade.php',
                'resources/views/cadastros/empresas/edit.blade.php',
                'resources/views/cadastros/clientes/index.blade.php',
                'resources/views/cadastros/clientes/create.blade.php',
                'resources/views/cadastros/clientes/edit.blade.php',
                'resources/views/cadastros/usuarios/index.blade.php',
                'resources/views/cadastros/usuarios/create.blade.php',
                'resources/views/cadastros/usuarios/edit.blade.php',
                'resources/views/cadastros/setores/**',
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
