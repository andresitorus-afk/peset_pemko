# Admin Panel & Auth Page — Pemanfaatan Aset Pemko Medan

## Tech Stack
- **Frontend:** Nuxt 3 (Vue 3), Tailwind CSS
- **Backend:** Laravel 13 API-only, Sanctum auth
- **DB:** PostgreSQL 15
- **No new frontend libraries** — stick to Nuxt built-in + inline composables

## Color System
```
Primary:    #0D9488 (teal-600)
Hover:      #0F766E (teal-700)
Light:      #14B8A6 (teal-500)
Bg page:    #F8FAFC (slate-50)
Bg card:    #FFFFFF
Text head:  #0F172A (slate-900)
Text body:  #334155 (slate-700)
Text mute:  #94A3B8 (slate-400)
Success:    #10B981
Warning:    #F59E0B
Error:      #EF4444
Border:     #E2E8F0
```

Font: system sans-serif, 16px base for readability.

## Pages

### 1. Auth Login — `/auth/login`
- Split 50/50
- **Left (form):** White bg, centered card. Logo Pemko Medan at top. Email input, password input (with show/hide toggle), checkbox "Ingat Saya", Login button (teal-600 gradient), link "Lupa Password?".
- **Right (visual):** Dark teal gradient bg. Foto landmark Medan (Balai Kota/Gedung Lama) with overlay. Logo Pemko Medan putih di tengah. Tagline "Kelola Aset Daerah untuk Medan Berkah" di bawah.
- No backend changes needed (AuthController already exists).

### 2. Dashboard — `/admin`
- Top bar: greeting "Selamat Datang, [Nama]" + logout button
- Grid 3 kolom: 9 card modul
- Setiap card: icon SVG (heroicons style), nama modul, deskripsi singkat, jumlah record
- Click card → masuk ke halaman CRUD modul (sidebar muncul)

### 3. Admin Layout (inside module)
- **Top bar:** Breadcrumb (Dashboard > Nama Modul) + User avatar/role + Logout
- **Sidebar kiri:** Daftar semua modul dengan icon, active state highlight
- **Main content:** Tabel/search/filter/pagination untuk List; Form untuk Create/Edit

### 4. CRUD Modules (9 modules)

#### a. OPD — `/admin/opd`
- Fields: kode_opd, nama_opd, alamat, telepon, kepala_opd, nip_kepala
- List: table, search by nama/kode

#### b. Kategori Aset — `/admin/kategori-aset`
- Hanya KIB A (Tanah) dan KIB C (Gedung & Bangunan)
- Cascading 3-level dropdown untuk filter
- CRUD: tambah/edit kategori dengan parent selection via cascading

#### c. GIS Layer — `/admin/gis-layer`
- Fields: nama_layer, warna (color picker), icon_marker, is_active
- Simple table

#### d. Jenis Pemanfaatan — `/admin/jenis-pemanfaatan`
- Fields: kode (SEWA, PKP, KSP, BGS, BSG), nama, dasar_hukum, ketentuan
- Table + form textarea untuk dasar_hukum/ketentuan

#### e. Pihak Ketiga — `/admin/pihak-ketiga`
- Fields: nama, jenis (Perorangan/Badan_Hukum/Pemda), npwp, alamat, telepon, email, penanggung_jawab
- Table with search

#### f. Aset — `/admin/aset`
- Fields: kode_barang, register, nama_barang, opd_id (dropdown), kategori_id (cascading), tahun_perolehan, nilai_perolehan, nilai_buku, luas, kondisi (Baik/Rusak_Ringan/Rusak_Berat), status (Aktif/Idle/Dimanfaatkan), alamat, keterangan
- List: table with search (by nama/kode) + filter (by opd, kategori, kondisi, status)
- Tambah foto aset (upload)
- View detail: all fields + foto gallery

#### g. Pemanfaatan — `/admin/pemanfaatan`
- Fields: aset_id (searchable dropdown), jenis_id, pihak_ketiga_id, nomor_perjanjian, tanggal_mulai, tanggal_selesai, nilai_kontrak, kontribusi_tahunan, peruntukan, status (Aktif/Berakhir/Dibatalkan), catatan
- List: table with filter by status, tanggal
- Tambah dokumen (upload)
- Auto-update status aset when pemanfaatan is created/ended

#### h. Users — `/admin/users`
- Fields: name, email, password, password_confirmation, role_id (dropdown)
- List: table with search

#### i. Roles — `/admin/roles` (new)
- Fields: name, guard_name
- Simple table

## Backend Changes

### New Migration: `roles`
```sql
CREATE TABLE roles (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL UNIQUE,
    guard_name VARCHAR(255) DEFAULT 'web',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### Migration: add `role_id` to `users`
```sql
ALTER TABLE users ADD COLUMN role_id BIGINT NULL REFERENCES roles(id) ON DELETE SET NULL;
```

### New: RoleController
- apiResource CRUD for roles

### Update: User model
- `belongsTo(Role::class)` relationship
- Include role data in auth response

### Update: AuthController
- Return role data with user on login

### New Routes:
```php
Route::apiResource('roles', RoleController::class);
```

## UX for PNS (non-tech-savvy users)
- Minimum 16px font size on all inputs and labels
- Large touch targets (min 44px height for buttons/links)
- High contrast text (#334155 on white, not gray-on-gray)
- Icons on all action buttons (trash icon on delete, pencil on edit)
- Confirmation modal before any delete action
- Toast notifications after every CRUD action
- Loading skeleton/spinner during API calls
- Form validation errors shown inline, clearly
- No icon-only buttons — always text + icon

## Frontend File Structure
```
frontend/
├── layouts/
│   └── admin.vue          # Admin layout (sidebar + top bar)
├── pages/
│   ├── auth/
│   │   └── login.vue       # Split-screen login
│   ├── admin/
│   │   ├── index.vue        # Dashboard cards
│   │   ├── opd.vue
│   │   ├── kategori-aset.vue
│   │   ├── gis-layer.vue
│   │   ├── jenis-pemanfaatan.vue
│   │   ├── pihak-ketiga.vue
│   │   ├── aset.vue
│   │   ├── pemanfaatan.vue
│   │   ├── users.vue
│   │   └── roles.vue
│   └── import-aset.vue     # Existing
├── components/
│   ├── admin/
│   │   ├── DataTable.vue     # Reusable table with search/pagination
│   │   ├── FormModal.vue     # Reusable create/edit modal
│   │   ├── DeleteConfirm.vue # Delete confirmation dialog
│   │   └── Sidebar.vue       # Sidebar navigation
│   ├── ui/
│   │   ├── Input.vue
│   │   ├── Button.vue
│   │   ├── Select.vue
│   │   ├── Toast.vue
│   │   └── Card.vue
│   └── auth/
│       └── LoginForm.vue
├── composables/
│   ├── useAuth.ts           # Login/logout/token management
│   ├── useApi.ts            # Fetch wrapper
│   └── useToast.ts          # Toast notification state
├── app.vue
└── nuxt.config.ts
```

## Data Flow
- Login → POST /api/login → simpan token di localStorage → redirect ke /admin
- Setiap request API: header Authorization Bearer <token>
- Logout → POST /api/logout → hapus token → redirect ke /auth/login
- Auth guard: middleware/route check di frontend

## Implementation Order
1. Backend: roles migration, model, controller, routes
2. Backend: add role_id to users, update User model, update AuthController
3. Frontend: layout admin (sidebar + top bar)
4. Frontend: auth page (login)
5. Frontend: reusable components (DataTable, FormModal, etc.)
6. Frontend: Dashboard page
7. Frontend: CRUD modules (one by one — OPD, Kategori, GIS, Jenis, Pihak, Aset, Pemanfaatan, Users, Roles)
