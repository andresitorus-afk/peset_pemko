<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class AsetResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id, 'kode_barang' => $this->kode_barang, 'register' => $this->register,
            'nama_barang' => $this->nama_barang, 'tahun_perolehan' => $this->tahun_perolehan,
            'nilai_perolehan' => $this->nilai_perolehan, 'nilai_buku' => $this->nilai_buku,
            'luas' => $this->luas, 'kondisi' => $this->kondisi, 'status' => $this->status,
            'alamat' => $this->alamat, 'keterangan' => $this->keterangan,
            'opd' => new OpdResource($this->whenLoaded('opd')),
            'kategori' => new KategoriAsetResource($this->whenLoaded('kategori')),
            'gis_aset' => new GisAsetResource($this->whenLoaded('gisAset')),
            'foto' => $this->whenLoaded('foto', fn() => $this->foto->toArray()),
            'pemanfaatan_count' => $this->whenCounted('pemanfaatan'),
            'foto_count' => $this->whenCounted('foto'),
            'created_at' => $this->created_at, 'updated_at' => $this->updated_at,
        ];
    }
}
