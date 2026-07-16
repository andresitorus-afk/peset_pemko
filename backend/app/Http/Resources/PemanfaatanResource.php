<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class PemanfaatanResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id, 'aset_id' => $this->aset_id, 'jenis_id' => $this->jenis_id,
            'pihak_ketiga_id' => $this->pihak_ketiga_id, 'nomor_perjanjian' => $this->nomor_perjanjian,
            'tanggal_mulai' => $this->tanggal_mulai?->format('Y-m-d'),
            'tanggal_selesai' => $this->tanggal_selesai?->format('Y-m-d'),
            'nilai_kontrak' => $this->nilai_kontrak, 'kontribusi_tahunan' => $this->kontribusi_tahunan,
            'peruntukan' => $this->peruntukan, 'status' => $this->status, 'catatan' => $this->catatan,
            'aset' => new AsetResource($this->whenLoaded('aset')),
            'jenis' => new JenisPemanfaatanResource($this->whenLoaded('jenis')),
            'pihak_ketiga' => new PihakKetigaResource($this->whenLoaded('pihakKetiga')),
            'dokumen' => DokumenPemanfaatanResource::collection($this->whenLoaded('dokumen')),
            'created_at' => $this->created_at,
        ];
    }
}
