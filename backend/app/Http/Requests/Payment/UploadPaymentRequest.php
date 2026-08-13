<?php

declare(strict_types=1);

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class UploadPaymentRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true;
  }

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
   */
  public function rules(): array
  {
    return [
      'proof_image' => [
        'required',
        'file',
        'image',
        'mimes:jpeg,png,jpg,webp',
        'max:2048',
      ],
    ];
  }

  public function messages(): array
  {
    return [
      'proof_image.required' => 'Bukti pembayaran wajib diunggah.',
      'proof_image.file' => 'Bukti pembayaran harus berupa file.',
      'proof_image.image' => 'Bukti pembayaran harus berupa gambar.',
      'proof_image.mimes' => 'Bukti pembayaran harus berformat JPEG, PNG, JPG, atau WebP.',
      'proof_image.max' => 'Ukuran bukti pembayaran maksimal 2MB.',
    ];
  }
}
