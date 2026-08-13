<?php

declare(strict_types=1);

namespace App\Http\Requests\Seller;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexOrderRequest extends FormRequest
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
      'status' => [
        'sometimes',
        'string',
        Rule::in(array_column(OrderStatus::cases(), 'value')),
      ],
      'start_date' => [
        'sometimes',
        'date',
        'before_or_equal:end_date',
      ],
      'end_date' => [
        'sometimes',
        'date',
        'after_or_equal:start_date',
      ],
      'sort' => [
        'sometimes',
        'string',
        Rule::in(['newest', 'oldest', 'total_asc', 'total_desc']),
      ],
      'per_page' => [
        'sometimes',
        'integer',
        'min:1',
        'max:100',
      ],
    ];
  }
}
