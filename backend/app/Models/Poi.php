<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Poi extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'poi';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = ['nama', 'tipe', 'alamat', 'latitude', 'longitude', 'aktif'];
}
