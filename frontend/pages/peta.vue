<template>
  <div class="min-h-screen bg-teal-50/40 text-slate-800">
    <header class="sticky top-0 z-[1100] backdrop-blur-md bg-teal-900/90 border-b border-teal-700/40 shadow-lg shadow-teal-900/10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 sm:h-20">
          <NuxtLink to="/" class="flex items-center gap-3 group">
            <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-xl bg-white/15 flex items-center justify-center overflow-hidden ring-1 ring-white/30 shadow-inner group-hover:scale-105 transition-transform duration-300">
              <img src="/logo-pemko.jpg" alt="Pemko Medan" width="32" height="36" class="h-7 sm:h-8">
            </div>
            <div>
              <p class="text-base sm:text-lg font-extrabold text-white leading-tight tracking-tight">LENSA</p>
              <p class="text-[11px] sm:text-xs text-teal-200 leading-tight">Pemanfaatan Aset Daerah</p>
            </div>
          </NuxtLink>
          <nav class="hidden md:flex items-center gap-1">
            <NuxtLink to="/#beranda" class="px-4 py-2 text-sm font-medium text-slate-200 hover:text-white rounded-lg hover:bg-white/10 transition-all">Beranda</NuxtLink>
            <NuxtLink to="/#aset" class="px-4 py-2 text-sm font-medium text-slate-200 hover:text-white rounded-lg hover:bg-white/10 transition-all">Daftar Aset</NuxtLink>
            <NuxtLink to="/peta" class="px-4 py-2 text-sm font-semibold text-white rounded-lg bg-teal-700/60 shadow-sm">Peta Aset</NuxtLink>
            <NuxtLink to="/#cara" class="px-4 py-2 text-sm font-medium text-slate-200 hover:text-white rounded-lg hover:bg-white/10 transition-all">Cara Pemanfaatan</NuxtLink>
            <NuxtLink to="/#tentang" class="px-4 py-2 text-sm font-medium text-slate-200 hover:text-white rounded-lg hover:bg-white/10 transition-all">Tentang</NuxtLink>
          </nav>
        </div>
      </div>
    </header>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
      <div class="mb-4">
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900">Peta Sebaran Aset Daerah</h1>
        <p class="text-sm sm:text-base text-slate-500 mt-1">Lokasi aset Pemerintah Kota Medan berdasarkan layer GIS. Klik aset untuk detail.</p>
      </div>

      <div class="flex flex-wrap items-center gap-3 mb-4">
        <div class="inline-flex rounded-xl bg-white border border-slate-200 p-1 shadow-sm">
          <button
            @click="mode = 'all'"
            :class="mode === 'all' ? 'bg-teal-700 text-white shadow' : 'text-slate-600 hover:bg-slate-100'"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition-all">Semua Aset</button>
          <button
            @click="mode = 'tersedia'"
            :class="mode === 'tersedia' ? 'bg-teal-700 text-white shadow' : 'text-slate-600 hover:bg-slate-100'"
            class="px-4 py-2 text-sm font-semibold rounded-lg transition-all">Aset Tersedia</button>
        </div>
        <span class="text-xs sm:text-sm text-slate-500">{{ visibleCount }} aset ditampilkan</span>
      </div>

      <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-lg">
        <div ref="mapEl" class="h-[60vh] sm:h-[70vh] w-full"></div>

        <div class="absolute bottom-4 left-4 z-[1000] bg-white/95 backdrop-blur rounded-xl border border-slate-200 shadow-lg p-3 text-sm max-w-xs">
          <p class="font-bold text-slate-800 mb-2">Layer GIS</p>
          <button
            v-for="ly in layers"
            :key="ly.nama_layer"
            @click="toggleLayer(ly)"
            class="flex items-center gap-2 w-full text-left px-1 py-1.5 rounded hover:bg-slate-50"
            :class="{ 'opacity-40': hiddenLayers.includes(ly.nama_layer) }">
            <span class="w-4 h-4 rounded-sm border border-black/10 shrink-0" :style="{ backgroundColor: ly.warna }"></span>
            <span class="flex-1 text-slate-700">{{ ly.nama_layer }}</span>
            <span class="text-xs text-slate-400">{{ ly.count }}</span>
          </button>
          <div class="mt-2 pt-2 border-t border-slate-200 flex items-center gap-2 text-xs text-slate-500">
            <div class="flex items-center gap-1"><span class="inline-block w-4 h-4" style="background:repeating-linear-gradient(45deg,#64748b,#64748b 3px,transparent 3px,transparent 6px)"></span> area</div>
            <div class="flex items-center gap-1"><svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/></svg> titik</div>
          </div>
        </div>
      </div>

      <p v-if="error" class="mt-4 text-sm text-red-600">{{ error }}</p>
    </section>
  </div>
</template>

<script setup lang="ts">
import { loadLeaflet } from '~/composables/useLeaflet'
import { useApi } from '~/composables/useApi'

useHead({ title: 'Peta Sebaran Aset - LENSA Pemko Medan' })

const { get } = useApi()
const mapEl = ref<HTMLElement | null>(null)
const mode = ref<'all' | 'tersedia'>('all')
const error = ref('')
const features = ref<any[]>([])
const layers = ref<any[]>([])
const hiddenLayers = ref<string[]>([])

let map: any = null
let geoLayer: any = null

const visibleFeatures = computed(() => {
  const f = mode.value === 'tersedia'
    ? features.value.filter(x => ['Aktif', 'Tersedia', 'Idle'].includes(x.properties.status))
    : features.value
  return f.filter(x => !hiddenLayers.value.includes(x.properties.layer))
})
const visibleCount = computed(() => visibleFeatures.value.length)

const iconShapes: Record<string, string> = {
  landmark: 'M6 21h12M7 17h10M7 3l5-2 5 2v4H7zM8 8h8v9H8z',
  building: 'M3 21h18M5 21V5a2 2 0 0 1 2-2h6v18M13 8h3a2 2 0 0 1 2 2v11M9 8h1M9 12h1M9 16h1',
  road: 'M4 21l4-18M20 21l-4-18M9 3h6M8 8h8M7 13h10M6 18h12',
  facility: 'M12 21a9 9 0 1 0-9-9 9 9 0 0 0 9 9zm0-18a9 9 0 0 1 9 9',
}

function svgIcon(key: string, color: string) {
  const d = iconShapes[key] || iconShapes.facility
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="32" height="32"><path d="${d}" fill="none" stroke="${color}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>`
  return `data:image/svg+xml;charset=utf-8,${encodeURIComponent(svg)}`
}

function statusLabel(s: string) {
  return s ? s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) : '-'
}

async function fetchData() {
  try {
    const json = await get('/public/gis/aset')
    features.value = json.features || json.data || []
    const layerMap = new Map<string, any>()
    for (const f of features.value) {
      const key = f.properties.layer || 'Lainnya'
      if (!layerMap.has(key)) {
        layerMap.set(key, { nama_layer: key, warna: f.properties.warna || '#64748b', icon_marker: f.properties.icon_marker, count: 0 })
      }
      layerMap.get(key).count++
    }
    layers.value = Array.from(layerMap.values())
  } catch (e: any) {
    error.value = e?.message || 'Gagal memuat data aset'
  }
}

function render() {
  const L: any = (window as any).L
  if (!map) return
  if (geoLayer) {
    geoLayer.clearLayers()
  } else {
    geoLayer = L.geoJSON().addTo(map)
  }
  for (const f of visibleFeatures.value) {
    const p = f.properties
    const color = p.warna || '#64748b'
    const geom = f.geometry
    const popup = `<div class="min-w-[180px]">
      <b class="text-slate-900">${escapeHtml(p.nama_barang)}</b>
      <div class="text-xs text-slate-500 mt-0.5">${escapeHtml(p.kode_barang || '')}</div>
      <div class="mt-1 text-xs text-slate-700 grid gap-0.5">
        <span>Status: <b>${statusLabel(p.status)}</b></span>
        <span>Kondisi: ${statusLabel(p.kondisi)}</span>
        <span>Layer: ${escapeHtml(p.layer)}</span>
        <span>Luas: ${p.luas_gis != null ? p.luas_gis.toLocaleString('id-ID') + ' m²' : '-'}</span>
      </div>
    </div>`
    if (geom && geom.type === 'Polygon') {
      L.geoJSON(geom, {
        style: { color, weight: 2, fillColor: color, fillOpacity: 0.35 },
      }).addTo(geoLayer).bindPopup(popup)
    } else if (geom && geom.type === 'Point') {
      const [lng, lat] = geom.coordinates
      const icon = L.divIcon({
        className: '',
        html: `<img src="${svgIcon(p.icon_marker, color)}" style="filter:drop-shadow(0 1px 2px rgba(0,0,0,.4));transform:translate(-50%,-100%)"/>`,
        iconAnchor: [16, 32],
      })
      L.marker([lat, lng], { icon }).addTo(geoLayer).bindPopup(popup)
    } else if (geom && geom.type === 'LineString') {
      L.geoJSON(geom, { style: { color, weight: 3 } }).addTo(geoLayer).bindPopup(popup)
    }
  }
  if (map) {
    const fit = geoLayer.getBounds()
    if (fit.isValid()) map.fitBounds(fit, { padding: [40, 40], maxZoom: 15 })
  }
}

function toggleLayer(ly: any) {
  const i = hiddenLayers.value.indexOf(ly.nama_layer)
  if (i >= 0) hiddenLayers.value.splice(i, 1)
  else hiddenLayers.value.push(ly.nama_layer)
  render()
}

function escapeHtml(s: string) {
  return String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c] as string))
}

async function initMap() {
  if (!mapEl.value) return
  await loadLeaflet()
  const L: any = (window as any).L
  map = L.map(mapEl.value, { zoomControl: true, scrollWheelZoom: true }).setView([3.585, 98.6753], 12)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors',
  }).addTo(map)
  render()
  setTimeout(() => map?.invalidateSize(), 300)
}

watch(mode, () => render())
watch(hiddenLayers, () => render(), { deep: true })

onBeforeUnmount(() => { map?.remove(); map = null })

onMounted(async () => {
  await fetchData()
  await initMap()
})
</script>
