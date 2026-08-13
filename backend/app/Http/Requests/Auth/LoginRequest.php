<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'exists:users,email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.exists' => 'Email tidak terdaftar.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.string' => 'Kata sandi harus berupa teks.',
            'remember.boolean' => 'Opsi remember harus berupa true/false.',
        ];
    }

    // Deskripsi untuk Swagger UI
    public function bodyParameters(): array
    {
        return [
            'email' => ['description' => 'Email pengguna terdaftar.'],
            'password' => ['description' => 'Kata sandi akun.'],
            'remember' => ['description' => 'Opsi untuk memperpanjang masa berlaku token.'],
        ];
    }
}
