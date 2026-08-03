# Ringkasan Eksekutif — PESET

> PESET (Pemanfaatan Aset Daerah) · Pemerintah Kota Medan
> Dokumen ini menjawab: *apa yang dibangun, untuk siapa, dan apa hasilnya.*

---

## 1. Masalah

Pemerintah Kota Medan memiliki banyak aset daerah (tanah & gedung) yang **menganggur (idle)** — tidak menghasilkan pendapatan. Kendala yang ada:

- Data aset **tersebar dan sulit diakses** publik.
- Masyarakat/pengusaha **tidak tahu aset apa yang bisa dimanfaatkan** dan bagaimana mengajukannya.
- Petugas kesulitan menentukan **aset paling tepat untuk dimanfaatkan jadi apa** — keputusan subjektif, tanpa dasar data.
- Belum ada **kanal tanya-jawab langsung** antara masyarakat dan petugas.

## 2. Solusi

**PESET** — satu portal digital resmi Pemko Medan yang mempertemukan sisi pemerintah (pengelola aset) dan sisi publik (calon mitra):

1. **Katalog aset publik** — landing page baru: ringkasan statistik aset real-time (jumlah, nilai, aset tersedia, jumlah OPD), aset unggulan, dan daftar tanah/gedung daerah dengan lokasi, status, foto, dan peta (GIS), bisa dicari & difilter. Transparan dan mudah diakses siapa saja.
2. **Panel admin lengkap** — 9 modul pengelolaan (OPD, kategori aset, aset, pemanfaatan, pihak ketiga, dll.) dengan kontrol hak akses per peran (Super Admin / Admin / Petugas).
3. **Rekomendasi AI otomatis** — setiap aset disimpan/diubah oleh petugas, sistem langsung membuat rekomendasi di latar belakang: aset ini cocok dijadikan apa (sewa, KSP, BGS, dst.) berdasarkan data aset + fasilitas di sekitarnya (kampus, mall, sekolah, rumah sakit, tempat budaya), lengkap dengan alasan yang terukur. Hasilnya langsung tampil di detail aset portal publik — tanpa tombol, tanpa langkah tambahan.
4. **Live Chat + Chatbot 24 jam** — pengunjung bisa bertanya kapan saja; bot menjawab otomatis dari 18 topik FAQ, dan jika tidak terjawab diteruskan ke petugas secara real-time.

## 3. Hasil yang Sudah Bekerja (MVP Selesai)

| Area | Status |
|---|---|
| Portal publik (landing v2 + katalog aset + peta GIS + pencarian + filter + statistik) | ✅ Selesai |
| Panel admin 9 modul + dashboard statistik | ✅ Selesai |
| Login berlapis sesuai peran (email `@pemkomedan.go.id`) | ✅ Selesai |
| Import aset dari Excel | ✅ Selesai |
| Rekomendasi AI pemanfaatan aset (Gemini + 42 POI, otomatis saat simpan/ubah aset) | ✅ Selesai |
| Live Chat real-time + Chatbot FAQ (18 topik, 265 kata kunci, tahan typo) | ✅ Selesai |
| Skema pemanfaatan legal (SEWA, PKP, KSP, BGS, BSG, KSPI) | ✅ Selesai |

## 4. Angka Kunci

- **9 modul** pengelolaan aset dalam satu sistem terpadu
- **42 titik** fasilitas terdekat (POI) untuk dasar rekomendasi AI
- **18 topik** FAQ chatbot + **265 kata kunci** yang paham salah ketik
- **5+ skema** pemanfaatan legal yang didukung
- **24 jam** layanan chatbot

## 5. Keunggulan

1. **Basis data (bukan tebakan)** — rekomendasi AI didukung pengukuran jarak nyata ke fasilitas sekitar.
2. **Transparan untuk publik** — aset dan statusnya bisa dilihat siapa saja.
3. **Efisien untuk petugas** — import massal, dashboard, dan rekomendasi otomatis menghemat waktu.
4. **Responsif** — berfungsi di komputer maupun ponsel.
5. **Siap bertumbuh** — arsitektur modular, roadmap ekspansi sudah disiapkan.

## 6. Kesimpulan

PESET mengubah aset menganggur menjadi **peluang pendapatan daerah** dengan cara yang transparan, terbantu AI, dan ramah pengguna. Sistem **sudah selesai dan berjalan**; modul-modul inti dapat langsung dipakai, dan tersedia jalur pengembangan lebih lanjut sesuai kebutuhan kota.

---
*Selengkapnya: [PRD](./02-prd.md) · [SRS](./03-srs.md) · [Data Flow](./04-data-flow.md) · [MVP & Roadmap](./05-mvp-roadmap.md)*
