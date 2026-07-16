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
