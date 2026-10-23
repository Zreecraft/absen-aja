<?php

use App\Http\Controllers\KelasController;
use App\Http\Controllers\MahasiswaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// parameters() memaksa nama parameter route tetap {kelas}, bukan {kela}
Route::apiResource('kelas', KelasController::class)->parameters(['kelas' => 'kelas']);
Route::apiResource('mahasiswa', MahasiswaController::class);
