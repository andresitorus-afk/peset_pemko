<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class PihakKetigaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id, 'nama' => $this->nama, 'jenis' => $this->jenis, 'npwp' => $this->npwp,
            'alamat' => $this->alamat, 'telepon' => $this->telepon, 'email' => $this->email,
            'penanggung_jawab' => $this->penanggung_jawab,
            'created_at' => $this->created_at, 'updated_at' => $this->updated_at,
        ];
    }
}
