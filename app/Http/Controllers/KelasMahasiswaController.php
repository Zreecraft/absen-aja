<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePendaftaranRequest;
use App\Http\Resources\MahasiswaResource;
use App\Models\Kehadiran;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KelasMahasiswaController extends Controller
{
    public function index(Kelas $kelas): AnonymousResourceCollection
    {
        $mahasiswa = $kelas->mahasiswa()->orderBy('mahasiswa.nim')->get();

        return MahasiswaResource::collection($mahasiswa);
    }

    public function store(StorePendaftaranRequest $request, Kelas $kelas): JsonResponse
    {
        $mahasiswa = Mahasiswa::findOrFail($request->validated('mahasiswa_id'));

        if ($kelas->mahasiswa()->whereKey($mahasiswa->id)->exists()) {
            return response()->json([
                'message' => 'Mahasiswa sudah terdaftar di kelas ini.',
            ], 422);
        }

        $kelas->mahasiswa()->attach($mahasiswa);

        return (new MahasiswaResource($mahasiswa))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Kelas $kelas, Mahasiswa $mahasiswa): JsonResponse
    {
        // belum terdaftar di kelas ini
        if (! $kelas->mahasiswa()->whereKey($mahasiswa->id)->exists()) {
            return response()->json([
                'message' => 'Mahasiswa tidak terdaftar di kelas ini.',
            ], 404);
        }

        // sudah punya kehadiran, tidak boleh dikeluarkan
        if (Kehadiran::where('kelas_id', $kelas->id)->where('mahasiswa_id', $mahasiswa->id)->exists()) {
            return response()->json([
                'message' => 'Mahasiswa sudah punya data kehadiran di kelas ini, tidak bisa dikeluarkan.',
            ], 422);
        }

        $kelas->mahasiswa()->detach($mahasiswa);

        return response()->json(null, 204);
    }
}
