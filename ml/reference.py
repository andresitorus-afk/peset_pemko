"""Cetak prediksi referensi dari model XGBoost (untuk unit test PHP XgboostScorer).

Menjalankan tree-ensemble dari JSON dump yang sama yang dibaca PHP, jadi nilainya
harus sama persis dengan apa yang dihasilkan XgboostScorer.
"""
import json
import os

import numpy as np

HERE = os.path.dirname(os.path.abspath(__file__))
MODEL = os.path.join(HERE, 'model')

FEATURES = [
    'opd_id', 'kategori_kib', 'kondisi', 'status', 'luas_m2', 'umur_aset',
    'poi_kampus_n', 'poi_kampus_d', 'poi_sekolah_n', 'poi_sekolah_d',
    'poi_mal_n', 'poi_mal_d', 'poi_pasar_n', 'poi_pasar_d',
    'poi_rumah_sakit_n', 'poi_rumah_sakit_d', 'poi_puskesmas_n', 'poi_puskesmas_d',
    'poi_stasiun_n', 'poi_stasiun_d', 'poi_kantor_n', 'poi_kantor_d',
    'poi_tempat_budaya_n', 'poi_tempat_budaya_d', 'poi_total',
]

VECTOR = [3, 0, 0, 1, 2500.0, 20, 0, 5.0, 1, 0.8, 2, 0.9, 1, 1.2, 2, 0.7, 0, 5.0, 0, 5.0, 0, 5.0, 1, 0.5, 6]


def score_tree(node, feats):
    if 'leaf' in node:
        return float(node['leaf'])
    value = feats[node['split']]
    if value is None:
        target = node['missing']
        for child in node['children']:
            if child['nodeid'] == target:
                return score_tree(child, feats)
    if value < float(node['split_condition']):
        return score_tree(node['children'][0], feats)
    return score_tree(node['children'][1], feats)


def predict_from_dump(dump, feats, base):
    num_class = len(base)
    n_rounds = len(dump) // num_class
    raw = list(base)
    for c in range(num_class):
        for r in range(n_rounds):
            raw[c] += score_tree(dump[r * num_class + c], feats)
    e = np.exp(np.array(raw) - np.max(raw))
    return (e / e.sum()).round(6).tolist()


def main():
    feats = dict(zip(FEATURES, VECTOR))
    meta = json.load(open(os.path.join(MODEL, 'meta.json')))
    y1 = predict_from_dump(json.load(open(os.path.join(MODEL, 'jenis_pemanfaatan.json'))), feats, meta['base_score_y1'])
    dump2 = json.load(open(os.path.join(MODEL, 'potensi_kontribusi.json')))
    margin = meta['base_score_y2'] + sum(score_tree(t, feats) for t in dump2)
    y3 = predict_from_dump(json.load(open(os.path.join(MODEL, 'perkiraan_permintaan.json'))), feats, meta['base_score_y3'])
    print('Y1_PROBS =', y1)
    print('Y1_CLASS =', meta['jenis'][int(np.argmax(y1))])
    print('Y2_MARGIN =', round(margin, 6))
    print('Y2_RP =', int(round(10 ** margin)))
    print('Y3_PROBS =', y3)
    print('Y3_CLASS =', meta['permintaan'][int(np.argmax(y3))])


if __name__ == '__main__':
    main()
