<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Aset extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'aset';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

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
