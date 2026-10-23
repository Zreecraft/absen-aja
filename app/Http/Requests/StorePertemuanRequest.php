<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePertemuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // nomor opsional; kalau tidak dikirim, controller menghitung nomor berikutnya
            'nomor' => [
                'nullable',
                'integer',
                'min:1',
                Rule::unique('pertemuan', 'nomor')->where('kelas_id', $this->route('kelas')->id),
            ],
            'tanggal' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'nomor.integer' => 'Nomor pertemuan harus berupa angka.',
            'nomor.min' => 'Nomor pertemuan minimal 1.',
            'nomor.unique' => 'Nomor pertemuan sudah dipakai di kelas ini.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Tanggal tidak valid.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nomor' => 'nomor pertemuan',
            'tanggal' => 'tanggal',
        ];
    }
}
