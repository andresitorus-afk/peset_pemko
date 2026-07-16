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
