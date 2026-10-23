<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PertemuanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kelas_id' => $this->kelas_id,
            'nomor' => $this->nomor,
            'tanggal' => $this->tanggal?->format('Y-m-d'),
            'jumlah_tercatat' => $this->kehadiran_count,
        ];
    }
}
