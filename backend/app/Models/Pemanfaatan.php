<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Pemanfaatan extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'pemanfaatan';

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

    public const UPDATED_AT = null;

    public function aset(): BelongsTo { return $this->belongsTo(Aset::class); }
    public function jenis(): BelongsTo { return $this->belongsTo(JenisPemanfaatan::class, 'jenis_id'); }
    public function pihakKetiga(): BelongsTo { return $this->belongsTo(PihakKetiga::class, 'pihak_ketiga_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function dokumen() { return $this->hasMany(DokumenPemanfaatan::class); }
}
