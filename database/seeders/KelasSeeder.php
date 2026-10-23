<?php

namespace Database\Seeders;

use App\Models\Kelas;
use Illuminate\Database\Seeder;

class KelasSeeder extends Seeder
{
    public function run(): void
    {
        Kelas::create(['mata_kuliah' => 'Basis Data', 'nama_kelas' => 'A', 'dosen' => 'Budi Santoso']);
        Kelas::create(['mata_kuliah' => 'Pemrograman Web', 'nama_kelas' => 'A', 'dosen' => 'Siti Rahayu']);
        Kelas::create(['mata_kuliah' => 'Struktur Data', 'nama_kelas' => 'B', 'dosen' => 'Hendra Gunawan']);
    }
}
