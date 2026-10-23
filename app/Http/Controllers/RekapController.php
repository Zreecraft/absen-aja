<?php

namespace App\Http\Controllers;

use App\Http\Resources\RekapResource;
use App\Models\Kelas;
use App\Services\RekapKehadiran;

class RekapController extends Controller
{
    public function show(Kelas $kelas, RekapKehadiran $rekap): RekapResource
    {
        $hasil = $rekap->untukKelas($kelas->id);

        return new RekapResource([
            'kelas' => [
                'id' => $kelas->id,
                'mata_kuliah' => $kelas->mata_kuliah,
                'nama_kelas' => $kelas->nama_kelas,
                'dosen' => $kelas->dosen,
            ],
            'pertemuan_tercatat' => $hasil['pertemuan_tercatat'],
            'rekap' => $hasil['rekap'],
        ]);
    }
}
