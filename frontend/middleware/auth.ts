import { useAuth } from '~/composables/useAuth'

export default defineNuxtRouteMiddleware((to) => {
  const auth = useAuth()
  const config = useRuntimeConfig()
  const loginPath = '/auth/' + config.public.loginHash
  if (!auth.token.value && to.path.startsWith('/admin')) {
    return navigateTo(loginPath)
  }
  if (auth.token.value && to.path.startsWith('/auth/')) {
    return navigateTo('/admin')
  }
})
