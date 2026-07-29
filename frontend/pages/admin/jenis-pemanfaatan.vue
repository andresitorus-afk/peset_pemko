<template>
  <div>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Jenis Pemanfaatan</h1>

    <AdminDataTable
      :columns="columns"
      :data="items"
      :page="page"
      :total-pages="lastPage"
      search-placeholder="Cari jenis pemanfaatan..."
      create-label="Tambah Jenis Pemanfaatan"
      @search="search = $event; fetchData()"
      @create="openCreate()"
      @edit="openEdit($event)"
      @delete="confirmDelete($event)"
      @page-change="page = $event; fetchData()"
    />

    <AdminFormModal
      :show="modal"
      :title="editing ? 'Edit Jenis Pemanfaatan' : 'Tambah Jenis Pemanfaatan'"
      :loading="saving"
      @close="closeModal"
      @submit="save"
    >
      <UiInput v-model="form.kode" label="Kode" required placeholder="SEWA" />

      <UiInput v-model="form.nama" label="Nama" required placeholder="Sewa" />

      <label class="block text-sm font-medium text-slate-700 mb-1.5">Dasar Hukum</label>
      <textarea v-model="form.dasar_hukum" class="w-full px-4 py-3 border border-slate-300 rounded-lg text-base focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none" rows="3"></textarea>

      <label class="block text-sm font-medium text-slate-700 mb-1.5">Ketentuan</label>
      <textarea v-model="form.ketentuan" class="w-full px-4 py-3 border border-slate-300 rounded-lg text-base focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none" rows="3"></textarea>
    </AdminFormModal>

    <UiModal :show="deleteModal" title="Hapus Jenis Pemanfaatan" @close="deleteModal = false">
      <p class="text-slate-600">Apakah anda yakin ingin menghapus jenis pemanfaatan <strong>{{ deletingItem?.nama }}</strong>?</p>
      <div class="flex justify-end gap-3 pt-4">
        <UiButton variant="secondary" @click="deleteModal = false">Batal</UiButton>
        <UiButton variant="danger" @click="doDelete" :disabled="deleting">{{ deleting ? 'Menghapus...' : 'Hapus' }}</UiButton>
      </div>
    </UiModal>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: 'admin' })

const api = useApi()
const toast = useToast()

const items = ref<any[]>([])
const page = ref(1)
const lastPage = ref(1)
const search = ref('')
const modal = ref(false)
const saving = ref(false)
const editing = ref<any | null>(null)
const deleteModal = ref(false)
const deleting = ref(false)
const deletingItem = ref<any | null>(null)

const form = ref({
  kode: '',
  nama: '',
  dasar_hukum: '',
  ketentuan: '',
})

const columns = [
  { key: 'kode', label: 'Kode' },
  { key: 'nama', label: 'Nama' },
  { key: 'dasar_hukum', label: 'Dasar Hukum' },
]

async function fetchData() {
  try {
    const res = await api.get(`/jenis-pemanfaatan?page=${page.value}&search=${search.value}`)
    if (Array.isArray(res)) {
      items.value = res
    } else {
      items.value = res.data || []
      lastPage.value = res.last_page || 1
    }
  } catch (e: any) {
    toast.show('Gagal memuat data: ' + e.message, 'error')
  }
}

function openCreate() {
  editing.value = null
  form.value = { kode: '', nama: '', dasar_hukum: '', ketentuan: '' }
  modal.value = true
}

function openEdit(item: any) {
  editing.value = item
  form.value = { ...item }
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
      await api.put(`/jenis-pemanfaatan/${editing.value.id}`, form.value)
      toast.show('Jenis Pemanfaatan berhasil diupdate', 'success')
    } else {
      await api.post('/jenis-pemanfaatan', form.value)
      toast.show('Jenis Pemanfaatan berhasil ditambahkan', 'success')
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
    await api.del(`/jenis-pemanfaatan/${deletingItem.value.id}`)
    toast.show('Jenis Pemanfaatan berhasil dihapus', 'success')
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
