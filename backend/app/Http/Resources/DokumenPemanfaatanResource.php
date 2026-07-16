<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class DokumenPemanfaatanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id, 'pemanfaatan_id' => $this->pemanfaatan_id,
            'jenis_dokumen' => $this->jenis_dokumen, 'nomor_dokumen' => $this->nomor_dokumen,
            'tanggal_dokumen' => $this->tanggal_dokumen?->format('Y-m-d'),
            'file_path' => $this->file_path, 'file_name' => $this->file_name,
            'created_at' => $this->created_at, 'updated_at' => $this->updated_at,
        ];
    }
}
