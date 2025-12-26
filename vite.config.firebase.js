import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'
import { copyFileSync, existsSync } from 'fs'

// Firebase-specific config - serves from root
export default defineConfig({
  plugins: [
    vue(),
    {
      name: 'copy-firebase-html',
      closeBundle() {
        // After build, copy index.firebase.html to index.html in dist
        const src = resolve(__dirname, 'dist/index.firebase.html')
        const dest = resolve(__dirname, 'dist/index.html')
        if (existsSync(src)) {
          copyFileSync(src, dest)
        }
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
      input: resolve(__dirname, 'index.firebase.html')
    }
  }
})
