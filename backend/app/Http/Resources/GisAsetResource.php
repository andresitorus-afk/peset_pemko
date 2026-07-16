<?php
namespace App\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class GisAsetResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id, 'aset_id' => $this->aset_id, 'layer_id' => $this->layer_id,
            'latitude' => $this->latitude, 'longitude' => $this->longitude,
            'polygon_geojson' => $this->polygon_geojson, 'luas_gis' => $this->luas_gis,
            'tipe_geometri' => $this->tipe_geometri, 'foto_udara_url' => $this->foto_udara_url,
            'sumber_koordinat' => $this->sumber_koordinat, 'surveyed_at' => $this->surveyed_at,
            'layer' => new GisLayerResource($this->whenLoaded('layer')),
            'aset' => $this->whenLoaded('aset', function () {
                return [
                    'id' => $this->aset->id, 'kode_barang' => $this->aset->kode_barang,
                    'nama_barang' => $this->aset->nama_barang, 'status' => $this->aset->status,
                    'kondisi' => $this->aset->kondisi,
                ];
            }),
            'created_at' => $this->created_at, 'updated_at' => $this->updated_at,
        ];
    }
}
