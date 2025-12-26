import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// Russian version dev config - uses Firebase router and main.firebase.js
// This is for local development of the Russian version
export default defineConfig({
  plugins: [vue()],
  base: '/',
  server: {
    port: 3000,
    open: true,
    proxy: {
      '/Apps/Printing/api': {
        target: 'https://noyanov.com',
        changeOrigin: true
      }
    }
  },
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
    rollupOptions: {
      input: resolve(__dirname, 'index.russian.dev.html')
    }
  }
})

