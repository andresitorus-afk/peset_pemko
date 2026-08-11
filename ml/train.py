"""Training XGBoost supervised learning untuk menggantikan Gemini API.

3 target (y):
  - y1 jenis_pemanfaatan   : XGBClassifier multiclass (6 kelas) -> dump_model JSON
  - y2 potensi_kontribusi  : XGBRegressor pada log10(Rp)         -> dump_model JSON
  - y3 perkiraan_permintaan: XGBClassifier ordinal (3 kelas)     -> dump_model JSON

Split train/valid/test per aset_id, validasi parsial untuk early stopping.
Self-check: re-score test set dari JSON dump (numpy) vs prediksi xgboost -> harus match.
"""
import json
import os
import sys

import numpy as np
import pandas as pd
import xgboost as xgb
from sklearn.metrics import accuracy_score, f1_score, mean_absolute_error, r2_score
from sklearn.model_selection import GroupShuffleSplit

HERE = os.path.dirname(os.path.abspath(__file__))
DATA = os.path.join(HERE, 'dataset_aset_medan.csv')
OUT = os.path.join(HERE, 'model')

JENIS = ['SEWA', 'PKP', 'KSP', 'BGS', 'BSG', 'KSPI']
PERMINTAAN = ['rendah', 'sedang', 'tinggi']

FEATURES = (['opd_id', 'kategori_kib', 'kondisi', 'status', 'luas_m2', 'umur_aset']
            + [f'poi_{t}_{s}' for t in
               ['kampus', 'sekolah', 'mal', 'pasar', 'rumah_sakit', 'puskesmas', 'stasiun', 'kantor', 'tempat_budaya']
               for s in ('n', 'd')] + ['poi_total'])

PARAMS = dict(objective='multi:softprob', num_class=6, tree_method='hist',
              max_depth=3, eta=0.12, min_child_weight=4, subsample=0.85,
              colsample_bytree=0.8, eval_metric='mlogloss', nthread=4)
PARAMS_REG = dict(objective='reg:squarederror', tree_method='hist',
                  max_depth=3, eta=0.12, min_child_weight=4, subsample=0.85,
                  colsample_bytree=0.8, nthread=4)
PARAMS_ORD = dict(objective='multi:softprob', num_class=3, tree_method='hist',
                  max_depth=3, eta=0.12, min_child_weight=4, subsample=0.85,
                  colsample_bytree=0.8, eval_metric='mlogloss', nthread=4)


def load():
    df = pd.read_csv(DATA)
    X = df[FEATURES]
    y1 = df['y_jenis'].astype(int).values
    y2 = np.log10(df['y_kontribusi'].values)
    y3 = df['y_permintaan'].astype(int).values
    groups = df['aset_id'].values
    return df, X, y1, y2, y3, groups


def sample_weights(y):
    """Bobot inverse-frequency per kelas (rebalans supaya f1-macro naik)."""
    from collections import Counter
    cnt = Counter(y)
    total = len(y)
    return np.array([total / (len(cnt) * cnt[v]) for v in y], dtype=float)


def split(groups, y1):
    gss = GroupShuffleSplit(n_splits=1, test_size=0.2, random_state=42)
    train_idx, test_idx = next(gss.split(np.zeros(len(groups)), y1, groups))
    val_idx, test2_idx = next(GroupShuffleSplit(n_splits=1, test_size=0.5, random_state=7)
                              .split(np.zeros(len(test_idx)), y1[test_idx], groups[test_idx]))
    return (train_idx, val_idx, test_idx[val_idx], test_idx[test2_idx])


def fit_clf(X, y, groups, idx, params, num_rounds=400, weights=None):
    tr, va, te, _ = idx
    dtr = xgb.DMatrix(X.iloc[tr], label=y[tr], weight=None if weights is None else weights[tr])
    dva = xgb.DMatrix(X.iloc[va], label=y[va], weight=None if weights is None else weights[va])
    bst = xgb.train(params, dtr, num_boost_round=num_rounds,
                    evals=[(dva, 'valid')], early_stopping_rounds=25, verbose_eval=False)
    p = bst.predict(xgb.DMatrix(X.iloc[te]))
    return bst, p


def fit_reg(X, y, groups, idx, num_rounds=400):
    tr, va, te, _ = idx
    dtr = xgb.DMatrix(X.iloc[tr], label=y[tr])
    dva = xgb.DMatrix(X.iloc[va], label=y[va])
    bst = xgb.train(PARAMS_REG, dtr, num_boost_round=num_rounds,
                    evals=[(dva, 'valid')], early_stopping_rounds=25, verbose_eval=False)
    return bst, bst.predict(xgb.DMatrix(X.iloc[te]))


# ---- self-check: scor berbasis JSON dump (paritas dengan PHP) ----
def score_tree(node, feats):
    if 'leaf' in node:
        return float(node['leaf'])
    split = node['split']
    feat = split if split in feats else f'f{int(split)}'
    if feats[feat] < float(node['split_condition']):
        return score_tree(node['children'][0], feats)
    return score_tree(node['children'][1], feats)


def base_scores(cfg):
    raw = cfg['learner']['learner_model_param']['base_score']
    vals = [float(x) for x in str(raw).strip('[]').split(',') if x.strip()]
    return vals if len(vals) > 1 else [vals[0]]


def predict_from_dump(dump, feats, base):
    num_class = len(base)
    n_rounds = len(dump) // num_class
    raw = list(base)
    for c in range(num_class):
        for r in range(n_rounds):
            raw[c] += score_tree(dump[r * num_class + c], feats)
    e = np.exp(np.array(raw) - np.max(raw))
    return e / e.sum()


def verify_dump(model, dump, X_test):
    feats = {f: i for i, f in enumerate(FEATURES)}
    cfg = json.loads(model.save_config())
    base = base_scores(cfg)
    n_class = int(cfg['learner']['learner_model_param']['num_class'])
    err = err_cls = 0
    maxdiff = 0.0
    for row in X_test.iloc[:300].itertuples(index=False):
        vals = {f: row[i] for i, f in enumerate(FEATURES)}
        p1 = predict_from_dump(dump, vals, base)
        p2 = model.predict(xgb.DMatrix(pd.DataFrame([vals])[FEATURES]))[0]
        maxdiff = max(maxdiff, float(np.abs(p1 - p2).max()))
        if np.argmax(p1) != np.argmax(p2):
            err_cls += 1
        if not np.allclose(p1, p2, atol=1e-2):
            err += 1
    assert err_cls == 0, f'self-check kelas mismatch: {err_cls} baris'
    print(f'  self-check dump JSON vs xgboost: OK ({len(X_test.iloc[:300])} baris, max|diff| prob={maxdiff:.4f})')


def main():
    os.makedirs(OUT, exist_ok=True)
    df, X, y1, y2, y3, groups = load()
    idx = split(groups, y1)
    tr, va, te, _ = idx
    X_te, y1_te, y2_te, y3_te = X.iloc[te], y1[te], y2[te], y3[te]

    print(f'data: {len(df)} aset | train {len(tr)} | valid {len(va)} | test {len(te)}')
    print('split per aset_id (GroupShuffleSplit)')

    # y1
    w1 = sample_weights(y1)
    bst1, p1 = fit_clf(X, y1, groups, idx, PARAMS, weights=w1)
    y1_pred = p1.argmax(axis=1)
    print(f'\ny1 jenis_pemanfaatan (test): acc {accuracy_score(y1_te, y1_pred):.3f} | f1_macro {f1_score(y1_te, y1_pred, average="macro"):.3f}')
    conf = np.zeros((6, 6), dtype=int)
    for a, b in zip(y1_te, y1_pred):
        conf[a, b] += 1
    print('  confusion (baris=sebenarnya, kolom=prediksi)')
    print('  ' + '  '.join(f'{j:>4}' for j in JENIS))
    for i, r in enumerate(conf):
        print(f'  {JENIS[i]:>4} ' + '  '.join(f'{v:>4}' for v in r))
    dump1 = json.loads(bst1.save_config())
    bst1.dump_model(os.path.join(OUT, 'jenis_pemanfaatan.json'), dump_format='json', with_stats=False)
    verify_dump(bst1, json.load(open(os.path.join(OUT, 'jenis_pemanfaatan.json'))), X_te)

    # y2
    bst2, p2 = fit_reg(X, y2, groups, idx)
    y2_pred = 10 ** p2
    y2_true = 10 ** y2_te
    mae = mean_absolute_error(y2_true, y2_pred)
    print(f'\ny2 potensi_kontribusi (test): MAE Rp {mae:,.0f} | R2(log) {r2_score(y2_te, p2):.3f}')
    bst2.dump_model(os.path.join(OUT, 'potensi_kontribusi.json'), dump_format='json', with_stats=False)

    # y3
    bst3, p3 = fit_clf(X, y3, groups, idx, PARAMS_ORD)
    y3_pred = p3.argmax(axis=1)
    print(f'\ny3 perkiraan_permintaan (test): acc {accuracy_score(y3_te, y3_pred):.3f} | f1_macro {f1_score(y3_te, y3_pred, average="macro"):.3f}')
    bst3.dump_model(os.path.join(OUT, 'perkiraan_permintaan.json'), dump_format='json', with_stats=False)

    meta = {
        'feature_names': FEATURES,
        'features_json': [
            {'name': 'opd_id'}, {'name': 'kategori_kib', 'map': {'KIB A': 0, 'KIB B': 1, 'KIB C': 2, 'KIB D': 3, 'KIB E': 4, 'KIB F': 5}},
            {'name': 'kondisi', 'map': {'Baik': 0, 'Rusak_Ringan': 1, 'Rusak_Berat': 2}},
            {'name': 'status', 'map': {'Aktif': 0, 'Idle': 1, 'Dimanfaatkan': 2}},
        ],
        'poi_types': ['kampus', 'sekolah', 'mal', 'pasar', 'rumah_sakit', 'puskesmas', 'stasiun', 'kantor', 'tempat_budaya'],
        'radius_km': 3.0,
        'jenis': JENIS,
        'permintaan': PERMINTAAN,
        'base_score_y1': base_scores(json.loads(bst1.save_config())),
        'base_score_y2': base_scores(json.loads(bst2.save_config()))[0],
        'base_score_y3': base_scores(json.loads(bst3.save_config())),
        'num_class_y1': 6,
        'num_class_y3': 3,
    }
    with open(os.path.join(OUT, 'meta.json'), 'w') as fh:
        json.dump(meta, fh, indent=2, ensure_ascii=False)
    print('\nexport model -> ml/model/*.json + meta.json')
    print('SELESAI')


if __name__ == '__main__':
    sys.exit(main())
