<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KelasResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'mata_kuliah' => $this->mata_kuliah,
            'nama_kelas' => $this->nama_kelas,
            'dosen' => $this->dosen,
            'jumlah_mahasiswa' => $this->mahasiswa_count,
        ];
    }
}
