# Rekomendasi Pemanfaatan Aset dengan AI (Gemini) — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Menambahkan fitur AI (Gemini) yang merekomendasikan pemanfaatan aset daerah (ide usaha spesifik + alasan berbasis POI terdekat) di admin aset dan modal detail publik.

**Architecture:** Laravel 13 API-only menampung semua logika: hitung jarak haversine ke POI dalam PHP, bangun prompt, panggil Gemini API via cURL, simpan hasil ke tabel `rekomendasi_ai`. Nuxt 3 hanya menampilkan: admin memicu generate, publik membaca hasil terakhir dari DB (tanpa biaya Gemini).

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL 15 (test: sqlite :memory:), Nuxt 3, Google Gemini API (`gemini-2.0-flash`).

## Global Constraints

- Key Gemini hanya di `.env` (`GEMINI_API_KEY`), tidak pernah dikirim ke frontend.
- Jarak dihitung backend dengan **haversine dalam PHP** (bukan SQL — sqlite test tidak punya `acos`/`sin`/`cos`).
- Alasan Gemini wajib merujuk data yang dikirim; prompt menyebutkan larangan mengarang.
- Endpoint admin memakai `auth:sanctum`; endpoint publik tanpa auth.
- Bahasa UI & prompt: Indonesia.

---

### Task 1: Migrasi, model, seeder, config

**Files:**
- Create: `backend/database/migrations/2026_08_02_000001_create_poi_table.php`
- Create: `backend/database/migrations/2026_08_02_000002_create_rekomendasi_ai_table.php`
- Create: `backend/app/Models/Poi.php`
- Create: `backend/app/Models/RekomendasiAi.php`
- Create: `backend/database/seeders/PoiSeeder.php`
- Modify: `backend/database/seeders/DatabaseSeeder.php` (register `PoiSeeder`)
- Modify: `backend/config/services.php` (blok `gemini`)
- Modify: `backend/.env.example` (tambah `GEMINI_API_KEY=`)

**Interfaces:**
- Produces: model `App\Models\Poi` (kolom: `id` uuid, `nama`, `tipe`, `alamat`, `latitude`, `longitude`, `aktif`).
- Produces: model `App\Models\RekomendasiAi` (kolom: `id` uuid, `aset_id`, `hasil` array-cast, `status`, `error`, `created_by`; `hasil` di-cast ke array).
- Produces: `config('services.gemini.key')`, `config('services.gemini.model')`.

- [ ] **Step 1: Tulis migration `poi`**

`backend/database/migrations/2026_08_02_000001_create_poi_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poi', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('nama');
            $table->string('tipe')->comment('kampus, sekolah, mal, pasar, rumah_sakit, puskesmas, kantor, perumahan, stasiun, lainnya');
            $table->string('alamat')->nullable();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poi');
    }
};
```

- [ ] **Step 2: Tulis migration `rekomendasi_ai`**

`backend/database/migrations/2026_08_02_000002_create_rekomendasi_ai_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rekomendasi_ai', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('aset_id')->constrained('aset')->cascadeOnUpdate()->cascadeOnDelete();
            $table->jsonb('hasil')->nullable();
            $table->string('status')->default('sukses')->comment('sukses | gagal');
            $table->text('error')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekomendasi_ai');
    }
};
```

- [ ] **Step 3: Tulis model `Poi`**

`backend/app/Models/Poi.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Poi extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'poi';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = ['nama', 'tipe', 'alamat', 'latitude', 'longitude', 'aktif'];
}
```

- [ ] **Step 4: Tulis model `RekomendasiAi`**

`backend/app/Models/RekomendasiAi.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RekomendasiAi extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'rekomendasi_ai';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = ['aset_id', 'hasil', 'status', 'error', 'created_by'];

    protected $casts = ['hasil' => 'array'];

    public function aset(): BelongsTo { return $this->belongsTo(Aset::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
```

- [ ] **Step 5: Tulis `PoiSeeder`**

`backend/database/seeders/PoiSeeder.php`:

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PoiSeeder extends Seeder
{
    public function run(): void
    {
        $pois = [
            ['USU', 'kampus', 3.5630, 98.6568, 'Jl. Dr. A. Sofyan, Padang Bulan'],
            ['UMSU', 'kampus', 3.5899, 98.6779, 'Jl. Kapten Muktar Basri No.3'],
            ['UNIMED', 'kampus', 3.6060, 98.6790, 'Jl. Willem Iskandar Psr V'],
            ['Politeknik Negeri Medan', 'kampus', 3.5956, 98.6883, 'Jl. Medan Tenggara No.2'],
            ['UIN Sumatera Utara', 'kampus', 3.6320, 98.6810, 'Jl. Lapangan Golf No.120'],
            ['Mal Medan Fair', 'mal', 3.5830, 98.6750, 'Jl. Kapten Maulana Lubis No.8'],
            ['Sun Plaza', 'mal', 3.5816, 98.6810, 'Jl. Zainul Arifin No.7'],
            ['Cambridge City Square', 'mal', 3.5874, 98.6790, 'Jl. S. Parman No.217'],
            ['Pasar Padang Bulan', 'pasar', 3.5549, 98.6637, 'Jl. Pendidikan'],
            ['Pasar Petisah', 'pasar', 3.5930, 98.6710, 'Jl. Haji Adam Malik'],
            ['RSUD Pirngadi Medan', 'rumah_sakit', 3.5850, 98.6720, 'Jl. Brigjen Katamso No.1'],
            ['RS Haji Medan', 'rumah_sakit', 3.6070, 98.6840, 'Jl. Rumah Sakit Haji No.2'],
            ['Stasiun Medan', 'stasiun', 3.5900, 98.6770, 'Jl. Stasiun Kereta Api'],
            ['Kantor Walikota Medan', 'kantor', 3.5940, 98.6750, 'Jl. Kapten Maulana Lubis No.2'],
        ];

        foreach ($pois as [$nama, $tipe, $lat, $lon, $alamat]) {
            DB::table('poi')->insert([
                'id' => (string) Str::uuid(),
                'nama' => $nama,
                'tipe' => $tipe,
                'alamat' => $alamat,
                'latitude' => $lat,
                'longitude' => $lon,
                'aktif' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
```

- [ ] **Step 6: Daftarkan seeder di `DatabaseSeeder`**

`backend/database/seeders/DatabaseSeeder.php` — di method `run()`, tepat setelah `$this->call(KategoriAsetSeeder::class);` tambahkan:

```php
        $this->call(PoiSeeder::class);
```

- [ ] **Step 7: Tambah config gemini**

`backend/config/services.php` — sebelum baris penutup `];`, tambahkan:

```php
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    ],
```

- [ ] **Step 8: Tambah env key**

`backend/.env.example` — tambahkan di akhir file:

```
GEMINI_API_KEY=
GEMINI_MODEL=gemini-2.0-flash
```

(untuk `.env` lokal, tambahkan key yang sama supaya runtime terbaca)

- [ ] **Step 9: Verifikasi migrasi & seeder jalan**

Run: `php artisan migrate:fresh --seed`
Expected: 17 migrasi jalan tanpa error, tabel `poi` berisi 14 baris, tabel `rekomendasi_ai` kosong.

- [ ] **Step 10: Commit**

```bash
git add backend/database/migrations/2026_08_02_000001_create_poi_table.php backend/database/migrations/2026_08_02_000002_create_rekomendasi_ai_table.php backend/app/Models/Poi.php backend/app/Models/RekomendasiAi.php backend/database/seeders/PoiSeeder.php backend/database/seeders/DatabaseSeeder.php backend/config/services.php backend/.env.example
git commit -m "feat: tabel poi & rekomendasi_ai, seeder POI Medan, config gemini"
```

---

### Task 2: `GeminiService` (prompt, call API, parse JSON)

**Files:**
- Create: `backend/app/Services/GeminiService.php`
- Test: `backend/tests/Unit/GeminiServiceTest.php`

**Interfaces:**
- Produces: `GeminiService::jarakKm(float $lat1, float $lon1, float $lat2, float $lon2): float`
- Produces: `GeminiService::nearbyPois(?float $lat, ?float $lon, int $radiusKm = 3, int $limit = 5): array`
- Produces: `GeminiService::buildPrompt(Aset $aset, array $pois): string`
- Produces: `GeminiService::generate(string $prompt): string` (throws `RuntimeException` saat gagal)
- Produces: `GeminiService::parseJson(string $text): array` (throws `RuntimeException` saat bukan JSON valid)

- [ ] **Step 1: Tulis test yang gagal**

`backend/tests/Unit/GeminiServiceTest.php`:

```php
<?php

namespace Tests\Unit;

use App\Models\Aset;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeminiServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_jarak_km_menghitung_jarak_benar(): void
    {
        $service = new GeminiService;
        $jarak = $service->jarakKm(3.5900, 98.6750, 3.5630, 98.6568);
        $this->assertEqualsWithDelta(3.6, $jarak, 0.2);
    }

    public function test_nearby_pois_memfilter_jarak(): void
    {
        $this->seed(\Database\Seeders\PoiSeeder::class);
        $service = new GeminiService;
        $pois = $service->nearbyPois(3.5900, 98.6750);
        $this->assertNotEmpty($pois);
        foreach ($pois as $p) {
            $this->assertLessThanOrEqual(3.0, $p['jarak']);
        }
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
}
```

Catatan: `nearby_pois_memfilter_jarak` butuh data `poi` di DB test. Trait `RefreshDatabase` membuat DB sqlite :memory: lalu `$this->seed(PoiSeeder::class)` mengisinya — cukup untuk test itu; test lain tidak tersentuh DB.

- [ ] **Step 2: Jalankan test, pastikan gagal**

Run: `php artisan test --filter=GeminiServiceTest`
Expected: FAIL — class `App\Services\GeminiService` tidak ada.

- [ ] **Step 3: Tulis `GeminiService`**

`backend/app/Services/GeminiService.php`:

```php
<?php

namespace App\Services;

use App\Models\Aset;
use App\Models\Poi;
use RuntimeException;

class GeminiService
{
    protected string $apiKey;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = (string) config('services.gemini.key');
        $this->model = (string) config('services.gemini.model', 'gemini-2.0-flash');
    }

    public function jarakKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $r = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return 2 * $r * asin(sqrt($a));
    }

    public function nearbyPois(?float $lat, ?float $lon, int $radiusKm = 3, int $limit = 5): array
    {
        if ($lat === null || $lon === null) {
            return [];
        }
        return Poi::query()
            ->where('aktif', true)
            ->get()
            ->map(fn (Poi $p) => [
                'nama' => $p->nama,
                'tipe' => $p->tipe,
                'jarak' => round($this->jarakKm($lat, $lon, (float) $p->latitude, (float) $p->longitude), 2),
            ])
            ->filter(fn (array $p) => $p['jarak'] <= $radiusKm)
            ->sortBy('jarak')
            ->take($limit)
            ->values()
            ->all();
    }

    public function buildPrompt(Aset $aset, array $pois): string
    {
        $kib = ($aset->kategori?->kode_kib ?? '-') . ' (' . ($aset->kategori?->nama_kategori ?? '-') . ')';
        $poiLines = $pois
            ? implode("\n", array_map(fn ($p) => "- {$p['nama']} ({$p['tipe']}) — {$p['jarak']} km", $pois))
            : '- Tidak ada POI terdekat.';

        return <<<PROMPT
Kamu asisten rekomendasi pemanfaatan aset daerah. Berikan rekomendasi paling detail dan spesifik.

DATA ASET:
- Nama: {$aset->nama_barang}
- Kategori: {$kib}
- Luas: {$aset->luas} m²
- Kondisi: {$aset->kondisi} | Status: {$aset->status}
- Alamat: {$aset->alamat ?: '-'}
- Keterangan: {$aset->keterangan ?: '-'}

POI TERDEKAT (hasil pengukuran backend, bukan tebakan):
{$poiLines}

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
  "alasan": ["alasan berbasis data aset & POI"],
  "alternatif": [{"ide": "...", "alasan": "..."}],
  "perkiraan_permintaan": "rendah/sedang/tinggi + penjelasan",
  "potensi_kontribusi": "estimasi dalam Rp, beri rentang",
  "catatan_legal": "persyaratan/dasar hukum"
}

Alasan wajib merujuk data yang dikirim (POI/jarak/luas/kondisi), dilarang mengarang.
PROMPT;
    }

    public function generate(string $prompt): string
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('GEMINI_API_KEY belum diatur di .env');
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";
        $payload = json_encode([
            'contents' => [['parts' => [['text' => $prompt]]]],
            'generationConfig' => ['temperature' => 0.4],
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 45,
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException('Gagal terhubung ke Gemini');
        }

        $data = json_decode((string) $response, true);
        if ($httpCode >= 400) {
            throw new RuntimeException($data['error']['message'] ?? "Gemini error HTTP {$httpCode}");
        }

        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if ($text === '') {
            throw new RuntimeException('Gemini mengembalikan respons kosong');
        }

        return $text;
    }

    public function parseJson(string $text): array
    {
        $clean = trim($text);
        $clean = preg_replace('/^```(?:json)?\s*/i', '', $clean) ?? $clean;
        $clean = preg_replace('/\s*```$/', '', $clean) ?? $clean;
        $data = json_decode(trim($clean), true);

        if (!is_array($data)) {
            $start = strpos($clean, '{');
            $end = strrpos($clean, '}');
            if ($start !== false && $end !== false) {
                $data = json_decode(substr($clean, $start, $end - $start + 1), true);
            }
        }

        if (!is_array($data)) {
            throw new RuntimeException('Respons Gemini bukan JSON valid');
        }

        return $data;
    }
}
```

- [ ] **Step 4: Jalankan test, pastikan lulus**

Run: `php artisan test --filter=GeminiServiceTest`
Expected: 4 test PASS.

- [ ] **Step 5: Commit**

```bash
git add backend/app/Services/GeminiService.php backend/tests/Unit/GeminiServiceTest.php
git commit -m "feat: GeminiService untuk rekomendasi pemanfaatan aset"
```

---

### Task 3: `PoiController` (admin CRUD) + routes

**Files:**
- Create: `backend/app/Http/Controllers/Api/PoiController.php`
- Modify: `backend/routes/api.php`

**Interfaces:**
- Consumes: `App\Models\Poi` (Task 1).
- Produces: route `Route::apiResource('poi', PoiController::class)`.

- [ ] **Step 1: Tulis `PoiController`**

`backend/app/Http/Controllers/Api/PoiController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Poi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PoiController extends Controller
{
    public function index(Request $request)
    {
        return Poi::query()
            ->when($request->search, fn ($q, $s) => $q->where('nama', 'ilike', "%{$s}%"))
            ->orderBy('nama')
            ->paginate($request->get('per_page', 15));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'tipe' => 'required|string|in:kampus,sekolah,mal,pasar,rumah_sakit,puskesmas,kantor,perumahan,stasiun,lainnya',
            'alamat' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'aktif' => 'nullable|boolean',
        ]);

        $poi = Poi::create($validated);
        return response()->json($poi, 201);
    }

    public function show(Poi $poi): JsonResponse
    {
        return response()->json($poi);
    }

    public function update(Request $request, Poi $poi): JsonResponse
    {
        $validated = $request->validate([
            'nama' => 'sometimes|required|string|max:255',
            'tipe' => 'sometimes|required|string|in:kampus,sekolah,mal,pasar,rumah_sakit,puskesmas,kantor,perumahan,stasiun,lainnya',
            'alamat' => 'nullable|string|max:255',
            'latitude' => 'sometimes|required|numeric|between:-90,90',
            'longitude' => 'sometimes|required|numeric|between:-180,180',
            'aktif' => 'nullable|boolean',
        ]);

        $poi->update($validated);
        return response()->json($poi);
    }

    public function destroy(Poi $poi): JsonResponse
    {
        $poi->delete();
        return response()->json(['message' => 'Berhasil dihapus.']);
    }
}
```

- [ ] **Step 2: Daftarkan route**

`backend/routes/api.php` — tambahkan `use App\Http\Controllers\Api\PoiController;` di blok import (setelah `RoleEmailDomainController`), lalu di grup `auth:sanctum` setelah `pihak-ketiga` (baris 42):

```php
    Route::apiResource('poi', PoiController::class);
```

- [ ] **Step 3: Verifikasi**

Run: `php artisan route:list --path=poi`
Expected: daftar `GET/POST/PUT/DELETE /api/poi` (dengan middleware `auth:sanctum`).

- [ ] **Step 4: Commit**

```bash
git add backend/app/Http/Controllers/Api/PoiController.php backend/routes/api.php
git commit -m "feat: CRUD POI di API admin"
```

---

### Task 4: `RekomendasiAiController` + endpoint publik

**Files:**
- Create: `backend/app/Http/Controllers/Api/RekomendasiAiController.php`
- Modify: `backend/app/Http/Controllers/Api/PublicController.php`
- Modify: `backend/routes/api.php`

**Interfaces:**
- Consumes: `GeminiService` (Task 2), `App\Models\Aset`, `App\Models\RekomendasiAi` (Task 1).
- Produces:
  - `POST /api/rekomendasi-ai/{aset}` → `{ "data": {...} }` (200) atau `{ "message": "Rekomendasi gagal: ..." }` (502).
  - `GET /api/rekomendasi-ai/{aset}` → `{ "data": [ ... ] }` riwayat desc.
  - `GET /api/public/aset/{id}/rekomendasi` → `{ "data": {...} | null }`.

- [ ] **Step 1: Tulis `RekomendasiAiController`**

`backend/app/Http/Controllers/Api/RekomendasiAiController.php`:

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\RekomendasiAi;
use App\Services\GeminiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class RekomendasiAiController extends Controller
{
    public function __construct(private readonly GeminiService $gemini)
    {
    }

    public function store(Request $request, Aset $aset): JsonResponse
    {
        $aset->load(['kategori', 'gisAset']);
        $pois = $this->gemini->nearbyPois(
            isset($aset->gisAset->latitude) ? (float) $aset->gisAset->latitude : null,
            isset($aset->gisAset->longitude) ? (float) $aset->gisAset->longitude : null
        );

        try {
            $text = $this->gemini->generate($this->gemini->buildPrompt($aset, $pois));
            $hasil = $this->gemini->parseJson($text);
            RekomendasiAi::create([
                'aset_id' => $aset->id,
                'hasil' => $hasil,
                'status' => 'sukses',
                'created_by' => $request->user()?->id,
            ]);
            return response()->json(['data' => $hasil]);
        } catch (Throwable $e) {
            RekomendasiAi::create([
                'aset_id' => $aset->id,
                'status' => 'gagal',
                'error' => $e->getMessage(),
                'created_by' => $request->user()?->id,
            ]);
            return response()->json(['message' => 'Rekomendasi gagal: ' . $e->getMessage()], 502);
        }
    }

    public function index(Aset $aset): JsonResponse
    {
        $items = RekomendasiAi::where('aset_id', $aset->id)
            ->orderByDesc('created_at')
            ->get(['id', 'hasil', 'status', 'error', 'created_at']);

        return response()->json(['data' => $items]);
    }
}
```

- [ ] **Step 2: Tambah endpoint publik di `PublicController`**

`backend/app/Http/Controllers/Api/PublicController.php` — tambahkan import `App\Models\RekomendasiAi` di bagian `use`, lalu method berikut:

```php
    public function rekomendasi(string $id): JsonResponse
    {
        $aset = Aset::findOrFail($id);

        $rekomendasi = RekomendasiAi::where('aset_id', $aset->id)
            ->where('status', 'sukses')
            ->orderByDesc('created_at')
            ->first();

        return response()->json(['data' => $rekomendasi?->hasil]);
    }
```

- [ ] **Step 3: Daftarkan route**

`backend/routes/api.php`:
- Tambah `use App\Http\Controllers\Api\RekomendasiAiController;` di blok import.
- Di section publik (setelah baris 25), tambah:

```php
Route::get('/public/aset/{id}/rekomendasi', [\App\Http\Controllers\Api\PublicController::class, 'rekomendasi']);
```

- Di grup `auth:sanctum`, setelah route `riwayat-aset` (baris 60), tambah:

```php
    Route::post('/rekomendasi-ai/{aset}', [RekomendasiAiController::class, 'store']);
    Route::get('/rekomendasi-ai/{aset}', [RekomendasiAiController::class, 'index']);
```

- [ ] **Step 4: Verifikasi route**

Run: `php artisan route:list --path=rekomendasi`
Expected: 3 route muncul: 2 auth + 1 publik.

- [ ] **Step 5: Verifikasi sintaks**

Run: `php -l app/Http/Controllers/Api/RekomendasiAiController.php && php -l app/Http/Controllers/Api/PublicController.php`
Expected: "No syntax errors detected".

- [ ] **Step 6: Commit**

```bash
git add backend/app/Http/Controllers/Api/RekomendasiAiController.php backend/app/Http/Controllers/Api/PublicController.php backend/routes/api.php
git commit -m "feat: endpoint rekomendasi AI (generate + riwayat + publik)"
```

---

### Task 5: Frontend admin — tombol & modal rekomendasi

**Files:**
- Modify: `frontend/pages/admin/aset.vue`

**Interfaces:**
- Consumes: `POST /rekomendasi-ai/{id}` → `{ data: { jenis_pemanfaatan, ide_utama, alasan[], alternatif[], perkiraan_permintaan, potensi_kontribusi, catatan_legal } }`; `GET /rekomendasi-ai/{id}` → `{ data: [...] }` via `useApi()`.

- [ ] **Step 1: Tambah kolom tombol di tabel**

Di `frontend/pages/admin/aset.vue` template `<AdminDataTable>`, tambah slot setelah `cell-opd.nama_opd`:

```html
      <template #cell-ai="{ row }">
        <button type="button" class="px-3 py-1.5 rounded-lg bg-violet-50 text-violet-700 text-xs font-semibold hover:bg-violet-100 transition-colors"
          @click="openRekomendasi(row)">
          <span v-if="aiLoadingId === row.id">Menunggu AI...</span>
          <span v-else>Rekomendasi AI</span>
        </button>
      </template>
```

Dan di array `columns` (sekitar baris 238), tambah di akhir:

```ts
  { key: 'ai', label: 'AI' },
```

- [ ] **Step 2: Tambah state & fungsi rekomendasi**

Di `<script setup>` `frontend/pages/admin/aset.vue`, setelah `const deletingItem = ref<any | null>(null)` tambahkan:

```ts
const aiLoadingId = ref<string | null>(null)
const aiModal = ref(false)
const aiHasil = ref<any | null>(null)
const aiError = ref('')

async function openRekomendasi(row: any) {
  aiLoadingId.value = row.id
  aiError.value = ''
  aiHasil.value = null
  try {
    const res = await api.post(`/rekomendasi-ai/${row.id}`)
    aiHasil.value = res.data
  } catch (e: any) {
    aiError.value = e.message
    toast.show('Gagal memuat rekomendasi: ' + e.message, 'error')
  } finally {
    aiLoadingId.value = null
  }
  if (aiHasil.value || aiError.value) aiModal.value = true
}
```

- [ ] **Step 3: Tambah modal hasil**

Di template, tepat sebelum penutup `</div>` terakhir (setelah `UiModal` hapus aset, baris 144), tambah:

```html
    <UiModal :show="aiModal" title="Rekomendasi AI Pemanfaatan" @close="aiModal = false">
      <div v-if="aiHasil" class="space-y-5">
        <div class="flex items-start justify-between gap-3">
          <p class="text-lg font-bold text-slate-900 leading-snug">{{ aiHasil.ide_utama }}</p>
          <span class="flex-shrink-0 px-3 py-1 rounded-full bg-teal-100 text-teal-700 text-xs font-bold">{{ aiHasil.jenis_pemanfaatan }}</span>
        </div>
        <div>
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Alasan</p>
          <ul class="space-y-2">
            <li v-for="(a, i) in aiHasil.alasan" :key="i" class="flex gap-2 text-sm text-slate-700">
              <span class="text-teal-600 mt-0.5">•</span><span>{{ a }}</span>
            </li>
          </ul>
        </div>
        <div v-if="aiHasil.alternatif && aiHasil.alternatif.length">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Alternatif</p>
          <div class="space-y-2">
            <div v-for="(alt, i) in aiHasil.alternatif" :key="i" class="bg-slate-50 rounded-lg p-3">
              <p class="text-sm font-semibold text-slate-800">{{ alt.ide }}</p>
              <p class="text-xs text-slate-500 mt-1">{{ alt.alasan }}</p>
            </div>
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div class="bg-slate-50 rounded-lg p-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Perkiraan Permintaan</p>
            <p class="text-sm text-slate-700 mt-1">{{ aiHasil.perkiraan_permintaan }}</p>
          </div>
          <div class="bg-slate-50 rounded-lg p-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Potensi Kontribusi</p>
            <p class="text-sm text-slate-700 mt-1">{{ aiHasil.potensi_kontribusi }}</p>
          </div>
        </div>
        <div v-if="aiHasil.catatan_legal">
          <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Catatan Legal</p>
          <p class="text-sm text-slate-600 bg-amber-50 rounded-lg p-3">{{ aiHasil.catatan_legal }}</p>
        </div>
        <p class="text-[11px] text-slate-400">Dihasilkan oleh AI (Google Gemini). Wajib diverifikasi pejabat sebelum diputuskan.</p>
      </div>
      <p v-else-if="aiError" class="text-sm text-red-600">{{ aiError }}</p>
      <p v-else class="text-sm text-slate-500">Memproses...</p>
    </UiModal>
```

- [ ] **Step 4: Verifikasi build frontend**

Run: `npm run build` (di `frontend/`)
Expected: build selesai tanpa error tipe/SFC.

- [ ] **Step 5: Commit**

```bash
git add frontend/pages/admin/aset.vue
git commit -m "feat: tombol & modal rekomendasi AI di halaman admin aset"
```

---

### Task 6: Frontend publik — section rekomendasi di modal detail

**Files:**
- Modify: `frontend/pages/index.vue`

**Interfaces:**
- Consumes: `GET /public/aset/{id}/rekomendasi` → `{ data: {...} | null }` via fetch langsung ke `apiBase`.

- [ ] **Step 1: Tambah state rekomendasi**

Di `<script setup>` `frontend/pages/index.vue`, setelah `const detailGis = ref<any | null>(null)` (sekitar baris 475), tambah:

```ts
const detailRekomendasi = ref<any | null>(null)
```

- [ ] **Step 2: Muat rekomendasi saat modal detail dibuka**

Cari fungsi `openDetail` di script (di bagian bawah file, sekitar baris 600-an). Di dalamnya, setelah data detail dimuat, tambahkan pemanggilan rekomendasi. Jika `openDetail` tidak ada — pakai pendekatan berikut: cari baris tempat `detailItem.value =` diisi di `openDetail`, lalu tambahkan di akhir fungsi `openDetail`:

```ts
  detailRekomendasi.value = null
  try {
    const res = await fetch(`${apiBase}/api/public/aset/${detailItem.value.id}/rekomendasi`)
    const json = await res.json()
    detailRekomendasi.value = json.data || null
  } catch { detailRekomendasi.value = null }
```

(Buka dulu fungsi `openDetail` yang ada untuk mencocokkan posisi sisip — lihat baris 500–651 file. Sisipkan `try/fetch` ini tepat setelah `detailGis` dan data pemanfaatan di-set di `openDetail`.)

- [ ] **Step 3: Tambah section di modal detail**

Di template modal detail, tepat setelah blok `v-if="detailPemanfaatan && detailPemanfaatan.length"` (baris 417–429) dan sebelum tombol WhatsApp/Email (baris 431), tambah:

```html
          <div v-if="detailRekomendasi" class="mt-6 border-t border-slate-200 pt-6">
            <p class="text-sm font-bold text-slate-900 mb-3">Rekomendasi Pemanfaatan (AI)</p>
            <div class="rounded-xl bg-violet-50 border border-violet-100 p-4">
              <div class="flex items-start justify-between gap-3">
                <p class="text-base font-bold text-slate-900 leading-snug">{{ detailRekomendasi.ide_utama }}</p>
                <span class="flex-shrink-0 px-2.5 py-1 rounded-full bg-teal-100 text-teal-700 text-xs font-bold">{{ detailRekomendasi.jenis_pemanfaatan }}</span>
              </div>
              <ul class="mt-3 space-y-2">
                <li v-for="(a, i) in detailRekomendasi.alasan" :key="i" class="flex gap-2 text-sm text-slate-700">
                  <span class="text-violet-600 mt-0.5">•</span><span>{{ a }}</span>
                </li>
              </ul>
              <div v-if="detailRekomendasi.alternatif && detailRekomendasi.alternatif.length" class="mt-3 space-y-2">
                <div v-for="(alt, i) in detailRekomendasi.alternatif" :key="'alt' + i" class="bg-white rounded-lg p-3 border border-violet-100">
                  <p class="text-sm font-semibold text-slate-800">{{ alt.ide }}</p>
                  <p class="text-xs text-slate-500 mt-1">{{ alt.alasan }}</p>
                </div>
              </div>
              <p class="text-[11px] text-slate-400 mt-3">Rekomendasi otomatis dari AI sebagai bahan pertimbangan.</p>
            </div>
          </div>
```

- [ ] **Step 4: Verifikasi build frontend**

Run: `npm run build` (di `frontend/`)
Expected: build selesai tanpa error.

- [ ] **Step 5: Commit**

```bash
git add frontend/pages/index.vue
git commit -m "feat: rekomendasi AI di modal detail aset halaman publik"
```

---

## Self-Review

- **Spec coverage:** Semua item spec terpetakan — tabel `poi`+`rekomendasi_ai` (Task 1), `GeminiService` (Task 2), `PoiController` (Task 3), generate+riwayat+publik (Task 4), admin (Task 5), publik detail modal (Task 6). YAGNI: halaman UI CRUD POI tidak dibuat (di luar desain; POI cukup dari seeder, tambah via API bila perlu).
- **Placeholder scan:** Tidak ada TBD/TODO; semua langkah berisi kode lengkap. Satu langkah (Task 6 Step 2) menyebut "buka fungsi openDetail untuk mencocokkan posisi sisip" karena posisi pastinya tergantung baris di file — ini instruksi konkret, bukan placeholder.
- **Type consistency:** `GeminiService::nearbyPois`, `buildPrompt`, `generate`, `parseJson` dipakai konsisten di controller. Kolom `hasil` di-cast `array` → dipakai sebagai `$rekomendasi?->hasil` (array) di endpoint publik. Field output Gemini (`jenis_pemanfaatan`, `ide_utama`, `alasan`, `alternatif`, `perkiraan_permintaan`, `potensi_kontribusi`, `catatan_legal`) dipakai identik di kedua halaman frontend.
