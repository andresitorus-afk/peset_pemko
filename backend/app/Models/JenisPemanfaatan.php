<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class JenisPemanfaatan extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'jenis_pemanfaatan';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = ['kode', 'nama', 'dasar_hukum', 'ketentuan'];
}
