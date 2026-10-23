<?php

namespace Database\Seeders;

use App\Models\Kehadiran;
use App\Models\Mahasiswa;
use App\Models\Pertemuan;
use Illuminate\Database\Seeder;

class KehadiranSeeder extends Seeder
{
    // huruf dalam pola => status kehadiran
    private const STATUS = [
        'H' => 'hadir',
        'I' => 'izin',
        'S' => 'sakit',
        'A' => 'alpa',
    ];

    // nomor kelas => pola per NIM; satu karakter per pertemuan (urut nomor),
    // '-' berarti pertemuan itu sengaja dibiarkan BELUM diisi kehadirannya.
    // Hanya mahasiswa yang terdaftar di kelas itu yang punya pola.
    private const POLA = [
        1 => [
            '2021000001' => 'HHHHH-',
            '2021000002' => 'HHIHH-',
            '2021000003' => 'HHIHS-',
            '2021000004' => 'HHIAS-',
            '2021000005' => 'AIASH-', // persentase rendah (20%)
            '2021000006' => 'HHHHI-',
        ],
        2 => [
            '2021000004' => 'HHHHH',
            '2021000005' => 'HHHIS',
            '2021000006' => 'HHIHH',
            '2021000007' => 'HSHIH',
            '2021000008' => 'HAIHA',
            '2021000009' => 'AASIH',
        ],
        3 => [
            '2021000010' => 'HHHH',
            '2021000011' => 'HHHI',
            '2021000012' => 'HHIA',
            '2021000013' => 'HIHA',
            '2021000014' => 'AHIS',
            '2021000015' => 'IAHA',
            '2021000001' => 'HHIH',
        ],
    ];

    public function run(): void
    {
        foreach (self::POLA as $kelasId => $perMahasiswa) {
            $pertemuan = Pertemuan::where('kelas_id', $kelasId)->orderBy('nomor')->get();

            foreach ($perMahasiswa as $nim => $pola) {
                $mahasiswa = Mahasiswa::where('nim', $nim)->sole();

                foreach ($pertemuan as $urutan => $p) {
                    $huruf = $pola[$urutan] ?? '-';

                    if ($huruf === '-' || ! isset(self::STATUS[$huruf])) {
                        continue;
                    }

                    Kehadiran::create([
                        'pertemuan_id' => $p->id,
                        'kelas_id' => $kelasId,
                        'mahasiswa_id' => $mahasiswa->id,
                        'status' => self::STATUS[$huruf],
                    ]);
                }
            }
        }
    }
}
