<?php

namespace App\Http\Requests\Buyer;

use Illuminate\Foundation\Http\FormRequest;

class StoreCodLocationRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'address' => 'required|string|min:10|max:500',
            'phone' => 'nullable|string|min:10|max:16',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'is_default' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama lokasi wajib diisi.',
            'name.string' => 'Nama lokasi harus berupa teks.',
            'name.max' => 'Nama lokasi maksimal 255 karakter.',
            "phone.required" => 'Nomor telepon wajib diisi',
            "phone.min" => 'Nomor telepon minimal 10 karakter',
            "phone.max" => 'Nomor telepon maksimal 15 karakter',
            'address.required' => 'Alamat lokasi wajib diisi.',
            'address.string' => 'Alamat harus berupa teks.',
            'address.min' => 'Alamat minimal 10 karakter.',
            'address.max' => 'Alamat maksimal 500 karakter.',
            'latitude.string' => 'Latitude harus berupa string.',
            'longitude.string' => 'Longitude harus berupa string.',
            'is_default.boolean' => 'Status default harus berupa true/false.',
        ];
    }
}
