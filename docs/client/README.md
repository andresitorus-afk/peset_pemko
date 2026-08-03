# PESET — Paket Dokumen Presentasi Client

**PESET** (Pemanfaatan Aset Daerah) — Portal Pemerintah Kota Medan untuk pengelolaan dan pemanfaatan aset daerah.

> Disusun untuk presentasi & serah terima ke client (Pemko Medan / BPKAD).

## Isi Paket

| Dokumen | Isi | Untuk Siapa |
|---|---|---|
| [01-ringkasan-eksekutif.md](./01-ringkasan-eksekutif.md) | Ringkasan 1 halaman: masalah, solusi, keunggulan, angka kunci | Pimpinan/pejabat yang waktunya singkat |
| [02-prd.md](./02-prd.md) | Kebutuhan produk: persona, fitur per modul, prioritas, alur pengguna, kriteria sukses | Tim pengadaan / reviewer kebutuhan |
| [03-srs.md](./03-srs.md) | Spesifikasi teknis: modul, role, data, aturan bisnis, skema pemanfaatan | Tim teknis client / kontrak cakupan |
| [04-data-flow.md](./04-data-flow.md) | Diagram arsitektur & alur data (Mermaid) | Tim teknis & manajemen |
| [05-mvp-roadmap.md](./05-mvp-roadmap.md) | Fitur selesai (MVP) vs roadmap ekspansi | Manajemen / pengambil keputusan |
| [presentasi.html](./presentasi.html) | Slide interaktif untuk dipresentasikan langsung | Semua audiens |

## Cara Menggunakan

1. **Presentasi langsung** → buka `presentasi.html` di browser, navigasi dengan panah keyboard. (Bisa juga dibuka di fullscreen.)
2. **Dokumen pendukung** → file `.md` dapat dibaca langsung di GitHub/VSCode, atau dikonversi ke PDF/Word.
3. **Diagram** → diagram Mermaid di `04-data-flow.md` otomatis dirender di GitHub dan VS Code (ekstensi Markdown Preview Mermaid Support).

## Teknologi di Balik PESET

- **Frontend:** Nuxt 3 (Vue) — responsif, tampil di komputer & HP
- **Backend:** Laravel 13 (REST API) — aman, terstruktur
- **Database:** PostgreSQL 15
- **AI:** Google Gemini untuk rekomendasi pemanfaatan aset
- **Real-time:** Live Chat (WebSocket)
- **Deployment:** Docker

---
*PESET — Pemanfaatan Aset Daerah Kota Medan*
