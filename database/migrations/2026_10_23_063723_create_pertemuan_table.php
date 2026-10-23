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
        Schema::create('pertemuan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->unsignedInteger('nomor');
            $table->date('tanggal');
            $table->timestamps();
            $table->unique(['kelas_id', 'nomor']);
            // unique (id, kelas_id) supaya bisa dirujuk FK komposit di tabel kehadiran
            $table->unique(['id', 'kelas_id']);
        });

        DB::statement('ALTER TABLE pertemuan ADD CONSTRAINT pertemuan_nomor_check CHECK (nomor > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertemuan');
    }
};
