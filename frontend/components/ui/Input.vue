<template>
  <div>
    <label v-if="label" :for="inputId" class="block text-sm font-medium text-slate-700 mb-1.5">{{ label }}</label>
    <div class="relative">
      <div v-if="prefixIcon" class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
        <span v-html="prefixIcon" />
      </div>
      <input
        :id="inputId"
        :type="inputType"
        :class="[
          'w-full px-4 py-3 border rounded-lg text-base transition-all duration-200 outline-none',
          'focus:ring-2 focus:ring-teal-500 focus:border-teal-500',
          error ? 'border-red-400 bg-red-50' : 'border-slate-300 bg-white',
          prefixIcon ? 'pl-10' : '',
          (suffixIcon || showPasswordToggle) ? 'pr-10' : '',
        ]"
        v-bind="$attrs"
        :value="modelValue"
        @input="handleInput"
      />
      <button
        v-if="suffixIcon && !showPasswordToggle"
        type="button"
        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
        @click="$emit('click:suffix')"
        :aria-label="suffixLabel || 'Toggle'"
        tabindex="-1"
      >
        <span v-html="suffixIcon" />
      </button>
      <button
        v-if="showPasswordToggle"
        type="button"
        class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition-colors"
        @click="togglePassword"
        :aria-label="showPassword ? 'Sembunyikan sandi' : 'Tampilkan sandi'"
        tabindex="-1"
      >
        <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
        </svg>
      </button>
    </div>
    <p v-if="error" role="alert" class="mt-1 text-sm text-red-600">{{ error }}</p>
    <p v-if="hint && !error" class="mt-1 text-sm text-slate-500">{{ hint }}</p>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, useAttrs } from 'vue'

const props = defineProps<{
  modelValue?: string | number
  label?: string
  error?: string
  hint?: string
  prefixIcon?: string
  suffixIcon?: string
  suffixLabel?: string
  showPasswordToggle?: boolean
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
  'click:suffix': []
}>()

const attrs = useAttrs()
const showPassword = ref(false)
const inputId = computed(() => (attrs as any).id || undefined)
const inputType = computed(() => props.showPasswordToggle ? (showPassword.value ? 'text' : 'password') : (attrs as any).type || 'text')

function togglePassword() {
  showPassword.value = !showPassword.value
}

function handleInput(e: Event) {
  emit('update:modelValue', (e.target as HTMLInputElement).value)
}
</script>
