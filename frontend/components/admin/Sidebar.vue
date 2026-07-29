<template>
  <aside :class="[
    'fixed left-0 top-0 h-full z-40 transition-all duration-300 flex flex-col border-r',
    collapsed ? 'w-16' : 'w-60',
    'bg-white border-slate-200 dark:bg-gray-900 dark:border-gray-800'
  ]">
    <div class="flex items-center gap-3 px-4 py-4 border-b border-slate-200 dark:border-gray-800">
      <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-teal-500 to-teal-600 flex items-center justify-center flex-shrink-0 shadow-sm">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
        </svg>
      </div>
      <div v-if="!collapsed" class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-slate-900 dark:text-gray-100 truncate">SIPEMKO</p>
        <p class="text-xs text-slate-500 dark:text-gray-400 truncate">PEMKO MEDAN</p>
      </div>
      <button v-if="!collapsed" class="p-1 rounded hover:bg-slate-100 dark:hover:bg-gray-800 text-slate-400 dark:text-gray-500" type="button">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
      </button>
    </div>

    <nav class="flex-1 py-4 overflow-y-auto space-y-0.5 px-2">
      <NuxtLink
        v-for="item in menuItems"
        :key="item.to"
        :to="item.to"
        :class="[
          'relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all',
          isActive(item.to)
            ? 'bg-teal-50 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300'
            : 'text-slate-600 dark:text-gray-400 hover:bg-slate-50 dark:hover:bg-gray-800 hover:text-slate-900 dark:hover:text-gray-200'
        ]"
        :title="collapsed ? item.label : ''"
      >
        <span class="w-5 h-5 flex-shrink-0" v-html="item.icon" />
        <span v-if="!collapsed" class="flex-1 truncate">{{ item.label }}</span>
        <span
          v-if="!collapsed && item.notifs"
          class="flex-shrink-0 h-5 min-w-[20px] px-1 rounded-full bg-red-500 text-white text-[11px] font-medium flex items-center justify-center"
        >
          {{ item.notifs }}
        </span>
      </NuxtLink>

      <div v-if="!collapsed" class="pt-4 mt-4 border-t border-slate-200 dark:border-gray-800">
        <p class="px-3 py-1 text-xs font-medium text-slate-400 dark:text-gray-500 uppercase tracking-wider">Account</p>
      </div>
      <NuxtLink
        v-for="item in bottomItems"
        :key="item.to"
        :to="item.to"
        :class="[
          'relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all',
          isActive(item.to)
            ? 'bg-teal-50 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300'
            : 'text-slate-600 dark:text-gray-400 hover:bg-slate-50 dark:hover:bg-gray-800 hover:text-slate-900 dark:hover:text-gray-200'
        ]"
        :title="collapsed ? item.label : ''"
      >
        <span class="w-5 h-5 flex-shrink-0" v-html="item.icon" />
        <span v-if="!collapsed" class="flex-1 truncate">{{ item.label }}</span>
      </NuxtLink>
    </nav>

    <button
      @click="$emit('toggle')"
      class="flex items-center gap-3 w-full px-4 py-3 text-sm font-medium border-t border-slate-200 dark:border-gray-800 text-slate-600 dark:text-gray-400 hover:bg-slate-50 dark:hover:bg-gray-800 transition-colors"
    >
      <div class="w-5 h-5 flex items-center justify-center flex-shrink-0">
        <svg
          class="w-4 h-4 transition-transform duration-300"
          :class="{ 'rotate-180': !collapsed }"
          fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
        </svg>
      </div>
      <span v-if="!collapsed" class="transition-opacity duration-200">Sembunyikan</span>
    </button>
  </aside>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'

defineProps<{ collapsed: boolean }>()
defineEmits<{ toggle: [] }>()

const route = useRoute()

function isActive(path: string) {
  if (path === '/admin') return route.path === '/admin'
  return route.path.startsWith(path)
}

const menuItems = [
  { to: '/admin', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>', label: 'Dashboard', notifs: 0 },
  { to: '/admin/opd', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>', label: 'OPD', notifs: 0 },
  { to: '/admin/kategori-aset', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>', label: 'Kategori', notifs: 0 },
  { to: '/admin/gis-layer', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>', label: 'GIS Layer', notifs: 0 },
  { to: '/admin/jenis-pemanfaatan', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>', label: 'Jenis', notifs: 0 },
  { to: '/admin/pihak-ketiga', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>', label: 'Pihak Ketiga', notifs: 0 },
  { to: '/admin/aset', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>', label: 'Aset', notifs: 0 },
  { to: '/admin/pemanfaatan', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>', label: 'Pemanfaatan', notifs: 0 },
  { to: '/admin/users', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/></svg>', label: 'Users', notifs: 0 },
  { to: '/admin/roles', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>', label: 'Roles', notifs: 0 },
]

const bottomItems = [
  { to: '/admin/settings', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>', label: 'Pengaturan' },
  { to: '/admin/bantuan', icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>', label: 'Bantuan' },
]
</script>
