<?php

namespace Tests\Unit;

use App\Models\Aset;
use App\Services\GeminiService;
use Tests\TestCase;

class GeminiServiceTest extends TestCase
{
    public function test_jarak_km_menghitung_jarak_benar(): void
    {
        $service = new GeminiService;
        $jarak = $service->jarakKm(3.5900, 98.6750, 3.5630, 98.6568);
        $this->assertEqualsWithDelta(3.6, $jarak, 0.2);
    }

    public function test_parse_json_menghilangkan_code_fence(): void
    {
        $service = new GeminiService;
        $raw = "```json\n{\"jenis_pemanfaatan\":\"SEWA\",\"ide_utama\":\"Laundry\"}\n```";
        $this->assertSame(['jenis_pemanfaatan' => 'SEWA', 'ide_utama' => 'Laundry'], $service->parseJson($raw));
    }

    public function test_prompt_tanpa_poi_mencantumkan_penanda_tidak_ada(): void
    {
        $service = new GeminiService;
        $aset = new Aset([
            'nama_barang' => 'Tanah Uji',
            'kategori_id' => null,
            'luas' => 100,
            'kondisi' => 'Baik',
            'status' => 'Idle',
            'alamat' => 'Jl. Uji No.1',
            'keterangan' => null,
        ]);
        $prompt = $service->buildPrompt($aset, []);
        $this->assertStringContainsString('Tidak ada POI terdekat', $prompt);
    }

    public function test_validate_hasil_menolak_struktur_salah(): void
    {
        $service = new GeminiService;
        $this->expectException(\RuntimeException::class);
        $service->validateHasil(['foo' => 'bar']);
    }
}
