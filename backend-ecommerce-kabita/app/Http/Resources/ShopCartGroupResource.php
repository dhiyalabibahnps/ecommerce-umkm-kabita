<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopCartGroupResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'shop' => [
        'id' => $this->shop_id,
        'name' => $this->shop_name,
        'slug' => $this->shop_slug,
        'logo' => $this->shop_logo,
      ],
      'items' => CartItemResource::collection($this->items),
      'subtotal' => $this->subtotal,
    ];
  }
}
