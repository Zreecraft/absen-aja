<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePertemuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nomor' => [
                'required',
                'integer',
                'min:1',
                // unik per kelas, abaikan nomor pertemuan yang sedang diedit
                Rule::unique('pertemuan', 'nomor')
                    ->ignore($this->route('pertemuan')->id)
                    ->where('kelas_id', $this->route('pertemuan')->kelas_id),
            ],
            'tanggal' => ['required', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'nomor.required' => 'Nomor pertemuan wajib diisi.',
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
