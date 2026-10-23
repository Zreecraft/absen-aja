<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Mahasiswa;
use Illuminate\Database\Seeder;

class PendaftaranSeeder extends Seeder
{
    public function run(): void
    {
        // nomor kelas => NIM yang terdaftar (ada yang ikut lebih dari satu kelas)
        $pendaftaran = [
            1 => ['2021000001', '2021000002', '2021000003', '2021000004', '2021000005', '2021000006'],
            2 => ['2021000004', '2021000005', '2021000006', '2021000007', '2021000008', '2021000009'],
            3 => ['2021000010', '2021000011', '2021000012', '2021000013', '2021000014', '2021000015', '2021000001'],
        ];

        foreach ($pendaftaran as $kelasId => $nims) {
            $kelas = Kelas::findOrFail($kelasId);

            foreach ($nims as $nim) {
                $kelas->mahasiswa()->attach(Mahasiswa::where('nim', $nim)->sole()->id);
            }
        }
    }
}
