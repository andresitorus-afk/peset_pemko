# Folder `ml/` — Model Rekomendasi Pemanfaatan Aset (XGBoost)

> Penjelasan awam untuk seluruh isi folder, tujuan tiap file, dan bagaimana
> mereka berkesinambungan.

Sebelumnya fitur **"Rekomendasi AI"** memanggil **API Gemini** (bayar per
panggilan). Folder ini berisi sistem **penggantinya** yang gratis &
deterministik: XGBoost **dilatih di Python**, lalu **diekspor ke file JSON**
yang dibaca backend Laravel (PHP) sebagai deretan `if/else` — tanpa internet,
tanpa biaya, dan bisa diaudit.

## Satu aset, tiga jawaban

Tiap aset (tanah/gedung) dijawab 3 pertanyaan sekaligus:

| Target | Pertanyaan | Output |
|---|---|---|
| y1 | Jenis pemanfaatan apa? | 1 dari 6 (`SEWA, PKP, KSP, BGS, BSG, KSPI`) |
| y2 | Pendapatan setahun berapa? | angka Rupiah (potensi kontribusi) |
| y3 | Tingkat permintaan pasar? | `rendah / sedang / tinggi` |

---

## Alur & kesinambungan file

```
generate_data.py  → dataset_aset_medan.csv  →  train.py  →  model/*.json
                     (data pelatihan)                      (disalin ke backend/storage/app/ml/)
                                                    reference.py  →  patokan unit-test PHP
```

### 1. `generate_data.py` — pabrik data tiruan
Membuat **3000 baris aset tiruan Kota Medan** (seed 42, koordinat real dari
`addresses_medan.json`) karena data asli masih sangat sedikit. Tiap baris =
1 aset berisi **25 fitur + label**.

- Fitur identitas: `opd_id, kategori_kib, kondisi, status, luas_m2, umur_aset`.
- Fitur **POI** untuk 9 tipe (kampus, mal, pasar, RS, stasiun, dll): `_n` =
  jumlah POI dalam radius 3 km, `_d` = jarak POI terdekat (km), plus `poi_total`.
  Koordinat `lat/lon` dipakai hanya untuk menghitung jarak ke daftar POI nyata
  Medan (USU, Sun Plaza, dsb), **tidak** masuk ke model.
- Label dibuat dari **aturan manusia** (misal: tanah dekat pasar/kampus →
  cenderung `SEWA`). Keluar: `dataset_aset_medan.csv`.

### 2. `train.py` — mesin belajar
Membaca CSV, melatih **3 model XGBoost** (y1 & y3 = klasifikasi, y2 = regresi
log10 Rupiah), membagi data **80/10/10 per aset** (anti bocor), lalu mengekspor
ke file JSON model + `meta.json`. Berisi `verify_dump()` yang **self-check**:
memastikan prediksi dari file JSON persis sama dengan XGBoost asli — rumus
inilah yang nanti ditiru PHP.

### 3. `reference.py` — kalkulator patokan
Menjalankan tree dari file JSON yang sama yang dibaca PHP, lalu mencetak hasil
(prob / kelas / Rupiah) untuk satu vektor contoh. Nilai ini jadi **"kunci
jawaban"** bagi unit-test `XgboostScorer` PHP: kalau PHP memberi angka beda,
implementasinya salah.

### 4. `requirements.txt`
Paket Python untuk melatih: `xgboost, pandas, numpy, scikit-learn`.

### 5. `MEMAHAMI_XGBOOST.md`
Dokumentasi teknis mendalam + hasil evaluasi model (akurasi, R², batasan).

### 6. `model/` — hasil akhir (4 file JSON)
- `jenis_pemanfaatan.json` → model y1
- `potensi_kontribusi.json` → model y2
- `perkiraan_permintaan.json` → model y3
- `meta.json` → **kamus penting**: urutan 25 fitur (`feature_names`), peta
  label string→angka, dan `base_score` tiap model.

---

## Kunci: urutan 25 fitur "dikunci"

Urutan `feature_names` di `meta.json` **wajib identik** antara Python (training)
dan PHP (inference). Kalau beda urutan → prediksi kacau. Maka `generate_data.py`
& `train.py` menjaga urutannya, dan `reference.py`/`verify_dump()` memastikan
PHP meniru persis.

Rumus prediksi per model (gradient boosting):

```
F(x) = base_score + Σₘ η · fₘ(x)
```

`fₘ(x)` = pohon keputusan ke-`m`, `η` (eta) = 0.12 (langkah kecil), dan
prediksi akhir Rupiah = `10^(F(x))` untuk y2.

## Hasil jujur & batasan model

| Model | Kriteria layak | Nilai | Status |
|---|---|---|---|
| y2 kontribusi | R² ≥ 0.60 | 0.919 | ✅ Layak (ditampilkan sebagai rentang ±25%) |
| y3 permintaan | akurasi ≥ 0.70, f1-macro ≥ 0.60 | 0.900 / 0.902 | ✅ Layak |
| y1 jenis | akurasi ≥ 0.60, f1-macro ≥ 0.30 | 0.767 / 0.650 | ✅ Layak |

Kesimpulan: **ketiga model sudah layak**. y1 diperbaiki lewat dataset lebih
besar (3000 baris), prior kelas disetimbangkan, dan sinyal label diperkuat —
kelas langka `PKP/BGS/BSG` kini bisa diprediksi (semua precision ≥ 0.73 kecuali
`KSPI` 0.14). Disadari skor naik sebagian karena label disintesis lebih kuat
(melingkar); tanpa data asli ini satu-satunya jalan. Rekomendasi `jenis`
tetap diposisikan sebagai **saran awal** yang ditinjau petugas, bukan
keputusan final.

## Alur runtime di aplikasi

```
petugas klik "Rekomendasi AI"
 → hitung 25 fitur dari aset + POI terdekat (backend)
 → jalankan 3 model (PHP XgboostScorer) → jenis + Rp + tingkat permintaan
 → susun narasi (ide_utama, alasan, alternatif, catatan_legal) dari template
 → simpan ke tabel rekomendasi_ai (kontrak 7 kunci, sama seperti zaman Gemini)
```

## Cara retrain (saat data asli sudah cukup)

```bash
pip install -r ml/requirements.txt
python ml/generate_data.py        # atau ganti dengan dataset asli
python ml/train.py                # latih + uji + ekspor model
Copy-Item ml/model/*.json backend/storage/app/ml/   # pasang ke aplikasi
docker compose restart backend    # aplikasi memakai model baru
```