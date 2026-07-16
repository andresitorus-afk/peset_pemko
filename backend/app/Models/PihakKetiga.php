<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PihakKetiga extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'pihak_ketiga';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = ['nama', 'jenis', 'npwp', 'alamat', 'telepon', 'email', 'penanggung_jawab'];
}
