<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMahasiswaRequest;
use App\Http\Requests\UpdateMahasiswaRequest;
use App\Http\Resources\MahasiswaResource;
use App\Models\Mahasiswa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MahasiswaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $mahasiswa = Mahasiswa::orderBy('nim')->paginate($request->integer('per_page', 10));

        return MahasiswaResource::collection($mahasiswa);
    }

    public function store(StoreMahasiswaRequest $request): JsonResponse
    {
        $mahasiswa = Mahasiswa::create($request->validated());

        return (new MahasiswaResource($mahasiswa))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Mahasiswa $mahasiswa): MahasiswaResource
    {
        return new MahasiswaResource($mahasiswa);
    }

    public function update(UpdateMahasiswaRequest $request, Mahasiswa $mahasiswa): MahasiswaResource
    {
        $mahasiswa->update($request->validated());

        return new MahasiswaResource($mahasiswa);
    }

    public function destroy(Mahasiswa $mahasiswa): JsonResponse
    {
        // kalau masih terdaftar di kelas mana pun, tolak
        if ($mahasiswa->kelas()->exists()) {
            return response()->json([
                'message' => 'Mahasiswa masih terdaftar di kelas. Keluarkan dulu dari kelas.',
            ], 422);
        }

        $mahasiswa->delete();

        return response()->json(null, 204);
    }
}
