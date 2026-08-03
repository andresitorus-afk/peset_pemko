<?php

namespace App\Services;

use App\Models\Aset;
use App\Models\RiwayatAset;

class AuditTrail
{
    public static function catat(
        Aset $target,
        \Illuminate\Database\Eloquent\Model $model,
        string $aksi,
        array $old,
        array $new,
        ?int $userId,
        ?string $deskripsi = null,
    ): ?RiwayatAset {
        $detail = [];
        foreach ($model->getFillable() as $field) {
            if (!array_key_exists($field, $old)) continue;
            $lama = $old[$field];
            $baru = $new[$field] ?? null;
            if ((string) $lama !== (string) $baru) {
                $detail[$field] = ['lama' => $lama, 'baru' => $baru];
            }
        }
        if (!$detail) return null;

        return $target->riwayat()->create([
            'aksi' => $aksi,
            'deskripsi' => $deskripsi ?? 'Data diperbarui',
            'detail' => $detail,
            'user_id' => $userId,
        ]);
    }
}
