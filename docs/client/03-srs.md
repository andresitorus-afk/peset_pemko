# SRS — PESET (Pemanfaatan Aset Daerah Kota Medan)

> **Software Requirements Specification**
> Versi: 1.0 · Tanggal: 2026-08-03 · Status: Final (MVP selesai)

---

## 1. Pendahuluan

### 1.1 Tujuan
Menjadi acuan teknis sistem PESET: modul, role, aturan bisnis, data, dan antarmuka. Dibaca oleh tim teknis client, reviewer pengadaan, dan tim pengembang.

### 1.2 Konvensi
- **P0** = wajib (sudah terimplementasi) · **P1** = penting · **P2** = nice-to-have.
- Istilah: **aset** = Barang Milik Daerah (tanah/gedung); **POI** = titik fasilitas umum.

---

## 2. Arsitektur Sistem

| Lapisan | Teknologi | Keterangan |
|---|---|---|
| Frontend | Nuxt 3 (Vue 3, SSR) | Portal publik + panel admin, Tailwind CSS |
| Backend | Laravel 13 (API-only) | REST API, Sanctum token auth |
| Database | PostgreSQL 15 | Semua data persisten |
| AI | Google Gemini (flash) | Generate rekomendasi pemanfaatan |
| Real-time | Laravel Reverb (WebSocket) | Live chat + notifikasi |
| Deployment | Docker Compose | 4 service: frontend, backend, postgres, reverb |

Diagram lengkap: [04-data-flow.md](./04-data-flow.md).

---

## 3. Manajemen Pengguna & Autentikasi

### 3.1 Peran (Role)

| Role | Hak Akses |
|---|---|
| **Super Admin** | Semua modul termasuk Users, Roles, mapping domain email |
| **Admin** | Semua modul master data & operasional (kecuali Users/Roles) |
| **Petugas** | Data aset, pemanfaatan, chat, rekomendasi AI |

### 3.2 Regulasi Login

- Semua email **`@pemkomedan.go.id`** dapat mendaftar sendiri.
- Role baru otomatis ditentukan dari **domain email** (tabel `role_email_domains`).
- Default role bila domain tak dikenal: **Petugas**.
- Sesi bertahan lewat **token + cookie** (tutup-tab tetap login selama masa cookie).

### 3.3 Endpoint Auth

| Method | Endpoint | Fungsi |
|---|---|---|
| POST | `/api/register` | Daftar (email @pemkomedan.go.id) |
| POST | `/api/login` | Masuk, kembali token |
| POST | `/api/logout` | Keluar, cabut token |
| GET | `/api/user` | Ambil data pengguna aktif |

---

## 4. Modul & Fungsionalitas

### 4.1 Dashboard
- Statistik: total aset, aset per kategori, per status, total nilai perolehan/buku, pemanfaatan aktif, segera berakhir, per jenis, pihak ketiga terbanyak.

### 4.2 Aset
- Field: kode_barang (unik), register, nama_barang, OPD, kategori (KIB), tahun_perolehan, nilai_perolehan, nilai_buku, luas (m²), kondisi (`Baik | Rusak_Ringan | Rusak_Berat`), status (`Aktif | Idle | Dimanfaatkan`), alamat, keterangan.
- Dukungan: foto (banyak), riwayat otomatis, koordinat GIS, **import Excel massal** + unduh template.
- Filter list: kata kunci, OPD, kategori, kondisi, status.

### 4.3 OPD (Organisasi Perangkat Daerah)
- Field: kode_opd (unik), nama_opd, alamat, telepon, kepala_opd, NIP kepala.

### 4.4 Kategori Aset (KIB)
- Klasifikasi KIB A–F, hirarki 3 level (induk → sub → leaf).
- Hanya KIB A (Tanah) dan KIB C (Gedung & Bangunan) yang relevan untuk pemanfaatan.

### 4.5 Jenis Pemanfaatan (Skema Legal)
| Kode | Nama | Catatan |
|---|---|---|
| SEWA | Sewa | Umumnya maks. 5 tahun |
| PKP | Pinjam Pakai | |
| KSP | Kerja Sama Pemanfaatan | Dapat lebih lama |
| BGS | Bangun Guna Serah | |
| BSG | Bangun Serah Guna | |
| KSPI | KSP untuk Infrastruktur | |

- Setiap jenis membawa `dasar_hukum` dan `ketentuan`.

### 4.6 Pihak Ketiga
- Field: nama, jenis (`Perorangan | Badan_Hukum | Pemda`), NPWP, alamat, telepon, email, penanggung_jawab.

### 4.7 Pemanfaatan
- Field: aset, jenis pemanfaatan, pihak ketiga, nomor_perjanjian, tanggal mulai/selesai, nilai kontrak, kontribusi tahunan, peruntukan, status (`Aktif | Berakhir | Dibatalkan`), catatan, dokumen.
- **Aturan bisnis:** buat pemanfaatan aktif → status aset menjadi `Dimanfaatkan`; batalkan/selesai → kembali ke semula. Otomatis terekam di riwayat.

### 4.8 GIS & Peta
- Aset dipetakan sebagai GeoJSON (Point/Polygon).
- Layer peta (warna & ikon) dikelola admin; publik melihatnya di portal.
- Query mendukung bounding box (`bbox`) & filter status.

### 4.9 POI (Titik Fasilitas) — untuk AI
- Tipe: `kampus, sekolah, mal, pasar, rumah_sakit, puskesmas, kantor, perumahan, stasiun, tempat_budaya, lainnya`.
- Data contoh Kota Medan: 42 POI (5 kampus, 8 mall, 7 sekolah, 8 RS, 2 puskesmas, 7 tempat budaya, dll).
- Dikelola admin; dipakai menghitung jarak aset ke fasilitas.

### 4.10 Rekomendasi AI (Gemini)
- **Alur:** klik tombol → backend hitung POI terdekat (≤3 km, haversine) → susun prompt (data aset + POI + peta kebutuhan + jenis legal) → panggil Gemini → simpan hasil → tampilkan.
- **Output:** `jenis_pemanfaatan`, `ide_utama`, `alasan[]`, `alternatif[]`, `perkiraan_permintaan`, `potensi_kontribusi`, `catatan_legal`.
- Publik membaca hasil **terakhir yang sukses** dari DB (tanpa panggil AI → nol biaya per kunjungan).
- Error Gemini → status `gagal` + pesan; tidak menghentikan sistem.

### 4.11 Live Chat + Chatbot
- **Publik:** widget melayang → buat sesi (anonim) → kirim pesan → bot menjawab.
- **Pencocok bot:** tokenisasi + skor kata kunci + **toleransi typo** (levenshtein: kata pendek 1 huruf, panjang 2 huruf). Skor multi-kata spesifik lebih diutamakan.
- **FAQ:** 18 topik, 265 kata kunci, dikelola admin (CRUD).
- **Tidak cocok:** balasan standar + `needs_attention=true` → notifikasi petugas (badge unread + toast + notifikasi browser).
- **Staff:** daftar sesi, balas, tandai dibaca, tutup sesi, CRUD FAQ, unread count.
- **Pengaman:** rate-limit pesan (10/menit/sesi), pesan maks 1.000 karakter.

---

## 5. Model Data (Ringkas)

| Tabel | PK | Kunci Utama |
|---|---|---|
| `users` | bigint | email unik, role_id |
| `roles` | bigint | name unik |
| `role_email_domains` | bigint | domain unik → role |
| `opd` | uuid | kode_opd unik |
| `kategori_aset` | uuid | kode_kib, parent_id (hirarki) |
| `aset` | uuid | kode_barang unik, FK opd/kategori |
| `gis_aset` | uuid | FK aset unik, latitude/longitude, polygon |
| `gis_layer` | uuid | nama_layer, warna, ikon |
| `jenis_pemanfaatan` | uuid | kode unik, dasar_hukum |
| `pihak_ketiga` | uuid | nama, npwp |
| `pemanfaatan` | uuid | FK aset/jenis/pihak_ketiga, periode |
| `dokumen_pemanfaatan` | uuid | FK pemanfaatan, file |
| `foto_aset` | uuid | FK aset, file |
| `riwayat_aset` | uuid | FK aset, aksi, user |
| `poi` | uuid | nama, tipe, lat/lon |
| `rekomendasi_ai` | uuid | FK aset, hasil JSON, status |
| `chat_sessions` | uuid | token unik, status, needs_attention |
| `chat_messages` | uuid | FK session, sender_type |
| `chatbot_faqs` | uuid | keywords JSON, answer, aktif |

---

## 6. API Publik

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/public/aset` | Daftar aset publik (cari/filter/paginate) |
| GET | `/api/public/aset/{id}` | Detail aset |
| GET | `/api/public/aset/{id}/rekomendasi` | Rekomendasi terakhir |
| POST | `/api/public/chat/sessions` | Buat sesi chat |
| GET | `/api/public/chat/{session}/messages` | Riwayat chat |
| POST | `/api/public/chat/{session}/messages` | Kirim pesan (bot merespons) |

*(Semua endpoint lain memerlukan login: aset CRUD, pemanfaatan, POI, rekomendasi AI, chat staff, dst.)*

---

## 7. Keamanan

- Autentikasi token **Sanctum** untuk semua endpoint admin.
- Middleware **Super Admin** melindungi Users/Roles/mapping domain.
- `GEMINI_API_KEY` hanya di `.env` server — **tidak pernah** dikirim ke browser.
- Rate-limit endpoint publik (chat: 10/menit/sesi; pembuatan sesi: 20/menit).
- Validasi input di sisi server untuk semua field (422 untuk data tidak valid).

---

## 8. Keandalan & Performa

- **Fallback chat:** bila WebSocket putus, pesan tetap tampil via polling riwayat & unread-count.
- **Biaya AI terkendali:** AI hanya dipanggil saat admin menekan tombol; publik hanya baca hasil tersimpan.
- **Sinkronisasi real-time:** broadcast async (tanpa antrian worker tambahan).

---

## 9. Persyaratan Pengujian

- Unit test: pencocok chatbot (normalisasi, skor, typo, nonaktif), parsing & prompt Gemini, jarak POI (haversine).
- Uji end-to-end manual: alur publik (katalog → detail → chat), alur admin (CRUD → import → rekomendasi), alur chat (bot → handover → balas).

---
*Selengkapnya: [PRD](./02-prd.md) · [Data Flow](./04-data-flow.md)*
