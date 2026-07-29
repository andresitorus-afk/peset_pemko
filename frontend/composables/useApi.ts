import { useRuntimeConfig } from '#app'

export function useApi() {
  const config = useRuntimeConfig()
  const baseURL = config.public?.apiBase || 'http://localhost:8000'

  function getToken(): string | null {
    if (typeof localStorage === 'undefined') return null
    return localStorage.getItem('token')
  }

  function headers(): Record<string, string> {
    const h: Record<string, string> = { 'Content-Type': 'application/json', 'Accept': 'application/json' }
    const token = getToken()
    if (token) h['Authorization'] = `Bearer ${token}`
    return h
  }

  async function get<T = any>(path: string): Promise<T> {
    const res = await fetch(`${baseURL}/api${path}`, { headers: headers() })
    if (!res.ok) { const e = await res.json(); throw new Error(e.message || JSON.stringify(e)) }
    return res.json()
  }

  async function post<T = any>(path: string, body?: any): Promise<T> {
    const h = headers()
    const res = await fetch(`${baseURL}/api${path}`, { method: 'POST', headers: h, body: body ? JSON.stringify(body) : undefined })
    if (!res.ok) { const e = await res.json(); throw new Error(e.message || JSON.stringify(e.errors || e)) }
    return res.json()
  }

  async function put<T = any>(path: string, body: any): Promise<T> {
    const h = headers()
    const res = await fetch(`${baseURL}/api${path}`, { method: 'PUT', headers: h, body: JSON.stringify(body) })
    if (!res.ok) { const e = await res.json(); throw new Error(e.message || JSON.stringify(e.errors || e)) }
    return res.json()
  }

  async function del<T = any>(path: string): Promise<T> {
    const h = headers()
    const res = await fetch(`${baseURL}/api${path}`, { method: 'DELETE', headers: h })
    if (!res.ok) { const e = await res.json(); throw new Error(e.message || Object.values(e.errors || {}).flat()[0] || JSON.stringify(e)) }
    return res.json()
  }

  async function upload<T = any>(path: string, formData: FormData): Promise<T> {
    const h: Record<string, string> = { 'Accept': 'application/json' }
    const token = getToken()
    if (token) h['Authorization'] = `Bearer ${token}`
    const res = await fetch(`${baseURL}/api${path}`, { method: 'POST', headers: h, body: formData })
    if (!res.ok) { const e = await res.json(); throw new Error(e.message || JSON.stringify(e.errors || e)) }
    return res.json()
  }

  return { get, post, put, del, upload, baseURL }
}
