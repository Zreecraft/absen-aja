<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateKelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mata_kuliah' => ['required', 'string', 'max:255'],
            'nama_kelas' => ['required', 'string', 'max:255'],
            'dosen' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'mata_kuliah.required' => 'Mata kuliah wajib diisi.',
            'mata_kuliah.string' => 'Mata kuliah harus berupa teks.',
            'mata_kuliah.max' => 'Mata kuliah maksimal 255 karakter.',
            'nama_kelas.required' => 'Nama kelas wajib diisi.',
            'nama_kelas.string' => 'Nama kelas harus berupa teks.',
            'nama_kelas.max' => 'Nama kelas maksimal 255 karakter.',
            'dosen.required' => 'Dosen wajib diisi.',
            'dosen.string' => 'Dosen harus berupa teks.',
            'dosen.max' => 'Dosen maksimal 255 karakter.',
        ];
    }

    public function attributes(): array
    {
        return [
            'mata_kuliah' => 'mata kuliah',
            'nama_kelas' => 'nama kelas',
            'dosen' => 'dosen',
        ];
    }
}
