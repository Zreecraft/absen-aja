<?php

namespace Tests\Feature;

use App\Models\Kehadiran;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Pertemuan;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AbsensiTest extends TestCase
{
    use RefreshDatabase;

    private function buatKelas(string $matkul = 'Basis Data', string $namaKelas = 'A', string $dosen = 'Budi Santoso'): Kelas
    {
        return Kelas::create([
            'mata_kuliah' => $matkul,
            'nama_kelas' => $namaKelas,
            'dosen' => $dosen,
        ]);
    }

    private function buatMahasiswa(string $nim = '2021000001', string $nama = 'Andi Pratama'): Mahasiswa
    {
        return Mahasiswa::create([
            'nim' => $nim,
            'nama' => $nama,
        ]);
    }

    private function buatPertemuan(Kelas $kelas, int $nomor = 1, string $tanggal = '2026-10-01'): Pertemuan
    {
        return Pertemuan::create([
            'kelas_id' => $kelas->id,
            'nomor' => $nomor,
            'tanggal' => $tanggal,
        ]);
    }

    // -------------------------------------------------------------------------
    // 1. REKAP KEHADIRAN
    // -------------------------------------------------------------------------

    public function test_rekap_menghitung_status_dan_persentase_dengan_benar(): void
    {
        $kelas = $this->buatKelas();

        $mhs1 = $this->buatMahasiswa('2021000001', 'Andi');
        $mhs2 = $this->buatMahasiswa('2021000002', 'Bella');
        $mhs3 = $this->buatMahasiswa('2021000003', 'Citra');
        $mhs4 = $this->buatMahasiswa('2021000004', 'Dimas'); // tanpa catatan kehadiran

        $kelas->mahasiswa()->attach([$mhs1->id, $mhs2->id, $mhs3->id, $mhs4->id]);

        $p1 = $this->buatPertemuan($kelas, 1, '2026-10-01');
        $p2 = $this->buatPertemuan($kelas, 2, '2026-10-08');
        $this->buatPertemuan($kelas, 3, '2026-10-15'); // pertemuan tanpa kehadiran

        // Pertemuan 1: mhs1 hadir, mhs2 izin, mhs3 alpa
        Kehadiran::create(['pertemuan_id' => $p1->id, 'kelas_id' => $kelas->id, 'mahasiswa_id' => $mhs1->id, 'status' => 'hadir']);
        Kehadiran::create(['pertemuan_id' => $p1->id, 'kelas_id' => $kelas->id, 'mahasiswa_id' => $mhs2->id, 'status' => 'izin']);
        Kehadiran::create(['pertemuan_id' => $p1->id, 'kelas_id' => $kelas->id, 'mahasiswa_id' => $mhs3->id, 'status' => 'alpa']);

        // Pertemuan 2: mhs1 hadir, mhs2 sakit, mhs3 alpa
        Kehadiran::create(['pertemuan_id' => $p2->id, 'kelas_id' => $kelas->id, 'mahasiswa_id' => $mhs1->id, 'status' => 'hadir']);
        Kehadiran::create(['pertemuan_id' => $p2->id, 'kelas_id' => $kelas->id, 'mahasiswa_id' => $mhs2->id, 'status' => 'sakit']);
        Kehadiran::create(['pertemuan_id' => $p2->id, 'kelas_id' => $kelas->id, 'mahasiswa_id' => $mhs3->id, 'status' => 'alpa']);

        $response = $this->getJson("/api/kelas/{$kelas->id}/rekap");

        $response->assertOk();

        // Pertemuan yang belum ada kehadiran tidak masuk penyebut: tercatat = 2
        $response->assertJsonPath('data.pertemuan_tercatat', 2);

        $rekap = collect($response->json('data.rekap'))->keyBy('nim');

        // mhs1: hadir 2 dari 2 pertemuan tercatat -> 100.0%
        $this->assertSame(2, $rekap['2021000001']['hadir']);
        $this->assertSame(0, $rekap['2021000001']['izin']);
        $this->assertSame(0, $rekap['2021000001']['sakit']);
        $this->assertSame(0, $rekap['2021000001']['alpa']);
        $this->assertEquals(100.0, $rekap['2021000001']['persentase']);

        // mhs2: izin 1, sakit 1 (keduanya tidak dihitung hadir) -> 0.0%
        $this->assertSame(0, $rekap['2021000002']['hadir']);
        $this->assertSame(1, $rekap['2021000002']['izin']);
        $this->assertSame(1, $rekap['2021000002']['sakit']);
        $this->assertSame(0, $rekap['2021000002']['alpa']);
        $this->assertEquals(0.0, $rekap['2021000002']['persentase']);

        // mhs3: alpa 2 -> 0.0%
        $this->assertSame(0, $rekap['2021000003']['hadir']);
        $this->assertSame(0, $rekap['2021000003']['izin']);
        $this->assertSame(0, $rekap['2021000003']['sakit']);
        $this->assertSame(2, $rekap['2021000003']['alpa']);
        $this->assertEquals(0.0, $rekap['2021000003']['persentase']);

        // mhs4: tanpa catatan kehadiran tetap muncul dengan nilai 0
        $this->assertNotNull($rekap->get('2021000004'));
        $this->assertSame(0, $rekap['2021000004']['hadir']);
        $this->assertSame(0, $rekap['2021000004']['izin']);
        $this->assertSame(0, $rekap['2021000004']['sakit']);
        $this->assertSame(0, $rekap['2021000004']['alpa']);
        $this->assertEquals(0.0, $rekap['2021000004']['persentase']);
    }

    public function test_rekap_kelas_tanpa_catatan_menghasilkan_persentase_nol(): void
    {
        $kelas = $this->buatKelas();
        $mhs = $this->buatMahasiswa();
        $kelas->mahasiswa()->attach($mhs->id);

        $response = $this->getJson("/api/kelas/{$kelas->id}/rekap");

        $response->assertOk();
        $response->assertJsonPath('data.pertemuan_tercatat', 0);
        $response->assertJsonPath('data.rekap.0.persentase', 0);
    }

    // -------------------------------------------------------------------------
    // 2. MAHASISWA TIDAK TERDAFTAR DITOLAK SAAT PUT KEHADIRAN (422)
    // -------------------------------------------------------------------------

    public function test_put_kehadiran_menolak_mahasiswa_tidak_terdaftar_dan_tidak_menyimpan_data(): void
    {
        $kelas = $this->buatKelas();
        $mhsTerdaftar = $this->buatMahasiswa('2021000001', 'Andi');
        $mhsLuar = $this->buatMahasiswa('2021000002', 'Budi Luar');

        $kelas->mahasiswa()->attach($mhsTerdaftar->id);
        $pertemuan = $this->buatPertemuan($kelas, 1);

        $payload = [
            'kehadiran' => [
                ['mahasiswa_id' => $mhsTerdaftar->id, 'status' => 'hadir'],
                ['mahasiswa_id' => $mhsLuar->id, 'status' => 'hadir'],
            ],
        ];

        $response = $this->putJson("/api/pertemuan/{$pertemuan->id}/kehadiran", $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['kehadiran']);

        // Pastikan tidak ada data kehadiran yang tersimpan sama sekali (atomik)
        $this->assertDatabaseCount('kehadiran', 0);
    }

    // -------------------------------------------------------------------------
    // 3. STATUS TIDAK VALID DITOLAK 422
    // -------------------------------------------------------------------------

    public function test_put_kehadiran_menolak_status_tidak_valid(): void
    {
        $kelas = $this->buatKelas();
        $mhs = $this->buatMahasiswa();
        $kelas->mahasiswa()->attach($mhs->id);
        $pertemuan = $this->buatPertemuan($kelas, 1);

        $payload = [
            'kehadiran' => [
                ['mahasiswa_id' => $mhs->id, 'status' => 'bolos'],
            ],
        ];

        $response = $this->putJson("/api/pertemuan/{$pertemuan->id}/kehadiran", $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['kehadiran.0.status']);
        $this->assertDatabaseCount('kehadiran', 0);
    }

    // -------------------------------------------------------------------------
    // 4. UPSERT IDEMPOTENT
    // -------------------------------------------------------------------------

    public function test_upsert_kehadiran_idempotent_dan_dapat_memperbarui_status(): void
    {
        $kelas = $this->buatKelas();
        $mhs = $this->buatMahasiswa();
        $kelas->mahasiswa()->attach($mhs->id);
        $pertemuan = $this->buatPertemuan($kelas, 1);

        $payloadHadir = [
            'kehadiran' => [
                ['mahasiswa_id' => $mhs->id, 'status' => 'hadir'],
            ],
        ];

        // PUT pertama: membuat baris baru
        $response1 = $this->putJson("/api/pertemuan/{$pertemuan->id}/kehadiran", $payloadHadir);
        $response1->assertOk();
        $this->assertDatabaseCount('kehadiran', 1);
        $this->assertDatabaseHas('kehadiran', [
            'pertemuan_id' => $pertemuan->id,
            'mahasiswa_id' => $mhs->id,
            'status' => 'hadir',
        ]);

        // PUT kedua dengan payload yang sama: idempotent, tidak membuat baris duplikat
        $response2 = $this->putJson("/api/pertemuan/{$pertemuan->id}/kehadiran", $payloadHadir);
        $response2->assertOk();
        $this->assertDatabaseCount('kehadiran', 1);

        // PUT ketiga: mengubah status menjadi 'izin'
        $payloadIzin = [
            'kehadiran' => [
                ['mahasiswa_id' => $mhs->id, 'status' => 'izin'],
            ],
        ];

        $response3 = $this->putJson("/api/pertemuan/{$pertemuan->id}/kehadiran", $payloadIzin);
        $response3->assertOk();
        $this->assertDatabaseCount('kehadiran', 1);
        $this->assertDatabaseHas('kehadiran', [
            'pertemuan_id' => $pertemuan->id,
            'mahasiswa_id' => $mhs->id,
            'status' => 'izin',
        ]);
    }

    // -------------------------------------------------------------------------
    // 5. KELUARKAN MAHASISWA DARI KELAS
    // -------------------------------------------------------------------------

    public function test_keluarkan_mahasiswa_dari_kelas_ditolak_jika_sudah_ada_kehadiran(): void
    {
        $kelas = $this->buatKelas();
        $mhs = $this->buatMahasiswa();
        $kelas->mahasiswa()->attach($mhs->id);
        $pertemuan = $this->buatPertemuan($kelas, 1);

        Kehadiran::create([
            'pertemuan_id' => $pertemuan->id,
            'kelas_id' => $kelas->id,
            'mahasiswa_id' => $mhs->id,
            'status' => 'hadir',
        ]);

        $response = $this->deleteJson("/api/kelas/{$kelas->id}/mahasiswa/{$mhs->id}");

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Mahasiswa sudah punya data kehadiran di kelas ini, tidak bisa dikeluarkan.',
        ]);

        // Mahasiswa masih terdaftar di kelas
        $this->assertDatabaseHas('kelas_mahasiswa', [
            'kelas_id' => $kelas->id,
            'mahasiswa_id' => $mhs->id,
        ]);
    }

    public function test_keluarkan_mahasiswa_dari_kelas_berhasil_jika_belum_ada_kehadiran(): void
    {
        $kelas = $this->buatKelas();
        $mhs = $this->buatMahasiswa();
        $kelas->mahasiswa()->attach($mhs->id);

        $response = $this->deleteJson("/api/kelas/{$kelas->id}/mahasiswa/{$mhs->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('kelas_mahasiswa', [
            'kelas_id' => $kelas->id,
            'mahasiswa_id' => $mhs->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // 6. HAPUS MAHASISWA
    // -------------------------------------------------------------------------

    public function test_hapus_mahasiswa_ditolak_jika_masih_terdaftar_di_kelas(): void
    {
        $kelas = $this->buatKelas();
        $mhs = $this->buatMahasiswa();
        $kelas->mahasiswa()->attach($mhs->id);

        $response = $this->deleteJson("/api/mahasiswa/{$mhs->id}");

        $response->assertStatus(422);
        $response->assertJson([
            'message' => 'Mahasiswa masih terdaftar di kelas. Keluarkan dulu dari kelas.',
        ]);

        $this->assertDatabaseHas('mahasiswa', ['id' => $mhs->id]);
    }

    public function test_hapus_mahasiswa_berhasil_jika_tidak_terdaftar_di_kelas(): void
    {
        $mhs = $this->buatMahasiswa();

        $response = $this->deleteJson("/api/mahasiswa/{$mhs->id}");

        $response->assertNoContent();
        $this->assertDatabaseMissing('mahasiswa', ['id' => $mhs->id]);
    }

    // -------------------------------------------------------------------------
    // 7. PERTEMUAN
    // -------------------------------------------------------------------------

    public function test_pertemuan_nomor_otomatis_berikutnya(): void
    {
        $kelas = $this->buatKelas();

        // Buat pertemuan pertama tanpa mengirimkan nomor
        $res1 = $this->postJson("/api/kelas/{$kelas->id}/pertemuan", [
            'tanggal' => '2026-10-01',
        ]);

        $res1->assertStatus(201);
        $res1->assertJsonPath('data.nomor', 1);

        // Buat pertemuan kedua tanpa nomor -> otomatis nomor 2
        $res2 = $this->postJson("/api/kelas/{$kelas->id}/pertemuan", [
            'tanggal' => '2026-10-08',
        ]);

        $res2->assertStatus(201);
        $res2->assertJsonPath('data.nomor', 2);
    }

    public function test_pertemuan_nomor_duplikat_di_kelas_sama_ditolak(): void
    {
        $kelas = $this->buatKelas();
        $this->buatPertemuan($kelas, 1);

        $response = $this->postJson("/api/kelas/{$kelas->id}/pertemuan", [
            'nomor' => 1,
            'tanggal' => '2026-10-08',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nomor']);
    }

    public function test_pertemuan_nomor_sama_di_kelas_berbeda_diperbolehkan(): void
    {
        $kelasA = $this->buatKelas('Basis Data', 'A');
        $kelasB = $this->buatKelas('Pemrograman Web', 'B');

        $this->buatPertemuan($kelasA, 1);

        $response = $this->postJson("/api/kelas/{$kelasB->id}/pertemuan", [
            'nomor' => 1,
            'tanggal' => '2026-10-08',
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('data.nomor', 1);
    }

    // -------------------------------------------------------------------------
    // 8. HAPUS KELAS MENGHAPUS PERTEMUAN DAN KEHADIRAN (CASCADE)
    // -------------------------------------------------------------------------

    public function test_hapus_kelas_menghapus_pertemuan_dan_kehadiran_secara_cascade(): void
    {
        $kelas = $this->buatKelas();
        $mhs = $this->buatMahasiswa();
        $kelas->mahasiswa()->attach($mhs->id);
        $pertemuan = $this->buatPertemuan($kelas, 1);

        $kehadiran = Kehadiran::create([
            'pertemuan_id' => $pertemuan->id,
            'kelas_id' => $kelas->id,
            'mahasiswa_id' => $mhs->id,
            'status' => 'hadir',
        ]);

        $response = $this->deleteJson("/api/kelas/{$kelas->id}");

        $response->assertNoContent();

        // Kelas terhapus
        $this->assertDatabaseMissing('kelas', ['id' => $kelas->id]);
        // Pertemuan terhapus via cascade
        $this->assertDatabaseMissing('pertemuan', ['id' => $pertemuan->id]);
        // Kehadiran terhapus via cascade
        $this->assertDatabaseMissing('kehadiran', ['id' => $kehadiran->id]);
        // Pendaftaran kelas_mahasiswa terhapus via cascade
        $this->assertDatabaseMissing('kelas_mahasiswa', [
            'kelas_id' => $kelas->id,
            'mahasiswa_id' => $mhs->id,
        ]);
        // Mahasiswa itu sendiri TIDAK terhapus
        $this->assertDatabaseHas('mahasiswa', ['id' => $mhs->id]);
    }

    // -------------------------------------------------------------------------
    // 9. CONSTRAINT DATABASE SEBAGAI PENGAMAN (QUERY EXCEPTION)
    // -------------------------------------------------------------------------

    public function test_constraint_database_menolak_status_kehadiran_tidak_valid(): void
    {
        $kelas = $this->buatKelas();
        $mhs = $this->buatMahasiswa();
        $kelas->mahasiswa()->attach($mhs->id);
        $pertemuan = $this->buatPertemuan($kelas, 1);

        $this->expectException(QueryException::class);

        DB::table('kehadiran')->insert([
            'pertemuan_id' => $pertemuan->id,
            'kelas_id' => $kelas->id,
            'mahasiswa_id' => $mhs->id,
            'status' => 'bolos',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_constraint_database_menolak_mahasiswa_tidak_terdaftar_di_kelas(): void
    {
        $kelas = $this->buatKelas();
        $mhs = $this->buatMahasiswa(); // tidak di-attach ke $kelas
        $pertemuan = $this->buatPertemuan($kelas, 1);

        $this->expectException(QueryException::class);

        // Foreign key gabungan (kelas_id, mahasiswa_id) harus menolak baris ini
        DB::table('kehadiran')->insert([
            'pertemuan_id' => $pertemuan->id,
            'kelas_id' => $kelas->id,
            'mahasiswa_id' => $mhs->id,
            'status' => 'hadir',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
