<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class KategoriAset extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'kategori_aset';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = Str::uuid()->toString();
            }
        });
    }

    protected $fillable = ['kode_kib', 'nama_kategori', 'keterangan'];

    public function aset()
    {
        return $this->hasMany(Aset::class, 'kategori_id');
    }
}
