<template>
  <div>
    <label v-if="label" class="block text-sm font-medium text-slate-700 mb-1.5">{{ label }}</label>
    <div class="relative">
      <select
        :class="[
          'w-full px-4 py-3 border rounded-lg text-base transition-all duration-200 outline-none appearance-none bg-white',
          'focus:ring-2 focus:ring-teal-500 focus:border-teal-500',
          error ? 'border-red-400 bg-red-50' : 'border-slate-300',
        ]"
        :value="modelValue"
        @change="handleChange"
        v-bind="$attrs"
      >
        <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
        <option v-for="opt in options" :key="opt.value" :value="opt.value">
          {{ opt.label }}
        </option>
      </select>
      <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </div>
    <p v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  modelValue?: string | number
  label?: string
  error?: string
  placeholder?: string
  options: { value: string | number; label: string }[]
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

function handleChange(e: Event) {
  emit('update:modelValue', (e.target as HTMLSelectElement).value)
}
</script>
