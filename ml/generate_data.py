"""Generate dataset aset Kota Medan realistik untuk training XGBoost.

Output: ml/dataset_aset_medan.csv (1 baris = 1 aset, fitur + label y1/y2/y3).
Koordinat & alamat = real Kota Medan (dari ml/addresses_medan.json, fetch OSM),
asetnya sendiri acak (tanah/gedung) karena tidak ada dataset pemerintah.
Fitur & skema dikunci agar paritas dengan inferensi PHP (XgboostScorer) terjaga.
"""
import csv
import json
import math
import os
import random

random.seed(42)

HERE = os.path.dirname(os.path.abspath(__file__))
ADDRESSES = os.path.join(HERE, 'addresses_medan.json')

# ===== POI Kota Medan (paritas dengan PoiSeeder) =====
POIS = [
    ('USU', 'kampus', 3.5630, 98.6568), ('UMSU', 'kampus', 3.5899, 98.6779),
    ('UNIMED', 'kampus', 3.6060, 98.6790), ('Politeknik Negeri Medan', 'kampus', 3.5956, 98.6883),
    ('UIN Sumatera Utara', 'kampus', 3.6320, 98.6810),
    ('Mal Medan Fair', 'mal', 3.5830, 98.6750), ('Sun Plaza', 'mal', 3.5816, 98.6810),
    ('Cambridge City Square', 'mal', 3.5874, 98.6790), ('Delipark Mall', 'mal', 3.6003, 98.6720),
    ('Ring Road City Walks', 'mal', 3.6260, 98.6710), ('Centre Point Mall', 'mal', 3.5890, 98.6790),
    ('Grand City Mall', 'mal', 3.5945, 98.6920), ('Hermes Palace Mall', 'mal', 3.5670, 98.6680),
    ('SMA Negeri 1 Medan', 'sekolah', 3.5895, 98.6840), ('SMA Negeri 2 Medan', 'sekolah', 3.6050, 98.6880),
    ('SMA Negeri 3 Medan', 'sekolah', 3.5930, 98.6720), ('SMP Negeri 1 Medan', 'sekolah', 3.5880, 98.6830),
    ('SMA Sutomo 1 Medan', 'sekolah', 3.5910, 98.6740), ('SMA Methodist 1 Medan', 'sekolah', 3.5920, 98.6760),
    ('Perguruan Buddhi', 'sekolah', 3.5820, 98.6950),
    ('RSUD Pirngadi Medan', 'rumah_sakit', 3.5850, 98.6720), ('RS Haji Medan', 'rumah_sakit', 3.6070, 98.6840),
    ('RS Adam Malik', 'rumah_sakit', 3.6100, 98.6810), ('RS Mitra Medika', 'rumah_sakit', 3.5830, 98.6930),
    ('RS Murni Teguh', 'rumah_sakit', 3.5880, 98.6850), ('RS Santa Elisabeth', 'rumah_sakit', 3.5890, 98.6650),
    ('RS Permata Bunda', 'rumah_sakit', 3.6080, 98.6960), ('RS Columbia Asia', 'rumah_sakit', 3.5960, 98.6810),
    ('Puskesmas Medan Baru', 'puskesmas', 3.5950, 98.6650), ('Puskesmas Padang Bulan', 'puskesmas', 3.5560, 98.6650),
    ('Stasiun Medan', 'stasiun', 3.5900, 98.6770), ('Kantor Walikota Medan', 'kantor', 3.5940, 98.6750),
    ('Pasar Padang Bulan', 'pasar', 3.5549, 98.6637), ('Pasar Petisah', 'pasar', 3.5930, 98.6710),
    ('Pasar Aksara', 'pasar', 3.5820, 98.6930),
    ('Istana Maimun', 'tempat_budaya', 3.5764, 98.6842), ('Masjid Raya Al Mashun', 'tempat_budaya', 3.5751, 98.6839),
    ('Tjong A Fie Mansion', 'tempat_budaya', 3.5870, 98.6820), ('Museum Negeri Sumatera Utara', 'tempat_budaya', 3.5890, 98.6550),
    ('Taman Budaya Sumatera Utara', 'tempat_budaya', 3.5940, 98.6680), ('Lapangan Merdeka Medan', 'tempat_budaya', 3.5922, 98.6782),
    ('Gedung London Sumatra', 'tempat_budaya', 3.5880, 98.6760),
]

TIPES = ['kampus', 'sekolah', 'mal', 'pasar', 'rumah_sakit', 'puskesmas', 'stasiun', 'kantor', 'tempat_budaya']
JENIS = ['SEWA', 'PKP', 'KSP', 'BGS', 'BSG', 'KSPI']
RING = 6371.0

# prior kelas y1 disetimbangkan (semua ~sama) supaya kelas langka punya contoh cukup
BASE_PROBS = [0.18, 0.16, 0.19, 0.14, 0.14, 0.19]

NO_RUMAH = 1, 2, 3, 4, 5, 6, 7, 8, 10, 12, 14, 15, 16, 18, 20, 22, 24, 25, 28, 30, 32, 34, 36, 40, 42, 45, 48, 50
NAMA_KOMPLEK = ['Komplek', 'Perumahan', 'Gang', 'Lorong', 'Blok', 'Lingkungan']


def jarak_km(lat1, lon1, lat2, lon2):
    dlat = math.radians(lat2 - lat1)
    dlon = math.radians(lon2 - lon1)
    a = math.sin(dlat / 2) ** 2 + math.cos(math.radians(lat1)) * math.cos(math.radians(lat2)) * math.sin(dlon / 2) ** 2
    return 2 * RING * math.asin(math.sqrt(a))


def poi_features(lat, lon):
    """Kembalikan dict fitur POI: n & jarak terdekat per tipe (<=5 km), plus total."""
    f = {}
    per_type = {t: [] for t in TIPES}
    for _, t, plat, plon in POIS:
        d = jarak_km(lat, lon, plat, plon)
        per_type[t].append(d)
    total = 0
    for t in TIPES:
        ds = [d for d in per_type[t] if d <= 3.0]
        f[f'poi_{t}_n'] = len(ds)
        near = [d for d in per_type[t] if d <= 5.0]
        f[f'poi_{t}_d'] = round(min(near), 4) if near else 5.0
        total += len(ds)
    f['poi_total'] = total
    return f


def softmax(v):
    m = max(v)
    e = [math.exp(x - m) for x in v]
    s = sum(e)
    return [x / s for x in e]


def sample_logits(logits):
    p = softmax(logits)
    r = random.random()
    acc = 0.0
    for i, pi in enumerate(p):
        acc += pi
        if r <= acc:
            return i
    return len(p) - 1


def jenis_probs(kib, kondisi, pf, luas):
    """Probabilitas per jenis dari aturan jelas (sinyal kuat yang bisa dipelajari)."""
    tanah = 1 if kib == 0 else 0
    gedung = 1 if kib == 2 else 0
    rusak = 1 if kondisi == 2 else 0
    rusak_ringan = 1 if kondisi == 1 else 0
    komersial = pf['poi_mal_n'] + pf['poi_pasar_n'] + pf['poi_kampus_n']
    layanan = pf['poi_rumah_sakit_n'] + pf['poi_puskesmas_n'] + pf['poi_kantor_n'] + pf['poi_sekolah_n']
    lebar = math.log10(max(luas, 1)) - 3

    s = [0.0] * 6
    # SEWA: tanah kosong di area komersial (mal/pasar/kampus), atau luas besar
    s[0] = 1.6 * tanah + 0.5 * komersial + 0.6 * lebar - 1.5 * rusak - 1.0 * gedung
    # PKP: gedung dalam kondisi baik, luas besar
    s[1] = 1.8 * gedung + 1.0 * (1 - rusak - rusak_ringan) + 0.5 * lebar - 0.8 * rusak
    # KSP: tanah di area layanan (RS/puskesmas/kantor/sekolah)
    s[2] = 1.4 * tanah + 0.8 * layanan + 0.4 * lebar - 1.2 * rusak
    # BGS: tanah rusak berat (perlu bangun dari nol)
    s[3] = 1.2 * tanah + 2.2 * rusak - 0.8 * gedung
    # BSG: gedung rusak berat (perlu bangun kembali)
    s[4] = 1.2 * gedung + 2.2 * rusak - 0.8 * tanah
    # KSPI: tanah luas strategis dekat mal (investasi)
    s[5] = 1.3 * tanah + 0.9 * pf['poi_mal_n'] + 0.7 * lebar - 1.5 * rusak

    logits = [math.log(BASE_PROBS[i]) + 2.0 * s[i] for i in range(6)]
    return logits


def jenis_label(kib, kondisi, pf, luas):
    """Logit sampling: prior seimbang + sinyal kuat. y1 bisa dipelajari dengan baik."""
    logits = jenis_probs(kib, kondisi, pf, luas)
    return sample_logits(logits)


def potensi_kontribusi(kib, luas, pf, jidx):
    """Nilai Rp/tahun nyata (skew, ~log-normal)."""
    tanah = 1 if kib == 0 else 0
    lebar = math.log10(max(luas, 1)) - 2.5
    density = pf['poi_total']
    base = 5.2 + 0.55 * tanah + 0.55 * lebar + 0.06 * density
    if jidx in (0, 2):
        base += 0.4
    if jidx == 5:
        base += 0.3
    val = 10 ** (base + random.gauss(0, 0.28))
    return int(max(round(val, -5), 100000))


def permintaan_latent(jidx, pf, luas):
    """Skor laten untuk y3; dipetakan ke tertil agar seimbang (rendah/sedang/tinggi)."""
    latent = 0.55 * (pf['poi_total'] - 5) + 0.6 * (math.log10(max(luas, 1)) - 3) + random.gauss(0, 0.8)
    if jidx == 5:
        latent += 0.6
    if jidx == 4:
        latent -= 0.5
    return latent


def alamat_random():
    """Alamat realistis: Jalan No. X, Kel. Y Kec. Z, Kota Medan."""
    kecamatan = ['Medan Petisah', 'Medan Baru', 'Medan Polonia', 'Medan Maimun', 'Medan Kota',
                 'Medan Area', 'Medan Perjuangan', 'Medan Timur', 'Medan Sunggal', 'Medan Selayang',
                 'Medan Johor', 'Medan Denai', 'Medan Helvetia', 'Medan Barat', 'Medan Deli',
                 'Medan Tembung', 'Medan Labuhan', 'Medan Marelan']
    kelurahan = ['Sei Sikambing', 'Petisah Tengah', 'Pusat Pasar', 'Kesawan', 'Polonia', 'Pulo Brayan',
                 'Suka Maju', 'Gaharu', 'Aur', 'Pasar Merah', 'Sunggal', 'Padang Bulan', 'Jati',
                 'Bantan', 'Sikambing', 'Pandan Selamat', 'Titi Kuning', 'Merdeka']
    jalan = random.choice(NAMA_KOMPLEK) if random.random() < 0.25 else 'Jl.'
    no = random.choice(NO_RUMAH)
    return (f'{jalan} {random.choice(["Gatot Subroto", "Iskandar Muda", "KH Wahid Hasyim", "Cut Mutia",
        "Teuku Cik Ditiro", "Teuku Daud", "HM Yamin", "Diponegoro", "Thamrin", "Ahmad Yani",
        "Sudirman", "Imam Bonjol", "Pemuda", "Balai Kota", "Katamso", "Veteran", "Sutomo",
        "Sisingamangaraja", "Jamin Ginting", "Putri Hijau", "Setiabudi", "Marelan Raya", "Yos Sudarso"])} '
            f'No. {no}, Kel. {random.choice(kelurahan)} Kec. {random.choice(kecamatan)}, Kota Medan')


def main():
    n = 3000
    out = os.path.join(HERE, 'dataset_aset_medan.csv')
    if not os.path.exists(ADDRESSES):
        print(f'ERROR: {ADDRESSES} tidak ada. Jalankan dulu: python ml/fetch_addresses.py')
        return 1
    try:
        addrs = json.load(open(ADDRESSES, encoding='utf-8'))
    except UnicodeDecodeError:
        addrs = json.load(open(ADDRESSES, encoding='cp1252'))
    if len(addrs) < 20:
        print(f'ERROR: addresses_medan.json cuma {len(addrs)} jalan.')
        return 1

    cols = (['aset_id', 'opd_id', 'kategori_kib', 'kondisi', 'status', 'luas_m2', 'umur_aset',
             'address', 'lat', 'lon']
            + [f'poi_{t}_{s}' for t in TIPES for s in ('n', 'd')] + ['poi_total']
            + ['y_jenis', 'y_kontribusi', 'y_permintaan'])
    rows = []
    for i in range(n):
        addr = random.choice(addrs)
        kib = random.choices([0, 2], weights=[0.55, 0.45])[0]      # KIB A (Tanah) / KIB C (Gedung)
        opd = random.randint(1, 6)
        kondisi = random.choices([0, 1, 2], weights=[0.55, 0.30, 0.15])[0]
        status = random.choices([0, 1, 2], weights=[0.25, 0.65, 0.10])[0]  # Aktif/Idle/Dimanfaatkan
        luas = round(random.lognormvariate(math.log(1800), 0.9)) if kib == 0 else round(random.lognormvariate(math.log(900), 0.7))
        luas = max(luas, 60)
        umur = random.randint(6, 28)
        # koordinat: center jalan real + offset kecil (±~350m)
        lat = addr['lat'] + random.gauss(0, 0.003)
        lon = addr['lon'] + random.gauss(0, 0.003)
        pf = poi_features(lat, lon)

        jidx = jenis_label(kib, kondisi, pf, luas)
        kontribusi = potensi_kontribusi(kib, luas, pf, jidx)
        latent = permintaan_latent(jidx, pf, luas)

        rows.append([f'aset-{i:04d}', opd, kib, kondisi, status, luas, umur,
                     alamat_random(), round(lat, 6), round(lon, 6)]
                    + [pf[f'poi_{t}_{s}'] for t in TIPES for s in ('n', 'd')] + [pf['poi_total']]
                    + [jidx, kontribusi, latent])

    # map latent y3 -> tertil (seimbang)
    latents = sorted(r[cols.index('y_permintaan')] for r in rows)
    q1 = latents[len(latents) // 3]
    q2 = latents[2 * len(latents) // 3]
    for r in rows:
        lv = r[cols.index('y_permintaan')]
        r[cols.index('y_permintaan')] = 2 if lv > q2 else (1 if lv > q1 else 0)

    with open(out, 'w', newline='', encoding='utf-8') as fh:
        w = csv.writer(fh)
        w.writerow(cols)
        w.writerows(rows)

    from collections import Counter
    print(f'OK: {n} baris -> {out} (dari {len(addrs)} jalan real Medan)')
    print('distribusi y_jenis:', dict(sorted(Counter(r[cols.index("y_jenis")] for r in rows).items())))
    print('distribusi y_permintaan:', dict(sorted(Counter(r[cols.index("y_permintaan")] for r in rows).items())))
    return 0


if __name__ == '__main__':
    import sys
    sys.exit(main())
