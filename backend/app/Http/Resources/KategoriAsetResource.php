<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class KategoriAsetResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id, 'kode_kib' => $this->kode_kib, 'kode_kategori' => $this->kode_kategori,
            'nama_kategori' => $this->nama_kategori, 'keterangan' => $this->keterangan,
            'parent_id' => $this->parent_id, 'is_leaf' => $this->is_leaf,
            'children' => KategoriAsetResource::collection($this->whenLoaded('children')),
            'created_at' => $this->created_at, 'updated_at' => $this->updated_at,
        ];
    }
}
