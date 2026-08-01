# Role Manajemen & Autentikasi Berlapis — Desain

Tanggal: 2026-08-02
Stack: Laravel (backend, Sanctum token) + Nuxt 3 (frontend)

## Masalah

1. User belum bisa mendaftar (register) di laman login admin.
2. Role user tidak otomatis ditentukan dari email.
3. Semua user yang login bisa mengakses & mengelola halaman Roles/Users (CRUD), padahal hanya Super Admin yang berhak.
4. Akses `/admin` tidak selalu lewat login; user bisa langsung masuk.
5. Tidak ada mekanisme "tinggalkan sebentar, balik tanpa login ulang" (cookie persistence).

## Keputusan (hasil brainstorming)

- Role ditentukan dari **domain email via mapping di DB** (tabel `role_email_domains`), dikelola Super Admin.
- **Semua email `@pemkomedan.go.id` boleh self-register**.
- Persistence: **cookie + token** (token disimpan juga di cookie, di samping localStorage).

## Perubahan Backend

### 1. Migrasi: tabel `role_email_domains`
- `id`, `domain` (string, unik), `role_id` (FK roles, nullable), `created_at`, `updated_at`.
- Model baru `RoleEmailDomain` (`fillable`: domain, role_id; relasi `role()`).
- Seeder: mapping contoh `pemkomedan.go.id → Petugas`.

### 2. `AuthController::register`
- Validasi: nama, email unik **+ regex email harus diakhiri `@pemkomedan.go.id`**, password min 8 + confirmed.
- Setelah `User::create`, cari `RoleEmailDomain` yang `domain`-nya cocok dengan bagian domain email.
  - Cocok → pakai `role_id` mapping.
  - Tidak cocok → `role_id` role "Petugas" (default).
- Load `role`, buat token, kembalikan `{user, token}` seperti `login`.

### 3. Middleware baru `EnsureSuperAdmin`
- `app/Http/Middleware/EnsureSuperAdmin.php`:
  - `if ($request->user()?->role?->name !== 'Super Admin') return 403 JSON`. Bonus: pindah ke `role->name === 'Super Admin'`.
- Daftar alias di `bootstrap/app.php` (mis. `super_admin`).
- Di `api.php`, route `roles` + `users` dibungkus `->middleware('super_admin')`.
  - `Route::apiResource('roles', ...)->middleware('super_admin')`
  - `Route::apiResource('users', ...)->middleware('super_admin')`

### 4. RoleEmailDomainController (CRUD)
- `apiResource('role-email-domains')` di dalam grup `auth:sanctum` + `super_admin` (hanya Super Admin yang mengelola).
- `index` dengan `with('role')`, `store/update` validasi domain unik + role_id exists, `destroy` bebas (tidak berpengaruh ke data user).

## Perubahan Frontend

### 1. Laman login `pages/auth/[hash].vue`
- Tambah tab "Masuk" / "Daftar".
- Form daftar: nama, email, password, konfirmasi password.
- Submit → `POST /register`, simpan token (via `useAuth`), redirect `/admin`.

### 2. `composables/useAuth.ts`
- Simpan token juga di cookie (document.cookie, mis. 7 hari) selain localStorage.
- Saat init: baca localStorage dulu, fallback ke cookie.
- `logout`: hapus dari localStorage **dan** cookie.
- Ekspor helper token baru untuk dipakai middleware.

### 3. Guard route & menu
- `Sidebar.vue`: filter `menuItems` — item `Users` & `Roles` hanya tampil jika `user?.role?.name === 'Super Admin'` (computed).
- `pages/admin/users.vue` & `pages/admin/roles.vue`: guard di `definePageMeta`/onMounted — jika bukan Super Admin redirect ke `/admin`.

### 4. `plugins/auth-global.ts`
- Saat route `/admin` dan ada token: panggil `fetchUser()` untuk validasi; jika gagal (token invalid) → hapus token + redirect ke login.
- Route `/auth/*` dengan token valid → redirect `/admin` (sudah ada).
- Route `/admin` tanpa token → redirect login (sudah ada, pertahankan).

## Data Flow

1. User buka `/admin` tanpa sesi → middleware redirect ke `/auth/{hash}` (login).
2. User daftar (email `@pemkomedan.go.id`) → role diambil dari `role_email_domains`, langsung login.
3. User masuk → token disimpan localStorage + cookie → buka/tutup laman sebentar tetap login.
4. Super Admin buka `/admin/users` → CRUD penuh.
5. Non-Super Admin buka `/admin/users` → 403 (API) + redirect `/admin` (frontend); menu hilang.

## Error Handling

- API sudah mengembalikan error validasi Laravel (`errors`). `useApi` melempar `Error(message)`.
- 403 dari middleware → frontend redirect.
- Register gagal (email sudah dipakai / domain salah) → tampilkan pesan dari API.

## Testing

- Manual smoke test via `php artisan serve` + `npm run dev`:
  1. Register email `@pemkomedan.go.id` baru → login → role sesuai mapping.
  2. Login sebagai Petugas → `/admin/roles` redirect, menu tidak ada.
  3. Login sebagai Super Admin → CRUD users/roles berjalan.
  4. Tutup tab, buka lagi dalam waktu cookie → masih login.
- Backend: unit test kecil untuk `register` menetapkan role berdasarkan domain mapping.

## Tidak Termasuk (YAGNI)

- Reset password / email verifikasi.
- Multi-tenant OPD per user.
- OAuth / 2FA.
- Penggantian penuh ke Sanctum session (tetap bearer token + cookie helper).
