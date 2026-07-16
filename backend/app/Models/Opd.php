<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Opd extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'opd';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = [
        'kode_opd', 'nama_opd', 'alamat', 'telepon', 'kepala_opd', 'nip_kepala',
    ];

    public function aset(): HasMany
    {
        return $this->hasMany(Aset::class);
    }
}
