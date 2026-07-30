<template>
  <UiCard>
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
      <div class="relative w-full sm:w-72">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input
          v-model="searchQuery"
          :placeholder="searchPlaceholder"
          class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none"
          @input="$emit('search', searchQuery)"
        />
      </div>
      <UiButton variant="primary" @click="$emit('create')">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        {{ createLabel }}
      </UiButton>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full">
        <thead>
          <tr class="border-b border-slate-200">
            <th v-for="col in columns" :key="col.key" class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4">
              {{ col.label }}
            </th>
            <th class="text-right text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4 w-24">Aksi</th>
          </tr>
        </thead>
        <tbody>
            <tr v-for="(row, index) in data" :key="row?.id ?? index" class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
            <td v-for="col in columns" :key="col.key" class="py-3 px-4 text-sm text-slate-700">
              <slot :name="`cell-${col.key}`" :row="row" :value="getNestedValue(row, col.key)">
                {{ getNestedValue(row, col.key) }}
              </slot>
            </td>
            <td class="py-3 px-4 text-right">
              <div class="flex items-center justify-end gap-1">
                <button @click="$emit('edit', row)" type="button" class="p-2 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-teal-600 transition-colors" title="Edit">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
                <button @click="$emit('delete', row)" type="button" class="p-2 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600 transition-colors" title="Hapus">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </td>
          </tr>
          <tr v-if="!data.length">
            <td :colspan="columns.length + 1" class="py-12 text-center text-slate-400">
              <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
              </svg>
              Tidak ada data
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="totalPages> 1" class="flex items-center justify-between pt-4 border-t border-slate-200 mt-4">
      <p class="text-sm text-slate-500">Halaman {{ page }} dari {{ totalPages }}</p>
      <div class="flex gap-1">
        <button :disabled="page <= 1" @click="$emit('page-change', page - 1)" type="button" class="px-3 py-1.5 rounded text-sm border border-slate-300 hover:bg-slate-50 disabled:opacity-50">
          Sebelumnya
        </button>
        <button :disabled="page >= totalPages" @click="$emit('page-change', page + 1)" type="button" class="px-3 py-1.5 rounded text-sm border border-slate-300 hover:bg-slate-50 disabled:opacity-50">
          Berikutnya
        </button>
      </div>
    </div>
  </UiCard>
</template>

<script setup lang="ts">
import { ref } from 'vue'

defineProps<{
  columns: { key: string; label: string }[]
  data: any[]
  page?: number
  totalPages?: number
  searchPlaceholder?: string
  createLabel?: string
  loading?: boolean
}>()

defineEmits<{
  search: [query: string]
  create: []
  edit: [row: any]
  delete: [row: any]
  pageChange: [page: number]
}>()

const searchQuery = ref('')

function getNestedValue(obj: any, path: string): any {
  return path.split('.').reduce((acc, part) => acc?.[part], obj) ?? '—'
}
</script>
