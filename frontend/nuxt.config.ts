// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2025-07-15',
  devtools: { enabled: true },
  modules: ['@nuxtjs/tailwindcss'],
  css: ['~/assets/css/dark.css'],
  pages: true,
  runtimeConfig: {
    public: {
      apiBase: process.env.NUXT_PUBLIC_API_BASE || 'http://localhost:8000',
      loginHash: process.env.NUXT_PUBLIC_LOGIN_HASH || '',
      reverbKey: process.env.NUXT_PUBLIC_REVERB_KEY || 'urkg7lwjohxtkxqm2jch',
      reverbHost: process.env.NUXT_PUBLIC_REVERB_HOST || 'localhost'
    }
  },
  app: {
    head: {
      link: [
        { rel: 'preload', as: 'image', href: '/logo-pemko.jpg', fetchpriority: 'high' },
        { rel: 'preconnect', href: 'https://fonts.googleapis.com' },
        { rel: 'preconnect', href: 'https://fonts.gstatic.com', crossorigin: '' },
        { rel: 'stylesheet', href: 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap', media: 'print', onload: 'this.media="all"' },
        { rel: 'stylesheet', href: 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', media: 'print', onload: 'this.media="all"' },
        { rel: 'stylesheet', href: 'https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css', media: 'print', onload: 'this.media="all"' }
      ],
      script: [
        { src: 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', defer: true },
        { src: 'https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js', defer: true }
      ]
    }
  }
})
