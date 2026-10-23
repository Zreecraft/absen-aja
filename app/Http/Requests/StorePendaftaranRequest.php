<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePendaftaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mahasiswa_id' => ['required', 'integer', 'exists:mahasiswa,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'mahasiswa_id.required' => 'ID mahasiswa wajib diisi.',
            'mahasiswa_id.integer' => 'ID mahasiswa harus berupa angka.',
            'mahasiswa_id.exists' => 'Mahasiswa tidak ditemukan.',
        ];
    }

    public function attributes(): array
    {
        return [
            'mahasiswa_id' => 'ID mahasiswa',
        ];
    }
}
