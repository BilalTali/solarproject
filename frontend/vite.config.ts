import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'
import { VitePWA } from 'vite-plugin-pwa'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    react(),
    VitePWA({
      registerType: 'autoUpdate',
      injectRegister: 'auto',
      manifest: {
        name: 'Andleeb Surya',
        short_name: 'AndleebSurya',
        description: 'AndleebSurya Registration Help — J&K and Ladakh',
        theme_color: '#FF9500',
        background_color: '#04111F',
        display: 'standalone',
        orientation: 'portrait',
        start_url: '/',
        icons: [
          {
            src: '/icons/icon-192.png',
            sizes: '192x192',
            type: 'image/png',
            purpose: 'any maskable'
          },
          {
            src: '/icons/icon-512.png',
            sizes: '512x512',
            type: 'image/png',
            purpose: 'any maskable'
          }
        ],
      },
      workbox: {
        navigateFallbackDenylist: [/^\/api/, /^\/storage/],
      }
    })
  ],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    port: 5173,
  },
  build: {
    sourcemap: false,
    chunkSizeWarningLimit: 1000,
    rollupOptions: {
      output: {
        manualChunks: {
          'vendor-react': ['react', 'react-dom', 'react-router-dom'],
          'vendor-ui': ['lucide-react', '@radix-ui/react-dialog', '@radix-ui/react-dropdown-menu', '@radix-ui/react-label', '@radix-ui/react-slot'],
          'vendor-utils': ['axios', '@tanstack/react-query', 'date-fns', 'clsx', 'tailwind-merge', 'zod', 'zustand'],
          'vendor-charts': ['recharts'],
        },
      },
    },
  },
  esbuild: {
    drop: ['console', 'debugger'],
  },
})
