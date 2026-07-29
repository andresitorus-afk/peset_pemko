<template>
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm" :class="padding ? 'p-6' : ''">
    <div v-if="title" class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-slate-900">{{ title }}</h3>
      <slot name="action" />
    </div>
    <slot />
  </div>
</template>

<script setup lang="ts">
defineProps<{
  title?: string
  padding?: boolean
}>()
</script>
