import { ref, computed } from 'vue'
import { useApi } from './useApi'

const token = ref<string | null>(null)
const user = ref<any>(null)
const initialized = ref(false)

export function useAuth() {
  const api = useApi()

  if (!initialized.value) {
    if (typeof localStorage !== 'undefined') {
      token.value = localStorage.getItem('token')
    }
    initialized.value = true
  }

  const isLoggedIn = computed(() => !!token.value)

  async function login(email: string, password: string) {
    const res = await api.post('/login', { email, password })
    token.value = res.token
    user.value = res.user
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem('token', res.token)
    }
    return res
  }

  async function logout() {
    try { await api.post('/logout') } catch {}
    token.value = null
    user.value = null
    if (typeof localStorage !== 'undefined') {
      localStorage.removeItem('token')
    }
  }

  async function fetchUser() {
    try {
      user.value = await api.get('/user')
    } catch {
      token.value = null
      user.value = null
      if (typeof localStorage !== 'undefined') {
        localStorage.removeItem('token')
      }
    }
  }

  return { token, user, isLoggedIn, login, logout, fetchUser }
}
