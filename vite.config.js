import { defineConfig } from 'vite'
import { resolve } from 'path'

export default defineConfig({
  root: './',
  base: './',
  publicDir: 'public',
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'public/index.html'),
      },
    },
  },
  css: {
    postcss: './postcss.config.js',
  },
  server: {
    port: 3000,
    open: true,
    host: true,
  },
  preview: {
    port: 4173,
  },
})
