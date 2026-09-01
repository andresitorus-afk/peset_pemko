import { useRuntimeConfig } from '#app'

export function useApi() {
  const config = useRuntimeConfig()
  const baseURL = config.public?.apiBase || 'http://localhost:8000'

  function readCookie(name: string): string | null {
    if (typeof document === 'undefined') return null
    const m = document.cookie.match(new RegExp('(?:^|; )' + name + '=([^;]*)'))
    return m ? decodeURIComponent(m[1]) : null
  }

  function getToken(): string | null {
    if (typeof localStorage !== 'undefined') {
      const t = localStorage.getItem('token')
      if (t) return t
    }
    return readCookie('peset_token')
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
    const json = await res.json()
    if (json && json.data && json.meta) {
      json.last_page = json.meta.last_page
      json.total = json.meta.total
    }
    return json
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

  async function download(path: string, filename: string): Promise<void> {
    const h: Record<string, string> = { 'Accept': 'application/octet-stream' }
    const token = getToken()
    if (token) h['Authorization'] = `Bearer ${token}`
    const res = await fetch(`${baseURL}/api${path}`, { headers: h })
    if (!res.ok) { const e = await res.json(); throw new Error(e.message || JSON.stringify(e)) }
    const blob = await res.blob()
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = filename
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(url)
  }

  return { get, post, put, del, upload, download, baseURL }
}
