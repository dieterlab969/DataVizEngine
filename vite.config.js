import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        react(),
        tailwindcss(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5000,
        strictPort: false,
        watch: {
            ignored: [
                '**/vendor/**',
                '**/storage/**',
                '**/bootstrap/cache/**',
                '**/.pythonlibs/**',
                '**/.cache/**',
                '**/venv/**',
                '**/node_modules/**',
            ],
            usePolling: false,
        },
        hmr: {
            clientPort: 443,
            host: process.env.REPL_SLUG ? `${process.env.REPL_SLUG}.${process.env.REPL_OWNER}.repl.co` : 'localhost',
        },
        proxy: {
            '/api': {
                target: 'http://localhost:8000',
                changeOrigin: true,
            },
            '/storage': {
                target: 'http://localhost:8000',
                changeOrigin: true,
            },
        },
    },
});
