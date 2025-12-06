import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

// Firebase-specific config - serves from root
export default defineConfig({
  plugins: [vue()],
  base: '/',
  server: {
    port: 3000
  },
  build: {
    outDir: 'dist',
    assetsDir: 'assets'
  }
})

