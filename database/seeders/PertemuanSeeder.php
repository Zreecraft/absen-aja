<?php

namespace Database\Seeders;

use App\Models\Pertemuan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PertemuanSeeder extends Seeder
{
    public function run(): void
    {
        // nomor kelas => pertemuan mingguan mulai dari tanggal ini
        $jadwal = [
            1 => ['mulai' => '2026-09-07', 'jumlah' => 6],
            2 => ['mulai' => '2026-09-08', 'jumlah' => 5],
            3 => ['mulai' => '2026-09-09', 'jumlah' => 4],
        ];

        foreach ($jadwal as $kelasId => $info) {
            $tanggal = Carbon::parse($info['mulai']);

            for ($nomor = 1; $nomor <= $info['jumlah']; $nomor++) {
                Pertemuan::create([
                    'kelas_id' => $kelasId,
                    'nomor' => $nomor,
                    'tanggal' => $tanggal->toDateString(),
                ]);

                $tanggal->addWeek();
            }
        }
    }
}
