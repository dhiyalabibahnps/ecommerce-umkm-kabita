<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $emailRule = app()->environment('development')
            ? ['required', 'email', Rule::unique('users')->where(fn($query) => $query->where('email', '!=', 'admin@kabita.test'))]
            : ['required', 'email', 'unique:users,email'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRule,
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['buyer', 'seller'])],
            'shop_name' => ['required_if:role,seller', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.string' => 'Nama harus berupa teks.',
            'name.max' => 'Nama maksimal 255 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.string' => 'Nomor telepon harus berupa teks.',
            'phone.max' => 'Nomor telepon maksimal 20 karakter.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.string' => 'Kata sandi harus berupa teks.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role tidak valid.',
            'shop_name.required_if' => 'Nama toko wajib diisi untuk role seller.',
            'shop_name.string' => 'Nama toko harus berupa teks.',
            'shop_name.max' => 'Nama toko maksimal 255 karakter.',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'role' => ['description' => 'Pilih role: buyer atau seller.'],
            'shop_name' => ['description' => 'Wajib diisi jika role adalah seller.'],
        ];
    }
}
