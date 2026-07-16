<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class JenisPemanfaatanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id, 'kode' => $this->kode, 'nama' => $this->nama,
            'dasar_hukum' => $this->dasar_hukum, 'ketentuan' => $this->ketentuan,
            'created_at' => $this->created_at, 'updated_at' => $this->updated_at,
        ];
    }
}
