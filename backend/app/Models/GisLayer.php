<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GisLayer extends Model
{
    use HasUuid;

    protected $table = 'gis_layer';

    protected $fillable = ['nama_layer', 'warna', 'icon_marker', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function gisAset(): HasMany
    {
        return $this->hasMany(GisAset::class, 'layer_id');
    }
}
