<template>
  <div>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Pemanfaatan</h1>

    <AdminDataTable
      :columns="columns"
      :data="items"
      :page="page"
      :total-pages="lastPage"
      search-placeholder="Cari nomor perjanjian atau aset..."
      create-label="Tambah Pemanfaatan"
      @search="search = $event; fetchData()"
      @create="openCreate()"
      @edit="openEdit($event)"
      @delete="confirmDelete($event)"
      @page-change="page = $event; fetchData()"
    />

    <AdminFormModal
      :show="modal"
      :title="editing ? 'Edit Pemanfaatan' : 'Tambah Pemanfaatan'"
      :loading="saving"
      @close="closeModal"
      @submit="save"
    >
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">Aset</label>
        <div class="relative">
          <input
            v-model="asetQuery"
            class="w-full px-4 py-3 border border-slate-300 rounded-lg text-base focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none"
            placeholder="Cari aset..."
            @input="filterAset"
            @focus="asetOpen = true"
            @blur="setTimeout(() => asetOpen = false, 200)"
          />
          <ul
            v-if="asetOpen && filteredAset.length"
            class="absolute z-10 w-full mt-1 bg-white border border-slate-200 rounded-lg shadow-lg max-h-48 overflow-y-auto"
          >
            <li
              v-for="(a, i) in filteredAset"
              :key="a?.id ?? i"
              class="px-4 py-2 text-sm hover:bg-teal-50 cursor-pointer"
              @mousedown.prevent="selectAset(a)"
            >
              {{ a.kode_barang }} - {{ a.nama_barang }}
            </li>
          </ul>
        </div>
      </div>

      <UiSelect
        v-model="form.jenis_id"
        label="Jenis Pemanfaatan"
        :options="jenisOptions"
        placeholder="Pilih jenis"
        required
      />
      <UiSelect
        v-model="form.pihak_ketiga_id"
        label="Pihak Ketiga"
        :options="pihakKetigaOptions"
        placeholder="Pilih pihak ketiga"
        required
      />
      <UiInput v-model="form.nomor_perjanjian" label="Nomor Perjanjian" required placeholder="Contoh: 123/SPK-PEM/2024" />
      <div class="grid grid-cols-2 gap-4">
        <UiInput v-model="form.tanggal_mulai" label="Tanggal Mulai" type="date" required />
        <UiInput v-model="form.tanggal_selesai" label="Tanggal Selesai" type="date" required />
      </div>
      <div class="grid grid-cols-2 gap-4">
        <UiInput v-model="form.nilai_kontrak" label="Nilai Kontrak" type="number" required />
        <UiInput v-model="form.kontribusi_tahunan" label="Kontribusi Tahunan" type="number" />
      </div>
      <UiInput v-model="form.peruntukan" label="Peruntukan" placeholder="Peruntukan aset" />
      <UiSelect
        v-model="form.status"
        label="Status"
        :options="statusOptions"
        placeholder="Pilih status"
        required
      />
      <label class="block text-sm font-medium text-slate-700 mb-1.5">Catatan</label>
      <textarea v-model="form.catatan" class="w-full px-4 py-3 border border-slate-300 rounded-lg text-base focus:ring-2 focus:ring-teal-500 focus:border-teal-500 outline-none" rows="3"></textarea>

      <template v-if="editing">
        <div class="border-t border-slate-200 pt-4 mt-4">
          <h4 class="text-sm font-semibold text-slate-700 mb-3">Dokumen</h4>
          <div v-if="dokumen.length" class="space-y-2 mb-4">
            <div v-for="(d, i) in dokumen" :key="d?.id ?? i" class="flex items-center justify-between bg-slate-50 rounded-lg px-3 py-2 text-sm">
              <div>
                <span class="font-medium text-slate-700">{{ d.jenis_dokumen }}</span>
                <span class="text-slate-500 ml-2">{{ d.nomor_dokumen || '—' }}</span>
              </div>
              <a v-if="d.file" :href="d.file" target="_blank" class="text-teal-600 hover:underline text-xs font-medium">Lihat</a>
            </div>
          </div>
          <p v-else class="text-sm text-slate-400 mb-4">Belum ada dokumen</p>
          <div class="space-y-3">
            <UiInput v-model="dokForm.jenis_dokumen" label="Jenis Dokumen" placeholder="Kontrak, BAST, dll" />
            <UiInput v-model="dokForm.nomor_dokumen" label="Nomor Dokumen" />
            <UiInput v-model="dokForm.tanggal_dokumen" label="Tanggal Dokumen" type="date" />
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1.5">File</label>
              <input ref="fileInput" type="file" class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100 cursor-pointer" />
            </div>
            <UiButton variant="primary" size="sm" type="button" @click="uploadDokumen" :disabled="dokUploading">
              {{ dokUploading ? 'Mengupload...' : 'Upload Dokumen' }}
            </UiButton>
          </div>
        </div>
      </template>
    </AdminFormModal>

    <UiModal :show="deleteModal" title="Hapus Pemanfaatan" @close="deleteModal = false">
      <p class="text-slate-600">Apakah anda yakin ingin menghapus pemanfaatan <strong>{{ deletingItem?.nomor_perjanjian }}</strong>?</p>
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
  aset_id: null as number | null,
  jenis_id: '',
  pihak_ketiga_id: '',
  nomor_perjanjian: '',
  tanggal_mulai: '',
  tanggal_selesai: '',
  nilai_kontrak: '',
  kontribusi_tahunan: '',
  peruntukan: '',
  status: '',
  catatan: '',
})

const asetList = ref<any[]>([])
const asetQuery = ref('')
const asetOpen = ref(false)
const filteredAset = ref<any[]>([])
const jenisList = ref<any[]>([])
const pihakKetigaList = ref<any[]>([])

const dokumen = ref<any[]>([])
const fileInput = ref<HTMLInputElement | null>(null)
const dokUploading = ref(false)
const dokForm = ref({
  jenis_dokumen: '',
  nomor_dokumen: '',
  tanggal_dokumen: '',
})

const jenisOptions = computed(() =>
  jenisList.value.filter(j => j?.id).map(j => ({ value: j.id, label: j.nama }))
)
const pihakKetigaOptions = computed(() =>
  pihakKetigaList.value.filter(p => p?.id).map(p => ({ value: p.id, label: p.nama }))
)
const statusOptions = [
  { value: 'Aktif', label: 'Aktif' },
  { value: 'Berakhir', label: 'Berakhir' },
  { value: 'Dibatalkan', label: 'Dibatalkan' },
]

const columns = [
  { key: 'nomor_perjanjian', label: 'Nomor Perjanjian' },
  { key: 'aset.nama_barang', label: 'Aset' },
  { key: 'jenis.nama', label: 'Jenis' },
  { key: 'pihak_ketiga.nama', label: 'Pihak Ketiga' },
  { key: 'tanggal_mulai', label: 'Mulai' },
  { key: 'tanggal_selesai', label: 'Selesai' },
  { key: 'status', label: 'Status' },
]

function filterAset() {
  const q = asetQuery.value.toLowerCase()
  filteredAset.value = asetList.value.filter(a =>
    `${a.kode_barang} ${a.nama_barang}`.toLowerCase().includes(q)
  )
  asetOpen.value = true
}

function selectAset(a: any) {
  form.value.aset_id = a.id
  asetQuery.value = `${a.kode_barang} - ${a.nama_barang}`
  asetOpen.value = false
}

async function fetchData() {
  try {
    const res = await api.get(`/pemanfaatan?page=${page.value}&search=${search.value}`)
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

async function fetchReferenceData() {
  try {
    const [asetRes, jenisRes, pihakRes] = await Promise.all([
      api.get('/aset'),
      api.get('/jenis-pemanfaatan'),
      api.get('/pihak-ketiga'),
    ])
    asetList.value = Array.isArray(asetRes) ? asetRes : asetRes.data || []
    jenisList.value = Array.isArray(jenisRes) ? jenisRes : jenisRes.data || []
    pihakKetigaList.value = Array.isArray(pihakRes) ? pihakRes : pihakRes.data || []
  } catch (e: any) {
    toast.show('Gagal memuat data referensi: ' + e.message, 'error')
  }
}

async function fetchDokumen(id: number) {
  try {
    const res = await api.get(`/pemanfaatan/${id}/dokumen`)
    dokumen.value = Array.isArray(res) ? res : res.data || []
  } catch (_) {
    dokumen.value = []
  }
}

function resetForm() {
  form.value = {
    aset_id: null,
    jenis_id: '',
    pihak_ketiga_id: '',
    nomor_perjanjian: '',
    tanggal_mulai: '',
    tanggal_selesai: '',
    nilai_kontrak: '',
    kontribusi_tahunan: '',
    peruntukan: '',
    status: '',
    catatan: '',
  }
  asetQuery.value = ''
  filteredAset.value = []
  dokForm.value = { jenis_dokumen: '', nomor_dokumen: '', tanggal_dokumen: '' }
  dokumen.value = []
}

function openCreate() {
  editing.value = null
  resetForm()
  modal.value = true
}

function openEdit(item: any) {
  editing.value = item
  form.value = {
    aset_id: item.aset_id,
    jenis_id: item.jenis_id,
    pihak_ketiga_id: item.pihak_ketiga_id,
    nomor_perjanjian: item.nomor_perjanjian,
    tanggal_mulai: item.tanggal_mulai,
    tanggal_selesai: item.tanggal_selesai,
    nilai_kontrak: item.nilai_kontrak,
    kontribusi_tahunan: item.kontribusi_tahunan,
    peruntukan: item.peruntukan,
    status: item.status,
    catatan: item.catatan,
  }
  asetQuery.value = item.aset ? `${item.aset.kode_barang} - ${item.aset.nama_barang}` : ''
  modal.value = true
  fetchDokumen(item.id)
}

function closeModal() {
  modal.value = false
  editing.value = null
}

async function save() {
  saving.value = true
  try {
    if (editing.value) {
      await api.put(`/pemanfaatan/${editing.value.id}`, form.value)
      toast.show('Pemanfaatan berhasil diupdate', 'success')
    } else {
      await api.post('/pemanfaatan', form.value)
      toast.show('Pemanfaatan berhasil ditambahkan', 'success')
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
    await api.del(`/pemanfaatan/${deletingItem.value.id}`)
    toast.show('Pemanfaatan berhasil dihapus', 'success')
    deleteModal.value = false
    deletingItem.value = null
    fetchData()
  } catch (e: any) {
    toast.show('Gagal menghapus: ' + e.message, 'error')
  } finally {
    deleting.value = false
  }
}

async function uploadDokumen() {
  if (!editing.value || !fileInput.value?.files?.[0]) {
    toast.show('Pilih file untuk diupload', 'error')
    return
  }
  dokUploading.value = true
  try {
    const fd = new FormData()
    fd.append('pemanfaatan_id', String(editing.value.id))
    fd.append('file', fileInput.value.files[0])
    fd.append('jenis_dokumen', dokForm.value.jenis_dokumen)
    fd.append('nomor_dokumen', dokForm.value.nomor_dokumen)
    fd.append('tanggal_dokumen', dokForm.value.tanggal_dokumen)
    await api.upload('/dokumen-pemanfaatan', fd)
    toast.show('Dokumen berhasil diupload', 'success')
    dokForm.value = { jenis_dokumen: '', nomor_dokumen: '', tanggal_dokumen: '' }
    if (fileInput.value) fileInput.value.value = ''
    fetchDokumen(editing.value.id)
  } catch (e: any) {
    toast.show('Gagal upload dokumen: ' + e.message, 'error')
  } finally {
    dokUploading.value = false
  }
}

fetchReferenceData()
fetchData()
</script>
