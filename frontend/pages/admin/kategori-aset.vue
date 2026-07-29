<template>
  <div>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Kategori Aset</h1>

    <AdminDataTable
      :columns="columns"
      :data="filteredItems"
      search-placeholder="Cari kategori..."
      create-label="Tambah Kategori"
      @search="searchQuery = $event"
      @create="openCreate()"
      @edit="openEdit($event)"
      @delete="confirmDelete($event)"
    />

    <AdminFormModal
      :show="modal"
      :title="editing ? 'Edit Kategori' : 'Tambah Kategori'"
      :loading="saving"
      @close="closeModal"
      @submit="save"
    >
      <UiSelect
        v-model="form.kode_kib"
        label="KIB"
        placeholder="Pilih KIB"
        :options="[
          { value: 'KIB A', label: 'KIB A - Tanah' },
          { value: 'KIB C', label: 'KIB C - Gedung dan Bangunan' },
        ]"
        required
      />
      <UiInput v-model="form.kode_kategori" label="Kode Kategori" placeholder="1.3.1.01.01.01.001" required />
      <UiInput v-model="form.nama_kategori" label="Nama Kategori" placeholder="Nama uraian kategori" required />
      <UiSelect
        v-model="form.parent_id"
        label="Parent Kategori"
        placeholder="Pilih parent (opsional)"
        :options="parentOptions"
      />
      <label class="flex items-center gap-3 cursor-pointer">
        <input type="checkbox" v-model="form.is_leaf" class="w-4 h-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500" />
        <span class="text-sm font-medium text-slate-700">Kategori Detail (Leaf)</span>
      </label>
    </AdminFormModal>

    <UiModal :show="deleteModal" title="Hapus Kategori" @close="deleteModal = false">
      <p class="text-slate-600">Apakah anda yakin ingin menghapus kategori <strong>{{ deletingItem?.nama_kategori }}</strong>?</p>
      <div class="flex justify-end gap-3 pt-4">
        <UiButton variant="secondary" @click="deleteModal = false">Batal</UiButton>
        <UiButton variant="danger" @click="doDelete" :disabled="deleting">{{ deleting ? 'Menghapus...' : 'Hapus' }}</UiButton>
      </div>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

definePageMeta({ layout: 'admin' })

const api = useApi()
const toast = useToast()

const items = ref<any[]>([])
const searchQuery = ref('')
const modal = ref(false)
const saving = ref(false)
const editing = ref<any | null>(null)
const deleteModal = ref(false)
const deleting = ref(false)
const deletingItem = ref<any | null>(null)

const form = ref({
  kode_kib: '',
  kode_kategori: '',
  nama_kategori: '',
  parent_id: '',
  is_leaf: false,
})

const columns = [
  { key: 'kode_kib', label: 'KIB' },
  { key: 'kode_kategori', label: 'Kode' },
  { key: 'nama_kategori', label: 'Nama Kategori' },
  { key: 'is_leaf', label: 'Detail' },
]

const parentOptions = computed(() => {
  if (!form.value.kode_kib) return []
  return items.value
    .filter((i: any) => i?.kode_kib === form.value.kode_kib && i?.id !== editing.value?.id)
    .filter((i: any) => i?.id)
    .map((i: any) => ({ value: i.id, label: `${i.nama_kategori} (${i.kode_kategori})` }))
})

const filteredItems = computed(() => {
  let result = items.value.filter((i: any) => i.kode_kib === 'KIB A' || i.kode_kib === 'KIB C')
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter((i: any) =>
      i.nama_kategori?.toLowerCase().includes(q) ||
      i.kode_kategori?.toLowerCase().includes(q)
    )
  }
  return result
})

async function fetchData() {
  try {
    const res = await api.get('/kategori-aset')
    items.value = Array.isArray(res) ? res : res.data || []
  } catch (e: any) {
    toast.show('Gagal memuat data: ' + e.message, 'error')
  }
}

function openCreate() {
  editing.value = null
  form.value = { kode_kib: '', kode_kategori: '', nama_kategori: '', parent_id: '', is_leaf: false }
  modal.value = true
}

function openEdit(item: any) {
  editing.value = item
  form.value = {
    kode_kib: item.kode_kib || '',
    kode_kategori: item.kode_kategori || '',
    nama_kategori: item.nama_kategori || '',
    parent_id: item.parent_id || '',
    is_leaf: !!item.is_leaf,
  }
  modal.value = true
}

function closeModal() {
  modal.value = false
  editing.value = null
}

async function save() {
  saving.value = true
  try {
    if (editing.value) {
      await api.put(`/kategori-aset/${editing.value.id}`, form.value)
      toast.show('Kategori berhasil diupdate', 'success')
    } else {
      await api.post('/kategori-aset', form.value)
      toast.show('Kategori berhasil ditambahkan', 'success')
    }
    closeModal()
    fetchData()
  } catch (e: any) {
    toast.show('Gagal menyimpan: ' + e.message, 'error')
  } finally {
    saving.value = false
  }
}

function confirmDelete(item: any) {
  deletingItem.value = item
  deleteModal.value = true
}

async function doDelete() {
  if (!deletingItem.value) return
  deleting.value = true
  try {
    await api.del(`/kategori-aset/${deletingItem.value.id}`)
    toast.show('Kategori berhasil dihapus', 'success')
    deleteModal.value = false
    deletingItem.value = null
    fetchData()
  } catch (e: any) {
    toast.show('Gagal menghapus: ' + e.message, 'error')
  } finally {
    deleting.value = false
  }
}

fetchData()
</script>
