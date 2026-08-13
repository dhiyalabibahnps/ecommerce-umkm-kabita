<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'seller';
    }

    public function rules(): array
    {
        $productId = $this->route('product');
        $productId = is_object($productId) ? $productId->id : $productId;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'gt:cost_price', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'images' => ['sometimes', 'array'],
            'images.*' => ['image', 'max:5120'],
            'delete_images' => ['sometimes', 'array'],
            'delete_images.*' => ['integer', 'exists:product_images,id'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name' => ['description' => 'Nama produk (opsional).'],
            'description' => ['description' => 'Deskripsi produk (opsional).'],
            'price' => ['description' => 'Harga jual produk (opsional).'],
            'cost_price' => ['description' => 'Harga modal produk (opsional).'],
            'stock' => ['description' => 'Stok produk (opsional).'],
            'weight' => ['description' => 'Berat produk dalam gram (opsional).'],
            'category_id' => ['description' => 'ID kategori produk (opsional).'],
            'images' => ['description' => 'Gambar baru (opsional, max 5MB per file).'],
            'delete_images' => ['description' => 'ID gambar yang ingin dihapus (opsional).'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            if ($this->has('name')) {
                $slug = Str::slug($this->name) . '-' . time();
                if (\App\Models\Product::where('slug', $slug)->where('id', '!=', $this->route('product')->id)->exists()) {
                    $v->errors()->add('name', 'Nama produk sudah digunakan.');
                }
            }
        });
    }
}
