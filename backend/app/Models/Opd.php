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
