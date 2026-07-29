<template>
  <div>
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-slate-900">GIS Layer</h1>
      <p class="text-slate-500 mt-1">Kelola layer peta aset daerah</p>
    </div>

    <AdminDataTable
      :columns="columns"
      :data="data"
      :page="page"
      :total-pages="totalPages"
      :loading="loading"
      search-placeholder="Cari layer..."
      create-label="Tambah Layer"
      @search="onSearch"
      @create="openCreate"
      @edit="openEdit"
      @delete="confirmDelete"
      @page-change="onPageChange"
    >
      <template #cell-warna="{ value }">
        <div class="flex items-center gap-2">
          <span class="w-5 h-5 rounded-full border border-slate-300" :style="{ backgroundColor: value || '#fff' }" />
          <span>{{ value || '—' }}</span>
        </div>
      </template>
      <template #cell-is_active="{ value }">
        <span
          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
          :class="value ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'"
        >
          {{ value ? 'Aktif' : 'Nonaktif' }}
        </span>
      </template>
    </AdminDataTable>

    <AdminFormModal
      :show="showModal"
      :title="editing ? 'Edit Layer' : 'Tambah Layer'"
      :loading="saving"
      @close="closeModal"
      @submit="save"
    >
      <UiInput
        v-model="form.nama_layer"
        label="Nama Layer"
        placeholder="Masukkan nama layer"
        :error="errors.nama_layer"
      />
      <UiInput
        v-model="form.warna"
        label="Warna"
        placeholder="Contoh: #FF0000"
        hint="Kode warna hex (contoh: #FF0000)"
        :error="errors.warna"
      />
      <UiInput
        v-model="form.icon_marker"
        label="Icon Marker"
        placeholder="Contoh: red-marker"
        hint="Nama icon marker"
        :error="errors.icon_marker"
      />
      <UiSelect
        v-model="form.is_active"
        label="Status"
        :options="statusOptions"
        :error="errors.is_active"
      />
    </AdminFormModal>

    <UiModal :show="showDeleteModal" title="Hapus Layer" @close="closeDeleteModal">
      <p class="text-slate-600">Yakin ingin menghapus layer <strong>{{ deleting?.nama_layer }}</strong>?</p>
      <div class="flex justify-end gap-3 pt-4 border-t border-slate-200 mt-4">
        <UiButton variant="secondary" @click="closeDeleteModal">Batal</UiButton>
        <UiButton variant="danger" :disabled="deleting" @click="doDelete">
          {{ deleting ? 'Menghapus...' : 'Hapus' }}
        </UiButton>
      </div>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin' })

const api = useApi()
const toast = useToast()

const columns = [
  { key: 'nama_layer', label: 'Nama Layer' },
  { key: 'warna', label: 'Warna' },
  { key: 'icon_marker', label: 'Icon Marker' },
  { key: 'is_active', label: 'Status' },
]

const statusOptions = [
  { value: 1, label: 'Aktif' },
  { value: 0, label: 'Nonaktif' },
]

const data = ref<any[]>([])
const page = ref(1)
const totalPages = ref(1)
const loading = ref(false)
const search = ref('')

const showModal = ref(false)
const showDeleteModal = ref(false)
const saving = ref(false)
const editing = ref<any | null>(null)
const deleting = ref<any | null>(null)

const form = reactive({ nama_layer: '', warna: '', icon_marker: '', is_active: 1 })
const errors = reactive({ nama_layer: '', warna: '', icon_marker: '', is_active: '' })

async function fetchData() {
  loading.value = true
  try {
    const params = new URLSearchParams({ page: String(page.value), per_page: '10' })
    if (search.value) params.set('search', search.value)
    const res: any = await api.get(`/gis-layer?${params}`)
    data.value = res.data || []
    totalPages.value = res.last_page || 1
  } catch (e: any) {
    toast.show(e.message, 'error')
  } finally {
    loading.value = false
  }
}

function onSearch(q: string) {
  search.value = q
  page.value = 1
  fetchData()
}

function onPageChange(p: number) {
  page.value = p
  fetchData()
}

function resetForm() {
  form.nama_layer = ''
  form.warna = ''
  form.icon_marker = ''
  form.is_active = 1
  errors.nama_layer = ''
  errors.warna = ''
  errors.icon_marker = ''
  errors.is_active = ''
}

function openCreate() {
  editing.value = null
  resetForm()
  showModal.value = true
}

function openEdit(row: any) {
  editing.value = row
  form.nama_layer = row.nama_layer
  form.warna = row.warna || ''
  form.icon_marker = row.icon_marker || ''
  form.is_active = row.is_active ? 1 : 0
  showModal.value = true
}

function closeModal() {
  showModal.value = false
  editing.value = null
}

async function save() {
  errors.nama_layer = ''
  errors.warna = ''
  errors.icon_marker = ''
  errors.is_active = ''
  saving.value = true
  try {
    if (editing.value) {
      await api.put(`/gis-layer/${editing.value.id}`, form)
      toast.show('Layer berhasil diperbarui', 'success')
    } else {
      await api.post('/gis-layer', form)
      toast.show('Layer berhasil ditambahkan', 'success')
    }
    closeModal()
    fetchData()
  } catch (e: any) {
    const msg = e.message || 'Terjadi kesalahan'
    try {
      const parsed = JSON.parse(msg)
      for (const key of ['nama_layer', 'warna', 'icon_marker', 'is_active']) {
        if (parsed[key]) (errors as any)[key] = Array.isArray(parsed[key]) ? parsed[key][0] : parsed[key]
      }
    } catch {
      toast.show(msg, 'error')
    }
  } finally {
    saving.value = false
  }
}

function confirmDelete(row: any) {
  deleting.value = row
  showDeleteModal.value = true
}

function closeDeleteModal() {
  showDeleteModal.value = false
  deleting.value = null
}

async function doDelete() {
  if (!deleting.value) return
  try {
    await api.del(`/gis-layer/${deleting.value.id}`)
    toast.show('Layer berhasil dihapus', 'success')
    closeDeleteModal()
    fetchData()
  } catch (e: any) {
    toast.show(e.message, 'error')
  }
}

onMounted(() => fetchData())
</script>
