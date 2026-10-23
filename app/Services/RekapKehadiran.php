<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class RekapKehadiran
{
    /**
     * Rekap kehadiran satu kelas dalam SATU query PostgreSQL.
     *
     * Aturan hitung:
     * - Hanya mahasiswa yang terdaftar di kelas (kelas_mahasiswa), urut NIM.
     *   Mahasiswa tanpa catatan tetap muncul bernilai 0.
     * - hadir/izin/sakit/alpa = COUNT(*) FILTER (WHERE status = '...') per mahasiswa.
     * - pertemuan_tercatat = pertemuan di kelas ini yang punya minimal satu baris
     *   kehadiran. Pertemuan yang dibuat tapi belum diisi TIDAK masuk penyebut.
     * - persentase = hadir / pertemuan_tercatat * 100, dibulatkan 1 desimal;
     *   bila pertemuan_tercatat = 0 hasilnya 0. Izin dan sakit TIDAK dihitung hadir.
     */
    public function untukKelas(int $kelasId): array
    {
        $baris = DB::select(<<<'SQL'
            WITH tercatat AS (
                SELECT COUNT(DISTINCT pertemuan_id) AS jumlah
                FROM kehadiran
                WHERE kelas_id = ?
            )
            SELECT
                m.id AS mahasiswa_id,
                m.nim,
                m.nama,
                t.jumlah AS pertemuan_tercatat,
                COUNT(*) FILTER (WHERE k.status = 'hadir') AS hadir,
                COUNT(*) FILTER (WHERE k.status = 'izin') AS izin,
                COUNT(*) FILTER (WHERE k.status = 'sakit') AS sakit,
                COUNT(*) FILTER (WHERE k.status = 'alpa') AS alpa,
                ROUND(
                    CASE
                        WHEN t.jumlah = 0 THEN 0
                        ELSE COUNT(*) FILTER (WHERE k.status = 'hadir')::numeric / t.jumlah * 100
                    END, 1
                ) AS persentase
            FROM mahasiswa m
            JOIN kelas_mahasiswa km ON km.mahasiswa_id = m.id
            CROSS JOIN tercatat t
            LEFT JOIN kehadiran k
                ON k.mahasiswa_id = m.id AND k.kelas_id = km.kelas_id
            WHERE km.kelas_id = ?
            GROUP BY m.id, m.nim, m.nama, t.jumlah
            ORDER BY m.nim
            SQL, [$kelasId, $kelasId]);

        return [
            'pertemuan_tercatat' => (int) ($baris[0]->pertemuan_tercatat ?? 0),
            'rekap' => array_map(fn ($r) => [
                'mahasiswa_id' => (int) $r->mahasiswa_id,
                'nim' => $r->nim,
                'nama' => $r->nama,
                'hadir' => (int) $r->hadir,
                'izin' => (int) $r->izin,
                'sakit' => (int) $r->sakit,
                'alpa' => (int) $r->alpa,
                'persentase' => (float) $r->persentase,
            ], $baris),
        ];
    }
}
