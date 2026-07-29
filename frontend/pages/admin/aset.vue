<template>
  <div>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Aset</h1>

    <AdminDataTable
      :columns="columns"
      :data="items"
      :page="page"
      :total-pages="lastPage"
      search-placeholder="Cari kode/nama barang..."
      create-label="Tambah Aset"
      :loading="loading"
      @search="search = $event; fetchData()"
      @create="openCreate()"
      @edit="openEdit($event)"
      @delete="confirmDelete($event)"
      @page-change="page = $event; fetchData()"
    >
      <template #cell-opd.nama_opd="{ row }">
        {{ row.opd?.nama_opd || '—' }}
      </template>
    </AdminDataTable>

    <AdminFormModal
      :show="modal"
      :title="editing ? 'Edit Aset' : 'Tambah Aset'"
      :loading="saving"
      @close="closeModal"
      @submit="save"
    >
      <div class="space-y-6">
        <div>
          <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Informasi Barang</h4>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <UiInput v-model="form.kode_barang" label="Kode Barang" required placeholder="BRG.001" />
            <UiInput v-model="form.register" label="Register" placeholder="001" />
          </div>
          <UiInput v-model="form.nama_barang" label="Nama Barang" required placeholder="Nama aset..." class="mt-4" />
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
            <UiSelect v-model="form.opd_id" label="OPD" :options="opdOptions" required placeholder="Pilih OPD" />
            <UiSelect v-model="form.kategori_id" label="Kategori" :options="kategoriOptions" required placeholder="Pilih Kategori" />
          </div>
        </div>

        <div>
          <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Detail Aset</h4>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <UiInput v-model="form.tahun_perolehan" label="Tahun Perolehan" type="number" placeholder="2024" />
            <UiInput v-model="form.nilai_perolehan" label="Nilai Perolehan" type="number" placeholder="0" />
            <UiInput v-model="form.nilai_buku" label="Nilai Buku" type="number" placeholder="0" />
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
            <UiInput v-model="form.luas" label="Luas (m²)" type="number" placeholder="0" />
            <UiSelect v-model="form.kondisi" label="Kondisi" :options="kondisiOptions" required placeholder="Pilih kondisi" />
            <UiSelect v-model="form.status" label="Status" :options="statusOptions" required placeholder="Pilih status" />
          </div>
        </div>

        <div>
          <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Lokasi & Keterangan</h4>
          <UiInput v-model="form.alamat" label="Alamat" placeholder="Alamat lokasi aset..." />
          <UiInput v-model="form.keterangan" label="Keterangan" placeholder="Catatan tambahan..." class="mt-4" />
        </div>

        <div v-if="editing">
          <h4 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-3">Foto Aset</h4>
          <div v-if="fotos.length" class="grid grid-cols-3 sm:grid-cols-4 gap-3 mb-4">
            <div v-for="(foto, i) in fotos" :key="foto?.id ?? i" class="relative group">
              <img :src="fotoUrl(foto.file)" class="w-full h-24 object-cover rounded-lg border border-slate-200" />
              <button @click="deleteFoto(foto)" type="button" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity">&times;</button>
              <p v-if="foto.caption" class="text-xs text-slate-500 mt-1 truncate">{{ foto.caption }}</p>
            </div>
          </div>
          <p v-else class="text-sm text-slate-400 mb-3">Belum ada foto</p>
          <div class="flex items-center gap-3">
            <input type="file" accept="image/*" @change="handleFotoSelect" class="text-sm text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100" />
            <input v-model="fotoCaption" placeholder="Keterangan foto" class="flex-1 px-3 py-2 border border-slate-300 rounded-lg text-sm outline-none focus:ring-2 focus:ring-teal-500" />
            <UiButton variant="primary" type="button" @click="uploadFoto" :disabled="!fotoFile || fotoUploading" size="sm">
              {{ fotoUploading ? 'Mengunggah...' : 'Upload' }}
            </UiButton>
          </div>
        </div>
      </div>
    </AdminFormModal>

    <UiModal :show="deleteModal" title="Hapus Aset" @close="deleteModal = false">
      <p class="text-slate-600">Apakah anda yakin ingin menghapus aset <strong>{{ deletingItem?.nama_barang }}</strong>?</p>
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
const loading = ref(false)
const modal = ref(false)
const saving = ref(false)
const editing = ref<any | null>(null)
const deleteModal = ref(false)
const deleting = ref(false)
const deletingItem = ref<any | null>(null)

const opdList = ref<any[]>([])
const kategoriList = ref<any[]>([])
const fotos = ref<any[]>([])
const fotoFile = ref<File | null>(null)
const fotoCaption = ref('')
const fotoUploading = ref(false)

const form = ref({
  kode_barang: '',
  register: '',
  nama_barang: '',
  opd_id: '',
  kategori_id: '',
  tahun_perolehan: '',
  nilai_perolehan: '',
  nilai_buku: '',
  luas: '',
  kondisi: '',
  status: '',
  alamat: '',
  keterangan: '',
})

const kondisiOptions = [
  { value: 'Baik', label: 'Baik' },
  { value: 'Rusak_Ringan', label: 'Rusak Ringan' },
  { value: 'Rusak_Berat', label: 'Rusak Berat' },
]

const statusOptions = [
  { value: 'Aktif', label: 'Aktif' },
  { value: 'Idle', label: 'Idle' },
  { value: 'Dimanfaatkan', label: 'Dimanfaatkan' },
]

const opdOptions = computed(() => opdList.value.filter((o: any) => o?.id).map((o: any) => ({ value: o.id, label: o.nama_opd })))
const kategoriOptions = computed(() => kategoriList.value.filter((k: any) => k?.id).map((k: any) => ({ value: k.id, label: k.nama_kategori || k.nama })))

const columns = [
  { key: 'kode_barang', label: 'Kode Barang' },
  { key: 'nama_barang', label: 'Nama Barang' },
  { key: 'opd.nama_opd', label: 'OPD' },
  { key: 'kondisi', label: 'Kondisi' },
  { key: 'status', label: 'Status' },
]

function fotoUrl(file: string) {
  return `${api.baseURL}/storage/${file}`
}

async function fetchData() {
  loading.value = true
  try {
    const res = await api.get(`/aset?page=${page.value}&search=${search.value}`)
    if (Array.isArray(res)) {
      items.value = res
    } else {
      items.value = res.data || []
      lastPage.value = res.last_page || 1
    }
  } catch (e: any) {
    toast.show('Gagal memuat data: ' + e.message, 'error')
  } finally {
    loading.value = false
  }
}

async function fetchOptions() {
  try {
    const [opdRes, katRes] = await Promise.all([
      api.get('/opd'),
      api.get('/kategori-aset'),
    ])
    opdList.value = Array.isArray(opdRes) ? opdRes : opdRes.data || []
    kategoriList.value = Array.isArray(katRes) ? katRes : katRes.data || []
  } catch (e: any) {
    toast.show('Gagal memuat data referensi', 'error')
  }
}

function openCreate() {
  editing.value = null
  form.value = { kode_barang: '', register: '', nama_barang: '', opd_id: '', kategori_id: '', tahun_perolehan: '', nilai_perolehan: '', nilai_buku: '', luas: '', kondisi: '', status: '', alamat: '', keterangan: '' }
  fotos.value = []
  fotoFile.value = null
  fotoCaption.value = ''
  modal.value = true
}

function openEdit(item: any) {
  editing.value = item
  form.value = {
    kode_barang: item.kode_barang || '',
    register: item.register || '',
    nama_barang: item.nama_barang || '',
    opd_id: item.opd_id || '',
    kategori_id: item.kategori_id || '',
    tahun_perolehan: item.tahun_perolehan || '',
    nilai_perolehan: item.nilai_perolehan || '',
    nilai_buku: item.nilai_buku || '',
    luas: item.luas || '',
    kondisi: item.kondisi || '',
    status: item.status || '',
    alamat: item.alamat || '',
    keterangan: item.keterangan || '',
  }
  fotos.value = item.foto || []
  fotoFile.value = null
  fotoCaption.value = ''
  modal.value = true
}

function closeModal() {
  modal.value = false
  editing.value = null
  fotos.value = []
  fotoFile.value = null
  fotoCaption.value = ''
}

async function save() {
  saving.value = true
  try {
    if (editing.value) {
      await api.put(`/aset/${editing.value.id}`, form.value)
      toast.show('Aset berhasil diupdate', 'success')
    } else {
      await api.post('/aset', form.value)
      toast.show('Aset berhasil ditambahkan', 'success')
    }
    closeModal()
    fetchData()
  } catch (e: any) {
    toast.show('Gagal menyimpan: ' + e.message, 'error')
  } finally {
    saving.value = false
  }
}

function handleFotoSelect(e: Event) {
  const target = e.target as HTMLInputElement
  fotoFile.value = target.files?.[0] || null
}

async function uploadFoto() {
  if (!fotoFile.value || !editing.value) return
  fotoUploading.value = true
  try {
    const fd = new FormData()
    fd.append('aset_id', editing.value.id)
    fd.append('file', fotoFile.value)
    fd.append('caption', fotoCaption.value)
    fd.append('tipe', 'Lainnya')
    const result = await api.upload('/foto-aset', fd)
    fotos.value.push(result.data || result)
    toast.show('Foto berhasil diupload', 'success')
    fotoFile.value = null
    fotoCaption.value = ''
  } catch (e: any) {
    toast.show('Gagal upload foto: ' + e.message, 'error')
  } finally {
    fotoUploading.value = false
  }
}

async function deleteFoto(foto: any) {
  try {
    await api.del(`/foto-aset/${foto.id}`)
    fotos.value = fotos.value.filter((f: any) => f.id !== foto.id)
    toast.show('Foto berhasil dihapus', 'success')
  } catch (e: any) {
    toast.show('Gagal menghapus foto: ' + e.message, 'error')
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
    await api.del(`/aset/${deletingItem.value.id}`)
    toast.show('Aset berhasil dihapus', 'success')
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
fetchOptions()
</script>