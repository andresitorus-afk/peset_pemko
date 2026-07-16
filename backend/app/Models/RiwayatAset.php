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
