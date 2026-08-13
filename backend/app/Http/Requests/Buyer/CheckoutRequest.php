<?php

declare(strict_types=1);

namespace App\Http\Requests\Buyer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
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
      'cart_items' => ['required', 'array'],
      'cart_items.*' => ['required', 'integer', 'exists:cart_items,id'],
      'shipping_method' => ['required', Rule::in(['cod', 'kurir'])],
      'payment_method' => ['required', Rule::in(['transfer', 'cod'])],
      'shipping_address' => [
        Rule::requiredIf(fn() => $this->input('shipping_method') === 'kurir' && !$this->filled('location_id')),
        'string',
        'max:255',
      ],
      'location_id' => [
        'nullable',
        'integer',
        Rule::exists('cod_locations', 'id')->where('user_id', $this->user()->id),
      ],
      'notes' => ['nullable', 'string', 'max:500'],
    ];
  }

  public function messages(): array
  {
    return [
      'cart_items.required' => 'Keranjang belanja wajib diisi.',
      'cart_items.array' => 'Item keranjang harus berupa array.',
      'cart_items.*.required' => 'Item keranjang wajib diisi.',
      'cart_items.*.integer' => 'Item keranjang harus berupa angka.',
      'cart_items.*.exists' => 'Item keranjang tidak valid.',
      'shipping_method.required' => 'Metode pengiriman wajib dipilih.',
      'shipping_method.in' => 'Metode pengiriman tidak valid.',
      'payment_method.required' => 'Metode pembayaran wajib dipilih.',
      'payment_method.in' => 'Metode pembayaran tidak valid.',
      'shipping_address.required' => 'Alamat pengiriman wajib diisi.',
      'shipping_address.string' => 'Alamat pengiriman harus berupa teks.',
      'shipping_address.max' => 'Alamat pengiriman maksimal 255 karakter.',
      'location_id.exists' => 'Lokasi pengiriman tidak valid atau bukan milik Anda.',
      'notes.string' => 'Catatan harus berupa teks.',
      'notes.max' => 'Catatan maksimal 500 karakter.',
    ];
  }

  // /**
  //  * Get custom messages for validator errors.
  //  *
  //  * @return array<string, string>
  //  */
  // public function messages(): array
  // {
  //   return [
  //     'cart_items.required' => 'The cart items field is required.',
  //     'cart_items.array' => 'The cart items must be an array.',
  //     'cart_items.*.required' => 'Each cart item must be a valid ID.',
  //     'cart_items.*.integer' => 'Each cart item must be a valid ID.',
  //     'cart_items.*.exists' => 'One or more cart items do not exist.',
  //     'shipping_method.required' => 'The shipping method field is required.',
  //     'shipping_method.in' => 'The shipping method must be either COD or kurir.',
  //     'payment_method.required' => 'The payment method field is required.',
  //     'payment_method.in' => 'The payment method must be either transfer or COD.',
  //     'shipping_address.required_if' => 'The shipping address is required when shipping method is kurir.',
  //     'shipping_address.string' => 'The shipping address must be a string.',
  //     'shipping_address.max' => 'The shipping address may not be greater than 255 characters.',
  //     'cod_location.required_if' => 'The COD location is required when payment method is COD.',
  //     'cod_location.string' => 'The COD location must be a string.',
  //     'cod_location.max' => 'The COD location may not be greater than 255 characters.',
  //     'notes.string' => 'The notes must be a string.',
  //     'notes.max' => 'The notes may not be greater than 500 characters.',
  //   ];
  // }
}
