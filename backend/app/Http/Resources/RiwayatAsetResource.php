<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class RiwayatAsetResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id, 'aset_id' => $this->aset_id, 'aksi' => $this->aksi,
            'deskripsi' => $this->deskripsi,
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at,
        ];
    }
}
