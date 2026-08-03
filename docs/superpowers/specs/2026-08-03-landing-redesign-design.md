# Landing Page PESET v2 — Design Doc

Tanggal: 2026-08-03
Status: Approved (user)
Fokus: `frontend/pages/index.vue` — konsep sama, tampilan premium/modern/elegant, carousel dipertahankan.

## Keputusan user
- Arah visual: **Elevasi teal pemerintah** (pertahankan identitas teal/slate, naik kelas)
- Section baru: **semua** — statistik real-time, cara pemanfaatan, aset unggulan, alur+FAQ, tim/OPD
- Interaksi: **Premium elegan** (fade/slide-in, count-up, hover halus; bukan parallax berat)

## Design system (ui-ux-pro-max: "Trust teal + professional blue")
- Primary `#0F766E` (teal-700) · Secondary `#14B8A6` (teal-500) · Accent `#0369A1`
- BG `#F0FDFA` (teal-50) · Foreground `#134E4A` · Card putih · Border `#99F6E4`
- Font: `Plus Jakarta Sans` (sudah ada di nuxt.config) — dipakai semua, header 700/800

## Struktur halaman (top→bottom)
1. **Navbar** sticky — glassmorphism blur, logo kiri, link smooth-scroll, CTA "Ajukan Pemanfaatan"
2. **Hero** — carousel 2 foto (dipertahankan) + overlay gradient teal, judul besar, search bar, dots
3. **Statistik real-time** (BARU) — 4 kartu: total aset, nilai total, aset tersedia, OPD. Data dari endpoint baru `/api/public/statistik`, animasi count-up
4. **Filter kategori** — sticky pill dipoles
5. **Grid aset** — kartu di-elevasi (shadow, hover lift/zoom, KIB accent, status badge)
6. **Cara Pemanfaatan** (BARU) — 4 langkah (Pilih Aset → Ajukan → Verifikasi → Tandatangani), ikon lingkaran teal, connect line
7. **Aset Unggulan** (BARU) — 3 kartu aset terpilih (KIB A/C, status Idle/Aktif), tombol panah
8. **Alur Permohonan + FAQ** (BARU) — accordion halus
9. **Tentang + Tim/OPD** (BARU) — paragraf + kartu OPD pengelola
10. **Footer** — dipoles (brand, kontak, link, admin panel)
11. **Modal detail** — semua fitur lama (foto slider, info, GIS map, rekomendasi AI, WA/email) tetap, styling harmoni

## Interaktivitas (premium elegan)
- Scroll reveal: IntersectionObserver + CSS transition (fade + translateY), `prefers-reduced-motion` dihormati
- Count-up statistik via rAF saat intersect
- Carousel autoplay 5s + dots
- Hover micro-interactions 200-300ms
- CSS untuk `scrollbar-hide` ditambahkan (dipakai template tapi belum ada definisi)

## Backend
- Endpoint baru `GET /public/statistik` di `PublicController::statistik()` (tanpa auth)
  - `total` (KIB A+C), `nilai` (sum nilai_perolehan), `tersedia` (status Idle), `opd` (distinct opd_id)
- Route ditambahkan di `routes/api.php`

## Non-goals
- Tidak ubah backend aset/filter/modal logic (hanya tambah endpoint statistik)
- Tidak pakai chart library — statistik cukup angka count-up
- Tidak parallax/tilt (berat di publik)

## Rollback
- Backup design lama: `docs/design-backup/index.vue.original`
