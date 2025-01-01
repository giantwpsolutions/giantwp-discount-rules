import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig(({ mode }) => {
  const isProduction = mode === 'production';

  return {
    plugins: [vue()],
    base: isProduction ? './' : '/', // Use relative paths in production
    build: {
      outDir: 'dist', // Output directory
      emptyOutDir: true,
      rollupOptions: {
        input: path.resolve(__dirname, 'src/main.js'), // Entry file
        output: {
          assetFileNames: 'assets/[name][extname]', // No hash for assets
          entryFileNames: 'assets/main.js', // Fixed name for JS entry
          chunkFileNames: 'assets/[name].js', // Fixed name for JS chunks
        },
      },
    },
    server: {
      port: 5173,
      strictPort: true,
      proxy: {
        '/wp-admin': 'http://localhost/giantwpsolutions', // Adjust to your local WordPress setup
        '/wp-content': 'http://localhost/giantwpsolutions',
      },
    },
  };
});
