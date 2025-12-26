import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'
import { copyFileSync, existsSync } from 'fs'

// Production Firebase config - uses original router and main.js
// This is for the English/original version
export default defineConfig({
  plugins: [
    vue(),
    {
      name: 'copy-production-html',
      closeBundle() {
        // After build, copy index.html to dist (standard build)
        // This uses the regular index.html and main.js
      }
    }
  ],
  base: '/',
  server: {
    port: 3000
  },
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
    rollupOptions: {
      input: resolve(__dirname, 'index.html')
    }
  }
})

