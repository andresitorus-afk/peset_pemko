<template>
  <div style="max-width: 800px; margin: 40px auto; font-family: sans-serif;">
    <h1>Import Data Aset</h1>

    <div style="margin-bottom: 20px; padding: 15px; background: #f0f0f0; border-radius: 8px;">
      <p><strong>Format kolom Excel/CSV:</strong></p>
      <code>kode_barang, register, nama_barang, opd, kode_kategori, tahun_perolehan, nilai_perolehan, nilai_buku, luas, kondisi, status, alamat, keterangan</code>
      <br><br>
      <p><strong>Catatan:</strong></p>
      <ul>
        <li>Kolom <code>opd</code>: isi nama OPD atau kode OPD (contoh: "Dinas Pendidikan" atau "OPD.001")</li>
        <li>Kolom <code>kode_kategori</code>: isi kode lengkap dari referensi KIB (contoh: "1.3.1.01.01.01.001" untuk Tanah Bangunan Rumah Negara Gol. I)</li>
        <li>Alternatif: kolom <code>kategori</code> atau <code>kode_kib</code> bisa isi nama kategori atau kode KIB induk</li>
        <li>Kolom <code>kondisi</code>: Baik / Rusak Ringan / Rusak Berat</li>
        <li>Kolom <code>status</code>: Aktif / Idle / Dimanfaatkan</li>
        <li>Kode barang yang sudah ada akan dilewati (tidak duplikat)</li>
      </ul>
      <a :href="`${API_BASE}/api/aset/template`" download style="display:inline-block; margin-top:10px; padding:8px 16px; background:#2196F3; color:white; text-decoration:none; border-radius:4px;">
        Download Template
      </a>
    </div>

    <div style="margin-bottom: 20px;">
      <input type="file" ref="fileInput" accept=".xlsx,.xls,.csv" @change="onFileChange" />
    </div>

    <button @click="doImport" :disabled="!file || loading" style="padding: 10px 24px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px;">
      {{ loading ? 'Mengimport...' : 'Import' }}
    </button>

    <div v-if="result" style="margin-top: 20px; padding: 15px; border-radius: 8px;" :style="{ background: result.failed > 0 ? '#fff3cd' : '#d4edda' }">
      <h3>Hasil Import</h3>
      <p>Berhasil: <strong>{{ result.success }}</strong></p>
      <p>Gagal: <strong>{{ result.failed }}</strong></p>
      <div v-if="result.errors.length">
        <p><strong>Error:</strong></p>
        <ul>
          <li v-for="(err, i) in result.errors" :key="i" style="color: #856404;">{{ err }}</li>
        </ul>
      </div>
    </div>

    <div v-if="error" style="margin-top: 20px; padding: 15px; background: #f8d7da; border-radius: 8px; color: #721c24;">
      {{ error }}
    </div>
  </div>
</template>

<script setup>
const config = useRuntimeConfig()
const API_BASE = config.public?.apiBase || 'http://localhost:8000'

const file = ref(null)
const loading = ref(false)
const result = ref(null)
const error = ref(null)
const fileInput = ref(null)

function onFileChange(e) {
  file.value = e.target.files[0]
  result.value = null
  error.value = null
}

async function doImport() {
  if (!file.value) return

  loading.value = true
  result.value = null
  error.value = null

  const formData = new FormData()
  formData.append('file', file.value)

  try {
    const token = localStorage.getItem('token')
    const res = await $fetch(`${API_BASE}/api/aset/import`, {
      method: 'POST',
      body: formData,
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    })
    result.value = res
  } catch (e) {
    error.value = e.data?.message || e.message || 'Terjadi kesalahan'
  } finally {
    loading.value = false
  }
}
</script>
