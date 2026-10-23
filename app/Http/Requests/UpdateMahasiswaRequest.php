<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMahasiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // abaikan NIM milik mahasiswa yang sedang diedit
            'nim' => ['required', 'string', 'max:30', 'unique:mahasiswa,nim,'.$this->route('mahasiswa')->id],
            'nama' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'nim.required' => 'NIM wajib diisi.',
            'nim.string' => 'NIM harus berupa teks.',
            'nim.max' => 'NIM maksimal 30 karakter.',
            'nim.unique' => 'NIM sudah terdaftar.',
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama harus berupa teks.',
            'nama.max' => 'Nama maksimal 255 karakter.',
        ];
    }

    public function attributes(): array
    {
        return [
            'nim' => 'NIM',
            'nama' => 'nama',
        ];
    }
}
