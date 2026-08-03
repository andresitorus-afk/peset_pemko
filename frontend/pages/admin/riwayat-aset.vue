<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Riwayat & Audit Trail</h1>
        <p class="text-slate-500 text-sm mt-1">Catatan setiap perubahan aset & pemanfaatan — siapa, kapan, dan berubah dari apa ke apa.</p>
      </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-4 mb-6 shadow-sm flex flex-wrap gap-3 items-end">
      <div class="flex-1 min-w-[180px]">
        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Aksi</label>
        <select v-model="filterAksi" @change="fetchData(1)" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal-500">
          <option value="">Semua</option>
          <option v-for="a in aksiOptions" :key="a" :value="a">{{ a }}</option>
        </select>
      </div>
      <div class="flex-1 min-w-[180px]">
        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Pengguna</label>
        <select v-model="filterUserId" @change="fetchData(1)" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal-500">
          <option value="">Semua</option>
          <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Dari</label>
        <input v-model="filterDari" type="date" @change="fetchData(1)" class="px-3 py-2 border border-slate-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal-500">
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1.5">Sampai</label>
        <input v-model="filterSampai" type="date" @change="fetchData(1)" class="px-3 py-2 border border-slate-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal-500">
      </div>
      <UiButton variant="ghost" size="sm" @click="resetFilter">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        Reset
      </UiButton>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4">Waktu</th>
              <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4">Aksi</th>
              <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4">Aset</th>
              <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4">Deskripsi</th>
              <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4">Pengguna</th>
              <th class="text-right text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4">Detail</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in items" :key="r.id" class="border-b border-slate-100 hover:bg-slate-50">
              <td class="py-3 px-4 text-sm text-slate-500 whitespace-nowrap">{{ formatTime(r.created_at) }}</td>
              <td class="py-3 px-4">
                <span :class="['px-2 py-1 rounded-full text-xs font-semibold', badgeClass(r.aksi)]">{{ r.aksi }}</span>
              </td>
              <td class="py-3 px-4 text-sm text-slate-700 max-w-[200px] truncate">{{ r.aset?.nama_barang || r.aset_id }}</td>
              <td class="py-3 px-4 text-sm text-slate-700">{{ r.deskripsi || '—' }}</td>
              <td class="py-3 px-4 text-sm text-slate-700">{{ r.user?.name || '—' }}</td>
              <td class="py-3 px-4 text-right">
                <button
                  v-if="r.detail && Object.keys(r.detail).length"
                  @click="toggleDetail(r.id)"
                  class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium text-teal-600 hover:bg-teal-50 rounded-lg transition-colors"
                >
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                  {{ expanded.has(r.id) ? 'Tutup' : 'Lihat' }}
                </button>
                <span v-else class="text-xs text-slate-300">—</span>
              </td>
            </tr>
            <tr v-if="!items.length">
              <td colspan="6" class="py-12 text-center text-slate-400 text-sm">Belum ada riwayat perubahan.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="expandedItems.length" class="mt-4">
        <div v-for="r in expandedItems" :key="'d-' + r.id" class="border border-slate-200 rounded-lg overflow-hidden mb-3">
          <div class="px-4 py-2.5 bg-slate-50 text-xs font-semibold text-slate-600">Detail perubahan — {{ r.aset?.nama_barang }}</div>
          <div class="divide-y divide-slate-100">
            <div v-for="(change, field) in r.detail" :key="field" class="px-4 py-2.5 flex items-start gap-4 text-sm">
              <span class="w-36 flex-shrink-0 font-medium text-slate-600">{{ fieldLabel(field) }}</span>
              <div class="flex-1 flex items-center gap-2 min-w-0">
                <span class="flex-1 px-2.5 py-1.5 bg-red-50 text-red-700 rounded-lg line-through decoration-red-300 truncate">{{ display(change.lama) }}</span>
                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                <span class="flex-1 px-2.5 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg truncate">{{ display(change.baru) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="lastPage > 1" class="flex items-center justify-between pt-4 border-t border-slate-200 mt-4">
        <p class="text-sm text-slate-500">Halaman {{ page }} dari {{ lastPage }}</p>
        <div class="flex gap-1">
          <button :disabled="page <= 1" @click="fetchData(page - 1)" class="px-3 py-1.5 rounded text-sm border border-slate-300 hover:bg-slate-50 disabled:opacity-50">Sebelumnya</button>
          <button :disabled="page >= lastPage" @click="fetchData(page + 1)" class="px-3 py-1.5 rounded text-sm border border-slate-300 hover:bg-slate-50 disabled:opacity-50">Berikutnya</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin' })

const api = useApi()
const toast = useToast()

const items = ref<any[]>([])
const users = ref<any[]>([])
const page = ref(1)
const lastPage = ref(1)
const filterAksi = ref('')
const filterUserId = ref('')
const filterDari = ref('')
const filterSampai = ref('')
const expanded = ref<Set<string>>(new Set())

const aksiOptions = ['Pemanfaatan', 'Pemeliharaan', 'Mutasi', 'Penghapusan', 'Revaluasi']

const expandedItems = computed(() => items.value.filter(r => expanded.value.has(r.id)))

const fieldLabels: Record<string, string> = {
  nama_barang: 'Nama Barang', kode_barang: 'Kode Barang', register: 'Register',
  opd_id: 'OPD', kategori_id: 'Kategori', tahun_perolehan: 'Tahun Perolehan',
  nilai_perolehan: 'Nilai Perolehan', nilai_buku: 'Nilai Buku', luas: 'Luas',
  kondisi: 'Kondisi', status: 'Status', alamat: 'Alamat', keterangan: 'Keterangan',
  aset_id: 'Aset', jenis_id: 'Jenis Pemanfaatan', pihak_ketiga_id: 'Pihak Ketiga',
  nomor_perjanjian: 'No. Perjanjian', tanggal_mulai: 'Tanggal Mulai', tanggal_selesai: 'Tanggal Selesai',
  nilai_kontrak: 'Nilai Kontrak', kontribusi_tahunan: 'Kontribusi Tahunan',
  peruntukan: 'Peruntukan', catatan: 'Catatan',
}

function fieldLabel(field: string): string {
  return fieldLabels[field] || field
}

function display(value: any): string {
  if (value === null || value === undefined || value === '') return '—'
  return String(value)
}

function formatTime(iso: string): string {
  if (!iso) return '—'
  const d = new Date(iso)
  return d.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}

function badgeClass(aksi: string): string {
  const map: Record<string, string> = {
    Pemanfaatan: 'bg-emerald-50 text-emerald-700',
    Pemeliharaan: 'bg-purple-50 text-purple-700',
    Mutasi: 'bg-blue-50 text-blue-700',
    Penghapusan: 'bg-red-50 text-red-700',
    Revaluasi: 'bg-amber-50 text-amber-700',
  }
  return map[aksi] || 'bg-slate-100 text-slate-700'
}

function toggleDetail(id: string) {
  const s = new Set(expanded.value)
  if (s.has(id)) s.delete(id)
  else s.add(id)
  expanded.value = s
}

function resetFilter() {
  filterAksi.value = ''
  filterUserId.value = ''
  filterDari.value = ''
  filterSampai.value = ''
  fetchData(1)
}

async function fetchData(targetPage = 1) {
  const params = new URLSearchParams({ per_page: '20' })
  if (filterAksi.value) params.set('aksi', filterAksi.value)
  if (filterUserId.value) params.set('user_id', filterUserId.value)
  if (filterDari.value) params.set('tanggal_dari', filterDari.value)
  if (filterSampai.value) params.set('tanggal_sampai', filterSampai.value)
  try {
    const res: any = await api.get(`/riwayat-aset?page=${targetPage}&${params}`)
    items.value = res.data || []
    lastPage.value = res.meta?.last_page || res.last_page || 1
    page.value = targetPage
    expanded.value = new Set()
  } catch (e: any) {
    toast.show('Gagal memuat riwayat: ' + e.message, 'error')
  }
}

async function fetchUsers() {
  try {
    const res: any = await api.get('/users?per_page=100')
    users.value = (Array.isArray(res) ? res : res.data || []).filter((u: any) => u?.id)
  } catch {}
}

fetchData()
fetchUsers()
</script>
