# Contributing Guide

## Prerequisites

- Git
- Docker & Docker Compose
- Node.js 18+ (untuk development tanpa Docker)

## Setup

```bash
git clone https://github.com/andresitorus-afk/peset_pemko.git
cp .env.example .env
docker-compose up -d
docker-compose exec backend php artisan key:generate
docker-compose exec backend php artisan migrate
```

## Branching

- `develop` — branch utama, semua PR masuk ke sini
- `feat/nama-fitur` — branch untuk fitur baru
- `fix/nama-bug` — branch untuk perbaikan bug

Contoh:
```bash
git checkout develop
git checkout -b feat/master-aset
```

## Commit Convention

Format: `type: deskripsi singkat`

### Type

| Type | Keterangan |
|------|------------|
| `feat` | Fitur baru |
| `fix` | Perbaikan bug |
| `docs` | Perubahan dokumentasi (README, CONTRIBUTING, dll) |
| `style` | Format kode tanpa mengubah isi (spasi, titik koma, dll) |
| `refactor` | Refaktor kode tanpa menambah fitur atau fix bug |
| `test` | Menambah atau mengubah test |
| `chore` | Setup tooling, CI/CD, dependency, atau konfigurasi |
| `db` | Perubahan terkait database (migration, seeder) |
| `api` | Perubahan endpoint atau request/response API |

### Contoh

```
feat: tambah halaman master aset
fix: koreksi validasi form pemanfaatan
chore: update docker-compose postgres
db: tambah migration tabel pihak_ketiga
api: tambah endpoint list aset
docs: tambah CONTRIBUTING.md
```

### Rules

- Deskripsi pakai **Bahasa Indonesia** atau **English**, pilih salah satu konsisten
- Huruf pertama **lowercase** (`feat: tambah`, bukan `feat: Tambah`)
- Deskripsi **pendek** maksimal 50 karakter
- Gunakan **imperative mood** — "tambah", bukan "menambah" atau "sudah tambah"

## Pull Request

1. Push branch ke remote
2. Buka PR ke branch `develop`
3. Isi judul dengan format yang sama seperti commit
4. Deskripsikan perubahan secara singkat
5. Tunggu review atau langsung merge kalau sudah oke

## Setelah Git Clone — Yang Harus Dilakukan

```bash
# 1. Copy env
cp .env.example .env

# 2. Jalankan Docker
docker compose up -d

# 3. Generate app key
docker compose exec backend php artisan key:generate

# 4. Install dependency PHP (jika belum)
docker compose exec backend composer install

# 5. Install dependency frontend
cd frontend && npm install && cd ..

# 6. Jalankan migration + seed (OPD, Kategori KIB dari XLSX, demo data)
docker compose exec backend php artisan migrate:fresh --seed --force

# 7. Pastikan file KIB XLSX ada di backend/ (untuk seeder kategori)
#    - backend/KIBA.xlsx
#    - backend/KIBC.xlsx
#    (file ini sudah di-commit di repo)

# 8. Jalankan frontend dev
cd frontend && npm run dev
```

### Akun Demo

| Email | Password | Role |
|-------|----------|------|
| admin@pemkomedan.go.id | password | Admin |
| petugas@pemkomedan.go.id | password | Petugas Aset |

### URL

- Frontend: http://localhost:3000
- Backend API: http://localhost:8000/api

### Checklist Setelah Setup

- [ ] `docker compose ps` — semua container running
- [ ] `docker compose exec backend php artisan tinker --execute="echo \App\Models\Aset::count();"` — harusnya > 0
- [ ] Buka http://localhost:8000/api/aset — harus return JSON (minta auth dulu)
