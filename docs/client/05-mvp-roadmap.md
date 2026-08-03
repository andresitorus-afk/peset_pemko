# MVP vs Roadmap — PESET

> Status pengembangan: **MVP selesai dan berjalan.** Dokumen ini memetakan apa yang sudah jadi dan apa yang bisa dikembangkan selanjutnya.

---

## 1. Ringkasan Status

Sistem PESET **telah selesai** untuk kebutuhan inti: portal publik, panel admin, autentikasi berlapis, rekomendasi AI, dan live chat. Semua modul di bawah sudah berfungsi dan dapat langsung digunakan.

---

## 2. Fitur yang SUDAH SELESAI (MVP)

### Inti Pengelolaan Aset
- [x] Portal publik: katalog aset, pencarian, filter, pagination
- [x] Peta interaktif (GIS) aset daerah
- [x] Detail aset: foto, spesifikasi, status, riwayat pemanfaatan
- [x] Panel admin **9 modul** (OPD, Kategori Aset KIB, Aset, Jenis Pemanfaatan, Pihak Ketiga, Pemanfaatan, GIS Layer, Users, Roles)
- [x] Import aset massal dari **Excel** + template download
- [x] Dashboard statistik
- [x] Upload foto & dokumen

### Autentikasi & Keamanan
- [x] Login/daftar dengan role otomatis dari **domain email** (`@pemkomedan.go.id`)
- [x] 3 peran: Super Admin, Admin, Petugas
- [x] Kontrol akses per modul (mis. Users/Roles khusus Super Admin)
- [x] Sesi tahan lama (token + cookie)

### Fitur Unggulan
- [x] **Rekomendasi AI** pemanfaatan aset (Gemini + 42 titik fasilitas terdekat)
- [x] **Live Chat real-time** pengunjung ↔ petugas (WebSocket)
- [x] **Chatbot 24 jam** — 18 topik FAQ, 265 kata kunci, **paham salah ketik**
- [x] Notifikasi petugas real-time (badge unread + toast + notifikasi browser)

### Fase 2 (Selesai)
- [x] **Ekspor penuh** — aset, pemanfaatan, & master (OPD, jenis, pihak ketiga) ke Excel
- [x] **Laporan statistik & grafik** — tren pemanfaatan, kontribusi per tahun, per jenis, per OPD, daftar kontrak berakhir 90 hari
- [x] **Audit trail terperinci** — setiap perubahan aset/pemanfaatan terekam field-level (nilai lama → baru, siapa, kapan)

### Saran Pengembangan Fase 2 (belum dikerjakan)
- [ ] **Notifikasi email/SMS** — pengingat kontrak hampir berakhir ke petugas/admin
- [ ] **Foto udara/koordinat presisi (survey)** — input koordinat dari lapangan untuk akurasi GIS

### Skema Pemanfaatan Legal
- [x] SEWA, PKP (Pinjam Pakai), KSP (Kerja Sama Pemanfaatan), BGS (Bangun Guna Serah), BSG (Bangun Serah Guna), KSPI

---

## 3. Roadmap Fase Berikutnya

Dibagi ke fase-fase logis berdasarkan nilai & kesiapan.

### Fase 2 — Penguatan Operasional (Rekomendasi)

| Fitur | Nilai | Catatan |
|---|---|---|
| **Import & ekspor penuh** | Data mudah dipindah/migrasi | ✅ Selesai — ekspor aset, pemanfaatan, master |
| **Laporan statistik & grafik** | Mendukung keputusan dinas | ✅ Selesai — tren, kontribusi, per OPD |
| **Riwayat lengkap & audit trail terperinci** | Akuntabilitas | ✅ Selesai — field-level (lama → baru) |
| **Foto udara/koordinat presisi (survey)** | Akurasi data GIS | ⏳ Saran — input koordinat dari lapangan |
| **Notifikasi email/SMS** | Jangkauan lebih luas | ⏳ Saran — pengingat kontrak hampir berakhir |

### Fase 3 — Integrasi Eksternal (Rekomendasi)

| Fitur | Nilai | Catatan |
|---|---|---|
| **Integrasi e-billing / pembayaran kas daerah** | Otomasi pendapatan | Sambung ke sistem pembayaran Pemko |
| **Lelang elektronik (e-lelang) terintegrasi** | Proses mitra lebih transparan | Sesuai Permendagri |
| **Integrasi SIMDA / aplikasi aset existing** | Satu sumber data | Hindari data ganda |
| **API publik untuk mitra/developer** | Ekosistem terbuka | Berbagi data katalog |

### Fase 4 — Kecerdasan & Pengalaman (Ekspansi)

| Fitur | Nilai | Catatan |
|---|---|---|
| **Rekomendasi AI lebih dalam** | Keputusan lebih baik | Multi-aset, simulasi skenario, dokumen draft kontrak |
| **Aplikasi mobile (Android/iOS)** | Akses lebih luas | Petugas & publik di lapangan |
| **Multi-bahasa** | Aksesibilitas | Indonesia/Inggris |
| **Peta kota interaktif penuh** | Visualisasi lebih baik | Layer tambahan, heatmap nilai aset |

---

## 4. Rekomendasi Langkah

1. **Sekarang:** adopsi & isi data nyata (aset, OPD, POI, pihak ketiga) bersama OPD/BPKAD. Fase 2 (laporan & audit) **sudah selesai**.
2. **Kuartal berikut:** notifikasi email/SMS & koordinat presisi GIS — biaya kecil, dampak langsung pada akuntabilitas & monitoring.
3. **Selanjutnya:** tentukan prioritas Fase 3/4 sesuai kebutuhan & anggaran.

---

## 5. Catatan Teknis (Ringkas)

- Sistem berjalan di **Docker** → mudah dipindah/di-deploy (on-premise maupun cloud).
- Data tersimpan di **PostgreSQL** → aman, terstruktur, bisa di-backup.
- AI hanya diaktifkan saat diminta → **biaya terkendali**.
- Chatbot & pencocokan berjalan **tanpa biaya AI** (rule-based + anti-typo).

---
*Kembali ke [README](./README.md)*
