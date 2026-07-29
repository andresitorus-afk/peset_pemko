<template>
  <div>
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 dark:text-gray-100">Dashboard</h1>
        <p class="text-slate-500 dark:text-gray-400 mt-1">Selamat datang kembali, {{ auth.user?.name || 'Pengguna' }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="p-6 rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
          <div class="p-2 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
        </div>
        <p class="text-sm font-medium text-slate-500 dark:text-gray-400">Total OPD</p>
        <p class="text-2xl font-bold text-slate-900 dark:text-gray-100 mt-1">{{ stats.total_opd }}</p>
      </div>

      <div class="p-6 rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
          <div class="p-2 bg-emerald-50 dark:bg-emerald-900/20 rounded-lg">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
        </div>
        <p class="text-sm font-medium text-slate-500 dark:text-gray-400">Total Aset</p>
        <p class="text-2xl font-bold text-slate-900 dark:text-gray-100 mt-1">{{ stats.total_aset }}</p>
      </div>

      <div class="p-6 rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
          <div class="p-2 bg-teal-50 dark:bg-teal-900/20 rounded-lg">
            <svg class="w-5 h-5 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
          </div>
        </div>
        <p class="text-sm font-medium text-slate-500 dark:text-gray-400">Pemanfaatan Aktif</p>
        <p class="text-2xl font-bold text-slate-900 dark:text-gray-100 mt-1">{{ stats.pemanfaatan_aktif }}</p>
      </div>

      <div class="p-6 rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
          <div class="p-2 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </div>
        </div>
        <p class="text-sm font-medium text-slate-500 dark:text-gray-400">Pihak Ketiga</p>
        <p class="text-2xl font-bold text-slate-900 dark:text-gray-100 mt-1">{{ stats.total_pihak_ketiga }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <div class="lg:col-span-2">
        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-slate-900 dark:text-gray-100">Aktivitas Terbaru</h3>
            <NuxtLink to="/admin/pemanfaatan" class="text-sm text-teal-600 dark:text-teal-400 hover:text-teal-700 font-medium">
              Lihat semua
            </NuxtLink>
          </div>
          <div class="space-y-3">
            <div v-for="(activity, i) in activities" :key="i" class="flex items-center gap-4 p-3 rounded-lg hover:bg-slate-50 dark:hover:bg-gray-800 transition-colors cursor-pointer">
              <div :class="['p-2 rounded-lg', activity.bg]">
                <span class="w-4 h-4 block" :class="activity.color" v-html="activity.icon" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-slate-900 dark:text-gray-100 truncate">{{ activity.title }}</p>
                <p class="text-xs text-slate-500 dark:text-gray-400 truncate">{{ activity.desc }}</p>
              </div>
              <span class="text-xs text-slate-400 dark:text-gray-500 flex-shrink-0">{{ activity.time }}</span>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-gray-100 mb-4">Statistik Cepat</h3>
          <div class="space-y-4">
            <div v-for="(stat, i) in quickStats" :key="i">
              <div class="flex justify-between items-center mb-1">
                <span class="text-sm text-slate-600 dark:text-gray-400">{{ stat.label }}</span>
                <span class="text-sm font-medium text-slate-900 dark:text-gray-100">{{ stat.value }}</span>
              </div>
              <div class="w-full bg-slate-200 dark:bg-gray-700 rounded-full h-2">
                <div :class="['h-2 rounded-full', stat.barColor]" :style="{ width: stat.pct + '%' }"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="rounded-xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-sm">
          <h3 class="text-lg font-semibold text-slate-900 dark:text-gray-100 mb-4">Menu Cepat</h3>
          <div class="grid grid-cols-2 gap-3">
            <NuxtLink
              v-for="(item, i) in quickLinks"
              :key="i"
              :to="item.to"
              class="flex flex-col items-center gap-2 p-4 rounded-xl border border-slate-200 dark:border-gray-700 bg-slate-50 dark:bg-gray-800 hover:border-teal-300 dark:hover:border-teal-600 hover:shadow-sm transition-all"
            >
              <span class="w-6 h-6" :class="item.color" v-html="item.icon" />
              <span class="text-xs font-medium text-slate-600 dark:text-gray-400 text-center">{{ item.label }}</span>
            </NuxtLink>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuth } from '~/composables/useAuth'

definePageMeta({ layout: 'admin' })

const api = useApi()
const auth = useAuth()

const stats = ref({ total_aset: 0, total_opd: 0, pemanfaatan_aktif: 0, total_pihak_ketiga: 0 })
const activities = ref<any[]>([])
const quickStats = ref<any[]>([])

const quickLinks = [
  { to: '/admin/aset', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>', label: 'Tambah Aset', color: 'text-teal-600 dark:text-teal-400' },
  { to: '/admin/pemanfaatan', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>', label: 'Pemanfaatan Baru', color: 'text-blue-600 dark:text-blue-400' },
  { to: '/admin/pihak-ketiga', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>', label: 'Mitra Baru', color: 'text-purple-600 dark:text-purple-400' },
  { to: '/admin/laporan', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.707.293V19a2 2 0 01-2 2z"/></svg>', label: 'Laporan', color: 'text-amber-600 dark:text-amber-400' },
]

const activityIcons: Record<string, { icon: string, bg: string, color: string }> = {
  Pemanfaatan: { icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>', bg: 'bg-emerald-50 dark:bg-emerald-900/20', color: 'text-emerald-600 dark:text-emerald-400' },
  Pemeliharaan: { icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', bg: 'bg-purple-50 dark:bg-purple-900/20', color: 'text-purple-600 dark:text-purple-400' },
  Mutasi: { icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>', bg: 'bg-purple-50 dark:bg-purple-900/20', color: 'text-purple-600 dark:text-purple-400' },
}

function timeAgo(date: string): string {
  const diff = Date.now() - new Date(date).getTime()
  const mins = Math.floor(diff / 60000)
  if (mins < 1) return 'baru saja'
  if (mins < 60) return `${mins} menit lalu`
  const hours = Math.floor(mins / 60)
  if (hours < 24) return `${hours} jam lalu`
  const days = Math.floor(hours / 24)
  if (days < 30) return `${days} hari lalu`
  return `${Math.floor(days / 30)} bulan lalu`
}

async function fetchDashboard() {
  try {
    const res: any = await api.get('/dashboard')
    stats.value = {
      total_aset: res.total_aset || 0,
      total_opd: res.total_opd || 0,
      pemanfaatan_aktif: res.pemanfaatan_aktif || 0,
      total_pihak_ketiga: res.total_pihak_ketiga || 0,
    }
    activities.value = (res.aktivitas_terbaru || []).map((a: any) => {
      const icon = activityIcons[a.aksi] || activityIcons.Pemanfaatan
      return { ...icon, title: a.aksi, desc: a.deskripsi, time: timeAgo(a.created_at) }
    })
    const totalAset = res.total_aset || 1
    const asetDimanfaatkan = (res.aset_per_status && res.aset_per_status.Dimanfaatkan) || 0
    const totalPemanfaatan = res.pemanfaatan_aktif || 0
    const totalOpd = res.total_opd || 1
    const opdCount = res.total_opd || 1
    quickStats.value = [
      { label: 'Aset Termanfaatkan', value: `${Math.round((asetDimanfaatkan / totalAset) * 100)}%`, pct: Math.round((asetDimanfaatkan / totalAset) * 100), barColor: 'bg-teal-500' },
      { label: 'Kontrak Aktif', value: `${totalPemanfaatan} kontrak`, pct: Math.min(100, Math.round((totalPemanfaatan / 50) * 100)), barColor: 'bg-blue-500' },
      { label: 'OPD Terdaftar', value: `${totalOpd} OPD`, pct: 100, barColor: 'bg-emerald-500' },
      { label: 'Nilai Perolehan', value: `Rp ${(res.total_nilai_perolehan || 0).toLocaleString('id-ID')}`, pct: Math.min(100, Math.round((res.total_nilai_perolehan / 1e12) * 100)), barColor: 'bg-amber-500' },
    ]
  } catch (_) {}
}

fetchDashboard()
</script>
