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
