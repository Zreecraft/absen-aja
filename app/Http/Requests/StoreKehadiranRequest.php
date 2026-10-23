<?php

namespace App\Http\Requests;

use App\Enums\StatusKehadiran;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreKehadiranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kehadiran' => ['required', 'array', 'min:1'],
            'kehadiran.*.mahasiswa_id' => ['required', 'integer', 'distinct'],
            'kehadiran.*.status' => ['required', Rule::enum(StatusKehadiran::class)],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $ids = collect($this->input('kehadiran', []))
                ->pluck('mahasiswa_id')
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            if ($ids->isEmpty()) {
                return;
            }

            // setiap mahasiswa harus terdaftar di kelas milik pertemuan ini
            $terdaftar = DB::table('kelas_mahasiswa')
                ->where('kelas_id', $this->route('pertemuan')->kelas_id)
                ->whereIn('mahasiswa_id', $ids)
                ->pluck('mahasiswa_id')
                ->map(fn ($id) => (int) $id);

            $tidakTerdaftar = $ids->diff($terdaftar);

            if ($tidakTerdaftar->isNotEmpty()) {
                $validator->errors()->add(
                    'kehadiran',
                    'Mahasiswa dengan ID '.implode(', ', $tidakTerdaftar->all()).' tidak terdaftar di kelas ini.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'kehadiran.required' => 'Data kehadiran wajib diisi.',
            'kehadiran.min' => 'Data kehadiran minimal berisi 1 mahasiswa.',
            'kehadiran.*.mahasiswa_id.required' => 'ID mahasiswa wajib diisi.',
            'kehadiran.*.mahasiswa_id.integer' => 'ID mahasiswa harus berupa angka.',
            'kehadiran.*.mahasiswa_id.distinct' => 'Ada ID mahasiswa yang duplikat dalam data.',
            'kehadiran.*.status.required' => 'Status kehadiran wajib diisi.',
            'kehadiran.*.status.enum' => 'Status tidak valid. Pilihan yang benar: hadir, izin, sakit, alpa.',
        ];
    }

    public function attributes(): array
    {
        return [
            'kehadiran' => 'data kehadiran',
            'kehadiran.*.mahasiswa_id' => 'ID mahasiswa',
            'kehadiran.*.status' => 'status',
        ];
    }
}
