<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class GisLayerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id, 'nama_layer' => $this->nama_layer, 'warna' => $this->warna,
            'icon_marker' => $this->icon_marker, 'is_active' => $this->is_active,
            'created_at' => $this->created_at, 'updated_at' => $this->updated_at,
        ];
    }
}
