# Backend API - Pemanfaatan Aset Pemko Medan

## Arsitektur Umum

Backend ini dibangun dengan **Laravel 13** + **PostgreSQL** + **Sanctum Auth**.
Strukturnya mengikuti pola standar Laravel: Model → Resource → Controller → Route.

```
backend/app/
├── Models/              # 11 model Eloquent (representasi tabel database)
├── Http/
│   ├── Controllers/Api/ # 13 controller (logic API)
│   └── Resources/       # 12 resource (format JSON response)
routes/api.php           # 53 endpoint API
```

---

## 1. Model (Representasi Database)

Model adalah class PHP yang merepresentasikan satu tabel database. Setiap model punya:
- `$table` — nama tabel
- `$fillable` — kolom yang boleh diisi massal
- `$casts` — otomatis konversi tipe data
- **Relationship** — relasi ke tabel lain

### Reference Tables (Tabel Referensi)

| Model | Tabel | Fungsi | Relasi |
|-------|-------|--------|--------|
| `Opd` | `opd` | Data Organisasi Perangkat Daerah |hasMany → Aset |
| `KategoriAset` | `kategori_aset` | Kategori aset (KIB A-F) | hasMany → Aset |
| `GisLayer` | `gis_layer` | Layer peta GIS (Tanah, Bangunan, dll) | hasMany → GisAset |
| `JenisPemanfaatan` | `jenis_pemanfaatan` | Jenis pemanfaatan (Sewa, Pinjam Pakai, dll) | hasMany → Pemanfaatan |
| `PihakKetiga` | `pihak_ketiga` | Data pihak ketiga (perorangan/badan hukum) | hasMany → Pemanfaatan |

### Core Tables (Tabel Inti)

| Model | Tabel | Fungsi | Relasi |
|-------|-------|--------|--------|
| `Aset` | `aset` | Data aset Pemko Medan | belongsTo Opd, KategoriAset; hasOne GisAset; hasMany Pemanfaatan, FotoAset, RiwayatAset |
| `GisAset` | `gis_aset` | Koordinat/geometri aset di peta | belongsTo Aset, GisLayer |
| `Pemanfaatan` | `pemanfaatan` | Record pemanfaatan/pinjam pakai aset | belongsTo Aset, JenisPemanfaatan, PihakKetiga, User; hasMany DokumenPemanfaatan |
| `DokumenPemanfaatan` | `dokumen_pemanfaatan` | File dokumen (SK, Perjanjian, dll) | belongsTo Pemanfaatan |
| `FotoAset` | `foto_aset` | Foto aset | belongsTo Aset |
| `RiwayatAset` | `riwayat_aset` | Log/perubahan status aset | belongsTo Aset, User |

### Fitur Khusus di Model

**Aset punya Scope untuk Search & Filter:**
```php
// Digunakan di controller seperti ini:
Aset::search($request->search)->filter($request->only(['opd_id', 'kategori_id']))->get();

// ScopeSearch: mencari by nama_barang atau kode_barang (case-insensitive pakai ilike)
// ScopeFilter: filter by opd_id, kategori_id, kondisi, status
```

**GisAset punya Method GeoJSON:**
```php
// Mengubah data aset jadi format GeoJSON untuk peta
$gis->toGeoJsonGeometry(); 
// → {"type": "Point", "coordinates": [98.6722, 3.5952]}
```

**Pemanfaatan & RiwayatAset:**
```php
// Tidak punya updated_at (hanya created_at)
const UPDATED_AT = null;
```

---

## 2. API Resource (Format JSON Response)

Resource mengontrol bagaimana data model ditampilkan sebagai JSON. Tanpa resource, Eloquent mengembalikan semua kolom. Dengan resource, kita bisa:
- Pilih kolom mana yang ditampilkan
- Sertakan relasi hanya jika di-load
- Format tanggal, angka, dll

### Contoh: AsetResource

```json
{
  "id": "uuid-aset",
  "kode_barang": "A.001",
  "nama_barang": "Gedung Kantor",
  "status": "Dimanfaatkan",
  "kondisi": "Baik",
  "opd": {
    "id": "uuid-opd",
    "nama_opd": "Dinas PUPR"
  },
  "kategori": {
    "id": "uuid-kategori",
    "kode_kib": "B",
    "nama_kategori": "Peralatan"
  },
  "pemanfaatan_count": 2,
  "foto_count": 5
}
```

**`whenLoaded()`** — relasi hanya muncul jika di-load di controller:
```php
'opd' => new OpdResource($this->whenLoaded('opd')),
// Jika $aset->load('opd') dipanggil → opd muncul
// Jika tidak → opd = null
```

---

## 3. Controller (Logic API)

Controller menangani request masuk, validasi, proses data, dan return response.

### AuthController
**Endpoint:** POST `/api/register`, `/api/login`, `/api/logout`, GET `/api/user`

```
Register → validasi input → hash password → buat user → generate Sanctum token → return token
Login → cek email & password → generate token → return token
Logout → hapus token saat ini
User → return data user dari token
```

**Kenapa pakai Sanctum Token?**
Sanctum membuat token unik untuk setiap login. Frontend menyimpan token ini dan mengirimkannya di setiap request:
```
Authorization: Bearer {token}
```
Tanpa token yang valid, request akan ditolak (401 Unauthorized).

### OpdController, KategoriAsetController, dll (5 Reference Controllers)
**Endpoint:** GET/POST `/api/opd`, GET/PUT/DELETE `/api/opd/{id}`

Semua mengikuti pola yang sama:
```
GET    /api/opd          → index()   : Daftar semua data (dengan search & pagination)
POST   /api/opd          → store()   : Buat data baru (validasi → create → return)
GET    /api/opd/{id}     → show()    : Lihat satu data
PUT    /api/opd/{id}     → update()  : Ubah data
DELETE /api/opd/{id}     → destroy() : Hapus data
```

**Validasi di `store()`:**
```php
$validated = $request->validate([
    'kode_opd' => 'required|string|max:255|unique:opd,kode_opd',
    // required = wajib diisi
    // string = harus string
    // max:255 = maksimal 255 karakter
    // unique:opd,kode_opd = tidak boleh sama dengan yang sudah ada di tabel opd
]);
```

### AsetController
**Endpoint:** GET/POST `/api/aset`, GET/PUT/DELETE `/api/aset/{id}`, GET `/api/aset/{id}/pemanfaatan`, dll

**Fitur khusus:**
- **Search:** Cari aset berdasarkan nama atau kode barang
- **Filter:** Filter by OPD, kategori, kondisi, status
- **Auto Riwayat:** Ketika status aset berubah, otomatis buat record riwayat

```php
// Di method update():
$oldStatus = $aset->status;
$aset->update($validated);

if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
    $aset->riwayat()->create([
        'aksi' => 'Pemanfaatan',
        'deskripsi' => "Status berubah dari {$oldStatus} ke {$validated['status']}",
        'user_id' => $request->user()->id,
    ]);
}
```

### PemanfaatanController
**Endpoint:** GET/POST `/api/pemanfaatan`, GET/PUT/DELETE `/api/pemanfaatan/{id}`

**Fitur khusus — Auto Status Update:**
```
Ketika pemanfaatan DIBUAT dengan status "Aktif":
  → aset.status otomatis berubah ke "Dimanfaatkan"
  → riwayat_aset otomatis tercatat

Ketika pemanfaatan DIHAPUS:
  → Cek apakah masih ada pemanfaatan aktif lain
  → Jika tidak ada → aset.status kembali ke "Aktif"
  → riwayat_aset otomatis tercatat
```

### GisAsetController
**Endpoint:** GET `/api/gis/aset`, GET `/api/gis/aset/{id}`, GET `/api/gis/layer/{id}`

**Mengembalikan data sebagai GeoJSON** (format standar untuk peta web):
```json
{
  "type": "FeatureCollection",
  "features": [
    {
      "type": "Feature",
      "geometry": {
        "type": "Point",
        "coordinates": [98.6722, 3.5952]
      },
      "properties": {
        "id": "uuid-aset",
        "nama_barang": "Gedung Kantor",
        "status": "Dimanfaatkan",
        "layer": "Bangunan"
      }
    }
  ]
}
```

**Filter bbox:** `GET /api/gis/aset?bbox=98.5,3.5,98.8,3.7`
→ Hanya aset dalam area kotak tersebut (west,south,east,north)

### DashboardController
**Endpoint:** GET `/api/dashboard`

Menghitung statistik:
- Total aset
- Aset per kategori
- Aset per status (Aktif/Idle/Dimanfaatkan)
- Total nilai perolehan & nilai buku
- Pemanfaatan aktif & yang segera berakhir (30 hari)
- Pemanfaatan per jenis
- Pihak ketiga terbanyak

### DokumenPemanfaatanController & FotoAsetController
**Endpoint:** POST `/api/dokumen-pemanfaatan`, POST `/api/foto-aset`

**File Upload:**
```php
$file = $request->file('file');
$path = $file->store('dokumen', 'public');
// File disimpan di: storage/app/public/dokumen/{nama_file}
// Perlu symlink: php artisan storage:link
```

### RiwayatAsetController
**Endpoint:** GET/POST `/api/riwayat-aset`

**Filter:** by aset_id, by aksi (Pemanfaatan/Pemeliharaan/Mutasi/Penghapusan/Revaluasi)

---

## 4. Route (API Endpoint)

Semua route didefinisikan di `routes/api.php`:

```php
// Public (tanpa login)
Route::post('/register', ...);
Route::post('/login', ...);

// Protected (harus login pakai token)
Route::middleware('auth:sanctum')->group(function () {
    // Semua endpoint di sini butuh token
    Route::apiResource('aset', AsetController::class);
    // → GET /api/aset, POST /api/aset, GET /api/aset/{id}, 
    //   PUT /api/aset/{id}, DELETE /api/aset/{id}
});
```

**`apiResource()`** membuat 5 route otomatis (index, store, show, update, destroy).

---

## 5. Database Migration

14 migration files yang membuat tabel:

| Migration | Tabel | Kolom Utama |
|-----------|-------|-------------|
| `create_opd_table` | opd | id(uuid), kode_opd, nama_opd |
| `create_kategori_aset_table` | kategori_aset | id(uuid), kode_kib, nama_kategori |
| `create_gis_layer_table` | gis_layer | id(uuid), nama_layer, warna |
| `create_jenis_pemanfaatan_table` | jenis_pemanfaatan | id(uuid), kode, nama |
| `create_pihak_ketiga_table` | pihak_ketiga | id(uuid), nama, jenis |
| `create_aset_table` | aset | id(uuid), opd_id(FK), kategori_id(FK), kode_barang |
| `create_gis_aset_table` | gis_aset | id(uuid), aset_id(FK), layer_id(FK), latitude, longitude |
| `create_pemanfaatan_table` | pemanfaatan | id(uuid), aset_id(FK), jenis_id(FK), pihak_ketiga_id(FK) |
| `create_dokumen_pemanfaatan_table` | dokumen_pemanfaatan | id(uuid), pemanfaatan_id(FK), file_path |
| `create_foto_aset_table` | foto_aset | id(uuid), aset_id(FK), file_path |
| `create_riwayat_aset_table` | riwayat_aset | id(uuid), aset_id(FK), aksi |

**Catatan:**
- Semua tabel pakai **UUID** sebagai primary key (bukan auto-increment integer)
- `pemanfaatan.created_by` dan `riwayat_aset.user_id` pakai **bigint** karena relasi ke tabel `users` yang pakai ID default Laravel

---

## 6. Cara Pakai API

### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@example.com", "password": "password"}'

# Response:
# {"user": {...}, "token": "1|abc123..."}
```

### Pakai Token
```bash
curl http://localhost:8000/api/aset \
  -H "Authorization: Bearer 1|abc123..."
```

### Contoh Lain
```bash
# Lihat dashboard
curl http://localhost:8000/api/dashboard -H "Authorization: Bearer TOKEN"

# Buat aset baru
curl -X POST http://localhost:8000/api/aset \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "opd_id": "uuid-opd",
    "kategori_id": "uuid-kategori",
    "kode_barang": "A.001",
    "nama_barang": "Gedung Kantor",
    "kondisi": "Baik"
  }'

# Cari aset
curl "http://localhost:8000/api/aset?search=kantor&status=Aktif" \
  -H "Authorization: Bearer TOKEN"

# Lihat data GIS sebagai GeoJSON
curl "http://localhost:8000/api/gis/aset?bbox=98.5,3.5,98.8,3.7" \
  -H "Authorization: Bearer TOKEN"
```

---

## 7. Fix yang Dilakukan Saat Build

| Fix | Kenapa |
|-----|--------|
| Tambah `HasApiTokens` ke User model | Sanctum butuh trait ini untuk generate token |
| Register api routes di `bootstrap/app.php` | Laravel 13 butuh registrasi explicit untuk route api |

---

## 8. File yang Dibuat/Diubah

```
backend/app/Models/
├── Opd.php
├── KategoriAset.php
├── GisLayer.php
├── JenisPemanfaatan.php
├── PihakKetiga.php
├── Aset.php
├── GisAset.php
├── Pemanfaatan.php
├── DokumenPemanfaatan.php
├── FotoAset.php
└── RiwayatAset.php

backend/app/Http/Controllers/Api/
├── AuthController.php
├── OpdController.php
├── KategoriAsetController.php
├── GisLayerController.php
├── JenisPemanfaatanController.php
├── PihakKetigaController.php
├── AsetController.php
├── GisAsetController.php
├── PemanfaatanController.php
├── DokumenPemanfaatanController.php
├── FotoAsetController.php
├── RiwayatAsetController.php
└── DashboardController.php

backend/app/Http/Resources/
├── OpdResource.php
├── KategoriAsetResource.php
├── GisLayerResource.php
├── JenisPemanfaatanResource.php
├── PihakKetigaResource.php
├── AsetResource.php
├── GisAsetResource.php
├── PemanfaatanResource.php
├── DokumenPemanfaatanResource.php
├── FotoAsetResource.php
├── RiwayatAsetResource.php
└── UserResource.php

backend/routes/api.php (diubah)
backend/app/Models/User.php (ditambah HasApiTokens)
backend/bootstrap/app.php (ditambah api route registration)
```
