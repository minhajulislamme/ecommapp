import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
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
    build: {
        rollupOptions: {
            external: ['fsevents'],
            output: {
                manualChunks(id) {
                    // Split large PDF and Excel libraries into separate chunks
                    if (id.includes('jspdf') || id.includes('pdf2pic')) {
                        return 'pdf-libs';
                    }
                    if (id.includes('exceljs') || id.includes('file-saver')) {
                        return 'excel-libs';
                    }
                    
                    // UI libraries
                    if (id.includes('vue') || id.includes('@inertiajs') || id.includes('@vueuse')) {
                        return 'ui-core';
                    }
                    
                    // Icon libraries
                    if (id.includes('lucide-vue-next')) {
                        return 'icons';
                    }
                    
                    // UI component libraries  
                    if (id.includes('reka-ui') || id.includes('class-variance-authority') || 
                        id.includes('clsx') || id.includes('tailwind-merge')) {
                        return 'ui-components';
                    }
                    
                    // Vendor chunk for other node_modules
                    if (id.includes('node_modules')) {
                        return 'vendor';
                    }
                }
            }
        },
        // Increase the chunk size warning limit to 1000kb to reduce warnings
        chunkSizeWarningLimit: 1000
    }
});
