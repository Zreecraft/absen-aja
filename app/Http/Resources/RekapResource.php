<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RekapResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'kelas' => $this->resource['kelas'],
            'pertemuan_tercatat' => $this->resource['pertemuan_tercatat'],
            'rekap' => $this->resource['rekap'],
        ];
    }
}
