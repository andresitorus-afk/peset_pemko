<template>
  <div>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Pihak Ketiga</h1>

    <AdminDataTable
      :columns="columns"
      :data="items"
      :page="page"
      :total-pages="lastPage"
      search-placeholder="Cari pihak ketiga..."
      create-label="Tambah Pihak Ketiga"
      @search="search = $event; fetchData()"
      @create="openCreate()"
      @edit="openEdit($event)"
      @delete="confirmDelete($event)"
      @page-change="page = $event; fetchData()"
    />

    <AdminFormModal
      :show="modal"
      :title="editing ? 'Edit Pihak Ketiga' : 'Tambah Pihak Ketiga'"
      :loading="saving"
      @close="closeModal"
      @submit="save"
    >
      <UiInput v-model="form.nama" label="Nama" required placeholder="Nama pihak ketiga" />
      <UiSelect v-model="form.jenis" label="Jenis" :options="jenisOptions" required />
      <UiInput v-model="form.npwp" label="NPWP" placeholder="XX.XXX.XXX.X-XXX.XXX" />
      <UiInput v-model="form.alamat" label="Alamat" />
      <UiInput v-model="form.telepon" label="Telepon" />
      <UiInput v-model="form.email" label="Email" type="email" />
      <UiInput v-model="form.penanggung_jawab" label="Penanggung Jawab" />
    </AdminFormModal>

    <UiModal :show="deleteModal" title="Hapus Pihak Ketiga" @close="deleteModal = false">
      <p class="text-slate-600">Apakah anda yakin ingin menghapus <strong>{{ deletingItem?.nama }}</strong>?</p>
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

const jenisOptions = [
  { value: 'Perorangan', label: 'Perorangan' },
  { value: 'Badan_Hukum', label: 'Badan Hukum' },
  { value: 'Pemda', label: 'Pemda' },
]

const form = ref({
  nama: '',
  jenis: '',
  npwp: '',
  alamat: '',
  telepon: '',
  email: '',
  penanggung_jawab: '',
})

const columns = [
  { key: 'nama', label: 'Nama' },
  { key: 'jenis', label: 'Jenis' },
  { key: 'telepon', label: 'Telepon' },
  { key: 'npwp', label: 'NPWP' },
]

async function fetchData() {
  try {
    const res = await api.get(`/pihak-ketiga?page=${page.value}&search=${search.value}`)
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
  form.value = { nama: '', jenis: '', npwp: '', alamat: '', telepon: '', email: '', penanggung_jawab: '' }
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
      await api.put(`/pihak-ketiga/${editing.value.id}`, form.value)
      toast.show('Pihak ketiga berhasil diupdate', 'success')
    } else {
      await api.post('/pihak-ketiga', form.value)
      toast.show('Pihak ketiga berhasil ditambahkan', 'success')
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
    await api.del(`/pihak-ketiga/${deletingItem.value.id}`)
    toast.show('Pihak ketiga berhasil dihapus', 'success')
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