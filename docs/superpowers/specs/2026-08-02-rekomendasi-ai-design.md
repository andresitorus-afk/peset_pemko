# Rekomendasi Pemanfaatan Aset dengan AI (Gemini) — Desain

Tanggal: 2026-08-02
Stack: Laravel 13 (backend, API-only, Sanctum) + Nuxt 3 (frontend) + PostgreSQL 15

## Masalah

Petugas/pejabat kesulitan menentukan aset daerah (tanah/gedung idle) baiknya dimanfaatkan jadi apa.
Keputusan saat ini subjektif dan tanpa dasar, padahal data aset (kategori, luas, kondisi, lokasi/GIS)
dan konteks sekitar (dekat kampus, pasar, RS) sudah tersedia di sistem.

## Keputusan (hasil brainstorming)

- Teknologi: **LLM API Google Gemini** (flash, murah) dipanggil backend via HTTP biasa. Bukan rule-based, agar bisa memberi ide usaha spesifik + alasan naratif.
- Konteks lokasi: **POI + jarak dari DB**. Backend menghitung jarak koordinat aset ke POI (kampus, pasar, RS, dll) dan memasukkan POI terdekat (≤3 km) ke prompt. Alasan menjadi terukur, bukan tebakan AI.
- Lokasi UI: **admin + halaman publik**. Admin memicu generate (tombol); publik hanya baca hasil terakhir dari DB (nol biaya per kunjungan).
- Output: **rekomendasi detail + simpan riwayat** (tabel `rekomendasi_ai` untuk jejak audit). Tidak membuat draft pemanfaatan.

## Alur Data

```
Tombol "Rekomendasi AI" (admin aset)
  → POST /rekomendasi-ai/{asetId}
     → Laravel: load aset + kategori + gis_aset
     → hitung jarak haversine ke semua POI, ambil ≤3 km
     → bangun prompt (atribut aset + POI terdekat + peta kebutuhan per tipe POI + daftar jenis pemanfaatan legal)
     → panggil Gemini API generateContent (key dari .env, timeout)
     → parse respons JSON terstruktur (fallback: simpan error bila gagal)
     → simpan ke rekomendasi_ai
     → return hasil ke frontend

Publik
  → GET /public/aset/{id}/rekomendasi (baca hasil sukses terakhir dari DB)
```

## Perubahan Backend

### 1. Migrasi & model: tabel `poi`
- Kolom: `id` (uuid PK), `nama` (string), `tipe` (string enum: kampus, sekolah, mal, pasar, rumah_sakit, puskesmas, kantor, perumahan, stasiun, lainnya), `alamat` (nullable), `latitude` (decimal 10,7), `longitude` (decimal 10,7), `aktif` (bool default true), timestamps.
- Model `Poi` (uuid, fillable di atas).
- Seeder `PoiSeeder`: POI Kota Medan (USU, UMSU, UNIMED, Polmed, Stikes, mal-mal, pasar, RS, puskesmas, stasiun + koordinat).

### 2. Migrasi & model: tabel `rekomendasi_ai`
- Kolom: `id` (uuid PK), `aset_id` (FK aset, cascade), `hasil` (jsonb nullable), `status` (string: sukses | gagal), `error` (text nullable), `created_by` (FK users nullable), `created_at`, `updated_at`.
- Model `RekomendasiAi` (uuid, fillable: aset_id, hasil, status, error, created_by; relasi `aset()`, `creator()`).
- Ambil rekomendasi sukses terakhir per aset: `RekomendasiAi::where('aset_id',$id)->where('status','sukses')->latest()->first()`.

### 3. Service `GeminiService`
- Satu class: `app/Services/GeminiService.php`.
- `generate(string $prompt): string` — cURL ke `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=GEMINI_API_KEY`, method `POST`, header JSON, body `{contents:[{parts:[{text:...}]}], generationConfig:{temperature:0.4}}`, timeout 45 detik.
- Key dibaca dari `config('services.gemini.key')` (env `GEMINI_API_KEY`), tanpa SDK tambahan.
- Error: lempar exception dengan pesan dari respons Gemini bila non-200.

### 4. Controller `RekomendasiAiController`
- `store(string $asetId)`:
  1. Load `Aset` with `kategori`, `gisAset`.
  2. Hitung POI terdekat ≤3 km via haversine (query builder, kalkulasi di SQL: `6371 * acos(...)`), ambil 5 terdekat.
  3. Bangun prompt (lihat Prompt).
  4. Panggil `GeminiService`, parse JSON dari respons (strip code fences bila ada).
  5. Simpan `rekomendasi_ai` (sukses: hasil = parsed; gagal: status `gagal`, error = message).
  6. Return `{ data: parsed }` atau 502 + pesan error bila gagal.
- `index(string $asetId)` — riwayat rekomendasi aset (desc).
- Route (grup `auth:sanctum`):
  - `POST /rekomendasi-ai/{aset}`
  - `GET /rekomendasi-ai/{aset}`
- `PoiController` — admin CRUD `Route::apiResource('poi', PoiController::class)` (grup auth).

### 5. Endpoint publik
- `PublicController::rekomendasi(string $id)` → `GET /public/aset/{id}/rekomendasi` — rekomendasi sukses terakhir (tanpa kolom internal), atau `null`.

### 6. Config & env
- `config/services.php`: blok `gemini` (`key` => env('GEMINI_API_KEY')).
- `.env` / `.env.example`: tambah `GEMINI_API_KEY=`.

## Prompt (disusun backend)

```
Kamu asisten rekomendasi pemanfaatan aset daerah. Berikan rekomendasi paling detail.

DATA ASET:
- Nama: ...
- Kategori: KIB A (Tanah) / KIB C (Gedung) — {nama_kategori}
- Luas: ... m²
- Kondisi: ... | Status: ...
- Alamat: ...
- Keterangan: ...

POI TERDEKAT (hasil pengukuran, bukan tebakan):
- {nama} ({tipe}) — {jarak} km
- ...

PETA KEBUTUHAN (acu untuk alasan):
- kampus: laundry, fotokopi, kos, coffee shop, warung makan, minimarket
- sekolah: kantin, jasa fotokopi, tempat les
- mal/pasar: kuliner, gudang, parkir
- rumah_sakit: apotek, kantin, kos karyawan, parkir
- puskesmas: apotek, kantin
- perumahan: minimarket, warung, laundry, tempat penitipan anak
- stasiun: parkir, kuliner, penginapan

JENIS PEMANFAATAN LEGAL (pilih kode):
SEWA, PKP (Pinjam Pakai), KSP (Kerja Sama Pemanfaatan), BGS (Bangun Guna Serah), BSG (Bangun Serah Guna), KSPI (KSP untuk Infrastruktur).

WAJIB: jawab HANYA JSON valid, tanpa markdown:
{
  "jenis_pemanfaatan": "kode",
  "ide_utama": "ide usaha/pemanfaatan paling tepat",
  "alasan": ["alasan berbasis data aset & POI", "..."],
  "alternatif": [{"ide": "...", "alasan": "..."}, ...],
  "perkiraan_permintaan": "rendah/sedang/tinggi + penjelasan",
  "potensi_kontribusi": "estimasi dalam Rp, beri rentang",
  "catatan_legal": "persyaratan/dasar hukum"
}
```

Alasan wajib merujuk data yang dikirim (POI/jarak/luas/kondisi), dilarang mengarang.

## Perubahan Frontend (Nuxt)

### 1. Admin `pages/admin/aset.vue`
- Tombol "Rekomendasi AI" per baris aset → panggil `POST /rekomendasi-ai/{id}`, tampilkan loading.
- Modal hasil (reuse `UiModal`): render `ide_utama` besar, daftar `alasan` berpoin, kartu `alternatif`, `perkiraan_permintaan`, `potensi_kontribusi`, `catatan_legal`.
- Tombol "Riwayat" → `GET /rekomendasi-ai/{id}` daftar hasil sebelumnya.
- Kalau aset belum punya koordinat & POI kosong: tampilkan toast info "tidak ada POI terdekat, rekomendasi tetap jalan".

### 2. Publik `pages/index.vue` (detail aset)
- Panel "Rekomendasi Pemanfaatan" menampilkan hasil terakhir dari `GET /public/aset/{id}/rekomendasi` (render sama seperti admin).
- Tanpa panggilan Gemini; hasil dibaca dari DB.

## Error Handling

- Gemini timeout/gagal → simpan `status=gagal` + pesan, frontend toast error.
- Aset tidak ada → 404.
- Aset tanpa koordinat → prompt tanpa blok POI (hanya alamat teks).
- JSON Gemini tidak parseable → coba strip code fence ````json`/` ``` `; masih gagal → simpan error, return 502.

## Biaya & Kontrol

- Hanya dipanggil saat tombol diklik (tidak auto-load di daftar/publik).
- Hasil disimpan di DB → halaman publik & riwayat gratis.
- Model Gemini flash, temperature 0.4.

## Keamanan

- `GEMINI_API_KEY` hanya di `.env`, tidak pernah dikirim ke frontend.
- Endpoint generate & POI memakai `auth:sanctum`.
- Input terbatas: `{asetId}` divalidasi uuid; cuma data aset yang dipunyai sistem yang dikirim ke Gemini, tanpa data pengguna lain.

## Testing (PHPUnit)

- `PoiDistanceTest`: haversine SQL mengembalikan jarak benar (bandingkan 2 koordinat Medan yang diketahui).
- `GeminiPromptTest`: prompt berisi POI terdekat hanya yang ≤3 km; prompt tanpa koordinat menghilangkan blok POI.
- `GeminiParseTest`: parsing respons ber-code-fence dan plain JSON menghasilkan array yang sama.
- Jalankan: `php artisan test`.

## Di luar cakupan (YAGNI)

- Draft pemanfaatan otomatis dari rekomendasi.
- Batch rekomendasi banyak aset sekaligus.
- Evaluasi/feedback rating terhadap rekomendasi.
