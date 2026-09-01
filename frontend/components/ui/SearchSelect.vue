<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-slate-700 mb-1.5">{{ label }}</label>
    <div class="relative">
      <button
        type="button"
        class="w-full px-4 py-3 border border-slate-300 rounded-lg text-base text-left flex items-center justify-between gap-2 bg-white focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none"
        @click="toggle"
      >
        <span :class="selected ? 'text-slate-900' : 'text-slate-400'">{{ selected?.label || placeholder }}</span>
        <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </button>
      <div
        v-if="open"
        class="absolute z-20 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg"
      >
        <div class="p-2 border-b border-slate-100">
          <input
            v-model="query"
            autofocus
            type="text"
            class="w-full px-3 py-2 border border-slate-300 rounded-md text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none"
            placeholder="Cari..."
            @keydown.esc="open = false"
          />
        </div>
        <ul class="max-h-60 overflow-y-auto">
          <li
            v-if="!filtered.length"
            class="px-4 py-2 text-sm text-slate-400"
          >Tidak ada data</li>
          <li
            v-for="opt in filtered"
            :key="opt.value"
            class="px-4 py-2 text-sm hover:bg-teal-50 cursor-pointer"
            @mousedown.prevent="select(opt)"
          >
            {{ opt.label }}
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const props = defineProps<{
  modelValue?: string | number
  label?: string
  placeholder?: string
  options: { value: string | number; label: string }[]
}>()

const emit = defineEmits<{ 'update:modelValue': [value: string] }>()

const open = ref(false)
const query = ref('')

const options = computed(() => props.options || [])
const selected = computed(() => props.modelValue ? options.value.find(o => String(o.value) === String(props.modelValue)) : undefined)
const filtered = computed(() => {
  const q = query.value.trim().toLowerCase()
  if (!q) return options.value
  return options.value.filter(o => o.label.toLowerCase().includes(q))
})

function toggle() {
  open.value = !open.value
  if (open.value) query.value = ''
}

function select(opt: { value: string | number }) {
  emit('update:modelValue', String(opt.value))
  open.value = false
  query.value = ''
}
</script>
