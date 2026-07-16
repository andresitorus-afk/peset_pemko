# Backend API Design: Pemanfaatan Aset Pemko Medan

## Overview

REST API backend for managing Medan city government asset utilization. Built with Laravel 13 + Sanctum + PostgreSQL.

## Database Schema (from migrations)

### Tables

| Table | PK | Key Fields |
|-------|----|----|
| `opd` | uuid | kode_opd (unique), nama_opd, alamat, telepon, kepala_opd, nip_kepala |
| `kategori_aset` | uuid | kode_kib (KIB A-F), nama_kategori, keterangan |
| `gis_layer` | uuid | nama_layer, warna, icon_marker, is_active |
| `jenis_pemanfaatan` | uuid | kode (SEWA/PKP/KSP/BGS/BSG/KSPI), nama, dasar_hukum, ketentuan |
| `pihak_ketiga` | uuid | nama, jenis (Perorangan/Badan_Hukum/Pemda), npwp, alamat, telepon, email, penanggung_jawab |
| `aset` | uuid | opd_id (FK), kategori_id (FK), kode_barang (unique), register, nama_barang, tahun_perolehan, nilai_perolehan, nilai_buku, luas, kondisi, status, alamat, keterangan |
| `gis_aset` | uuid | aset_id (FK unique), layer_id (FK), latitude, longitude, polygon_geojson (jsonb), luas_gis, tipe_geometri, foto_udara_url, sumber_koordinat, surveyed_at |
| `pemanfaatan` | uuid | aset_id (FK), jenis_id (FK), pihak_ketiga_id (FK), nomor_perjanjian, tanggal_mulai, tanggal_selesai, nilai_kontrak, kontribusi_tahunan, peruntukan, status, catatan, created_by (FK users) |
| `dokumen_pemanfaatan` | uuid | pemanfaatan_id (FK), jenis_dokumen, nomor_dokumen, tanggal_dokumen, file_path, file_name |
| `foto_aset` | uuid | aset_id (FK), file_path, caption, tipe, tanggal_foto |
| `riwayat_aset` | uuid | aset_id (FK), aksi, deskripsi, user_id (FK users) |

### Relationships

```
opd ──────────────┐
                   ├──< aset
kategori_aset ────┘
                    │
                    ├──< pemanfaatan >── jenis_pemanfaatan
                    │       │
                    │       └──< dokumen_pemanfaatan
                    │
                    ├──< foto_aset
                    ├──< riwayat_aset
                    └────── gis_aset >── gis_layer

pihak_ketiga ────── pemanfaatan
users ───────────── pemanfaatan (created_by)
users ───────────── riwayat_aset (user_id)
```

## Architecture

```
backend/app/
├── Http/
│   └── Controllers/
│       └── Api/
│           ├── AuthController.php
│           ├── OpdController.php
│           ├── KategoriAsetController.php
│           ├── GisLayerController.php
│           ├── JenisPemanfaatanController.php
│           ├── PihakKetigaController.php
│           ├── AsetController.php
│           ├── GisAsetController.php
│           ├── PemanfaatanController.php
│           ├── DokumenPemanfaatanController.php
│           ├── FotoAsetController.php
│           ├── RiwayatAsetController.php
│           └── DashboardController.php
├── Http/Resources/
│   ├── OpdResource.php
│   ├── KategoriAsetResource.php
│   ├── GisLayerResource.php
│   ├── JenisPemanfaatanResource.php
│   ├── PihakKetigaResource.php
│   ├── AsetResource.php
│   ├── GisAsetResource.php
│   ├── PemanfaatanResource.php
│   ├── DokumenPemanfaatanResource.php
│   ├── FotoAsetResource.php
│   └── RiwayatAsetResource.php
├── Models/
│   ├── Opd.php
│   ├── KategoriAset.php
│   ├── GisLayer.php
│   ├── JenisPemanfaatan.php
│   ├── PihakKetiga.php
│   ├── Aset.php
│   ├── GisAset.php
│   ├── Pemanfaatan.php
│   ├── DokumenPemanfaatan.php
│   ├── FotoAset.php
│   └── RiwayatAset.php
routes/api.php
```

## API Endpoints

### Authentication

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| POST | `/api/register` | Register user | No |
| POST | `/api/login` | Login, return token | No |
| POST | `/api/logout` | Revoke current token | Yes |
| GET | `/api/user` | Get current user | Yes |

### Dashboard

| Method | Endpoint | Description | Auth |
|--------|----------|-------------|------|
| GET | `/api/dashboard` | Summary stats | Yes |

**Response:**
```json
{
  "total_aset": 1234,
  "aset_per_kategori": [...],
  "aset_per_status": {...},
  "total_nilai_perolehan": 123456789,
  "total_nilai_buku": 987654321,
  "pemanfaatan_aktif": 45,
  "pemanfaatan_segera_berakhir": 5,
  "pemanfaatan_per_jenis": [...],
  "pihak_ketiga_terbanyak": [...]
}
```

### Reference Tables (CRUD)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET/POST | `/api/opd` | List / Create |
| GET/PUT/DELETE | `/api/opd/{id}` | Show / Update / Delete |
| GET/POST | `/api/kategori-aset` | List / Create |
| GET/PUT/DELETE | `/api/kategori-aset/{id}` | Show / Update / Delete |
| GET/POST | `/api/gis-layer` | List / Create |
| GET/PUT/DELETE | `/api/gis-layer/{id}` | Show / Update / Delete |
| GET/POST | `/api/jenis-pemanfaatan` | List / Create |
| GET/PUT/DELETE | `/api/jenis-pemanfaatan/{id}` | Show / Update / Delete |
| GET/POST | `/api/pihak-ketiga` | List / Create |
| GET/PUT/DELETE | `/api/pihak-ketiga/{id}` | Show / Update / Delete |

### Aset

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/aset` | List (search, filter, paginate) |
| POST | `/api/aset` | Create |
| GET | `/api/aset/{id}` | Show (with relations) |
| PUT | `/api/aset/{id}` | Update |
| DELETE | `/api/aset/{id}` | Delete |
| GET | `/api/aset/{id}/pemanfaatan` | Pemanfaatan for asset |
| GET | `/api/aset/{id}/foto` | Photos for asset |
| GET | `/api/aset/{id}/riwayat` | History for asset |

**Query Parameters for GET /api/aset:**
- `search` — search by nama_barang or kode_barang
- `opd_id` — filter by OPD
- `kategori_id` — filter by kategori
- `kondisi` — filter by kondisi (Baik/Rusak_Ringan/Rusak_Berat)
- `status` — filter by status (Aktif/Idle/Dimanfaatkan)
- `per_page` — pagination (default 15)

### Pemanfaatan

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/pemanfaatan` | List (filter by status, jenis, pihak_ketiga) |
| POST | `/api/pemanfaatan` | Create + auto-update aset status |
| GET | `/api/pemanfaatan/{id}` | Show (with relations) |
| PUT | `/api/pemanfaatan/{id}` | Update |
| DELETE | `/api/pemanfaatan/{id}` | Delete + revert aset status |
| GET | `/api/pemanfaatan/{id}/dokumen` | Documents for pemanfaatan |

### Dokumen & Foto

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/dokumen-pemanfaatan` | Upload document |
| DELETE | `/api/dokumen-pemanfaatan/{id}` | Delete document |
| POST | `/api/foto-aset` | Upload photo |
| DELETE | `/api/foto-aset/{id}` | Delete photo |

### Riwayat Aset

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/riwayat-aset` | List (filter by aksi, aset_id) |
| POST | `/api/riwayat-aset` | Create (auto-recorded on aset changes) |

### GIS

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/gis/aset` | All assets as GeoJSON FeatureCollection |
| GET | `/api/gis/layer/{id}` | Assets by layer as GeoJSON |
| GET | `/api/gis/aset/{id}` | Single asset as GeoJSON Feature |

**Query Parameters:**
- `bbox` — bounding box filter (west,south,east,north)
- `layer_id` — filter by GIS layer
- `status` — filter by aset status

**GeoJSON Response:**
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
        "id": "uuid",
        "nama_barang": "Gedung Kantor",
        "kode_barang": "A.001",
        "status": "Dimanfaatkan",
        "kondisi": "Baik",
        "layer": "Bangunan",
        "pemanfaatan": {
          "jenis": "Sewa",
          "pihak_ketiga": "PT XYZ",
          "tanggal_selesai": "2026-12-31"
        }
      }
    }
  ]
}
```

## Notes

- `pemanfaatan.created_by` and `riwayat_aset.user_id` use `foreignId()` (bigint) to match Laravel's default `users.id`. All other tables use UUID primary keys.
- The `aset.status` field has 3 states: Aktif (not utilized), Idle (available), Dimanfaatkan (currently utilized).

## Key Behaviors

1. **Aset status auto-update**: When pemanfaatan is created with status "Aktif", aset status changes to "Dimanfaatkan". When pemanfaatan ends/cancelled, aset status reverts to "Aktif".

2. **Riwayat auto-recording**: Aset status changes auto-create riwayat_aset records.

3. **File storage**: Dokumen and foto stored in `storage/app/public/` with symbolic link from `public/storage/`.

4. **Pagination**: All list endpoints return paginated results with `data`, `links`, `meta` structure.

5. **Error responses**: Standard Laravel validation errors with 422 status, 401 for unauthenticated, 403 for unauthorized, 404 for not found.

## Tech Stack

- Laravel 13 + PHP 8.3
- Laravel Sanctum (token auth)
- PostgreSQL 15
- Eloquent ORM with UUID primary keys
- Laravel API Resources for JSON transformation
- Laravel Storage for file uploads
