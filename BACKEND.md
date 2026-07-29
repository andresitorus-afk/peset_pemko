# Backend — Pemanfaatan Aset Pemko Medan

## Arsitektur Umum

```
┌──────────────┐    ┌──────────────┐    ┌────────────────────┐
│   Frontend   │───▶│   Laravel    │───▶│   PostgreSQL 15    │
│   Nuxt 3     │    │   API-only   │    │   (via Docker)     │
│   :3000      │    │   :8000      │    │   :5433 external   │
└──────────────┘    └──────┬───────┘    └────────────────────┘
                           │
                    ┌──────▼───────┐
                    │  Sanctum     │
                    │  Token Auth  │
                    └──────────────┘
```

**Stack:** Laravel 13 (API-only, no Blade), Nuxt 3 (SSR), PostgreSQL 15, Docker Compose, Sanctum token auth, Maatwebsite Excel.

---

## Database — Schema & Hubungan Antar Tabel

### ERD (Entity Relationship Diagram)

```
┌─────────────────┐       ┌──────────────────┐
│     users        │       │       opd         │
├─────────────────┤       ├──────────────────┤
│ id (bigint PK)  │       │ id (uuid PK)     │
│ name             │       │ kode_opd (unique) │
│ email (unique)   │       │ nama_opd         │
│ password         │       │ alamat           │
│ ...              │       │ telepon          │
└────────┬────────┘       │ kepala_opd       │
         │                 │ nip_kepala       │
         │                 └────────┬─────────┘
         │                          │
         │ 1:N (created_by)         │ 1:N (opd_id)
         │                          │
┌────────▼──────────────────────────▼─────────┐
│                   aset                        │
├──────────────────────────────────────────────┤
│ id (uuid PK)                                │
│ opd_id (FK → opd.id)           RESTRICT DEL │
│ kategori_id (FK → kategori_aset.id) RESTRICT│
│ kode_barang (unique)                         │
│ register                                     │
│ nama_barang                                  │
│ tahun_perolehan (int)                        │
│ nilai_perolehan (decimal 20,2)               │
│ nilai_buku (decimal 20,2)                    │
│ luas (decimal 15,2) — m²                     │
│ kondisi: Baik | Rusak_Ringan | Rusak_Berat   │
│ status: Aktif | Idle | Dimanfaatkan          │
│ alamat                                       │
│ keterangan                                   │
│ created_at, updated_at                       │
└───┬──────────┬──────────┬──────────┬────────┘
    │          │          │          │
    │1:1       │1:N       │1:N       │1:N
    ▼          ▼          ▼          ▼
┌────────┐ ┌────────┐ ┌────────┐ ┌──────────┐
│gis_aset│ │pemanfaa-│ │foto_aset│ │riwayat_aset│
│        │ │tan     │ │        │ │          │
└────────┘ └────┬───┘ └────────┘ └──────────┘
                │
                │1:N
                ▼
          ┌────────────────────┐
          │dokumen_pemanfaatan │
          └────────────────────┘
```

### Tabel-Tabel Utama

#### 1. `users` — Pengguna Sistem
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | bigint (PK) | Auto-increment |
| name | varchar | Nama pengguna |
| email | varchar (unique) | Email login |
| password | varchar | Hashed |
| remember_token | varchar | Remember me |

Relasi: `riwayat_aset.user_id` → users (nullOnDelete). Juga `pemanfaatan.created_by` → users.

---

#### 2. `opd` — Organisasi Perangkat Daerah
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid (PK) | gen_random_uuid() |
| kode_opd | varchar (unique) | Contoh: "OPD.001" |
| nama_opd | varchar | Contoh: "Dinas Pendidikan" |
| alamat | varchar (nullable) | |
| telepon | varchar (nullable) | |
| kepala_opd | varchar (nullable) | Nama kepala |
| nip_kepala | varchar (nullable) | NIP kepala |

Relasi: 1 OPD → banyak Aset (`opd.aset()` → HasMany Aset)

---

#### 3. `kategori_aset` — Klasifikasi Aset (KIB + Sub-kategori)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid (PK) | gen_random_uuid() |
| kode_kib | varchar | Kode induk: "KIB A" s/d "KIB F" |
| kode_kategori | varchar (nullable) | Kode lengkap, misal "1.3.1.01.01.01.001" |
| nama_kategori | varchar | Nama uraian |
| keterangan | varchar (nullable) | |
| parent_id | uuid FK → kategori_aset (nullable) | Self-referencing FK untuk hirarki |
| is_leaf | boolean (default false) | true = sub-kategori paling detail, bisa dipilih untuk aset |

**Struktur Hirarki:**
```
KIB A (induk, parent_id=null, is_leaf=false)
  └── 1.3.1 Tanah (kode_kategori, is_leaf=false)
        └── 1.3.1.01.01.01.001 [Uraian] (is_leaf=true) ← dipilih untuk aset
KIB C (induk, parent_id=null, is_leaf=false)
  └── ...
```

**Isi seed dari XLS:**
- KIB A (Tanah): 226 sub-kategori dari `KIBA.xlsx`
- KIB C (Gedung dan Bangunan): 378 sub-kategori dari `KIBC.xlsx`

Relasi: 
- 1 Kategori → banyak Aset (`kategori.aset()` → HasMany)
- 1 Kategori → parent (`kategori.parent()` → BelongsTo self)
- 1 Kategori → children (`kategori.children()` → HasMany self)

---

#### 4. `aset` — Data Aset (Tabel Inti)
Relasi:
- `aset.opd()` → BelongsTo Opd (opd_id FK, restrictOnDelete)
- `aset.kategori()` → BelongsTo KategoriAset (kategori_id FK, restrictOnDelete)
- `aset.gisAset()` → HasOne GisAset (satu aset punya satu data GIS)
- `aset.pemanfaatan()` → HasMany Pemanfaatan (satu aset bisa dimanfaatkan berkali-kali)
- `aset.foto()` → HasMany FotoAset
- `aset.riwayat()` → HasMany RiwayatAset

Scope: `search(nama_barang/kode_barang ilike)`, `filter(opd_id/kategori_id/kondisi/status)`

---

#### 5. `gis_aset` — Koordinat/Polygon Aset di Peta
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid (PK) | |
| aset_id | uuid FK (unique) → aset | CASCADE delete |
| layer_id | uuid FK → gis_layer | RESTRICT delete |
| latitude | decimal(10,7) | |
| longitude | decimal(10,7) | |
| polygon_geojson | jsonb | Batas area polygon |
| luas_gis | decimal(15,2) | Luas dari pengukuran |
| tipe_geometri | varchar | Point, Polygon, Polyline |
| foto_udara_url | varchar (nullable) | |
| sumber_koordinat | varchar | GPS, Survey, GoogleMaps |
| surveyed_at | timestamp | |

Relasi: `gis_aset.aset()` → BelongsTo Aset, `gis_aset.layer()` → BelongsTo GisLayer

---

#### 6. `gis_layer` — Layer Peta
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid (PK) | |
| nama_layer | varchar | Tanah, Bangunan, Jalan, Fasilitas Umum |
| warna | varchar | Hex color |
| icon_marker | varchar | |
| is_active | boolean | |

Relasi: `layer.gisAset()` → HasMany GisAset

---

#### 7. `pemanfaatan` — Pemanfaatan Aset (Inti Bisnis)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid (PK) | |
| aset_id | FK → aset | RESTRICT delete |
| jenis_id | FK → jenis_pemanfaatan | RESTRICT delete |
| pihak_ketiga_id | FK → pihak_ketiga | RESTRICT delete |
| nomor_perjanjian | varchar | |
| tanggal_mulai | date | |
| tanggal_selesai | date | |
| nilai_kontrak | decimal(20,2) | |
| kontribusi_tahunan | decimal(20,2) | |
| peruntukan | varchar | |
| status | varchar | Aktif, Berakhir, Dibatalkan |
| catatan | text | |
| created_by | FK → users | nullOnDelete |
| created_at | timestamp | created_at only (UPDATED_AT = null) |

Relasi:
- `pemanfaatan.aset()` → BelongsTo Aset
- `pemanfaatan.jenis()` → BelongsTo JenisPemanfaatan
- `pemanfaatan.pihakKetiga()` → BelongsTo PihakKetiga
- `pemanfaatan.creator()` → BelongsTo User (created_by)
- `pemanfaatan.dokumen()` → HasMany DokumenPemanfaatan

---

#### 8. `jenis_pemanfaatan` — Jenis Pemanfaatan
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid (PK) | |
| kode | varchar | SEWA, PKP, KSP, BGS, BSG, KSPI |
| nama | varchar | Sewa, Pinjam Pakai, KSP, dsb |
| dasar_hukum | text | |
| ketentuan | text | |

Relasi: 1 jenis → banyak Pemanfaatan

---

#### 9. `pihak_ketiga` — Pihak Ketiga
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid (PK) | |
| nama | varchar | Nama perusahaan/orang |
| jenis | varchar | Perorangan, Badan_Hukum, Pemda |
| npwp | varchar | |
| alamat, telepon, email | varchar | |
| penanggung_jawab | varchar | |

Relasi: 1 pihak → banyak Pemanfaatan

---

#### 10. `dokumen_pemanfaatan` — File Dokumen Perjanjian
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid (PK) | |
| pemanfaatan_id | FK → pemanfaatan | CASCADE delete |
| jenis_dokumen | varchar | SK, Perjanjian, BA, Perpanjangan |
| nomor_dokumen | varchar | |
| tanggal_dokumen | date | |
| file_path | varchar | Storage path |
| file_name | varchar | Original filename |

Relasi: `dokumen.pemanfaatan()` → BelongsTo Pemanfaatan

---

#### 11. `foto_aset` — Foto Aset
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid (PK) | |
| aset_id | FK → aset | CASCADE delete |
| file_path | varchar | Storage path |
| caption | varchar | |
| tipe | varchar | Depan, Samping, Udara, Lainnya |
| tanggal_foto | date | |

---

#### 12. `riwayat_aset` — Log Aktivitas Aset
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| id | uuid (PK) | |
| aset_id | FK → aset | CASCADE delete |
| aksi | varchar | Pemanfaatan, Pemeliharaan, Mutasi, Penghapusan, Revaluasi |
| deskripsi | text | |
| user_id | FK → users | nullOnDelete |
| created_at | timestamp | created_at only |

---

## API Routes

Semua route di prefix `/api`, kecuali `/register` dan `/login`.

### Publik
| Metode | Endpoint | Controller | Keterangan |
|--------|----------|------------|------------|
| POST | `/api/register` | AuthController@register | Daftar akun baru |
| POST | `/api/login` | AuthController@login | Login, dapat token |

### Authenticated (auth:sanctum)

#### Auth
| GET | `/api/user` | AuthController@user | Data user login |
| POST | `/api/logout` | AuthController@logout | Hapus token |

#### Dashboard
| GET | `/api/dashboard` | DashboardController@index | Statistik total aset, nilai, pemanfaatan |

#### Master Data
| Method | Endpoint | Controller | Keterangan |
|--------|----------|------------|------------|
| CRUD | `/api/opd` | OpdController | OPD (apiResource) |
| CRUD | `/api/kategori-aset` | KategoriAsetController | Kategori Aset dengan children |
| CRUD | `/api/gis-layer` | GisLayerController | Layer Peta |
| CRUD | `/api/jenis-pemanfaatan` | JenisPemanfaatanController | Jenis Pemanfaatan |
| CRUD | `/api/pihak-ketiga` | PihakKetigaController | Pihak Ketiga |

#### Aset
| Method | Endpoint | Controller | Keterangan |
|--------|----------|------------|------------|
| GET/POST | `/api/aset` | AsetController@index/store | List (dengan search + filter) / Create |
| GET/PUT/DELETE | `/api/aset/{id}` | AsetController@show/update/destroy | Detail / Update / Hapus |
| POST | `/api/aset/import` | AsetController@import | Import Excel/CSV |
| GET | `/api/aset/template` | AsetController@template | Download template XLSX |
| GET | `/api/aset/{id}/pemanfaatan` | AsetController@pemanfaatan | Pemanfaatan per aset |
| GET | `/api/aset/{id}/foto` | AsetController@foto | Foto per aset |
| GET | `/api/aset/{id}/riwayat` | AsetController@riwayat | Riwayat per aset |

#### Pemanfaatan
| Method | Endpoint | Controller | Keterangan |
|--------|----------|------------|------------|
| GET/POST | `/api/pemanfaatan` | PemanfaatanController@index/store | List / Create |
| GET/PUT/DELETE | `/api/pemanfaatan/{id}` | PemanfaatanController@show/update/destroy | Detail / Update / Hapus |
| GET | `/api/pemanfaatan/{id}/dokumen` | PemanfaatanController@dokumen | Dokumen per pemanfaatan |

#### Dokumen & Foto (Upload)
| POST | `/api/dokumen-pemanfaatan` | DokumenPemanfaatanController@store | Upload dokumen |
| DELETE | `/api/dokumen-pemanfaatan/{id}` | DokumenPemanfaatanController@destroy | Hapus dokumen |
| POST | `/api/foto-aset` | FotoAsetController@store | Upload foto |
| DELETE | `/api/foto-aset/{id}` | FotoAsetController@destroy | Hapus foto |

#### Riwayat
| GET | `/api/riwayat-aset` | RiwayatAsetController@index | List riwayat |
| POST | `/api/riwayat-aset` | RiwayatAsetController@store | Tambah riwayat manual |

#### GIS (Peta)
| GET | `/api/gis/aset` | GisAsetController@index | GeoJSON (supports bbox & layer_id) |
| GET | `/api/gis/aset/{id}` | GisAsetController@show | GeoJSON single fitur |
| GET | `/api/gis/layer/{id}` | GisAsetController@byLayer | GeoJSON semua aset di layer |

---

## Alur Kerja Utama

### 1. Login & Auth Flow

```
Frontend (Nuxt)              Backend (Laravel)           PostgreSQL
    │                              │                         │
    │  POST /api/login             │                         │
    │  {email, password}          │                         │
    │ ─────────────────────────▶  │  Auth::attempt()        │
    │                              │ ─────────────────────▶  │ SELECT users WHERE email=?
    │                              │  ◀───────────────────── │
    │                              │                         │
    │  {user, token}              │  $user->createToken()   │
    │ ◀─────────────────────────  │ ─────────────────────▶  │ INSERT personal_access_tokens
    │                              │                         │
    │  Header: Authorization:      │                         │
    │  Bearer <token>              │                         │
```

Frontend menyimpan token, setiap request API kirim header `Authorization: Bearer <token>`.

---

### 2. Alur CRUD Aset

```
Frontend                      Backend                       DB
   │                              │                          │
   │  GET /api/aset               │                          │
   │  ?search=...&opd_id=...     │  Aset::query()           │
   │ ─────────────────────────▶  │    ->with('opd,kategori') │
   │                              │    ->search()            │
   │                              │    ->filter()            │
   │                              │    ->paginate()          │
   │                              │ ─────────────────────▶   │ SELECT + JOIN
   │                              │ ◀─────────────────────   │
   │  AsetResource::collection() │                          │
   │ ◀─────────────────────────  │                          │
```

**Store:** `POST /api/aset` → validate → `Aset::create()` → return AsetResource

**Update:** `PUT /api/aset/{id}` → validate → `$aset->update()` → jika status berubah, otomatis buat RiwayatAset → return AsetResource

---

### 3. Alur Pemanfaatan Aset (Bisnis Inti)

```
                    ┌─────────────────────────────────────┐
                    │         PEMANFAATAN ASET             │
                    └─────────────────────────────────────┘

1. POST /api/pemanfaatan
   Input: aset_id, jenis_id, pihak_ketiga_id, tanggal, nilai, dll

2. Backend:
   - Validate semua FK exists
   - Pemanfaatan::create()
   - JIKA status Aktif:
     → Aset.update(status='Dimanfaatkan')
     → Aset.riwayat().create(aksi='Pemanfaatan', deskripsi=...)

3. DELETE /api/pemanfaatan/{id}
   - Delete pemanfaatan
   - Cek: apakah aset masih punya pemanfaatan aktif?
   - JIKA TIDAK → Aset.update(status='Aktif') + riwayat
```

Status Aset otomatis berubah:
- `Aktif` → `Dimanfaatkan` (saat pemanfaatan baru dibuat)
- `Dimanfaatkan` → `Aktif` (saat semua pemanfaatan dihapus)

---

### 4. Alur Import Excel

```
Frontend                      Backend                       DB
   │                              │                          │
   │  POST /api/aset/import       │                          │
   │  file: aset.xlsx             │                          │
   │ ─────────────────────────▶  │                          │
   │                              │  Excel::import()         │
   │                              │  AsetImport::collection()│
   │                              │    ┌──────────────┐      │
   │                              │    │ DB::beginTransaction│
   │                              │    └──────────────┘      │
   │                              │    ForEach row:          │
   │                              │      resolveOpd(row)     │
   │                              │        → cache lookup    │
   │                              │      resolveKategori(row)│
   │                              │        → cache lookup    │
   │                              │      Check duplicate     │
   │                              │        → skip if exists  │
   │                              │      normalizeKondisi()  │
   │                              │      normalizeStatus()   │
   │                              │      Aset::create()      │
   │                              │ ─────────────────────▶   │ INSERT aset
   │                              │    ┌──────────────┐      │
   │                              │    │ DB::commit() │      │
   │                              │    └──────────────┘      │
   │  {success, failed, errors}  │                          │
   │ ◀─────────────────────────  │                          │
```

**Kolom XLS yang diperlukan:**
`kode_barang`, `register`, `nama_barang`, `opd`, `kode_kategori`, `tahun_perolehan`, `nilai_perolehan`, `nilai_buku`, `luas`, `kondisi`, `status`, `alamat`, `keterangan`

**Resolusi Otomatis:**
- `opd` bisa diisi nama_opd ATAU kode_opd → resolve ke opd.id
- `kode_kategori` / `kode_kib` / `nama_kategori` → resolve ke kategori_aset.id
- `kondisi`: "baik" → Baik, "rusak ringan" → Rusak_Ringan, "rusak berat" → Rusak_Berat
- `status`: "dimanfaatkan" → Dimanfaatkan, "idle" → Idle, default Aktif

**Template Download:** `GET /api/aset/template` → XLSX dengan 3 sheet:
1. Sheet utama: header kolom + 1 baris sample
2. Sheet "Data OPD": daftar kode_opd & nama_opd
3. Sheet "Data Kategori": daftar kode_kategori & nama (is_leaf=true)

---

### 5. Alur GIS (Peta)

```
Frontend (Map)                 Backend                       DB
   │                              │                          │
   │  GET /api/gis/aset           │                          │
   │  ?bbox=west,south,east,north │                          │
   │  &layer_id=...               │                          │
   │ ─────────────────────────▶  │  GisAset::query()        │
   │                              │    ->with('aset.opd,layer│
   │                              │    ->whereBetween(lon)   │
   │                              │    ->whereBetween(lat)   │
   │                              │ ─────────────────────▶   │ SELECT gis_aset + JOIN
   │                              │ ◀─────────────────────   │
   │                              │  map → GeoJSON Feature   │
   │  FeatureCollection {         │  FeatureCollection {     │
   │    features: [...]           │    features: [...]       │
   │  }                           │  }                       │
   │ ◀─────────────────────────  │                          │
```

Setiap fitur GeoJSON berisi:
- `geometry`: Point atau Polygon dari gis_aset
- `properties`: kode_barang, nama, status, kondisi, layer, luas

---

## Seed Data (Demo)

`DatabaseSeeder` menjalankan `KategoriAsetSeeder` dulu, lalu insert demo data:

| Entity | Jumlah | Isi |
|--------|--------|-----|
| User | 2 | admin@pemkomedan.go.id, petugas@pemkomedan.go.id |
| OPD | 6 | Dinas Pendidikan, Kesehatan, PUPR, Kebersihan, Perhubungan, Pengendalian Penduduk |
| Kategori KIB | 6 induk + 604 sub | KIB A (226 sub), KIB C (378 sub) |
| GIS Layer | 4 | Tanah, Bangunan, Jalan, Fasilitas Umum |
| Jenis Pemanfaatan | 5 | Sewa, Pinjam Pakai, KSP, Bagi Hasil, Bantuan Sosial |
| Pihak Ketiga | 6 | Bank Sumut, Telkom, Tirtanadi, Pertamina, Perorangan, Yayasan |
| Aset | 10 | Tanah (3), Gedung (3), Peralatan (4) |
| GisAset | 4 | 2 polygon tanah, 2 point bangunan |
| Pemanfaatan | 4 | 3 aktif, 1 berakhir |
| Dokumen | 3 | SK + Perjanjian |
| Foto | 3 | Depan/Samping |
| Riwayat | 3 | Pemanfaatan, Pemeliharaan, Mutasi |

---

## File Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php          # Register, Login, Logout, User
│   │   │   ├── DashboardController.php     # Statistik ringkasan
│   │   │   ├── AsetController.php          # CRUD + import + template
│   │   │   ├── OpdController.php           # CRUD OPD
│   │   │   ├── KategoriAsetController.php  # CRUD Kategori (hirarki)
│   │   │   ├── GisLayerController.php      # CRUD Layer Peta
│   │   │   ├── GisAsetController.php       # GeoJSON API
│   │   │   ├── JenisPemanfaatanController.php
│   │   │   ├── PihakKetigaController.php
│   │   │   ├── PemanfaatanController.php   # CRUD + auto-status aset
│   │   │   ├── DokumenPemanfaatanController.php # Upload dokumen
│   │   │   ├── FotoAsetController.php      # Upload foto
│   │   │   └── RiwayatAsetController.php   # Log aktivitas
│   │   └── Resources/                      # 12 JsonResource (format output)
│   ├── Imports/
│   │   └── AsetImport.php                  # Maatwebsite Excel import
│   └── Models/                             # 12 Model (Eloquent)
├── database/
│   ├── migrations/                         # 16 migration files
│   └── seeders/
│       ├── DatabaseSeeder.php              # Full demo data
│       └── KategoriAsetSeeder.php          # Import KIB dari XLSX
├── routes/
│   └── api.php                             # Semua route API
└── config/
    ├── auth.php                            # Eloquent driver
    └── sanctum.php                         # Token auth, no expiry
```

---

## Tech Notes

- **UUID PK** pada semua tabel domain (bukan auto-increment) — generated via `gen_random_uuid()` di PostgreSQL
- **Cascade policy:** `cascadeOnUpdate` + `restrictOnDelete` pada FK utama, `cascadeOnDelete` pada child (foto, dokumen, riwayat, gis_aset)
- **Pemanfaatan.updated_at = null** — hanya created_at yang dicatat
- **Search menggunakan `ILIKE`** (case-insensitive LIKE, PostgreSQL-specific)
- **File upload** ke `storage/app/public/` via Laravel Storage disk `public`
- **No rate limiting** atau throttle pada API (bisa ditambahkan via middleware)
- **Sanctum token expiry = null** (token tidak expired)
