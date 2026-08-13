<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
  /**
   * Transform the resource into an array.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return array<string, mixed>
   */
  public function toArray($request): array
  {
    return [
      'id' => $this->id,
      'order_id' => $this->order_id,
      'product_id' => $this->product_id,
      'quantity' => $this->quantity,
      'price_snapshot' => $this->price_snapshot,
      'cost_snapshot' => $this->cost_snapshot,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'product' => new ProductResource($this->whenLoaded('product')),
    ];
  }
}
