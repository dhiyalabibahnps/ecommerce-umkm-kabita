<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @return array<string, mixed>
   */
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'cart_id' => $this->cart_id,
      'product_id' => $this->product_id,
      'quantity' => $this->quantity,
      'product' => new ProductResource($this->whenLoaded('product')),
      'subtotal' => $this->whenLoaded('product', fn() => $this->product->price * $this->quantity),
      'created_at' => $this->created_at?->toDateTimeString(),
      'updated_at' => $this->updated_at?->toDateTimeString(),
    ];
  }
}
