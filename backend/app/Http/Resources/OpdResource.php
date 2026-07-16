<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class OpdResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id, 'kode_opd' => $this->kode_opd, 'nama_opd' => $this->nama_opd,
            'alamat' => $this->alamat, 'telepon' => $this->telepon, 'kepala_opd' => $this->kepala_opd,
            'nip_kepala' => $this->nip_kepala, 'created_at' => $this->created_at, 'updated_at' => $this->updated_at,
        ];
    }
}
