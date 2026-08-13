<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\PaymentSetting $resource
 */
class PaymentSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'bank_name' => $this->resource->bank_name,
            'account_number' => $this->resource->account_number,
            'account_holder_name' => $this->resource->account_holder_name,
            'is_active' => (bool) $this->resource->is_active,
        ];
    }
}
