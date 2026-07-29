<template>
  <button
    :class="[
      'inline-flex items-center justify-center font-medium rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed',
      variant === 'primary' ? 'bg-teal-600 hover:bg-teal-700 text-white shadow-sm' : '',
      variant === 'secondary' ? 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-300' : '',
      variant === 'danger' ? 'bg-red-600 hover:bg-red-700 text-white shadow-sm' : '',
      variant === 'ghost' ? 'hover:bg-slate-100 text-slate-600' : '',
      size === 'sm' ? 'px-3 py-2 text-sm' : size === 'lg' ? 'px-6 py-3 text-base' : 'px-4 py-2.5 text-sm',
    ]"
    v-bind="$attrs"
  >
    <slot />
  </button>
</template>

<script setup lang="ts">
defineProps<{
  variant?: 'primary' | 'secondary' | 'danger' | 'ghost'
  size?: 'sm' | 'md' | 'lg'
}>()
</script>
