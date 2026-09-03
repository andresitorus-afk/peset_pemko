<template>
  <header :class="[
    'fixed top-0 right-0 h-16 bg-white border-b border-slate-200 dark:bg-gray-900 dark:border-gray-800 flex items-center justify-between px-6 transition-all duration-300 z-30',
    sidebarCollapsed ? 'left-16' : 'left-60'
  ]">
    <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-gray-400">
      <NuxtLink to="/admin" class="hover:text-teal-600 dark:hover:text-teal-400">Dashboard</NuxtLink>
      <template v-for="(crumb, i) in breadcrumbs" :key="i">
        <svg class="w-4 h-4 text-slate-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span v-if="i === breadcrumbs.length - 1" class="text-slate-900 dark:text-gray-100 font-medium">{{ crumb }}</span>
        <NuxtLink v-else :to="'/admin/' + crumb.toLowerCase()" class="hover:text-teal-600 dark:hover:text-teal-400">{{ crumb }}</NuxtLink>
      </template>
    </div>
    <div class="flex items-center gap-3">
      <button
        class="relative p-2 rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-500 dark:text-gray-400 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors"
        type="button"
        title="Notifikasi"
      >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <span class="absolute -top-1 -right-1 h-2.5 w-2.5 bg-red-500 rounded-full border-2 border-white dark:border-gray-900"></span>
      </button>

      <button
        @click="$emit('toggleDark')"
        class="flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-slate-500 dark:text-gray-400 hover:bg-slate-50 dark:hover:bg-gray-700 transition-colors"
        type="button"
        :title="isDark ? 'Mode Terang' : 'Mode Gelap'"
      >
        <svg v-if="isDark" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
      </button>

      <div class="flex items-center gap-3 pl-3 border-l border-slate-200 dark:border-gray-700">
        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-teal-400 to-teal-600 flex items-center justify-center text-white text-sm font-semibold flex-shrink-0">
          {{ (user?.name || 'U').charAt(0).toUpperCase() }}
        </div>
        <div class="hidden sm:block">
          <p class="text-sm font-medium text-slate-900 dark:text-gray-100 leading-tight">{{ user?.name || 'Pengguna' }}</p>
          <p class="text-xs text-slate-500 dark:text-gray-400">{{ user?.role?.name || '—' }}</p>
        </div>
        <button @click="handleLogout" type="button" class="text-slate-400 dark:text-gray-500 hover:text-red-600 dark:hover:text-red-400 transition-colors ml-1" title="Logout">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
          </svg>
        </button>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '~/composables/useAuth'

defineProps<{ sidebarCollapsed: boolean; isDark: boolean }>()
defineEmits<{ toggleDark: [] }>()

const route = useRoute()
const router = useRouter()
const { user, logout } = useAuth()
const config = useRuntimeConfig()

async function handleLogout() {
  await logout()
  router.push('/login')
}

const breadcrumbs = computed(() => {
  const parts = route.path.split('/').filter(Boolean)
  if (parts.length <= 1) return []
  const crumbs: string[] = []
  for (const p of parts) {
    if (p === 'admin') continue
    crumbs.push(p.replace(/-/g, ' ').replace(/\b\w/g, c => c.toUpperCase()))
  }
  return crumbs
})
</script>
