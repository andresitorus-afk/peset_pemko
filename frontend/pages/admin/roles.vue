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

fetchData()
</script>