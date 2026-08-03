<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold text-slate-900">Laporan & Ekspor</h1>
      <div class="flex gap-2">
        <UiButton variant="secondary" size="sm" @click="exportFile('aset', 'laporan_aset.xlsx')">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
          Ekspor Aset
        </UiButton>
        <UiButton variant="secondary" size="sm" @click="exportFile('pemanfaatan', 'laporan_pemanfaatan.xlsx')">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
          Ekspor Pemanfaatan
        </UiButton>
        <UiButton variant="primary" size="sm" @click="exportFile('master', 'master_pemko.xlsx')">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
          Ekspor Master
        </UiButton>
      </div>
    </div>

    <div v-if="loading" class="text-center py-12 text-slate-400 text-sm">Memuat laporan...</div>

    <template v-else>
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-slate-900 mb-1">Tren Pemanfaatan per Tahun</h3>
          <p class="text-xs text-slate-400 mb-4">Jumlah pemanfaatan yang mulai berjalan tiap tahun</p>
          <div v-if="tren.length" class="space-y-3">
            <div v-for="(item, i) in tren" :key="i">
              <div class="flex justify-between items-center mb-1">
                <span class="text-sm text-slate-600">{{ item.tahun }}</span>
                <span class="text-sm font-medium text-slate-900">{{ item.jumlah }}</span>
              </div>
              <div class="w-full bg-slate-200 rounded-full h-3">
                <div class="h-3 rounded-full bg-teal-500" :style="{ width: barWidth(item.jumlah, maxTren) + '%' }"></div>
              </div>
            </div>
          </div>
          <p v-else class="text-sm text-slate-400">Belum ada data pemanfaatan.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-slate-900 mb-1">Pemanfaatan per Jenis</h3>
          <p class="text-xs text-slate-400 mb-4">Distribusi jenis skema pemanfaatan</p>
          <div v-if="perJenis.length" class="space-y-3">
            <div v-for="(item, i) in perJenis" :key="i">
              <div class="flex justify-between items-center mb-1">
                <span class="text-sm text-slate-600">{{ item.nama }}</span>
                <span class="text-sm font-medium text-slate-900">{{ item.total }}</span>
              </div>
              <div class="w-full bg-slate-200 rounded-full h-3">
                <div class="h-3 rounded-full bg-blue-500" :style="{ width: barWidth(item.total, maxJenis) + '%' }"></div>
              </div>
            </div>
          </div>
          <p v-else class="text-sm text-slate-400">Belum ada data pemanfaatan.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-slate-900 mb-1">Kontribusi per Tahun</h3>
          <p class="text-xs text-slate-400 mb-4">Total kontribusi tahunan (Rp) yang dijanjikan</p>
          <div v-if="kontribusi.length" class="space-y-3">
            <div v-for="(item, i) in kontribusi" :key="i">
              <div class="flex justify-between items-center mb-1">
                <span class="text-sm text-slate-600">{{ item.tahun }}</span>
                <span class="text-sm font-medium text-slate-900">Rp {{ formatRupiah(item.total) }}</span>
              </div>
              <div class="w-full bg-slate-200 rounded-full h-3">
                <div class="h-3 rounded-full bg-amber-500" :style="{ width: barWidth(item.total, maxKontribusi) + '%' }"></div>
              </div>
            </div>
          </div>
          <p v-else class="text-sm text-slate-400">Belum ada data kontribusi.</p>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-slate-900 mb-1">Pemanfaatan per OPD</h3>
          <p class="text-xs text-slate-400 mb-4">Top 10 OPD pemilik aset termanfaatkan</p>
          <div v-if="perOpd.length" class="space-y-3">
            <div v-for="(item, i) in perOpd" :key="i">
              <div class="flex justify-between items-center mb-1">
                <span class="text-sm text-slate-600 truncate">{{ item.nama_opd }}</span>
                <span class="text-sm font-medium text-slate-900">{{ item.total }}</span>
              </div>
              <div class="w-full bg-slate-200 rounded-full h-3">
                <div class="h-3 rounded-full bg-violet-500" :style="{ width: barWidth(item.total, maxOpd) + '%' }"></div>
              </div>
            </div>
          </div>
          <p v-else class="text-sm text-slate-400">Belum ada data pemanfaatan.</p>
        </div>
      </div>

      <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-lg font-semibold text-slate-900 mb-4">Kontrak Berakhir 90 Hari</h3>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-slate-200">
                <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4">Aset</th>
                <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4">Pihak Ketiga</th>
                <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4">No. Perjanjian</th>
                <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4">Selesai</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in kontrakBerakhir" :key="i" class="border-b border-slate-100 hover:bg-slate-50">
                <td class="py-3 px-4 text-sm text-slate-700">{{ item.aset }}</td>
                <td class="py-3 px-4 text-sm text-slate-700">{{ item.pihak_ketiga }}</td>
                <td class="py-3 px-4 text-sm text-slate-700">{{ item.nomor_perjanjian || '—' }}</td>
                <td class="py-3 px-4 text-sm">
                  <span class="px-2 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-semibold">{{ item.tanggal_selesai }}</span>
                </td>
              </tr>
              <tr v-if="!kontrakBerakhir.length">
                <td colspan="4" class="py-8 text-center text-slate-400 text-sm">Tidak ada kontrak yang berakhir dalam 90 hari.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin' })

const api = useApi()
const toast = useToast()

const loading = ref(false)
const tren = ref<any[]>([])
const perJenis = ref<any[]>([])
const kontribusi = ref<any[]>([])
const perOpd = ref<any[]>([])
const kontrakBerakhir = ref<any[]>([])

const maxTren = computed(() => Math.max(1, ...tren.value.map((t: any) => t.jumlah)))
const maxJenis = computed(() => Math.max(1, ...perJenis.value.map((t: any) => t.total)))
const maxKontribusi = computed(() => Math.max(1, ...kontribusi.value.map((t: any) => Number(t.total))))
const maxOpd = computed(() => Math.max(1, ...perOpd.value.map((t: any) => t.total)))

function barWidth(value: number, max: number): number {
  return Math.max(4, Math.round((Number(value) / max) * 100))
}

function formatRupiah(val: any): string {
  return Number(val || 0).toLocaleString('id-ID')
}

async function exportFile(kind: string, filename: string) {
  try {
    await api.download(`/laporan/${kind}`, filename)
    toast.show(`${filename} berhasil diunduh`, 'success')
  } catch (e: any) {
    toast.show('Gagal mengunduh: ' + e.message, 'error')
  }
}

async function fetchStatistik() {
  loading.value = true
  try {
    const res: any = await api.get('/laporan/statistik')
    tren.value = Object.entries(res.tren_pemanfaatan || {}).map(([tahun, jumlah]: any) => ({ tahun, jumlah }))
    kontribusi.value = Object.entries(res.kontribusi_per_tahun || {}).map(([tahun, total]: any) => ({ tahun, total }))
    perJenis.value = res.pemanfaatan_per_jenis || []
    perOpd.value = res.pemanfaatan_per_opd || []
    kontrakBerakhir.value = res.kontrak_berakhir || []
  } catch (e: any) {
    toast.show('Gagal memuat laporan: ' + e.message, 'error')
  } finally {
    loading.value = false
  }
}

fetchStatistik()
</script>
