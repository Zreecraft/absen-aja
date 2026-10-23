<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AbsensiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'pertemuan' => $this->resource['pertemuan'],
            'kehadiran' => $this->resource['kehadiran'],
        ];
    }
}
