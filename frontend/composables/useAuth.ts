import { ref, computed } from 'vue'
import { useApi } from './useApi'

const COOKIE = 'peset_token'
const COOKIE_DAYS = 7

const token = ref<string | null>(null)
const user = ref<any>(null)
const initialized = ref(false)

function readCookie(name: string): string | null {
  if (typeof document === 'undefined') return null
  const m = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'))
  return m ? decodeURIComponent(m[1]) : null
}

function writeCookie(name: string, value: string) {
  if (typeof document === 'undefined') return
  const expires = new Date(Date.now() + COOKIE_DAYS * 864e5).toUTCString()
  document.cookie = `${name}=${encodeURIComponent(value)}; expires=${expires}; path=/; SameSite=Lax`
}

function clearCookie(name: string) {
  if (typeof document === 'undefined') return
  document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/`
}

export function useAuth() {
  const api = useApi()

  if (!initialized.value) {
    if (typeof localStorage !== 'undefined') {
      token.value = localStorage.getItem('token') ?? readCookie(COOKIE)
    } else {
      token.value = readCookie(COOKIE)
    }
    initialized.value = true
  }

  const isLoggedIn = computed(() => !!token.value)
  const isSuperAdmin = computed(() => user.value?.role?.name === 'Super Admin')

  function persistToken(value: string) {
    token.value = value
    if (typeof localStorage !== 'undefined') {
      localStorage.setItem('token', value)
    }
    writeCookie(COOKIE, value)
  }

  function clearToken() {
    token.value = null
    user.value = null
    if (typeof localStorage !== 'undefined') {
      localStorage.removeItem('token')
    }
    clearCookie(COOKIE)
  }

  async function login(email: string, password: string) {
    const res = await api.post('/login', { email, password })
    persistToken(res.token)
    user.value = res.user
    return res
  }

  async function register(name: string, email: string, password: string) {
    const res = await api.post('/register', {
      name,
      email,
      password,
      password_confirmation: password,
    })
    persistToken(res.token)
    user.value = res.user
    return res
  }

  async function logout() {
    try { await api.post('/logout') } catch {}
    clearToken()
  }

  async function fetchUser() {
    try {
      user.value = await api.get('/user')
    } catch {
      clearToken()
    }
  }

  return { token, user, isLoggedIn, isSuperAdmin, login, register, logout, fetchUser, clearToken }
}
