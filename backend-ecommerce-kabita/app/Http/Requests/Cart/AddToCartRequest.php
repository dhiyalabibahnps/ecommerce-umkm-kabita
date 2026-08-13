<?php

declare(strict_types=1);

namespace App\Http\Requests\Cart;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
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
      'product_id' => [
        'required',
        'integer',
        'exists:products,id',
        function (string $attribute, mixed $value, callable $fail) {
          $product = Product::find($value);

          if (!$product) {
            $fail('Produk tidak ditemukan.');
          } elseif (!$product->isAvailableForPurchase()) {
            $fail('Produk tidak tersedia untuk dibeli.');
          }
        },
      ],
      'quantity' => [
        'required',
        'integer',
        'min:1',
        function (string $attribute, mixed $value, callable $fail) {
          $productId = $this->input('product_id');
          $product = Product::find($productId);

          if ($product && $value > $product->stock) {
            $fail('Jumlah yang diminta melebihi stok yang tersedia.');
          }
        },
      ],
    ];
  }

  /**
   * Get custom messages for validator errors.
   *
   * @return array<string, string>
   */
  public function messages(): array
  {
    return [
      'product_id.required' => 'ID produk harus diisi.',
      'product_id.exists' => 'Produk tidak ditemukan.',
      'quantity.required' => 'Jumlah barang harus diisi.',
      'quantity.integer' => 'Jumlah barang harus berupa angka.',
      'quantity.min' => 'Jumlah barang minimal adalah 1.',
    ];
  }
}
