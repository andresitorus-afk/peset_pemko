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
