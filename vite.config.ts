import { resolve } from 'node:path'
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

const basePath = process.env.BASE_PATH?.replace(/\/$/, '') ?? ''

export default defineConfig({
  plugins: [react()],
  base: basePath ? `${basePath}/` : '/',
  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
    },
  },
})
