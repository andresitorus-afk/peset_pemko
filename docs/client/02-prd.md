# PRD — PESET (Pemanfaatan Aset Daerah Kota Medan)

> **Product Requirements Document**
> Versi: 1.0 · Tanggal: 2026-08-03 · Status: Final (MVP selesai)

---

## 1. Ringkasan Produk

PESET adalah portal digital resmi Pemerintah Kota Medan yang mempertemukan **pengelola aset daerah** (OPD/BPKAD) dengan **pihak ketiga/masyarakat** yang ingin memanfaatkan aset. Produk menyediakan katalog aset yang transparan, panel administrasi terpadu, rekomendasi AI untuk pemanfaatan aset, serta layanan tanya-jawab 24 jam.

### Tujuan Produk

1. Meningkatkan **transparansi** informasi aset daerah kepada publik.
2. Mengaktifkan kembali aset **idle** menjadi aset **produktif** (sumber pendapatan daerah).
3. Mempercepat & mendasarkan keputusan pemanfaatan aset pada **data** (bukan subjektivitas).
4. Menyediakan **satu pintu** pengelolaan data aset lintas OPD.

### Metrik Keberhasilan

| Metrik | Target |
|---|---|
| Aset idle yang terlihat publik | 100% aset berstatus idle tampil di portal |
| Waktu menentukan usulan pemanfaatan | Turun berkat rekomendasi AI |
| Pertanyaan publik yang terjawab otomatis | Mayoritas via chatbot, sisanya diteruskan petugas |
| Ketersediaan layanan | 24/7 (chatbot) + 1 klik human handover |

---

## 2. Persona & Pengguna

| Persona | Peran di Sistem | Kebutuhan Utama |
|---|---|---|
| **Masyarakat / Calon Mitra** | Publik (tanpa login) | Melihat aset yang tersedia, lokasi, cara mengajukan, bertanya |
| **Petugas** (OPD) | Login, mengelola data harian | Input/update aset, kelola pemanfaatan, jawab chat |
| **Admin** | Login, mengelola master data | Kelola OPD, kategori, pihak ketiga, jenis pemanfaatan |
| **Super Admin** | Login, kontrol penuh | Kelola pengguna, role, mapping domain email |

---

## 3. Fitur per Modul

### A. Portal Publik (Pengunjung)

| Fitur | Deskripsi | Prioritas |
|---|---|---|
| Katalog aset | Daftar tanah/gedung daerah dengan foto, status, OPD, alamat | P0 |
| Pencarian & filter | Cari berdasarkan kata kunci, kategori (KIB), lokasi | P0 |
| Peta (GIS) | Lihat aset pada peta interaktif | P0 |
| Detail aset | Foto, spesifikasi, status, riwayat pemanfaatan, rekomendasi AI | P0 |
| Rekomendasi AI (baca) | Menampilkan hasil rekomendasi terakhir per aset | P0 |
| Live Chat | Tanya jawab real-time dengan bot/petugas | P0 |
| Kontak | Informasi BPKAD, link resmi | P0 |

### B. Panel Admin (Login)

| Modul | Deskripsi | Prioritas |
|---|---|---|
| Dashboard | Statistik: jumlah aset, status, nilai, pemanfaatan aktif, pihak ketiga | P0 |
| Aset | CRUD aset, foto, riwayat, **import Excel massal**, template download | P0 |
| OPD | Kelola Organisasi Perangkat Daerah | P0 |
| Kategori Aset | Klasifikasi KIB A–F, hirarki 3 level | P0 |
| Jenis Pemanfaatan | Skema legal: SEWA, PKP, KSP, BGS, BSG, KSPI + dasar hukum | P0 |
| Pihak Ketiga | Data mitra (badan hukum/perorangan/Pemda) | P0 |
| Pemanfaatan | Kontrak pemanfaatan, dokumen, auto-update status aset | P0 |
| GIS | Layer peta, koordinat aset | P1 |
| POI (Titik Fasilitas) | Data kampus, mall, sekolah, RS, tempat budaya (untuk AI) | P1 |
| Users & Roles | Kelola akun & peran (khusus Super Admin) | P0 |
| Live Chat (staff) | Balas chat publik, kelola FAQ, badge unread | P0 |
| Rekomendasi AI (generate) | Tombol generate rekomendasi per aset | P0 |

### C. Fitur Unggulan

1. **Rekomendasi AI** — klik satu tombol → sistem menghitung fasilitas terdekat (≤3 km) → AI memberi usulan: jenis pemanfaatan, ide utama, alasan, alternatif, estimasi kontribusi.
2. **Live Chat real-time + Chatbot** — bot menjawab 24 jam dari FAQ; jika tidak cocok, diteruskan ke petugas (badge + notifikasi real-time). Chatbot paham salah ketik (toleransi 1–2 huruf).

---

## 4. Alur Pengguna (User Journey)

### Alur Publik — Melihat & Bertanya

```
Pengunjung membuka portal
  → melihat katalog aset (pencarian/filter)
  → klik "Detail" pada aset
  → melihat foto, status, lokasi, rekomendasi AI
  → bertanya lewat Live Chat bila masih ragu
  → info cara pengajuan dijawab bot/petugas
```

### Alur Admin — Mengelola Aset

```
Petugas login
  → pilih modul Aset
  → tambah/edit aset (atau import Excel)
  → melengkapi foto & koordinat
  → (opsional) klik "Rekomendasi AI" untuk usulan pemanfaatan
  → bila aset dimanfaatkan → buat record Pemanfaatan
  → status aset otomatis berubah jadi "Dimanfaatkan"
```

### Alur Chat — Diteruskan ke Petugas

```
Pengunjung bertanya
  → chatbot cari jawaban (paham typo)
  → cocok? → jawab otomatis
  → tidak cocok? → balasan standar + tandai "perlu perhatian"
  → petugas dapat notifikasi real-time → balas
```

---

## 5. Kebutuhan Fungsional & Non-Fungsional

### Fungsional (inti)

- Semua data aset dapat dicari, difilter, dan di-paginate.
- Status aset (Aktif / Idle / Dimanfaatkan) otomatis sinkron dengan data pemanfaatan.
- Setiap perubahan penting terekam sebagai riwayat (audit trail).
- Hak akses dibatasi per peran (Super Admin / Admin / Petugas).

### Non-Fungsional

| Aspek | Persyaratan |
|---|---|
| Performa | Respon cepat, hasil publik dibaca dari database (tanpa biaya AI per kunjungan) |
| Keamanan | Token autentikasi (Sanctum), API terlindungi, key AI tidak pernah tampil di frontend |
| Keandalan | Layanan chat punya cadangan (fallback) bila koneksi WebSocket putus |
| Aksesibilitas | Font ≥16px, target tombol besar, kontras tinggi (ramah PNS) |
| Kompatibilitas | Browser umum; tampilan responsif (desktop & HP) |
| Pelindungan data | Rate-limit pada endpoint publik (anti-spam chat) |

---

## 6. Kriteria Penerimaan (Acceptance Criteria)

1. Pengunjung dapat mencari aset dan melihat detail + rekomendasi AI **tanpa login**.
2. Admin dapat import aset dari **template Excel** dan melihat hasilnya di daftar.
3. Saat record pemanfaatan aktif dibuat, **status aset berubah otomatis** menjadi "Dimanfaatkan".
4. Chatbot menjawab pertanyaan umum dan **meneruskan ke petugas** saat tidak tahu — petugas menerima notifikasi.
5. Hanya **Super Admin** yang dapat mengelola Users & Roles.
6. Rekomendasi AI menyertakan **fasilitas terdekat yang terukur** (bukan tebakan).

---

## 7. Di Luar Cakupan (untuk Fase Berikutnya)

- Integrasi pembayaran/e-billing otomatis
- Aplikasi mobile native
- Lelang/lelang elektronik terintegrasi
- Multi-bahasa
- Notifikasi email/SMS
- (Detail lengkap di [05-mvp-roadmap.md](./05-mvp-roadmap.md))

---
*Selengkapnya: [Ringkasan Eksekutif](./01-ringkasan-eksekutif.md) · [SRS](./03-srs.md)*
