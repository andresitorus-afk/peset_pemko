<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class KategoriAsetResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id, 'kode_kib' => $this->kode_kib, 'nama_kategori' => $this->nama_kategori,
            'keterangan' => $this->keterangan, 'created_at' => $this->created_at, 'updated_at' => $this->updated_at,
        ];
    }
}
