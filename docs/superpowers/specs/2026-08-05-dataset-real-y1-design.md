# Dataset Real + y1 Layak — Design

Tanggal: 2026-08-05
Status: disetujui user (brainstorming)

## Masalah
1. `ml/dataset_aset_medan.csv` = data dummy koordinat acak, bukan lokasi real Medan.
2. y1 (jenis pemanfaatan) tidak layak: acc 0.40, f1-macro 0.195. Kelas langka (PKP/BGS/BSG) tak terprediksi.

## Keputusan
- Lokasi aset = koordinat real Kota Medan dari OpenStreetMap (Overpass API), cache ke `ml/addresses_medan.json`. Fallback: daftar ~50 jalan kurasi bila fetch gagal.
- Aset tetap acak (tanah/gedung acak di atas lokasi real) — sesuai arahan, karena tidak ada dataset pemerintah.
- y1 diperbaiki dengan: dataset lebih besar (~3000), prior kelas disetimbangkan, sinyal label diperkuat (multiplier 0.9→1.5), aturan lebih bersih. Disadari: skor naik sebagian karena label disintesis lebih kuat (melingkar) — satu-satunya jalan tanpa data asli, sudah disetujui user.
- Kontrak 25 fitur & skema `meta.json` TIDAK berubah → PHP `XgboostScorer` & `RekomendasiLocalService` tetap kompatibel.

## Perubahan
| File | Aksi |
|---|---|
| `ml/fetch_addresses.py` | baru: Overpass → `addresses_medan.json` |
| `ml/generate_data.py` | ambil koordinat dari addresses, generate ~3000 baris, perkuat label, tambah kolom `address` |
| `ml/train.py` | FEATURES tetap; tambah balance (class_weight bila perlu); output sama |
| `ml/model/*.json` | diekspor ulang |
| `backend/storage/app/ml/*.json` | disalin ulang |
| `ml/README.md`, `ml/MEMAHAMI_XGBOOST.md` | update hasil/skor |

## Penerimaan (y1)
- acc ≥ 0.60 dan f1-macro ≥ 0.30 pada test set.
- y2 (R² ≥ 0.60) & y3 (acc ≥ 0.70) tidak boleh turun di bawah ambang.

## Verifikasi
- `python ml/generate_data.py && python ml/train.py` → cek skor.
- `backend/tests/Unit/XgboostScorerTest.php` tetap hijau (model di storage/app/ml).
