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
