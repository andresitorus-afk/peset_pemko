<template>
  <div>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Roles</h1>

    <AdminDataTable
      :columns="columns"
      :data="items"
      :page="page"
      :total-pages="lastPage"
      search-placeholder="Cari role..."
      create-label="Tambah Role"
      @search="search = $event; fetchData()"
      @create="openCreate()"
      @edit="openEdit($event)"
      @delete="confirmDelete($event)"
      @page-change="page = $event; fetchData()"
    >
      <template #cell-users_count="{ value }">
        {{ value }} pengguna
      </template>
    </AdminDataTable>

    <AdminFormModal
      :show="modal"
      :title="editing ? 'Edit Role' : 'Tambah Role'"
      :loading="saving"
      @close="closeModal"
      @submit="save"
    >
      <UiInput v-model="form.name" label="Nama Role" required placeholder="Admin" />
      <UiInput v-model="form.guard_name" label="Guard Name" required placeholder="web" />
    </AdminFormModal>

    <UiModal :show="deleteModal" title="Hapus Role" @close="deleteModal = false">
      <p class="text-slate-600">Apakah anda yakin ingin menghapus role <strong>{{ deletingItem?.name }}</strong>?</p>
      <div class="flex justify-end gap-3 pt-4">
        <UiButton variant="secondary" @click="deleteModal = false">Batal</UiButton>
        <UiButton variant="danger" @click="doDelete" :disabled="deletingState">{{ deletingState ? 'Menghapus...' : 'Hapus' }}</UiButton>
      </div>
    </UiModal>

    <UiCard class="mt-6">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="text-lg font-bold text-slate-900">Mapping Domain Email ke Role</h2>
          <p class="text-sm text-slate-500">Role user otomatis ditentukan dari domain email saat mendaftar.</p>
        </div>
        <UiButton variant="primary" @click="openDomainCreate">
          <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Tambah Domain
        </UiButton>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead>
            <tr class="border-b border-slate-200">
              <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4">Domain</th>
              <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4">Role</th>
              <th class="text-right text-xs font-semibold text-slate-500 uppercase tracking-wider py-3 px-4 w-24">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in domains" :key="row.id" class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
              <td class="py-3 px-4 text-sm text-slate-700 font-mono">@{{ row.domain }}</td>
              <td class="py-3 px-4 text-sm text-slate-700">{{ row.role?.name || '—' }}</td>
              <td class="py-3 px-4 text-right">
                <div class="flex items-center justify-end gap-1">
                  <button @click="openDomainEdit(row)" type="button" class="p-2 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-teal-600 transition-colors" title="Edit">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button @click="confirmDomainDelete(row)" type="button" class="p-2 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600 transition-colors" title="Hapus">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="!domains.length">
              <td colspan="3" class="py-8 text-center text-slate-400">Belum ada mapping domain</td>
            </tr>
          </tbody>
        </table>
      </div>
    </UiCard>

    <AdminFormModal
      :show="domainModal"
      :title="domainEditing ? 'Edit Mapping Domain' : 'Tambah Mapping Domain'"
      :loading="saving"
      @close="closeDomainModal"
      @submit="saveDomain"
    >
      <UiInput v-model="domainForm.domain" label="Domain" required placeholder="pemkomedan.go.id" />
      <UiSelect v-model="domainForm.role_id" label="Role" :options="roleOptions" placeholder="Pilih role" required />
    </AdminFormModal>

    <UiModal :show="domainDeleteModal" title="Hapus Mapping Domain" @close="domainDeleteModal = false">
      <p class="text-slate-600">Apakah anda yakin ingin menghapus mapping domain <strong>@{{ domainDeleting?.domain }}</strong>?</p>
      <div class="flex justify-end gap-3 pt-4">
        <UiButton variant="secondary" @click="domainDeleteModal = false">Batal</UiButton>
        <UiButton variant="danger" @click="doDomainDelete" :disabled="deletingState">{{ deletingState ? 'Menghapus...' : 'Hapus' }}</UiButton>
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
const deletingState = ref(false)
const deletingItem = ref<any | null>(null)

const domains = ref<any[]>([])
const domainModal = ref(false)
const domainEditing = ref<any | null>(null)
const domainDeleteModal = ref(false)
const domainDeleting = ref<any | null>(null)

const domainForm = ref({
  domain: '',
  role_id: '',
})

const roleOptions = computed(() =>
  items.value.filter(r => r?.id).map(r => ({ value: r.id, label: r.name }))
)

const form = ref({
  name: '',
  guard_name: '',
})

const columns = [
  { key: 'name', label: 'Nama Role' },
  { key: 'guard_name', label: 'Guard Name' },
  { key: 'users_count', label: 'Pengguna' },
]

async function fetchData() {
  try {
    const res = await api.get(`/roles?page=${page.value}&search=${search.value}`)
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
  form.value = { name: '', guard_name: '' }
  modal.value = true
}

function openEdit(item: any) {
  editing.value = item
  form.value = { name: item.name, guard_name: item.guard_name }
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
      await api.put(`/roles/${editing.value.id}`, form.value)
      toast.show('Role berhasil diupdate', 'success')
    } else {
      await api.post('/roles', form.value)
      toast.show('Role berhasil ditambahkan', 'success')
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
  deletingState.value = true
  try {
    await api.del(`/roles/${deletingItem.value.id}`)
    toast.show('Role berhasil dihapus', 'success')
    deleteModal.value = false
    deletingItem.value = null
    fetchData()
  } catch (e: any) {
    toast.show(e.message, 'error')
  } finally {
    deletingState.value = false
  }
}

async function fetchDomains() {
  try {
    const res = await api.get('/role-email-domains')
    domains.value = Array.isArray(res) ? res : res.data || []
  } catch (e: any) {
    toast.show('Gagal memuat mapping domain: ' + e.message, 'error')
  }
}

function openDomainCreate() {
  domainEditing.value = null
  domainForm.value = { domain: '', role_id: '' }
  domainModal.value = true
}

function openDomainEdit(item: any) {
  domainEditing.value = item
  domainForm.value = { domain: item.domain, role_id: item.role_id ?? '' }
  domainModal.value = true
}

function closeDomainModal() {
  domainModal.value = false
  domainEditing.value = null
}

async function saveDomain() {
  saving.value = true
  try {
    const payload = { ...domainForm.value }
    if (!payload.role_id) delete payload.role_id
    if (domainEditing.value) {
      await api.put(`/role-email-domains/${domainEditing.value.id}`, payload)
      toast.show('Mapping domain berhasil diupdate', 'success')
    } else {
      await api.post('/role-email-domains', payload)
      toast.show('Mapping domain berhasil ditambahkan', 'success')
    }
    closeDomainModal()
    fetchDomains()
  } catch (e: any) {
    toast.show('Gagal menyimpan: ' + e.message, 'error')
  } finally {
    saving.value = false
  }
}

function confirmDomainDelete(item: any) {
  domainDeleting.value = item
  domainDeleteModal.value = true
}

async function doDomainDelete() {
  if (!domainDeleting.value) return
  deletingState.value = true
  try {
    await api.del(`/role-email-domains/${domainDeleting.value.id}`)
    toast.show('Mapping domain berhasil dihapus', 'success')
    domainDeleteModal.value = false
    domainDeleting.value = null
    fetchDomains()
  } catch (e: any) {
    toast.show(e.message, 'error')
  } finally {
    deletingState.value = false
  }
}

fetchData()
fetchDomains()
</script>