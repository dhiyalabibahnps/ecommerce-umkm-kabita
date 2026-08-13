<?php

declare(strict_types=1);

namespace App\Http\Requests\Shop;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CreateShopRequest extends FormRequest
{
  public function authorize(): bool
  {
    return Auth::check() && Auth::user()->role === 'seller';
  }

  public function rules(): array
  {
    return [
      'name' => ['required', 'string', 'max:255'],
      'description' => ['nullable', 'string', 'max:1000'],
      'logo' => [
        'nullable',
        'file',
        'mimes:jpeg,png,jpg,webp',
        'max:2048',
        'image',
      ],
    ];
  }

  public function bodyParameters(): array
  {
    return [
      'name' => ['description' => 'Nama toko (wajib).'],
      'description' => ['description' => 'Deskripsi toko (opsional).'],
      'logo' => ['description' => 'Logo toko (opsional, max 2MB).'],
    ];
  }

  public function withValidator($validator): void
  {
    $validator->after(function ($v) {
      $slug = Str::slug($this->name) . '-' . time();
      if (\App\Models\Shop::where('slug', $slug)->exists()) {
        $v->errors()->add('name', 'Nama toko sudah digunakan.');
      }
    });
  }
}
