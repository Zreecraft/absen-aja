<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pertemuan_id');
            $table->unsignedBigInteger('kelas_id');
            $table->unsignedBigInteger('mahasiswa_id');
            $table->string('status');
            $table->timestamps();
            $table->unique(['pertemuan_id', 'mahasiswa_id']);
        });

        DB::statement("ALTER TABLE kehadiran ADD CONSTRAINT kehadiran_status_check CHECK (status IN ('hadir','izin','sakit','alpa'))");

        // FK komposit: mahasiswa harus terdaftar di kelas yang sama.
        // ON DELETE CASCADE: saat kelas dihapus, pendaftaran di pivot ikut terhapus
        // dan kehadiran tidak boleh tersisa mereferensinya.
        DB::statement('ALTER TABLE kehadiran ADD CONSTRAINT kehadiran_kelas_mahasiswa_fk FOREIGN KEY (kelas_id, mahasiswa_id) REFERENCES kelas_mahasiswa (kelas_id, mahasiswa_id) ON DELETE CASCADE');

        // FK komposit: pertemuan harus milik kelas yang sama, ikut terhapus jika pertemuan dihapus
        DB::statement('ALTER TABLE kehadiran ADD CONSTRAINT kehadiran_pertemuan_fk FOREIGN KEY (pertemuan_id, kelas_id) REFERENCES pertemuan (id, kelas_id) ON DELETE CASCADE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kehadiran');
    }
};
