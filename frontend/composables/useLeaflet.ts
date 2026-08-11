let leafletPromise: Promise<void> | null = null

export function loadLeaflet(): Promise<void> {
  if (leafletPromise) return leafletPromise
  if (import.meta.server || typeof window === 'undefined') {
    return Promise.resolve()
  }
  leafletPromise = new Promise((resolve, reject) => {
    const w = window as any
    if (w.L?.Control?.Draw) { resolve(); return }
    const base = 'https://cdn.jsdelivr.net/npm/'
    ;['leaflet@1.9.4/dist/leaflet.css', 'leaflet-draw@1.0.4/dist/leaflet.draw.css'].forEach((href) => {
      if (!document.querySelector(`link[href="${base + href}"]`)) {
        const l = document.createElement('link')
        l.rel = 'stylesheet'
        l.href = base + href
        document.head.appendChild(l)
      }
    })
    const srcs = [
      'leaflet@1.9.4/dist/leaflet.js',
      'leaflet-draw@1.0.4/dist/leaflet.draw.js',
    ]
    let i = w.L ? 1 : 0
    const next = () => {
      if (i >= srcs.length) { resolve(); return }
      const s = document.createElement('script')
      s.src = base + srcs[i++]
      s.onload = next
      s.onerror = () => reject(new Error('Gagal memuat peta: ' + s.src))
      document.head.appendChild(s)
    }
    next()
  })
  return leafletPromise
}
