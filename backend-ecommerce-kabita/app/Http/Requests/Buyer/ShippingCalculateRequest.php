<?php

declare(strict_types=1);

namespace App\Http\Requests\Buyer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShippingCalculateRequest extends FormRequest
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
      'weight' => ['required', 'numeric', 'min:0'],
      'shipping_method' => ['required', Rule::in(['cod', 'kurir'])],
      'courier_type' => [
        Rule::when(
          $this->input('shipping_method') === 'kurir',
          ['required', Rule::in(['reguler', 'express'])],
          ['nullable', Rule::in(['reguler', 'express'])]
        ),
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
      'weight.required' => 'The weight field is required.',
      'weight.numeric' => 'The weight must be a number.',
      'weight.min' => 'The weight must be at least 0.',
      'shipping_method.required' => 'The shipping method field is required.',
      'shipping_method.in' => 'The shipping method must be either COD or kurir.',
      'courier_type.required' => 'The courier type is required when shipping method is kurir.',
      'courier_type.in' => 'The courier type must be either reguler or express.',
    ];
  }
}
