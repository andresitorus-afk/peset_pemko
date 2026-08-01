import { defineNuxtPlugin, addRouteMiddleware, navigateTo, useRuntimeConfig } from '#app'
import { useAuth } from '~/composables/useAuth'

export default defineNuxtPlugin(() => {
  addRouteMiddleware('auth', (to) => {
    const auth = useAuth()
    const config = useRuntimeConfig()
    const loginPath = '/auth/' + config.public.loginHash
    if (!auth.token.value && to.path.startsWith('/admin')) {
      return navigateTo(loginPath)
    }
    if (auth.token.value && to.path.startsWith('/auth/')) {
      return navigateTo('/admin')
    }
  }, { global: true })
})
