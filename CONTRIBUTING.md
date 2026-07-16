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
