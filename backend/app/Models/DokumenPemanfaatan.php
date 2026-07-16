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
