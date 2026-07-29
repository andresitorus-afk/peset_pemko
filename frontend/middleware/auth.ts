import { useAuth } from '~/composables/useAuth'

export default defineNuxtRouteMiddleware((to) => {
  const auth = useAuth()
  if (!auth.token.value && to.path.startsWith('/admin')) {
    return navigateTo('/auth/login')
  }
  if (auth.token.value && to.path === '/auth/login') {
    return navigateTo('/admin')
  }
})
