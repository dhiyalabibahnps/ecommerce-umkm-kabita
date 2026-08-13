<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\PaymentSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read array<string, string> $messages
 */
class UpdatePaymentSettingRequest extends FormRequest
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
     * @return array<string, array<int, string|Rule>>
     */
    public function rules(): array
    {
        $paymentSetting = $this->route('paymentSetting');

        return [
            'bank_name' => ['required', 'string', 'max:255'],
            'account_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_settings', 'account_number')->ignore($paymentSetting),
            ],
            'account_holder_name' => ['required', 'string', 'max:255'],
            'is_active' => ['boolean'],
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
            'bank_name.required' => 'Nama bank wajib diisi.',
            'bank_name.string' => 'Nama bank harus berupa teks.',
            'bank_name.max' => 'Nama bank maksimal 255 karakter.',
            'account_number.required' => 'Nomor rekening wajib diisi.',
            'account_number.string' => 'Nomor rekening harus berupa teks.',
            'account_number.max' => 'Nomor rekening maksimal 255 karakter.',
            'account_number.unique' => 'Nomor rekening sudah terdaftar.',
            'account_holder_name.required' => 'Nama pemilik rekening wajib diisi.',
            'account_holder_name.string' => 'Nama pemilik rekening harus berupa teks.',
            'account_holder_name.max' => 'Nama pemilik rekening maksimal 255 karakter.',
            'is_active.boolean' => 'Status aktif harus berupa boolean.',
        ];
    }
}
