<template>
  <div>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Aset</h1>

    <AdminDataTable
      :columns="columns"
      :data="items"
      :page="page"
      :total-pages="lastPage"
      search-placeholder="Cari kode/nama barang..."
      create-label="Tambah Aset"
      :loading="loading"
      @search="search = $event; fetchData()"
      @create="openCreate()"
      @edit="openEdit($event)"
      @delete="confirmDelete($event)"
      @page-change="page = $event; fetchData()"
    >
      <template #cell-opd.nama_opd="{ row }">
        {{ row.opd?.nama_opd || '—' }}
      </template>
      <template #cell-nilai_perolehan="{ row }">
        <span class="tabular-nums">{{ row.nilai_perolehan ? 'Rp ' + formatRupiah(String(row.nilai_perolehan)) : '—' }}</span>
      </template>
    </AdminDataTable>

    <AdminFormModal
      :show="modal"
      :title="editing ? 'Edit Aset' : 'Tambah Aset'"
      :loading="saving"
      @close="closeModal"
      @submit="save"
    >
      <div class="space-y-6">
        <div>
          <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Informasi Barang</h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <UiInput v-model="form.kode_barang" label="Kode Barang" required placeholder="BRG.001" />
            <UiInput v-model="form.register" label="Register" placeholder="001" />
          </div>
          <UiInput v-model="form.nama_barang" label="Nama Barang" required placeholder="Nama aset..." class="mt-4" />
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
            <UiSelect v-model="form.opd_id" label="OPD" :options="opdOptions" required placeholder="Pilih OPD" />
            <UiSelect v-model="form.kategori_id" label="Kategori" :options="kategoriOptions" required placeholder="Pilih Kategori" />
          </div>
        </div>

        <div>
          <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Detail Aset</h4>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <UiInput v-model="form.tahun_perolehan" label="Tahun Perolehan" type="number" placeholder="2024" />
            <UiInput :model-value="formatRupiah(form.nilai_perolehan)" @update:model-value="form.nilai_perolehan = unformatNumber($event)" label="Nilai Perolehan (Rp)" placeholder="0" />
            <UiInput :model-value="formatRupiah(form.nilai_buku)" @update:model-value="form.nilai_buku = unformatNumber($event)" label="Nilai Buku (Rp)" placeholder="0" />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
            <UiInput v-model="form.luas" label="Luas (m²)" type="number" placeholder="0" />
            <UiSelect v-model="form.kondisi" label="Kondisi" :options="kondisiOptions" required placeholder="Pilih kondisi" />
            <UiSelect v-model="form.status" label="Status" :options="statusOptions" required placeholder="Pilih status" />
          </div>
        </div>

        <div>
          <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Lokasi & Keterangan</h4>
          <div class="relative">
            <label class="block text-xs font-semibold text-slate-500 mb-1.5">Cari Lokasi</label>
            <input v-model="gisSearchQuery" @input="onGisSearch" type="text" placeholder="Ketik alamat untuk cari di peta..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal-500" />
            <ul v-if="gisSearchResults.length" class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
              <li v-for="(r, i) in gisSearchResults" :key="i" @click="selectGisResult(r)" class="px-3 py-2 text-sm text-slate-700 hover:bg-teal-50 cursor-pointer border-b border-slate-100 last:border-0">{{ r.display_name }}</li>
            </ul>
          </div>
          <UiInput v-model="form.alamat" label="Alamat" placeholder="Alamat lokasi aset..." class="mt-4" />
          <!-- Map -->
          <div v-if="form.latitude" ref="mapContainer" class="w-full h-64 sm:h-80 rounded-xl border border-slate-200 overflow-hidden z-0 mt-4"></div>
          <div class="grid grid-cols-2 gap-3 mt-4">
            <div>
              <label class="block text-xs font-semibold text-slate-500 mb-1.5">Latitude</label>
              <input v-model="form.latitude" type="text" readonly class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 text-slate-600">
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-500 mb-1.5">Longitude</label>
              <input v-model="form.longitude" type="text" readonly class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 text-slate-600">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-3 mt-3">
            <UiSelect v-model="form.gis_layer_id" label="Layer GIS" :options="gisLayerOptions" placeholder="Pilih layer" />
            <div>
              <label class="block text-xs font-semibold text-slate-500 mb-1.5">Luas Polygon (m²)</label>
              <input v-model="form.luas_gis" type="text" readonly class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 text-slate-600">
            </div>
          </div>
          <p class="text-xs text-slate-400 mt-1.5">Gambar polygon di peta dengan tool <strong>Draw Polygon</strong> (icon segi lima). Edit/hapus setelah digambar.</p>
          <UiInput v-model="form.keterangan" label="Keterangan" placeholder="Catatan tambahan..." class="mt-4" />
        </div>

        <div v-if="editing">
          <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Foto Aset</h4>

          <!-- Gallery -->
          <div v-if="fotos.length" class="relative">
            <div class="flex gap-3 overflow-x-auto pb-2 snap-x snap-mandatory scrollbar-hide">
              <div v-for="(foto, i) in fotos" :key="foto?.id ?? i" class="snap-start shrink-0 relative group">
                <img :src="fotoUrl(foto.file_path || foto.file)" class="w-48 sm:w-56 h-32 sm:h-36 object-cover rounded-xl border border-slate-200 shadow-sm" />
                <button @click="deleteFoto(foto)" type="button" class="absolute top-2 right-2 bg-red-500/90 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm opacity-0 group-hover:opacity-100 transition-opacity shadow-lg hover:bg-red-600">&times;</button>
                <p v-if="foto.caption" class="text-xs text-slate-500 mt-1.5 truncate w-48 sm:w-56">{{ foto.caption }}</p>
              </div>
            </div>
            <div v-if="fotos.length > 1" class="flex items-center justify-center gap-1.5 mt-3">
              <span v-for="(_, i) in fotos" :key="i" class="w-2 h-2 rounded-full transition-colors" :class="i === fotoSlide ? 'bg-teal-600' : 'bg-slate-300'" />
            </div>
          </div>
          <p v-else class="text-sm text-slate-400 mb-3">Belum ada foto</p>

          <!-- Upload -->
          <div class="flex flex-col sm:flex-row gap-3 mt-3">
            <div class="flex-1 relative flex items-center gap-3 p-3 border-2 border-dashed border-slate-300 rounded-xl bg-slate-50/50">
              <svg class="w-8 h-8 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
              <div class="flex-1">
                <p class="text-sm font-medium text-slate-600">Klik untuk pilih {{ fotoFiles.length > 0 ? 'lagi' : 'foto' }}</p>
                <p v-if="fotoFiles.length" class="text-xs text-teal-600 mt-0.5">{{ fotoFiles.length }} file dipilih</p>
                <p v-else class="text-xs text-slate-400">Bisa pilih beberapa foto sekaligus</p>
                <input type="file" accept="image/*" multiple @change="handleFotoSelect" class="absolute inset-0 opacity-0 cursor-pointer" />
              </div>
            </div>
            <div class="flex gap-2">
              <input v-model="fotoCaption" placeholder="Keterangan foto" class="flex-1 sm:w-40 px-3 py-2 border border-slate-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal-500" />
              <UiButton variant="primary" type="button" @click="uploadFoto" :disabled="!fotoFiles.length || fotoUploading" size="sm">
                {{ fotoUploading ? 'Mengunggah...' : 'Upload' }}
              </UiButton>
            </div>
          </div>
        </div>
      </div>
    </AdminFormModal>

    <UiModal :show="deleteModal" title="Hapus Aset" @close="deleteModal = false">
      <p class="text-slate-600">Apakah anda yakin ingin menghapus aset <strong>{{ deletingItem?.nama_barang }}</strong>?</p>
      <div class="flex justify-end gap-3 pt-4">
        <UiButton variant="secondary" @click="deleteModal = false">Batal</UiButton>
        <UiButton variant="danger" @click="doDelete" :disabled="deleting">{{ deleting ? 'Menghapus...' : 'Hapus' }}</UiButton>
      </div>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin' })

const api = useApi()
const toast = useToast()

const items = ref<any[]>([])
const page = ref(1)
const lastPage = ref(1)
const search = ref('')
const loading = ref(false)
const modal = ref(false)
const saving = ref(false)
const editing = ref<any | null>(null)
const deleteModal = ref(false)
const deleting = ref(false)
const deletingItem = ref<any | null>(null)

const opdList = ref<any[]>([])
const kategoriList = ref<any[]>([])
const fotos = ref<any[]>([])
const fotoFiles = ref<File[]>([])
const fotoCaption = ref('')
const fotoUploading = ref(false)
const fotoSlide = ref(0)

// GIS
const gisLayerList = ref<any[]>([])
const gisLayerOptions = computed(() => gisLayerList.value.filter((l: any) => l?.id).map((l: any) => ({ value: l.id, label: l.nama_layer })))
const gisSearchResults = ref<any[]>([])
const gisSearching = ref(false)
const gisSearchQuery = ref('')
let gisSearchTimer: ReturnType<typeof setTimeout>
const mapContainer = ref<HTMLDivElement | null>(null)
let mapInstance: any = null
let mapMarker: any = null
let drawnLayer: any = null
let drawnItems: any = null
const gisReady = ref(false)
const gisDrawActive = ref(false)

function formatRupiah(val: string | number | undefined) {
  if (val === undefined || val === null || val === '') return ''
  const s = String(val).replace(/\D/g, '')
  if (!s) return ''
  return new Intl.NumberFormat('id-ID').format(Number(s))
}

function unformatNumber(val: string) {
  return val.replace(/\D/g, '')
}

const form = ref({
  kode_barang: '',
  register: '',
  nama_barang: '',
  opd_id: '',
  kategori_id: '',
  tahun_perolehan: '',
  nilai_perolehan: '',
  nilai_buku: '',
  luas: '',
  kondisi: '',
  status: '',
  alamat: '',
  keterangan: '',
  latitude: '',
  longitude: '',
  gis_layer_id: '',
  polygon_geojson: null as any,
  luas_gis: '',
})

const kondisiOptions = [
  { value: 'Baik', label: 'Baik' },
  { value: 'Rusak_Ringan', label: 'Rusak Ringan' },
  { value: 'Rusak_Berat', label: 'Rusak Berat' },
]

const statusOptions = [
  { value: 'Aktif', label: 'Aktif' },
  { value: 'Idle', label: 'Idle' },
  { value: 'Dimanfaatkan', label: 'Dimanfaatkan' },
]

const opdOptions = computed(() => opdList.value.filter((o: any) => o?.id).map((o: any) => ({ value: o.id, label: o.nama_opd })))
const kategoriOptions = computed(() => kategoriList.value.filter((k: any) => k?.id).map((k: any) => ({ value: k.id, label: k.nama_kategori || k.nama })))

const columns = [
  { key: 'kode_barang', label: 'Kode Barang' },
  { key: 'nama_barang', label: 'Nama Barang' },
  { key: 'opd.nama_opd', label: 'OPD' },
  { key: 'nilai_perolehan', label: 'Nilai Aset' },
  { key: 'kondisi', label: 'Kondisi' },
  { key: 'status', label: 'Status' },
]

function fotoUrl(path: string) {
  if (!path) return ''
  return `${api.baseURL}/storage/${path}`
}

function onGisSearch() {
  clearTimeout(gisSearchTimer)
  gisSearchTimer = setTimeout(async () => {
    const q = gisSearchQuery.value.trim()
    if (q.length < 3) { gisSearchResults.value = []; return }
    gisSearching.value = true
    try {
      const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=5&countrycodes=id`, { headers: { 'User-Agent': 'PesetPemko/1.0' } })
      gisSearchResults.value = await res.json()
    } catch { gisSearchResults.value = [] } finally { gisSearching.value = false }
  }, 400)
}

async function selectGisResult(r: any) {
  gisSearchQuery.value = r.display_name
  gisSearchResults.value = []
  form.value.alamat = r.display_name
  form.value.latitude = r.lat
  form.value.longitude = r.lon
  await nextTick()
  initMap()
}

async function initMap() {
  destroyMap()
  if (!mapContainer.value || !form.value.latitude || !form.value.longitude) return
  await loadLeaflet()
  const L = (window as any).L
  const lat = parseFloat(form.value.latitude)
  const lng = parseFloat(form.value.longitude)
  if (isNaN(lat) || isNaN(lng)) return

  mapInstance = L.map(mapContainer.value, { zoomControl: true }).setView([lat, lng], 18)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap' }).addTo(mapInstance)

  mapMarker = L.marker([lat, lng]).addTo(mapInstance).bindPopup(form.value.nama_barang || 'Lokasi')

  drawnItems = new L.FeatureGroup()
  mapInstance.addLayer(drawnItems)

  if (form.value.polygon_geojson) drawPolygonOnMap(form.value.polygon_geojson)

  const drawControl = new L.Control.Draw({
    edit: { featureGroup: drawnItems },
    draw: { polygon: { allowIntersection: false, showArea: true }, polyline: false, circle: false, circlemarker: false, rectangle: false, marker: false },
  })
  mapInstance.addControl(drawControl)

  mapInstance.on(L.Draw.Event.CREATED, (e: any) => {
    drawnItems.clearLayers()
    e.layer.setStyle?.(layerStyle())
    drawnItems.addLayer(e.layer)
    drawnLayer = e.layer
    saveDrawnPolygon(e.layer)
  })

  mapInstance.on(L.Draw.Event.EDITED, (e: any) => {
    e.layers.eachLayer((l: any) => { drawnLayer = l; saveDrawnPolygon(l) })
  })

  mapInstance.on(L.Draw.Event.DELETED, () => {
    drawnLayer = null
    form.value.polygon_geojson = null
    form.value.luas_gis = ''
  })

  setTimeout(() => mapInstance?.invalidateSize(), 400)
  gisReady.value = true
}

function layerStyle() {
  const layer = gisLayerList.value.find((l: any) => l.id === form.value.gis_layer_id)
  const color = layer?.warna || '#0f766e'
  return { color, weight: 3, fillColor: color, fillOpacity: 0.35 }
}

function drawPolygonOnMap(geo: any) {
  if (!mapInstance || !drawnItems) return
  drawnItems.clearLayers()
  const L = (window as any).L
  let g = geo
  if (typeof g === 'string') { try { g = JSON.parse(g); if (typeof g === 'string') g = JSON.parse(g) } catch { g = null } }
  if (!g?.type) return
  const poly = L.geoJSON(g, { style: layerStyle })
  poly.eachLayer((l: any) => {
    drawnItems.addLayer(l)
    mapInstance.fitBounds(l.getBounds())
  })
  drawnLayer = poly
  saveDrawnPolygon(poly)
}

function saveDrawnPolygon(layer: any) {
  if (!layer) return
  try {
    const geo = layer.toGeoJSON?.() || layer
    if (geo.type === 'Feature') {
      form.value.polygon_geojson = geo.geometry
    } else if (geo.type === 'FeatureCollection') {
      form.value.polygon_geojson = geo.features[0]?.geometry || null
    } else {
      form.value.polygon_geojson = geo
    }
    if (form.value.polygon_geojson) {
      form.value.luas_gis = Math.round(polygonAreaM2(form.value.polygon_geojson) * 100) / 100
    }
  } catch {}
}

function destroyMap() {
  if (mapInstance) { mapInstance.remove(); mapInstance = null }
  mapMarker = null; drawnLayer = null; drawnItems = null; gisReady.value = false
}

function closeModal() {
  modal.value = false
  editing.value = null
  fotos.value = []
  fotoFiles.value = []
  fotoCaption.value = ''
  destroyMap()
}

async function fetchData() {
  loading.value = true
  try {
    const res = await api.get(`/aset?page=${page.value}&search=${search.value}`)
    if (Array.isArray(res)) {
      items.value = res
    } else {
      items.value = res.data || []
      lastPage.value = res.last_page || 1
    }
  } catch (e: any) {
    toast.show('Gagal memuat data: ' + e.message, 'error')
  } finally {
    loading.value = false
  }
}

async function fetchOptions() {
  try {
    const [opdRes, katRes, gisLayerRes] = await Promise.all([
      api.get('/opd'),
      api.get('/kategori-aset'),
      api.get('/gis-layer'),
    ])
    opdList.value = Array.isArray(opdRes) ? opdRes : opdRes.data || []
    kategoriList.value = Array.isArray(katRes) ? katRes : katRes.data || []
    const layers = Array.isArray(gisLayerRes) ? gisLayerRes : gisLayerRes.data || []
    gisLayerList.value = layers.filter((l: any) => l?.id)
  } catch (e: any) {
    toast.show('Gagal memuat data referensi', 'error')
  }
}

function openCreate() {
  editing.value = null
  form.value = { kode_barang: '', register: '', nama_barang: '', opd_id: '', kategori_id: '', tahun_perolehan: '', nilai_perolehan: '', nilai_buku: '', luas: '', kondisi: '', status: '', alamat: '', keterangan: '', latitude: '', longitude: '', gis_layer_id: '', polygon_geojson: null, luas_gis: '' }
  fotos.value = []
  fotoFiles.value = []
  fotoCaption.value = ''
  gisSearchQuery.value = ''
  gisSearchResults.value = []
  destroyMap()
  modal.value = true
}

async function openEdit(item: any) {
  editing.value = item
  form.value = {
    kode_barang: item.kode_barang || '',
    register: item.register || '',
    nama_barang: item.nama_barang || '',
    opd_id: item.opd_id || '',
    kategori_id: item.kategori_id || '',
    tahun_perolehan: item.tahun_perolehan || '',
    nilai_perolehan: item.nilai_perolehan || '',
    nilai_buku: item.nilai_buku || '',
    luas: item.luas || '',
    kondisi: item.kondisi || '',
    status: item.status || '',
    alamat: item.alamat || '',
    keterangan: item.keterangan || '',
    latitude: item.gis_aset?.latitude ?? '',
    longitude: item.gis_aset?.longitude ?? '',
    gis_layer_id: item.gis_aset?.layer_id ?? '',
    polygon_geojson: item.gis_aset?.polygon_geojson ?? null,
    luas_gis: item.gis_aset?.luas_gis ?? '',
  }
  if (!form.value.kategori_id && item.kategori?.kode_kib) {
    const kibLayer: Record<string, string> = { 'KIB A': 'Tanah', 'KIB C': 'Bangunan' }
    const layerName = kibLayer[item.kategori.kode_kib]
    if (layerName) {
      const found = gisLayerList.value.find((l: any) => l.nama_layer === layerName)
      if (found) form.value.gis_layer_id = found.id
    }
  }
  gisSearchQuery.value = ''
  gisSearchResults.value = []
  fotoFiles.value = []
  fotoCaption.value = ''
  fotos.value = item.foto || []
  if (Array.isArray(fotos.value) && fotos.value.length) {
    fotos.value = fotos.value.map((f: any) => f.data || f)
  } else {
    try {
      const detail: any = await api.get(`/aset/${item.id}`)
      const raw = detail.foto || detail.data?.foto || []
      fotos.value = Array.isArray(raw) ? raw : raw.data || []
    } catch {}
  }
  modal.value = true
  await nextTick()
  initMap()
}

async function save() {
  saving.value = true
  const payload = { ...form.value }
  if (payload.latitude && payload.longitude && payload.gis_layer_id) {
    payload.gis = {
      latitude: payload.latitude,
      longitude: payload.longitude,
      polygon_geojson: payload.polygon_geojson,
      luas_gis: payload.luas_gis,
      layer_id: payload.gis_layer_id,
      tipe_geometri: payload.polygon_geojson ? 'Polygon' : 'Point',
    }
  }
  delete payload.latitude; delete payload.longitude; delete payload.gis_layer_id
  delete payload.polygon_geojson; delete payload.luas_gis
  try {
    if (editing.value) {
      await api.put(`/aset/${editing.value.id}`, payload)
      toast.show('Aset berhasil diupdate', 'success')
    } else {
      await api.post('/aset', payload)
      toast.show('Aset berhasil ditambahkan', 'success')
    }
    closeModal()
    fetchData()
  } catch (e: any) {
    toast.show('Gagal menyimpan: ' + e.message, 'error')
  } finally {
    saving.value = false
  }
}

function handleFotoSelect(e: Event) {
  const target = e.target as HTMLInputElement
  if (!target.files?.length) return
  fotoFiles.value = [...fotoFiles.value, ...Array.from(target.files)]
  target.value = ''
}

async function uploadFoto() {
  if (!fotoFiles.value.length || !editing.value) return
  fotoUploading.value = true
  let ok = 0, fail = 0
  for (const file of fotoFiles.value) {
    try {
      const fd = new FormData()
      fd.append('aset_id', editing.value.id)
      fd.append('file', file)
      fd.append('caption', fotoCaption.value)
      fd.append('tipe', 'Lainnya')
      const result = await api.upload('/foto-aset', fd)
      fotos.value.push(result.data || result)
      ok++
    } catch {
      fail++
    }
  }
  if (ok) toast.show(`${ok} foto berhasil diupload${fail ? `, ${fail} gagal` : ''}`, 'success')
  if (fail && !ok) toast.show('Gagal upload foto', 'error')
  fotoFiles.value = []
  fotoCaption.value = ''
  fotoUploading.value = false
}

async function deleteFoto(foto: any) {
  try {
    await api.del(`/foto-aset/${foto.id}`)
    fotos.value = fotos.value.filter((f: any) => f.id !== foto.id)
    toast.show('Foto berhasil dihapus', 'success')
  } catch (e: any) {
    toast.show('Gagal menghapus foto: ' + e.message, 'error')
  }
}

function confirmDelete(item: any) {
  deletingItem.value = item
  deleteModal.value = true
}

async function doDelete() {
  if (!deletingItem.value) return
  deleting.value = true
  try {
    await api.del(`/aset/${deletingItem.value.id}`)
    toast.show('Aset berhasil dihapus', 'success')
    deleteModal.value = false
    deletingItem.value = null
    fetchData()
  } catch (e: any) {
    toast.show('Gagal menghapus: ' + e.message, 'error')
  } finally {
    deleting.value = false
  }
}

fetchData()
fetchOptions()
</script>