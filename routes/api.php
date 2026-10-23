<?php

use App\Http\Controllers\KelasController;
use App\Http\Controllers\KelasMahasiswaController;
use App\Http\Controllers\KehadiranController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PertemuanController;
use App\Http\Controllers\RekapController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// parameters() memaksa nama parameter route tetap {kelas}, bukan {kela}
Route::apiResource('kelas', KelasController::class)->parameters(['kelas' => 'kelas']);
Route::apiResource('mahasiswa', MahasiswaController::class);

// Pendaftaran mahasiswa ke kelas (index/store/destroy saja)
Route::apiResource('kelas.mahasiswa', KelasMahasiswaController::class)
    ->only(['index', 'store', 'destroy'])
    ->parameters(['kelas' => 'kelas', 'mahasiswa' => 'mahasiswa']);

// Pertemuan: daftar/buat di dalam kelas, ubah/hapus lewat /api/pertemuan/{pertemuan}
Route::apiResource('kelas.pertemuan', PertemuanController::class)
    ->only(['index', 'store'])
    ->parameters(['kelas' => 'kelas', 'pertemuan' => 'pertemuan']);
Route::apiResource('pertemuan', PertemuanController::class)
    ->only(['update', 'destroy'])
    ->parameters(['pertemuan' => 'pertemuan']);

// Kehadiran satu pertemuan (GET = lembar absensi, PUT = simpan sekaligus)
Route::get('/pertemuan/{pertemuan}/kehadiran', [KehadiranController::class, 'show']);
Route::put('/pertemuan/{pertemuan}/kehadiran', [KehadiranController::class, 'update']);

// Rekap kehadiran per kelas
Route::get('/kelas/{kelas}/rekap', [RekapController::class, 'show']);
