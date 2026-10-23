<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePertemuanRequest;
use App\Http\Requests\UpdatePertemuanRequest;
use App\Http\Resources\PertemuanResource;
use App\Models\Kelas;
use App\Models\Pertemuan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PertemuanController extends Controller
{
    public function index(Kelas $kelas): AnonymousResourceCollection
    {
        $pertemuan = $kelas->pertemuan()->withCount('kehadiran')->orderBy('nomor')->get();

        return PertemuanResource::collection($pertemuan);
    }

    public function store(StorePertemuanRequest $request, Kelas $kelas): JsonResponse
    {
        $validated = $request->validated();

        // nomor tidak dikirim -> lanjut dari nomor terbesar di kelas ini (atau 1)
        $nomor = $validated['nomor'] ?? ($kelas->pertemuan()->max('nomor') ?? 0) + 1;

        $pertemuan = $kelas->pertemuan()->create([
            'nomor' => $nomor,
            'tanggal' => $validated['tanggal'],
        ]);

        return (new PertemuanResource($pertemuan->loadCount('kehadiran')))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdatePertemuanRequest $request, Pertemuan $pertemuan): PertemuanResource
    {
        $pertemuan->update($request->validated());

        return new PertemuanResource($pertemuan->loadCount('kehadiran'));
    }

    public function destroy(Pertemuan $pertemuan): JsonResponse
    {
        // kehadiran di pertemuan ini ikut terhapus oleh FK ON DELETE CASCADE
        $pertemuan->delete();

        return response()->json(null, 204);
    }
}
