<template>
  <div>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">OPD</h1>

    <AdminDataTable
      :columns="columns"
      :data="items"
      :page="page"
      :total-pages="lastPage"
      search-placeholder="Cari OPD..."
      create-label="Tambah OPD"
      @search="search = $event; fetchData()"
      @create="openCreate()"
      @edit="openEdit($event)"
      @delete="confirmDelete($event)"
      @page-change="page = $event; fetchData()"
    />

    <AdminFormModal
      :show="modal"
      :title="editing ? 'Edit OPD' : 'Tambah OPD'"
      :loading="saving"
      @close="closeModal"
      @submit="save"
    >
      <UiInput v-model="form.kode_opd" label="Kode OPD" required placeholder="OPD.001" />
      <UiInput v-model="form.nama_opd" label="Nama OPD" required placeholder="Dinas Pendidikan" />
      <UiInput v-model="form.alamat" label="Alamat" />
      <UiInput v-model="form.telepon" label="Telepon" />
      <UiInput v-model="form.kepala_opd" label="Kepala OPD" />
      <UiInput v-model="form.nip_kepala" label="NIP Kepala" />
    </AdminFormModal>

    <UiModal :show="deleteModal" title="Hapus OPD" @close="deleteModal = false">
      <p class="text-slate-600">Apakah anda yakin ingin menghapus OPD <strong>{{ deletingItem?.nama_opd }}</strong>?</p>
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
  kode_opd: '',
  nama_opd: '',
  alamat: '',
  telepon: '',
  kepala_opd: '',
  nip_kepala: '',
})

const columns = [
  { key: 'kode_opd', label: 'Kode' },
  { key: 'nama_opd', label: 'Nama OPD' },
  { key: 'telepon', label: 'Telepon' },
  { key: 'kepala_opd', label: 'Kepala' },
]

async function fetchData() {
  try {
    const res = await api.get(`/opd?page=${page.value}&search=${search.value}`)
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
  form.value = { kode_opd: '', nama_opd: '', alamat: '', telepon: '', kepala_opd: '', nip_kepala: '' }
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
      await api.put(`/opd/${editing.value.id}`, form.value)
      toast.show('OPD berhasil diupdate', 'success')
    } else {
      await api.post('/opd', form.value)
      toast.show('OPD berhasil ditambahkan', 'success')
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
    await api.del(`/opd/${deletingItem.value.id}`)
    toast.show('OPD berhasil dihapus', 'success')
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
