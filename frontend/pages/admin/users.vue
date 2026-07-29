<template>
  <div>
    <h1 class="text-2xl font-bold text-slate-900 mb-6">Users</h1>

    <AdminDataTable
      :columns="columns"
      :data="items"
      :page="page"
      :total-pages="lastPage"
      search-placeholder="Cari user..."
      create-label="Tambah User"
      @search="search = $event; fetchData()"
      @create="openCreate()"
      @edit="openEdit($event)"
      @delete="confirmDelete($event)"
      @page-change="page = $event; fetchData()"
    />

    <AdminFormModal
      :show="modal"
      :title="editing ? 'Edit User' : 'Tambah User'"
      :loading="saving"
      @close="closeModal"
      @submit="save"
    >
      <UiInput v-model="form.name" label="Nama" required placeholder="Nama lengkap" />
      <UiInput v-model="form.email" label="Email" type="email" required placeholder="user@example.com" />
  <UiInput
    v-model="form.password"
    label="Password"
    type="password"
    :required="!editing"
    :placeholder="editing ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter'"
  />
  <UiInput
    v-if="!editing || form.password"
    v-model="form.password_confirmation"
    label="Konfirmasi Password"
    type="password"
    placeholder="Ulangi password"
  />
      <UiSelect
        v-model="form.role_id"
        label="Role"
        :options="roleOptions"
        placeholder="Pilih role"
        required
      />
    </AdminFormModal>

    <UiModal :show="deleteModal" title="Hapus User" @close="deleteModal = false">
      <p class="text-slate-600">Apakah anda yakin ingin menghapus user <strong>{{ deletingItem?.name }}</strong>?</p>
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
const roles = ref<any[]>([])

const form = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role_id: '',
})

const roleOptions = computed(() =>
  roles.value.filter(r => r?.id).map(r => ({ value: r.id, label: r.name }))
)

const columns = [
  { key: 'name', label: 'Nama' },
  { key: 'email', label: 'Email' },
  { key: 'role.name', label: 'Role' },
]

async function fetchRoles() {
  try {
    const res = await api.get('/roles?limit=100')
    roles.value = Array.isArray(res) ? res : res.data || []
  } catch (_) {}
}

async function fetchData() {
  try {
    const res = await api.get(`/users?page=${page.value}&search=${search.value}`)
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
  form.value = { name: '', email: '', password: '', role_id: '' }
  modal.value = true
}

function openEdit(item: any) {
  editing.value = item
  form.value = { name: item.name, email: item.email, password: '', role_id: item.role_id }
  modal.value = true
}

function closeModal() {
  modal.value = false
  editing.value = null
}

async function save() {
  saving.value = true
  try {
      const payload = { ...form.value }
      if (editing.value && !payload.password) {
        delete payload.password
        delete payload.password_confirmation
      }
    if (editing.value) {
      await api.put(`/users/${editing.value.id}`, payload)
      toast.show('User berhasil diupdate', 'success')
    } else {
      await api.post('/users', payload)
      toast.show('User berhasil ditambahkan', 'success')
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
    await api.del(`/users/${deletingItem.value.id}`)
    toast.show('User berhasil dihapus', 'success')
    deleteModal.value = false
    deletingItem.value = null
    fetchData()
  } catch (e: any) {
    toast.show('Gagal menghapus: ' + e.message, 'error')
  } finally {
    deleting.value = false
  }
}

fetchRoles()
fetchData()
</script>
