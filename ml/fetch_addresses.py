"""Fetch jalan real Kota Medan dari OpenStreetMap (Overpass API).

Output: ml/addresses_medan.json — list [{name, lat, lon}] center tiap street.
Cache: dipakai generate_data.py. Bila fetch gagal/offline, fallback daftar kurasi.
"""
import json
import os
import sys
import time
import urllib.request

HERE = os.path.dirname(os.path.abspath(__file__))
OUT = os.path.join(HERE, 'addresses_medan.json')
APIS = ['https://overpass.kumi.systems/api/interpreter',
        'https://maps.mail.ru/osm/tools/overpass/api/interpreter',
        'https://overpass-api.de/api/interpreter']

# bbox Kota Medan (south, west, north, east)
BBOX = (3.50, 98.60, 3.64, 98.72)
OVERPASS_QUERY = f"""
[out:json][timeout:60];
way["highway"~"residential|primary|secondary|tertiary|unclassified|service"]["name"]({BBOX[0]},{BBOX[1]},{BBOX[2]},{BBOX[3]});
out center;
"""

FALLBACK = [
    # (nama jalan, lat, lon)
    ('Jl. Gatot Subroto', 3.5776, 98.6720), ('Jl. Iskandar Muda', 3.5900, 98.6650),
    ('Jl. KH Wahid Hasyim', 3.5905, 98.6630), ('Jl. Cut Mutia', 3.5895, 98.6620),
    ('Jl. Teuku Cik Ditiro', 3.5910, 98.6660), ('Jl. Teuku Daud', 3.5900, 98.6600),
    ('Jl. HM Yamin', 3.5860, 98.6680), ('Jl. Diponegoro', 3.5880, 98.6640),
    ('Jl. M.H. Thamrin', 3.5940, 98.6720), ('Jl. Ahmad Yani', 3.5950, 98.6770),
    ('Jl. Jenderal Sudirman', 3.5970, 98.6800), ('Jl. Imam Bonjol', 3.5980, 98.6820),
    ('Jl. Pemuda', 3.5985, 98.6840), ('Jl. Balai Kota', 3.5920, 98.6740),
    ('Jl. Brigjen Katamso', 3.5920, 98.6760), ('Jl. Veteran', 3.5890, 98.6780),
    ('Jl. Jend. A.H. Nasution', 3.5650, 98.6600), ('Jl. Sutomo', 3.5870, 98.6700),
    ('Jl. Kapten Maulana Lubis', 3.5900, 98.6690), ('Jl. Putri Hijau', 3.5990, 98.6710),
    ('Jl. Sisingamangaraja', 3.5800, 98.6690), ('Jl. Jamin Ginting', 3.6000, 98.6650),
    ('Jl. Selamat', 3.5960, 98.6620), ('Jl. Yos Sudarso', 3.6100, 98.6300),
    ('Jl. Marelan Raya', 3.6200, 98.6100), ('Jl. Tanjung Mulia', 3.6150, 98.6250),
    ('Jl. Setiabudi', 3.6030, 98.6600), ('Jl. Perintis Kemerdekaan', 3.6050, 98.6620),
    ('Jl. Ring Road', 3.6260, 98.6710), ('Jl. TB Simatupang', 3.5700, 98.6800),
    ('Jl. Pancing', 3.5650, 98.6780), ('Jl. Amplas', 3.5550, 98.6750),
    ('Jl. Mangaan', 3.5750, 98.6900), ('Jl. Pasar Baru', 3.5900, 98.6760),
    ('Jl. Mistar', 3.5750, 98.6850), ('Jl. Karya Dalam', 3.5850, 98.6550),
    ('Jl. Glugur', 3.5850, 98.6600), ('Jl. Silau', 3.5800, 98.6500),
    ('Jl. Bunga Rampai', 3.5600, 98.6700), ('Jl. S. Parman', 3.5790, 98.6750),
    ('Jl. Dr. Mansyur', 3.5600, 98.6500), ('Jl. H. Adam Malik', 3.5700, 98.6500),
    ('Jl. Cemara', 3.5600, 98.6750), ('Jl. Tenggiri', 3.5550, 98.6800),
    ('Jl. Medan Deli', 3.6100, 98.6450), ('Jl. Karya Jaya', 3.5950, 98.6600),
    ('Jl. Krakatau', 3.5920, 98.6500), ('Jl. Seram', 3.5900, 98.6550),
    ('Jl. Bogor', 3.5880, 98.6500), ('Jl. Sei Deli', 3.5850, 98.6450),
    ('Jl. K.L. Yos Sudarso', 3.6150, 98.6350), ('Jl. Pertiwi', 3.6100, 98.6150),
]


def fetch_osm():
    for api in APIS:
        try:
            req = urllib.request.Request(api, data=OVERPASS_QUERY.encode(),
                                         headers={'User-Agent': 'pemko-aset-gen/1.0'})
            with urllib.request.urlopen(req, timeout=45) as r:
                data = json.loads(r.read().decode())
            out = []
            seen = set()
            for el in data.get('elements', []):
                c = el.get('center')
                name = el.get('tags', {}).get('name', '').strip()
                if not name or not c or (name, c['lat'], c['lon']) in seen:
                    continue
                seen.add((name, c['lat'], c['lon']))
                out.append({'name': name, 'lat': c['lat'], 'lon': c['lon']})
            if len(out) >= 20:
                return out
        except Exception as e:
            print(f'  {api} gagal: {e}')
    return []


def main():
    if os.path.exists(OUT):
        print(f'cache ada: {OUT} ({len(json.load(open(OUT)))} jalan)')
        return 0
    try:
        addrs = fetch_osm()
        if len(addrs) < 20:
            raise RuntimeError(f'hanya {len(addrs)} jalan dari OSM, pakai fallback')
        print(f'OSM OK: {len(addrs)} jalan real Kota Medan')
    except Exception as e:
        print(f'OSM gagal ({e}); fallback ke daftar kurasi')
        addrs = [{'name': n, 'lat': la, 'lon': lo} for n, la, lo in FALLBACK]
    json.dump(addrs, open(OUT, 'w', encoding='utf-8'), indent=1, ensure_ascii=False)
    print(f'disimpan: {OUT} ({len(addrs)} jalan)')
    return 0


if __name__ == '__main__':
    sys.exit(main())
