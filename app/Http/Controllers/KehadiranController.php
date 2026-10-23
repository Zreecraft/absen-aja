<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKehadiranRequest;
use App\Http\Resources\AbsensiResource;
use App\Models\Kehadiran;
use App\Models\Pertemuan;
use Illuminate\Support\Facades\DB;

class KehadiranController extends Controller
{
    public function show(Pertemuan $pertemuan): AbsensiResource
    {
        return $this->absensi($pertemuan);
    }

    public function update(StoreKehadiranRequest $request, Pertemuan $pertemuan): AbsensiResource
    {
        $now = now();

        // kelas_id diambil dari pertemuan; created_at/updated_at manual (upsert tidak mengisinya)
        $baris = collect($request->validated('kehadiran'))->map(fn (array $item) => [
            'pertemuan_id' => $pertemuan->id,
            'kelas_id' => $pertemuan->kelas_id,
            'mahasiswa_id' => $item['mahasiswa_id'],
            'status' => $item['status'],
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        DB::transaction(function () use ($baris) {
            // upsert pada kunci unik (pertemuan_id, mahasiswa_id): idempotent,
            // baris sama tidak diduplikasi, hanya status dan updated_at yang diperbarui
            Kehadiran::upsert(
                $baris,
                ['pertemuan_id', 'mahasiswa_id'],
                ['status', 'updated_at']
            );
        });

        return $this->absensi($pertemuan);
    }

    // satu query left join: semua mahasiswa terdaftar di kelas pertemuan ini,
    // status null bila belum dicatat — tanpa N+1
    private function absensi(Pertemuan $pertemuan): AbsensiResource
    {
        $baris = DB::table('mahasiswa')
            ->join('kelas_mahasiswa', 'kelas_mahasiswa.mahasiswa_id', '=', 'mahasiswa.id')
            ->leftJoin('kehadiran', function ($join) use ($pertemuan) {
                $join->on('kehadiran.mahasiswa_id', '=', 'mahasiswa.id')
                    ->where('kehadiran.pertemuan_id', $pertemuan->id)
                    ->where('kehadiran.kelas_id', $pertemuan->kelas_id);
            })
            ->where('kelas_mahasiswa.kelas_id', $pertemuan->kelas_id)
            ->orderBy('mahasiswa.nim')
            ->select('mahasiswa.id as mahasiswa_id', 'mahasiswa.nim', 'mahasiswa.nama', 'kehadiran.status')
            ->get();

        return new AbsensiResource([
            'pertemuan' => [
                'id' => $pertemuan->id,
                'kelas_id' => $pertemuan->kelas_id,
                'nomor' => $pertemuan->nomor,
                'tanggal' => $pertemuan->tanggal->format('Y-m-d'),
            ],
            'kehadiran' => $baris->map(fn ($data) => [
                'mahasiswa_id' => (int) $data->mahasiswa_id,
                'nim' => $data->nim,
                'nama' => $data->nama,
                'status' => $data->status,
            ])->all(),
        ]);
    }
}
