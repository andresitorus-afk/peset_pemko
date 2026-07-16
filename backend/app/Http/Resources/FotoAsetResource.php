<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class FotoAsetResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id, 'aset_id' => $this->aset_id, 'file_path' => $this->file_path,
            'caption' => $this->caption, 'tipe' => $this->tipe,
            'tanggal_foto' => $this->tanggal_foto?->format('Y-m-d'),
            'created_at' => $this->created_at, 'updated_at' => $this->updated_at,
        ];
    }
}
