<?php

namespace Tests\Unit;

use App\Services\XgboostScorer;
use Tests\TestCase;

class XgboostScorerTest extends TestCase
{
    private function modelDir(): string
    {
        return (string) storage_path('app/ml');
    }

    private function meta(): array
    {
        $raw = json_decode((string) file_get_contents($this->modelDir() . '/meta.json'), true);
        $this->assertIsArray($raw);
        return $raw;
    }

    private function features(): array
    {
        // vektor tetap, referensi dari ml/reference.py
        return [
            'opd_id' => 3, 'kategori_kib' => 0, 'kondisi' => 0, 'status' => 1,
            'luas_m2' => 2500.0, 'umur_aset' => 20,
            'poi_kampus_n' => 0, 'poi_kampus_d' => 5.0,
            'poi_sekolah_n' => 1, 'poi_sekolah_d' => 0.8,
            'poi_mal_n' => 2, 'poi_mal_d' => 0.9,
            'poi_pasar_n' => 1, 'poi_pasar_d' => 1.2,
            'poi_rumah_sakit_n' => 2, 'poi_rumah_sakit_d' => 0.7,
            'poi_puskesmas_n' => 0, 'poi_puskesmas_d' => 5.0,
            'poi_stasiun_n' => 0, 'poi_stasiun_d' => 5.0,
            'poi_kantor_n' => 0, 'poi_kantor_d' => 5.0,
            'poi_tempat_budaya_n' => 1, 'poi_tempat_budaya_d' => 0.5,
            'poi_total' => 6,
        ];
    }

    public function test_y1_matches_python_reference(): void
    {
        $meta = $this->meta();
        $scorer = XgboostScorer::fromMeta(
            $this->modelDir() . '/jenis_pemanfaatan.json',
            $meta['base_score_y1'],
            $meta['num_class_y1']
        );

        $probs = $scorer->predict($this->features());

        $this->assertCount(6, $probs);
        $this->assertEqualsWithDelta(0.022415, $probs[0], 0.01, 'prob SEWA');
        $this->assertEqualsWithDelta(0.800599, $probs[2], 0.01, 'prob KSP');
        $this->assertSame('KSP', $meta['jenis'][array_search(max($probs), $probs)]);
    }

    public function test_y2_regression_margin_matches_python(): void
    {
        $meta = $this->meta();
        $scorer = XgboostScorer::fromMeta(
            $this->modelDir() . '/potensi_kontribusi.json',
            $meta['base_score_y2'],
            1
        );

        $margin = $scorer->raw($this->features());

        $this->assertEqualsWithDelta(7.267099, $margin, 0.01, 'margin log10(Rp)');
        $this->assertEqualsWithDelta(18496903, 10 ** $margin, 100000, 'prediksi Rp');
    }

    public function test_y3_matches_python_reference(): void
    {
        $meta = $this->meta();
        $scorer = XgboostScorer::fromMeta(
            $this->modelDir() . '/perkiraan_permintaan.json',
            $meta['base_score_y3'],
            $meta['num_class_y3']
        );

        $probs = $scorer->predict($this->features());

        $this->assertCount(3, $probs);
        $this->assertEqualsWithDelta(0.003696, $probs[0], 0.01, 'prob rendah');
        $this->assertSame('sedang', $meta['permintaan'][array_search(max($probs), $probs)]);
    }
}
