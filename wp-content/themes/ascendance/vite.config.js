import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    outDir: 'assets/dist',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        theme: resolve(__dirname, 'style.css'),
        main: resolve(__dirname, 'assets/js/main.js'),
        pages: resolve(__dirname, 'assets/js/pages.js'),
        pages_css: resolve(__dirname, 'assets/css/pages.css')
      },
      output: {
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/[name].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name && assetInfo.name.endsWith('.css')) {
            const name = assetInfo.name.includes('pages_css') ? 'pages' : '[name]';
            return `css/${name}.[ext]`;
          }
          return '[name].[ext]';
        }
      }
    },
    minify: 'esbuild'
  }
});
