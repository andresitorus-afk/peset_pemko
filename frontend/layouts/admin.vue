<template>
  <div :class="['min-h-screen bg-slate-50', { dark: isDark }]">
    <div class="flex min-h-screen w-full bg-slate-50 text-slate-900 dark:bg-gray-950 dark:text-gray-100">
      <AdminSidebar :collapsed="sidebarCollapsed" :is-dark="isDark" @toggle="sidebarCollapsed = !sidebarCollapsed" @toggle-dark="toggleDark" />
      <AdminTopBar :sidebar-collapsed="sidebarCollapsed" :is-dark="isDark" @toggle-dark="toggleDark" />
      <main :class="['pt-16 min-h-screen transition-all duration-300', sidebarCollapsed ? 'ml-16' : 'ml-60']">
        <div class="p-6">
          <slot />
        </div>
      </main>
      <UiToast />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const sidebarCollapsed = ref(false)
const isDark = ref(false)

function toggleDark() {
  isDark.value = !isDark.value
  document.documentElement.classList.toggle('dark', isDark.value)
}
</script>
