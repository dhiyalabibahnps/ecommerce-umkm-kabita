<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBuyerProfileRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $userId = $this->user()->id;

    return [
      'name' => ['required', 'string', 'max:255'],
      'phone' => ['required', 'string', 'max:20'],
      'address' => ['nullable', 'string', 'max:500'],
      'photo' => ['nullable', 'image', 'max:2048'],
      'email' => [
        'required',
        'email',
        'max:255',
        Rule::unique('users')->ignore($userId),
      ],
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'Nama lengkap wajib diisi.',
      'name.string' => 'Nama harus berupa teks.',
      'name.max' => 'Nama maksimal 255 karakter.',
      'phone.required' => 'Nomor telepon wajib diisi.',
      'phone.string' => 'Nomor telepon harus berupa teks.',
      'phone.max' => 'Nomor telepon maksimal 20 karakter.',
      'address.string' => 'Alamat harus berupa teks.',
      'address.max' => 'Alamat maksimal 500 karakter.',
      'photo.image' => 'File foto harus berupa gambar.',
      'photo.max' => 'Ukuran foto maksimal 2MB.',
      'email.required' => 'Email wajib diisi.',
      'email.email' => 'Format email tidak valid.',
      'email.unique' => 'Email sudah terdaftar.',
    ];
  }

  public function bodyParameters(): array
  {
    return [
      'name' => ['description' => 'Nama lengkap pembeli.'],
      'phone' => ['description' => 'Nomor telepon pembeli.'],
      'address' => ['description' => 'Alamat pembeli.'],
      'photo' => ['description' => 'Foto profil pembeli (image).'],
      'email' => ['description' => 'Email pembeli.'],
    ];
  }
}
