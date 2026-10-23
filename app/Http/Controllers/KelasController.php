<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreKelasRequest;
use App\Http\Requests\UpdateKelasRequest;
use App\Http\Resources\KelasResource;
use App\Models\Kelas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KelasController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $kelas = Kelas::withCount('mahasiswa')->latest()->paginate($request->integer('per_page', 10));

        return KelasResource::collection($kelas);
    }

    public function store(StoreKelasRequest $request): JsonResponse
    {
        $kelas = Kelas::create($request->validated());

        return (new KelasResource($kelas->loadCount('mahasiswa')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Kelas $kelas): KelasResource
    {
        return new KelasResource($kelas->loadCount('mahasiswa'));
    }

    public function update(UpdateKelasRequest $request, Kelas $kelas): KelasResource
    {
        $kelas->update($request->validated());

        return new KelasResource($kelas->loadCount('mahasiswa'));
    }

    public function destroy(Kelas $kelas): JsonResponse
    {
        $kelas->delete();

        return response()->json(null, 204);
    }
}
