<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GisLayer extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'gis_layer';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = ['nama_layer', 'warna', 'icon_marker', 'is_active'];

    public function gisAset()
    {
        return $this->hasMany(GisAset::class, 'layer_id');
    }
}
