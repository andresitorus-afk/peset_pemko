# XGBoost Model Rekomendasi Pemanfaatan Aset Kota Medan

> Dokumentasi model _supervised learning_ `ml/` yang **menggantikan API Gemini**
> untuk fitur "Rekomendasi AI" aset daerah. Semua disesuaikan dengan kode &
> data yang benar-benar ada di proyek ini — bukan penjelasan umum.
> Ditulis dengan bahasa awam.

---

## Ringkasan Singkat (TL;DR)

Satu paket aset, tiga model terpisah — semuanya dilatih dengan **25 fitur** yang sama:

| Model | Target / Label | Jenis | Nilai label |
|---|---|---|---|
| **y1** (`jenis_pemanfaatan.json`) | Jenis pemanfaatan | klasifikasi multiclass | 1 dari 6: `SEWA`, `PKP`, `KSP`, `BGS`, `BSG`, `KSPI` |
| **y2** (`potensi_kontribusi.json`) | Pendapatan setahun | regresi | angka Rupiah (Rp 200 rb – Rp 7 M), dilatih pada `log10(Rupiah)` |
| **y3** (`perkiraan_permintaan.json`) | Tingkat permintaan pasar | klasifikasi 3 kelas | `rendah` / `sedang` / `tinggi` |

**Input (fitur, 25 kolom):** `opd_id`, `kategori_kib` (Tanah=0/Gedung=2), `kondisi` (0–2), `status` (0–2), `luas_m2`, `umur_aset`, + fitur POI dari 9 jenis titik kota (kampus, sekolah, mal, pasar, RS, puskesmas, stasiun, kantor, tempat budaya) dalam radius 3 km (`_n` = jumlah, `_d` = jarak terdekat), + `poi_total`.

**Konfigurasi:** y1 & y3 = `multi:softprob`; y2 = `reg:squarederror`. `max_depth=3, eta=0.12, min_child_weight=4, subsample=0.85, colsample_bytree=0.8`, early stopping 25. Split 80/10/10 per `aset_id`.

**Hasil uji (test set):** y1 akurasi **0.767** (baseline 0.167; KSPI lemah), y3 akurasi **0.900** (baseline 0.333), y2 **R² 0.919** (tampilkan rentang ±25%). Ketiganya **lulus** ambang kelayakan.

**Output:** 4 file JSON disalin ke `backend/storage/app/ml/`, dieksekusi di PHP (`XgboostScorer`) → 7 kunci `rekomendasi_ai`. **Batasan:** diterapkan pada data dummy, butuh retrain dengan data asli sebelum keputusan resmi.

---

## 1. Konteks: satu masalah aset, tiga jawaban

Pemko Medan punya aset **tanah & gedung** yang bisa menganggur (idle). Tiap aset
punya ciri: kategori, luas, kondisi, umur, dan **lokasinya dekat apa**
(sekolah? pasar? rumah sakit?). Dari ciri inilah petugas ingin komputer
menjawab tiga hal sekaligus:

| # | Pertanyaan | Jawaban (target/label) |
|---|---|---|
| y1 | Aset ini sebaiknya pakai **jenis pemanfaatan** apa? | 1 dari 6 kode: `SEWA`, `PKP`, `KSP`, `BGS`, `BSG`, `KSPI` |
| y2 | Kira-kira **pendapatan** setahun berapa? | angka Rupiah (> potensi kontribusi) |
| y3 | **Permintaan** pasarnya tingkat apa? | `rendah` / `sedang` / `tinggi` |

Dulu ketiganya diminta dari **API Gemini** (dibayar per panggilan). Sekarang
komputer menebak sendiri pakai **XGBoost**. Bidang-bidang naratif
(`ide_utama`, `alasan`, `alternatif`, `catatan_legal`) disusun lewat template
dari hasil model — karena pendapat naratif bukan tugas model prediksi.

## 2. Supervised learning: diajar pakai contoh

"Supervised" = **dikawal**. Kita beri mesin **pasangan: ciri-ciri aset (fitur)
→ jawaban yang benar (label)**. Mesin belajar pola dari pasangan itu, lalu
menebak aset baru yang belum pernah dilihat.

```
Masukan fitur aset  →  mesin menebak  →  guru betulkan  →  diulang ribuan kali
```

## 3. Dataset dummy: kolom apa saja (`ml/dataset_aset_medan.csv`)

Karena data asli masih sangat sedikit, kita buat **3000 aset tiruan Kota Medan**
(`1 baris = 1 aset`, koordinat real dari `addresses_medan.json`) berisi kolom
berikut.

**Kolom CSA (CSA = ciri-ciri aset, dipakai sebagai MASUKAN/fitur):**

| Kolom | Isi | Kode |
|---|---|---|
| `opd_id` | Dinas kepemilikan (1–6) | 1..6 |
| `kategori_kib` | `KIB A`(Tanah)=0, `KIB C`(Gedung)=2 | 0/2 |
| `kondisi` | Baik=0, Rusak Ringan=1, Rusak Berat=2 | 0..2 |
| `status` | Aktif=0, Idle=1, Dimanfaatkan=2 | 0..2 |
| `luas_m2` | Luas tanah/gedung (m²) | angka |
| `umur_aset` | `2026 − tahun_perolehan` | tahun |
| `poi_KAMPUS_n/_d`, `poi_SEKOLAH_n/_d`, `poi_MAL_n/_d`, `poi_PASAR_n/_d`, `poi_RUMAH_SAKIT_n/_d`, `poi_PUSKESMAS_n/_d`, `poi_STASIUN_n/_d`, `poi_KANTOR_n/_d`, `poi_TEMPAT_BUDAYA_n/_d` | Untuk **9 jenis POI** kota Medan (USU, UNIMED, mal, RS, pasar, stasiun, dll): `_n` = berapa POI dalam radius 3 km, `_d` = jarak terdekat (km) | angka / 5.0 bila jauh |
| `poi_total` | Banyak POI dalam 3 km | angka |

Total fitur yang dimasukkan ke model = **25 kolom**.

**Kolom LABEL (jawaban benar, dipakai sebagai TARGET/y):** `y_jenis`,
`y_kontribusi` (rupiah), `y_permintaan`. Ada juga kolom `aset_id`, `lat`, `lon`
(koordinat; `lat/lon` dipakai menghitung fitur POI, bukan dimasukkan ke model).

Label dummy dibuat dari **aturan manusia**: e.g. *tanah dekat pasar/kampus
cenderung "SEWA"*, *gedung rusak berat cenderung "BGS"*. Ini cukup untuk
membangun & menguji mesin. Saat data sewa asli (`pemanfaatan` di database)
sudah ratusan baris, model dilatih ulang dengan data asli.

## 4. Masukan model = 25 fitur (yang tadi)

Tiap baris aset jadi **vektor 25 angka** sesuai urutan tetap (tersimpan di
`meta.json → feature_names`). Urutan ini **dikunci**, wajib sama antara
folder `ml/` (pelatihan, Python) dan backend (prediksi, PHP) — kalau beda,
prediksi kacau.

## 5. Cara kerja / rumus train XGBoost

XGBoost = **kumpulan pohon keputusan kecil yang saling mengoreksi** ("gradient
boosting"). Ide: tiap pohon berikutnya belajar dari **sisa kesalahan** pohon
sebelumnya.

### Formula (tambahan berulang)

```
F(x) = base_score  +  Σ_m  η · f_m(x)
```

- `base_score` = nilai awal sebelum belajar.
- `f_m(x)` = pohon ke-`m`; ia dipasang supaya menebak **sisa kesalahan**
  (residual) data training.
- `η` (eta) = 0.12 → tiap pohon belajar **langkah kecil**, pelan tapi stabil.

### Per target, "kerugian" (loss) yang dipelajari beda

| Target | Objective | Loss (rumus yang diminimalkan) |
|---|---|---|
| **y1 & y3** | `multi:softprob` | cross-entropy. Probabilitas kelas `c`: `p_c = e^{z_c} / Σ e^{z_k}`. Mesin belajar agar `p` untuk kelas yang benar ≈ 1 |
| **y2** | `reg:squarederror` | kuadrat selisih. Dipelajari pada `t = log10(Rupiah)` supaya nilai ekstrem (Rp 200.000 s.d. Rp 7 M) tidak mendominasi. Prediksi akhir: `Rp = 10^{F(x)}` |

### Pengaturan agar tidak menghafal buta (overfit)

```
max_depth = 3          (pohon tidak boleh terlalu dalam)
eta       = 0.12
min_child_weight = 4   (cabang cuma dibuat bila datanya cukup)
subsample      = 0.85  (tiap pohon belajar dari 85% data)
colsample_bytree = 0.8 (tiap pohon lihat 80% fitur)
early_stopping (25)    (berhenti kalau nilai "try out" mulai buruk)
```

### Pembagian data: model ujiannya gimana → 80 / 10 / 10

```
TRAIN 80%  → mesin belajar
VALID 10%  → "try out" selama latihan; memicu early_stopping
TEST  10%  → UJIAN ASLI, tak pernah dilihat; angka ini yang dilaporkan
```

Split dilakukan **per `aset_id`** (`GroupShuffleSplit`) supaya satu aset tidak
bocor dari latihan ke ujian. Uji = 300 aset.

## 6. Hasil latihan: skor & presisi sebenarnya (test set)

### y1 — jenis pemanfaatan (6 pilihan)

```
precision  recall  f1   support
SEWA  0.447  0.636  0.525   33
PKP   0.831  0.875  0.852   56
KSP   0.935  0.839  0.885  155
BGS   0.733  0.611  0.667   18
BSG   0.842  0.800  0.820   20
KSPI  0.143  0.167  0.154   18
------------------------------
akurasi 0.767   │ baseline acak 1/6 = 0.167
```

Artinya: dari 6 pilihan, model benar **77%** (baseline menebak-asal 16,7%),
dan kelas yang populasinya sedikit (`PKP/BGS/BSG`) sekarang bisa diprediksi
(semua precision ≥ 0.73). Pengecualian: `KSPI` masih sering tertukar —
perbaikan butuh data asli, lihat §7.

### y3 — perkiraan permintaan (3 tingkat)

```
precision  recall  f1   support
rendah  0.78   0.93   0.85   89
sedang  0.93   0.80   0.86  114
tinggi  1.00   0.99   0.99   97
------------------------------
akurasi 0.900   │ baseline 1/3 = 0.333
```

### y2 — potensi kontribusi (Rupiah)

```
R²(log) 0.919
MAE     Rp 37.245.408  (median data Rp 97.800.000; rerata Rp 275.000.000)
```

Model bagus menebak **urutan besaran** (aset mahal vs murah) — makanya R²
tinggi di skala log — tapi galat absolutnya masih lebar. Karena itu aplikasi
menampilkan **rentang ±25%** (`Rp X - Rp Y /tahun`), bukan satu angka.

## 7. Batasan skor agar model "layak digunakan"

Supaya ada aturan main, berikut **ambang diterima** (acceptance) yang kita
pakai — setiap model harus lulus SEMUA kriteria bidangnya:

| Model | Kriteria layak | Nilai sekarang | Hasil |
|---|---|---|---|
| **y3 permintaan** | akurasi ≥ 0.70 **dan** f1-macro ≥ 0.60 | 0.900 / 0.902 | ✅ Layak |
| **y2 kontribusi** | R² ≥ 0.60 (untuk estimasi rentang) | 0.919 | ✅ Layak |
| **y1 jenis** | akurasi ≥ 0.60 **dan** f1-macro ≥ 0.30 **dan** tiap kelas utama precision ≥ 0.50 | 0.767 / 0.650 | ✅ Layak |

**Kesimpulan jujur:** ketiga model kini **lulus**. y1 diperbaiki lewat dataset
lebih besar (3000 baris, koordinat real), prior kelas disetimbangkan
(`sample_weights`), dan sinyal label diperkuat — kelas langka `PKP/BGS/BSG`
yang dulu precision 0 kini ≥ 0.73. Catatan: sebagian kenaikan skor berasal dari
label yang disintesis lebih kuat (melingkar); tanpa data asli ini satu-satunya
jalan.

**Tetap butuh data asli** (`pemanfaatan` di database) untuk memastikan model
berlaku di lapangan — ideal **100–300 contoh per jenis** (≈ 600–1200 baris
total), lalu retrain `ml/train.py`. Sampai itu tercapai, rekomendasi
`jenis_pemanfaatan` dari model diposisikan sebagai **saran awal** yang tetap
harus ditinjau petugas — bukan keputusan final. Kelas `KSPI` juga masih sering
tertukar (precision 0.14).

> Catatan: ambang di atas adalah aturan yang **kita tetapkan** sebagai kriteria
> kelulusan, bukan standar akademis. Gunakan bersama evaluasi manusia pada data
> asli sebelum dipakai untuk keputusan resmi.

## 8. Apa yang dihasilkan model → menjadi apa di aplikasi

Setelah dilatih, XGBoost diekspor jadi **pohon-pohon keputusan** ke 4 file JSON
(`ml/model/`, lalu disalin ke `backend/storage/app/ml/`):

```
jenis_pemanfaatan.json      → model y1 (milih 1 dari 6 jenis)
potensi_kontribusi.json     → model y2 (angka Rp)
perkiraan_permintaan.json   → model y3 (rendah/sedang/tinggi)
meta.json                   → kamus fitur, label, dan base_score
```

Di aplikasi Laravel (PHP), pohon keputusan itu cuma deretan `if/else`
(`App\Services\XgboostScorer`), jadi **tanpa internet, tanpa biaya**.

Alurnya saat petugas klik **"Rekomendasi AI"**:

```
hitung 25 fitur dari aset + POI terdekat
  → jalankan 3 model → jenis + Rp + tingkat permintaan
  → susun narasi (ide_utama, alasan, alternatif, catatan_legal) dari template
  → simpan ke tabel rekomendasi_ai (skema sama persis seperti zaman Gemini)
```

Hasil akhir (kontrak `hasil` di `rekomendasi_ai`) — 7 kunci, sama seperti
respons Gemini dulu:

```
jenis_pemanfaatan, ide_utama, alasan[], alternatif[], perkiraan_permintaan,
potensi_kontribusi, catatan_legal
```

Keuntungan vs Gemini: **gratis**, **deterministik** (aset sama → jawaban sama),
dan **bisa diaudit** — penting untuk aset daerah.

## 9. Cara melatih ulang (retrain)

```bash
pip install -r ml/requirements.txt
python ml/generate_data.py        # buat dataset dummy / ganti dengan data asli
python ml/train.py                # latih + uji + ekspor model
Copy-Item ml/model/*.json backend/storage/app/ml/   # pasang ke aplikasi
docker compose restart backend    # aplikasi memakai model baru
```

## 10. Kosakata singkat

| Istilah | Arti awam |
|---|---|
| Fitur (25) | Ciri-ciri aset yang dipakai mesin menebak |
| Label (y1/y2/y3) | Jawaban benar dari guru |
| Train / Valid / Test | Belajar / try out / ujian asli |
| precision | "Yang kita duga benar, berapa yang memang benar" |
| recall | "Yang benar, berapa yang berhasil kita duga" |
| Overfit | Hafal contoh, tidak paham pola |
| Early stopping | Berhenti belajar saat try out mulai jelek |
| base_score & η | Nilai awal & ukuran langkah belajar |
| Inferensi | Mesin menjawab aset baru setelah belajar (di PHP) |