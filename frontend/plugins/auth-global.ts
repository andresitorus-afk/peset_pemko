import { defineNuxtPlugin, addRouteMiddleware, navigateTo, useRuntimeConfig } from '#app'
import { useAuth } from '~/composables/useAuth'

export default defineNuxtPlugin(() => {
  addRouteMiddleware('auth', async (to) => {
    const auth = useAuth()
    const config = useRuntimeConfig()
    const loginPath = '/auth/' + config.public.loginHash

    if (to.path.startsWith('/admin')) {
      if (!auth.token.value) {
        return navigateTo(loginPath)
      }
      if (!auth.user.value) {
        await auth.fetchUser()
      }
      if (!auth.token.value) {
        return navigateTo(loginPath)
      }
      if (!auth.isSuperAdmin.value && ['/admin/users', '/admin/roles'].includes(to.path)) {
        return navigateTo('/admin')
      }
    }

    if (auth.token.value && to.path.startsWith('/auth/')) {
      return navigateTo('/admin')
    }
  }, { global: true })
})
