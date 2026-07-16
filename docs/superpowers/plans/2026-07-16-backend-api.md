# Backend API Pemanfaatan Aset Pemko Medan - Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build REST API backend with CRUD, dashboard stats, and GIS endpoints for Medan city asset utilization system.

**Architecture:** Laravel 13 + Sanctum + PostgreSQL. Standard Resource Controllers with Eloquent models, API Resources for JSON transformation, token-based auth.

**Tech Stack:** PHP 8.3, Laravel 13, Sanctum, PostgreSQL 15, Eloquent UUID

## Global Constraints

- UUID primary keys on all custom tables (not `users`)
- `pemanfaatan.created_by` and `riwayat_aset.user_id` use `foreignId()` (bigint) to match `users.id`
- Sanctum token auth for all endpoints except login/register
- Default pagination: 15 items, configurable via `?per_page=`
- All list endpoints return `data`, `links`, `meta` structure
- GeoJSON output for GIS endpoints

---

### Task 1: Models - Reference Tables

**Files:**
- Create: `backend/app/Models/Opd.php`
- Create: `backend/app/Models/KategoriAset.php`
- Create: `backend/app/Models/GisLayer.php`
- Create: `backend/app/Models/JenisPemanfaatan.php`
- Create: `backend/app/Models/PihakKetiga.php`

- [ ] **Step 1: Create Opd model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opd extends Model
{
    use HasUuid;

    protected $table = 'opd';

    protected $fillable = [
        'kode_opd', 'nama_opd', 'alamat', 'telepon', 'kepala_opd', 'nip_kepala',
    ];

    public function aset(): HasMany
    {
        return $this->hasMany(Aset::class);
    }
}
```

- [ ] **Step 2: Create KategoriAset model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriAset extends Model
{
    use HasUuid;

    protected $table = 'kategori_aset';

    protected $fillable = ['kode_kib', 'nama_kategori', 'keterangan'];

    public function aset(): HasMany
    {
        return $this->hasMany(Aset::class, 'kategori_id');
    }
}
```

- [ ] **Step 3: Create GisLayer model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GisLayer extends Model
{
    use HasUuid;

    protected $table = 'gis_layer';

    protected $fillable = ['nama_layer', 'warna', 'icon_marker', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function gisAset(): HasMany
    {
        return $this->hasMany(GisAset::class, 'layer_id');
    }
}
```

- [ ] **Step 4: Create JenisPemanfaatan model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisPemanfaatan extends Model
{
    use HasUuid;

    protected $table = 'jenis_pemanfaatan';

    protected $fillable = ['kode', 'nama', 'dasar_hukum', 'ketentuan'];

    public function pemanfaatan(): HasMany
    {
        return $this->hasMany(Pemanfaatan::class, 'jenis_id');
    }
}
```

- [ ] **Step 5: Create PihakKetiga model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PihakKetiga extends Model
{
    use HasUuid;

    protected $table = 'pihak_ketiga';

    protected $fillable = [
        'nama', 'jenis', 'npwp', 'alamat', 'telepon', 'email', 'penanggung_jawab',
    ];

    public function pemanfaatan(): HasMany
    {
        return $this->hasMany(Pemanfaatan::class);
    }
}
```

- [ ] **Step 6: Commit**

```bash
git add backend/app/Models/Opd.php backend/app/Models/KategoriAset.php backend/app/Models/GisLayer.php backend/app/Models/JenisPemanfaatan.php backend/app/Models/PihakKetiga.php
git commit -m "feat: add reference table models"
```

---

### Task 2: Models - Core Tables

**Files:**
- Create: `backend/app/Models/Aset.php`
- Create: `backend/app/Models/GisAset.php`
- Create: `backend/app/Models/Pemanfaatan.php`
- Create: `backend/app/Models/DokumenPemanfaatan.php`
- Create: `backend/app/Models/FotoAset.php`
- Create: `backend/app/Models/RiwayatAset.php`

- [ ] **Step 1: Create Aset model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Aset extends Model
{
    use HasUuid;

    protected $table = 'aset';

    protected $fillable = [
        'opd_id', 'kategori_id', 'kode_barang', 'register', 'nama_barang',
        'tahun_perolehan', 'nilai_perolehan', 'nilai_buku', 'luas',
        'kondisi', 'status', 'alamat', 'keterangan',
    ];

    protected $casts = [
        'tahun_perolehan' => 'integer',
        'nilai_perolehan' => 'decimal:2',
        'nilai_buku' => 'decimal:2',
        'luas' => 'decimal:2',
    ];

    public function opd(): BelongsTo { return $this->belongsTo(Opd::class); }
    public function kategori(): BelongsTo { return $this->belongsTo(KategoriAset::class, 'kategori_id'); }
    public function gisAset(): HasOne { return $this->hasOne(GisAset::class); }
    public function pemanfaatan(): HasMany { return $this->hasMany(Pemanfaatan::class); }
    public function foto(): HasMany { return $this->hasMany(FotoAset::class, 'aset_id'); }
    public function riwayat(): HasMany { return $this->hasMany(RiwayatAset::class, 'aset_id'); }

    public function scopeSearch($query, ?string $search)
    {
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_barang', 'ilike', "%{$search}%")
                  ->orWhere('kode_barang', 'ilike', "%{$search}%");
            });
        }
        return $query;
    }

    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['opd_id'])) $query->where('opd_id', $filters['opd_id']);
        if (!empty($filters['kategori_id'])) $query->where('kategori_id', $filters['kategori_id']);
        if (!empty($filters['kondisi'])) $query->where('kondisi', $filters['kondisi']);
        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        return $query;
    }
}
```

- [ ] **Step 2: Create GisAset model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GisAset extends Model
{
    use HasUuid;

    protected $table = 'gis_aset';

    protected $fillable = [
        'aset_id', 'layer_id', 'latitude', 'longitude', 'polygon_geojson',
        'luas_gis', 'tipe_geometri', 'foto_udara_url', 'sumber_koordinat', 'surveyed_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'luas_gis' => 'decimal:2',
        'polygon_geojson' => 'array',
        'surveyed_at' => 'datetime',
    ];

    public function aset(): BelongsTo { return $this->belongsTo(Aset::class); }
    public function layer(): BelongsTo { return $this->belongsTo(GisLayer::class, 'layer_id'); }

    public function toGeoJsonGeometry(): array
    {
        if ($this->tipe_geometri === 'Polygon' && $this->polygon_geojson) {
            return $this->polygon_geojson;
        }
        return [
            'type' => 'Point',
            'coordinates' => [(float) $this->longitude, (float) $this->latitude],
        ];
    }
}
```

- [ ] **Step 3: Create Pemanfaatan model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pemanfaatan extends Model
{
    use HasUuid;

    protected $table = 'pemanfaatan';

    protected $fillable = [
        'aset_id', 'jenis_id', 'pihak_ketiga_id', 'nomor_perjanjian',
        'tanggal_mulai', 'tanggal_selesai', 'nilai_kontrak', 'kontribusi_tahunan',
        'peruntukan', 'status', 'catatan', 'created_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'nilai_kontrak' => 'decimal:2',
        'kontribusi_tahunan' => 'decimal:2',
    ];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    public function aset(): BelongsTo { return $this->belongsTo(Aset::class); }
    public function jenis(): BelongsTo { return $this->belongsTo(JenisPemanfaatan::class, 'jenis_id'); }
    public function pihakKetiga(): BelongsTo { return $this->belongsTo(PihakKetiga::class); }
    public function creator(): BelongsTo { return $this->belongsTo(\App\Models\User::class, 'created_by'); }
    public function dokumen(): HasMany { return $this->hasMany(DokumenPemanfaatan::class, 'pemanfaatan_id'); }
}
```

- [ ] **Step 4: Create DokumenPemanfaatan model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenPemanfaatan extends Model
{
    use HasUuid;

    protected $table = 'dokumen_pemanfaatan';

    protected $fillable = [
        'pemanfaatan_id', 'jenis_dokumen', 'nomor_dokumen', 'tanggal_dokumen', 'file_path', 'file_name',
    ];

    protected $casts = ['tanggal_dokumen' => 'date'];

    public function pemanfaatan(): BelongsTo { return $this->belongsTo(Pemanfaatan::class); }
}
```

- [ ] **Step 5: Create FotoAset model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoAset extends Model
{
    use HasUuid;

    protected $table = 'foto_aset';

    protected $fillable = ['aset_id', 'file_path', 'caption', 'tipe', 'tanggal_foto'];

    protected $casts = ['tanggal_foto' => 'date'];

    public function aset(): BelongsTo { return $this->belongsTo(Aset::class); }
}
```

- [ ] **Step 6: Create RiwayatAset model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatAset extends Model
{
    use HasUuid;

    protected $table = 'riwayat_aset';

    protected $fillable = ['aset_id', 'aksi', 'deskripsi', 'user_id'];

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    public function aset(): BelongsTo { return $this->belongsTo(Aset::class); }
    public function user(): BelongsTo { return $this->belongsTo(\App\Models\User::class); }
}
```

- [ ] **Step 7: Commit**

```bash
git add backend/app/Models/Aset.php backend/app/Models/GisAset.php backend/app/Models/Pemanfaatan.php backend/app/Models/DokumenPemanfaatan.php backend/app/Models/FotoAset.php backend/app/Models/RiwayatAset.php
git commit -m "feat: add core models"
```

---

### Task 3: API Resources

**Files:**
- Create: `backend/app/Http/Resources/OpdResource.php`
- Create: `backend/app/Http/Resources/KategoriAsetResource.php`
- Create: `backend/app/Http/Resources/GisLayerResource.php`
- Create: `backend/app/Http/Resources/JenisPemanfaatanResource.php`
- Create: `backend/app/Http/Resources/PihakKetigaResource.php`
- Create: `backend/app/Http/Resources/AsetResource.php`
- Create: `backend/app/Http/Resources/GisAsetResource.php`
- Create: `backend/app/Http/Resources/PemanfaatanResource.php`
- Create: `backend/app/Http/Resources/DokumenPemanfaatanResource.php`
- Create: `backend/app/Http/Resources/FotoAsetResource.php`
- Create: `backend/app/Http/Resources/RiwayatAsetResource.php`
- Create: `backend/app/Http/Resources/UserResource.php`

- [ ] **Step 1: Create all resources** - Each resource maps model attributes to a consistent JSON shape. Use `whenLoaded()` for optional relations. Key resources:

**OpdResource:** id, kode_opd, nama_opd, alamat, telepon, kepala_opd, nip_kepala
**KategoriAsetResource:** id, kode_kib, nama_kategori, keterangan
**GisLayerResource:** id, nama_layer, warna, icon_marker, is_active
**JenisPemanfaatanResource:** id, kode, nama, dasar_hukum, ketentuan
**PihakKetigaResource:** id, nama, jenis, npwp, alamat, telepon, email, penanggung_jawab
**AsetResource:** id, kode_barang, register, nama_barang, tahun_perolehan, nilai_perolehan, nilai_buku, luas, kondisi, status, alamat, keterangan, + whenLoaded opd, kategori, gisAset, pemanfaatan_count, foto_count
**GisAsetResource:** id, aset_id, layer_id, latitude, longitude, polygon_geojson, luas_gis, tipe_geometri, + whenLoaded layer, aset summary
**PemanfaatanResource:** id, aset_id, jenis_id, pihak_ketiga_id, nomor_perjanjian, tanggal_mulai, tanggal_selesai, nilai_kontrak, kontribusi_tahunan, peruntukan, status, catatan, + whenLoaded aset, jenis, pihak_ketiga, dokumen
**DokumenPemanfaatanResource:** id, pemanfaatan_id, jenis_dokumen, nomor_dokumen, tanggal_dokumen, file_path, file_name
**FotoAsetResource:** id, aset_id, file_path, caption, tipe, tanggal_foto
**RiwayatAsetResource:** id, aset_id, aksi, deskripsi, + whenLoaded user
**UserResource:** id, name, email

- [ ] **Step 2: Commit**

```bash
git add backend/app/Http/Resources/
git commit -m "feat: add API resources for all models"
```

---

### Task 4: Auth Controller + Routes Setup

**Files:**
- Create: `backend/app/Http/Controllers/Api/AuthController.php`
- Modify: `backend/routes/api.php`

- [ ] **Step 1: Create AuthController**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Berhasil logout.']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }
}
```

- [ ] **Step 2: Setup routes in api.php**

```php
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OpdController;
use App\Http\Controllers\Api\KategoriAsetController;
use App\Http\Controllers\Api\GisLayerController;
use App\Http\Controllers\Api\JenisPemanfaatanController;
use App\Http\Controllers\Api\PihakKetigaController;
use App\Http\Controllers\Api\AsetController;
use App\Http\Controllers\Api\GisAsetController;
use App\Http\Controllers\Api\PemanfaatanController;
use App\Http\Controllers\Api\DokumenPemanfaatanController;
use App\Http\Controllers\Api\FotoAsetController;
use App\Http\Controllers\Api\RiwayatAsetController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::apiResource('opd', OpdController::class);
    Route::apiResource('kategori-aset', KategoriAsetController::class);
    Route::apiResource('gis-layer', GisLayerController::class);
    Route::apiResource('jenis-pemanfaatan', JenisPemanfaatanController::class);
    Route::apiResource('pihak-ketiga', PihakKetigaController::class);

    Route::apiResource('aset', AsetController::class);
    Route::get('/aset/{id}/pemanfaatan', [AsetController::class, 'pemanfaatan']);
    Route::get('/aset/{id}/foto', [AsetController::class, 'foto']);
    Route::get('/aset/{id}/riwayat', [AsetController::class, 'riwayat']);

    Route::apiResource('pemanfaatan', PemanfaatanController::class);
    Route::get('/pemanfaatan/{id}/dokumen', [PemanfaatanController::class, 'dokumen']);

    Route::post('/dokumen-pemanfaatan', [DokumenPemanfaatanController::class, 'store']);
    Route::delete('/dokumen-pemanfaatan/{id}', [DokumenPemanfaatanController::class, 'destroy']);
    Route::post('/foto-aset', [FotoAsetController::class, 'store']);
    Route::delete('/foto-aset/{id}', [FotoAsetController::class, 'destroy']);

    Route::get('/riwayat-aset', [RiwayatAsetController::class, 'index']);
    Route::post('/riwayat-aset', [RiwayatAsetController::class, 'store']);

    Route::prefix('gis')->group(function () {
        Route::get('/aset', [GisAsetController::class, 'index']);
        Route::get('/aset/{id}', [GisAsetController::class, 'show']);
        Route::get('/layer/{id}', [GisAsetController::class, 'byLayer']);
    });
});
```

- [ ] **Step 3: Commit**

```bash
git add backend/app/Http/Controllers/Api/AuthController.php backend/routes/api.php
git commit -m "feat: add auth controller and API routes"
```

---

### Task 5: Reference Table Controllers

**Files:**
- Create: `backend/app/Http/Controllers/Api/OpdController.php`
- Create: `backend/app/Http/Controllers/Api/KategoriAsetController.php`
- Create: `backend/app/Http/Controllers/Api/GisLayerController.php`
- Create: `backend/app/Http/Controllers/Api/JenisPemanfaatanController.php`
- Create: `backend/app/Http/Controllers/Api/PihakKetigaController.php`

All 5 controllers follow the same pattern: `index` (with search + paginate), `store` (validate + create), `show`, `update` (validate + update), `destroy`. Each uses its corresponding Model and Resource.

- [ ] **Step 1: Create OpdController**

Standard CRUD with search on `nama_opd`, validation: `kode_opd` required unique, `nama_opd` required.

- [ ] **Step 2: Create KategoriAsetController**

Standard CRUD with search on `nama_kategori`, validation: `kode_kib` required, `nama_kategori` required.

- [ ] **Step 3: Create GisLayerController**

Standard CRUD with search on `nama_layer`, validation: `nama_layer` required, `is_active` boolean.

- [ ] **Step 4: Create JenisPemanfaatanController**

Standard CRUD with search on `nama`, validation: `kode` required, `nama` required.

- [ ] **Step 5: Create PihakKetigaController**

Standard CRUD with search on `nama`, validation: `nama` required, `jenis` required in:Perorangan,Badan_Hukum,Pemda, `email` nullable email.

- [ ] **Step 6: Commit**

```bash
git add backend/app/Http/Controllers/Api/OpdController.php backend/app/Http/Controllers/Api/KategoriAsetController.php backend/app/Http/Controllers/Api/GisLayerController.php backend/app/Http/Controllers/Api/JenisPemanfaatanController.php backend/app/Http/Controllers/Api/PihakKetigaController.php
git commit -m "feat: add CRUD controllers for reference tables"
```

---

### Task 6: Aset Controller

**Files:**
- Create: `backend/app/Http/Controllers/Api/AsetController.php`

- [ ] **Step 1: Create AsetController**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AsetResource;
use App\Models\Aset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\AnonymousResourceCollection;

class AsetController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $aset = Aset::query()
            ->with(['opd', 'kategori'])
            ->search($request->search)
            ->filter($request->only(['opd_id', 'kategori_id', 'kondisi', 'status']))
            ->orderBy('kode_barang')
            ->paginate($request->get('per_page', 15));

        return AsetResource::collection($aset);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'opd_id' => 'required|uuid|exists:opd,id',
            'kategori_id' => 'required|uuid|exists:kategori_aset,id',
            'kode_barang' => 'required|string|max:255|unique:aset,kode_barang',
            'register' => 'nullable|string|max:255',
            'nama_barang' => 'required|string|max:255',
            'tahun_perolehan' => 'nullable|integer|min:1900|max:' . date('Y'),
            'nilai_perolehan' => 'nullable|numeric|min:0',
            'nilai_buku' => 'nullable|numeric|min:0',
            'luas' => 'nullable|numeric|min:0',
            'kondisi' => 'required|string|in:Baik,Rusak_Ringan,Rusak_Berat',
            'status' => 'nullable|string|in:Aktif,Idle,Dimanfaatkan',
            'alamat' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $aset = Aset::create($validated);

        return (new AsetResource($aset->load(['opd', 'kategori'])))
            ->response()->setStatusCode(201);
    }

    public function show(Aset $aset): AsetResource
    {
        $aset->load(['opd', 'kategori', 'gisAset.layer', 'pemanfaatan.jenis', 'pemanfaatan.pihakKetiga']);
        return new AsetResource($aset);
    }

    public function update(Request $request, Aset $aset): AsetResource
    {
        $validated = $request->validate([
            'opd_id' => 'sometimes|required|uuid|exists:opd,id',
            'kategori_id' => 'sometimes|required|uuid|exists:kategori_aset,id',
            'kode_barang' => 'sometimes|required|string|max:255|unique:aset,kode_barang,' . $aset->id . ',id',
            'register' => 'nullable|string|max:255',
            'nama_barang' => 'sometimes|required|string|max:255',
            'tahun_perolehan' => 'nullable|integer|min:1900|max:' . date('Y'),
            'nilai_perolehan' => 'nullable|numeric|min:0',
            'nilai_buku' => 'nullable|numeric|min:0',
            'luas' => 'nullable|numeric|min:0',
            'kondisi' => 'sometimes|required|string|in:Baik,Rusak_Ringan,Rusak_Berat',
            'status' => 'sometimes|required|string|in:Aktif,Idle,Dimanfaatkan',
            'alamat' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $oldStatus = $aset->status;
        $aset->update($validated);

        if (isset($validated['status']) && $validated['status'] !== $oldStatus) {
            $aset->riwayat()->create([
                'aksi' => 'Pemanfaatan',
                'deskripsi' => "Status berubah dari {$oldStatus} ke {$validated['status']}",
                'user_id' => $request->user()->id,
            ]);
        }

        return new AsetResource($aset->fresh(['opd', 'kategori']));
    }

    public function destroy(Aset $aset): JsonResponse
    {
        $aset->delete();
        return response()->json(['message' => 'Berhasil dihapus.']);
    }

    public function pemanfaatan(Aset $aset): AnonymousResourceCollection
    {
        return \App\Http\Resources\PemanfaatanResource::collection(
            $aset->pemanfaatan()->with(['jenis', 'pihakKetiga'])->orderByDesc('created_at')->paginate(15)
        );
    }

    public function foto(Aset $aset): AnonymousResourceCollection
    {
        return \App\Http\Resources\FotoAsetResource::collection(
            $aset->foto()->orderByDesc('created_at')->paginate(15)
        );
    }

    public function riwayat(Aset $aset): AnonymousResourceCollection
    {
        return \App\Http\Resources\RiwayatAsetResource::collection(
            $aset->riwayat()->with('user')->orderByDesc('created_at')->paginate(15)
        );
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add backend/app/Http/Controllers/Api/AsetController.php
git commit -m "feat: add Aset controller with search, filter, and sub-resources"
```

---

### Task 7: Pemanfaatan Controller

**Files:**
- Create: `backend/app/Http/Controllers/Api/PemanfaatanController.php`

- [ ] **Step 1: Create PemanfaatanController**

Key behaviors:
- `store`: When creating pemanfaatan with status "Aktif", auto-set aset status to "Dimanfaatkan" and create riwayat_aset record
- `destroy`: When deleting pemanfaatan, revert aset status to "Aktif" and create riwayat_aset record

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PemanfaatanResource;
use App\Models\Aset;
use App\Models\Pemanfaatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\AnonymousResourceCollection;

class PemanfaatanController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Pemanfaatan::query()
            ->with(['aset', 'jenis', 'pihakKetiga']);

        if ($request->aset_id) $query->where('aset_id', $request->aset_id);
        if ($request->status) $query->where('status', $request->status);
        if ($request->jenis_id) $query->where('jenis_id', $request->jenis_id);
        if ($request->pihak_ketiga_id) $query->where('pihak_ketiga_id', $request->pihak_ketiga_id);

        return PemanfaatanResource::collection(
            $query->orderByDesc('created_at')->paginate($request->get('per_page', 15))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'aset_id' => 'required|uuid|exists:aset,id',
            'jenis_id' => 'required|uuid|exists:jenis_pemanfaatan,id',
            'pihak_ketiga_id' => 'required|uuid|exists:pihak_ketiga,id',
            'nomor_perjanjian' => 'nullable|string|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'nilai_kontrak' => 'nullable|numeric|min:0',
            'kontribusi_tahunan' => 'nullable|numeric|min:0',
            'peruntukan' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:Aktif,Berakhir,Dibatalkan',
            'catatan' => 'nullable|string',
        ]);

        $validated['created_by'] = $request->user()->id;
        $pemanfaatan = Pemanfaatan::create($validated);

        if (($validated['status'] ?? 'Aktif') === 'Aktif') {
            $aset = Aset::findOrFail($validated['aset_id']);
            $aset->update(['status' => 'Dimanfaatkan']);
            $aset->riwayat()->create([
                'aksi' => 'Pemanfaatan',
                'deskripsi' => "Pemanfaatan aktif: {$pemanfaatan->nomor_perjanjian}",
                'user_id' => $request->user()->id,
            ]);
        }

        return (new PemanfaatanResource($pemanfaatan->load(['jenis', 'pihakKetiga'])))
            ->response()->setStatusCode(201);
    }

    public function show(Pemanfaatan $pemanfaatan): PemanfaatanResource
    {
        $pemanfaatan->load(['aset', 'jenis', 'pihakKetiga', 'dokumen']);
        return new PemanfaatanResource($pemanfaatan);
    }

    public function update(Request $request, Pemanfaatan $pemanfaatan): PemanfaatanResource
    {
        $validated = $request->validate([
            'jenis_id' => 'sometimes|required|uuid|exists:jenis_pemanfaatan,id',
            'pihak_ketiga_id' => 'sometimes|required|uuid|exists:pihak_ketiga,id',
            'nomor_perjanjian' => 'nullable|string|max:255',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'nilai_kontrak' => 'nullable|numeric|min:0',
            'kontribusi_tahunan' => 'nullable|numeric|min:0',
            'peruntukan' => 'nullable|string|max:255',
            'status' => 'sometimes|required|string|in:Aktif,Berakhir,Dibatalkan',
            'catatan' => 'nullable|string',
        ]);

        $pemanfaatan->update($validated);
        return new PemanfaatanResource($pemanfaatan->fresh(['jenis', 'pihakKetiga']));
    }

    public function destroy(Pemanfaatan $pemanfaatan): JsonResponse
    {
        $aset = $pemanfaatan->aset;
        $pemanfaatan->delete();

        $hasActive = $aset->pemanfaatan()->where('status', 'Aktif')->exists();
        if (!$hasActive && $aset->status === 'Dimanfaatkan') {
            $aset->update(['status' => 'Aktif']);
            $aset->riwayat()->create([
                'aksi' => 'Pemanfaatan',
                'deskripsi' => 'Pemanfaatan dihapus, status kembali Aktif',
            ]);
        }

        return response()->json(['message' => 'Berhasil dihapus.']);
    }

    public function dokumen(Pemanfaatan $pemanfaatan): AnonymousResourceCollection
    {
        return \App\Http\Resources\DokumenPemanfaatanResource::collection(
            $pemanfaatan->dokumen()->orderByDesc('created_at')->paginate(15)
        );
    }
}
```

- [ ] **Step 2: Commit**

```bash
git add backend/app/Http/Controllers/Api/PemanfaatanController.php
git commit -m "feat: add Pemanfaatan controller with auto aset status update"
```

---

### Task 8: GIS, Dashboard, Dokumen, Foto, Riwayat Controllers

**Files:**
- Create: `backend/app/Http/Controllers/Api/GisAsetController.php`
- Create: `backend/app/Http/Controllers/Api/DashboardController.php`
- Create: `backend/app/Http/Controllers/Api/DokumenPemanfaatanController.php`
- Create: `backend/app/Http/Controllers/Api/FotoAsetController.php`
- Create: `backend/app/Http/Controllers/Api/RiwayatAsetController.php`

- [ ] **Step 1: Create GisAsetController**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GisAset;
use App\Models\GisLayer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GisAsetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = GisAset::with(['aset.opd', 'layer']);

        if ($request->bbox) {
            $coords = explode(',', $request->bbox);
            if (count($coords) === 4) {
                [$west, $south, $east, $north] = array_map('floatval', $coords);
                $query->whereBetween('longitude', [$west, $east])
                      ->whereBetween('latitude', [$south, $north]);
            }
        }

        if ($request->layer_id) $query->where('layer_id', $request->layer_id);

        $features = $query->get()->map(function ($gis) {
            return [
                'type' => 'Feature',
                'geometry' => $gis->toGeoJsonGeometry(),
                'properties' => [
                    'id' => $gis->aset->id ?? null,
                    'gis_id' => $gis->id,
                    'kode_barang' => $gis->aset->kode_barang ?? null,
                    'nama_barang' => $gis->aset->nama_barang ?? null,
                    'status' => $gis->aset->status ?? null,
                    'kondisi' => $gis->aset->kondisi ?? null,
                    'layer' => $gis->layer->nama_layer ?? null,
                    'layer_id' => $gis->layer_id,
                    'luas_gis' => $gis->luas_gis,
                    'tipe_geometri' => $gis->tipe_geometri,
                ],
            ];
        });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $gis = GisAset::with(['aset', 'layer'])->where('aset_id', $id)->firstOrFail();

        return response()->json([
            'type' => 'Feature',
            'geometry' => $gis->toGeoJsonGeometry(),
            'properties' => array_merge($gis->toArray(), [
                'aset' => $gis->aset->only(['id', 'kode_barang', 'nama_barang', 'status', 'kondisi']),
                'layer' => $gis->layer->only(['id', 'nama_layer']),
            ]),
        ]);
    }

    public function byLayer(GisLayer $gis_layer): JsonResponse
    {
        $features = $gis_layer->gisAset()
            ->with(['aset.opd'])
            ->get()
            ->map(function ($gis) {
                return [
                    'type' => 'Feature',
                    'geometry' => $gis->toGeoJsonGeometry(),
                    'properties' => [
                        'id' => $gis->aset->id ?? null,
                        'nama_barang' => $gis->aset->nama_barang ?? null,
                        'status' => $gis->aset->status ?? null,
                    ],
                ];
            });

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
```

- [ ] **Step 2: Create DashboardController**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\Pemanfaatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalAset = Aset::count();

        $asetPerKategori = Aset::select('kategori_id', DB::raw('count(*) as total'))
            ->join('kategori_aset', 'aset.kategori_id', '=', 'kategori_aset.id')
            ->selectRaw('kategori_aset.nama_kategori, count(*) as total')
            ->groupBy('kategori_aset.nama_kategori')
            ->get();

        $asetPerStatus = Aset::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalNilaiPerolehan = Aset::sum('nilai_perolehan');
        $totalNilaiBuku = Aset::sum('nilai_buku');

        $pemanfaatanAktif = Pemanfaatan::where('status', 'Aktif')->count();
        $pemanfaatanSegeraBerakhir = Pemanfaatan::where('status', 'Aktif')
            ->whereBetween('tanggal_selesai', [now(), now()->addDays(30)])
            ->count();

        $pemanfaatanPerJenis = Pemanfaatan::select('jenis_pemanfaatan.nama', DB::raw('count(*) as total'))
            ->join('jenis_pemanfaatan', 'pemanfaatan.jenis_id', '=', 'jenis_pemanfaatan.id')
            ->where('pemanfaatan.status', 'Aktif')
            ->groupBy('jenis_pemanfaatan.nama')
            ->get();

        $pihakKetigaTerbanyak = Pemanfaatan::select('pihak_ketiga.nama', DB::raw('count(*) as total'))
            ->join('pihak_ketiga', 'pemanfaatan.pihak_ketiga_id', '=', 'pihak_ketiga.id')
            ->where('pemanfaatan.status', 'Aktif')
            ->groupBy('pihak_ketiga.nama')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return response()->json([
            'total_aset' => $totalAset,
            'aset_per_kategori' => $asetPerKategori,
            'aset_per_status' => $asetPerStatus,
            'total_nilai_perolehan' => $totalNilaiPerolehan,
            'total_nilai_buku' => $totalNilaiBuku,
            'pemanfaatan_aktif' => $pemanfaatanAktif,
            'pemanfaatan_segera_berakhir' => $pemanfaatanSegeraBerakhir,
            'pemanfaatan_per_jenis' => $pemanfaatanPerJenis,
            'pihak_ketiga_terbanyak' => $pihakKetigaTerbanyak,
        ]);
    }
}
```

- [ ] **Step 3: Create DokumenPemanfaatanController**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DokumenPemanfaatanResource;
use App\Models\DokumenPemanfaatan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DokumenPemanfaatanController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'pemanfaatan_id' => 'required|uuid|exists:pemanfaatan,id',
            'jenis_dokumen' => 'required|string|max:255',
            'nomor_dokumen' => 'nullable|string|max:255',
            'tanggal_dokumen' => 'nullable|date',
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $path = $file->store('dokumen', 'public');

        $dokumen = DokumenPemanfaatan::create([
            'pemanfaatan_id' => $validated['pemanfaatan_id'],
            'jenis_dokumen' => $validated['jenis_dokumen'],
            'nomor_dokumen' => $validated['nomor_dokumen'] ?? null,
            'tanggal_dokumen' => $validated['tanggal_dokumen'] ?? null,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
        ]);

        return (new DokumenPemanfaatanResource($dokumen))
            ->response()->setStatusCode(201);
    }

    public function destroy(DokumenPemanfaatan $dokumen_pemanfaatan): JsonResponse
    {
        Storage::disk('public')->delete($dokumen_pemanfaatan->file_path);
        $dokumen_pemanfaatan->delete();

        return response()->json(['message' => 'Berhasil dihapus.']);
    }
}
```

- [ ] **Step 4: Create FotoAsetController**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FotoAsetResource;
use App\Models\FotoAset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FotoAsetController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'aset_id' => 'required|uuid|exists:aset,id',
            'caption' => 'nullable|string|max:255',
            'tipe' => 'required|string|in:Depan,Samping,Udara,Lainnya',
            'tanggal_foto' => 'nullable|date',
            'file' => 'required|image|max:5120',
        ]);

        $file = $request->file('file');
        $path = $file->store('foto', 'public');

        $foto = FotoAset::create([
            'aset_id' => $validated['aset_id'],
            'file_path' => $path,
            'caption' => $validated['caption'] ?? null,
            'tipe' => $validated['tipe'],
            'tanggal_foto' => $validated['tanggal_foto'] ?? null,
        ]);

        return (new FotoAsetResource($foto))
            ->response()->setStatusCode(201);
    }

    public function destroy(FotoAset $foto_aset): JsonResponse
    {
        Storage::disk('public')->delete($foto_aset->file_path);
        $foto_aset->delete();

        return response()->json(['message' => 'Berhasil dihapus.']);
    }
}
```

- [ ] **Step 5: Create RiwayatAsetController**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RiwayatAsetResource;
use App\Models\RiwayatAset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\AnonymousResourceCollection;

class RiwayatAsetController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = RiwayatAset::with(['aset', 'user']);

        if ($request->aset_id) $query->where('aset_id', $request->aset_id);
        if ($request->aksi) $query->where('aksi', $request->aksi);

        return RiwayatAsetResource::collection(
            $query->orderByDesc('created_at')->paginate($request->get('per_page', 15))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'aset_id' => 'required|uuid|exists:aset,id',
            'aksi' => 'required|string|in:Pemanfaatan,Pemeliharaan,Mutasi,Penghapusan,Revaluasi',
            'deskripsi' => 'nullable|string',
        ]);

        $validated['user_id'] = $request->user()->id;
        $riwayat = RiwayatAset::create($validated);

        return (new RiwayatAsetResource($riwayat->load('user')))
            ->response()->setStatusCode(201);
    }
}
```

- [ ] **Step 6: Commit**

```bash
git add backend/app/Http/Controllers/Api/GisAsetController.php backend/app/Http/Controllers/Api/DashboardController.php backend/app/Http/Controllers/Api/DokumenPemanfaatanController.php backend/app/Http/Controllers/Api/FotoAsetController.php backend/app/Http/Controllers/Api/RiwayatAsetController.php
git commit -m "feat: add GIS, Dashboard, Dokumen, Foto, Riwayat controllers"
```

---

### Task 9: Storage Link + Final Verification

- [ ] **Step 1: Run migrations**

```bash
cd backend && php artisan migrate:fresh
```

- [ ] **Step 2: Create storage link**

```bash
cd backend && php artisan storage:link
```

- [ ] **Step 3: Run route list to verify**

```bash
cd backend && php artisan route:list --path=api
```

- [ ] **Step 4: Verify no syntax errors**

```bash
cd backend && php artisan route:cache
```

- [ ] **Step 5: Commit any fixes**

```bash
git add -A
git commit -m "fix: verify and fix any issues"
```
