<template>
  <UiModal :show="show" :title="title" @close="$emit('close')">
    <form @submit.prevent="$emit('submit')" class="space-y-4">
      <slot />
      <div class="flex justify-end gap-3 pt-4 border-t border-slate-200">
        <UiButton variant="secondary" @click="$emit('close')" type="button">Batal</UiButton>
        <UiButton variant="primary" type="submit" :disabled="loading">
          {{ loading ? 'Menyimpan...' : 'Simpan' }}
        </UiButton>
      </div>
    </form>
  </UiModal>
</template>

<script setup lang="ts">
defineProps<{
  show: boolean
  title: string
  loading?: boolean
}>()

defineEmits<{
  close: []
  submit: []
}>()
</script>
