<?php

namespace Database\Seeders;

use App\Models\Mahasiswa;
use Illuminate\Database\Seeder;

class MahasiswaSeeder extends Seeder
{
    public function run(): void
    {
        // [NIM, nama]
        $mahasiswa = [
            ['2021000001', 'Andi Pratama'],
            ['2021000002', 'Bella Safitri'],
            ['2021000003', 'Citra Lestari'],
            ['2021000004', 'Dimas Prasetyo'],
            ['2021000005', 'Eka Wulandari'],
            ['2021000006', 'Farhan Maulana'],
            ['2021000007', 'Gita Puspita'],
            ['2021000008', 'Hendra Saputra'],
            ['2021000009', 'Indah Permata'],
            ['2021000010', 'Joko Susilo'],
            ['2021000011', 'Kartika Sari'],
            ['2021000012', 'Lutfi Hakim'],
            ['2021000013', 'Maya Anggraini'],
            ['2021000014', 'Nurul Hidayah'],
            ['2021000015', 'Bayu Nugroho'],
        ];

        foreach ($mahasiswa as [$nim, $nama]) {
            Mahasiswa::create(['nim' => $nim, 'nama' => $nama]);
        }
    }
}
