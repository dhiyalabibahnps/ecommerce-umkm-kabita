<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
      'order_number' => $this->order_number,
      'buyer_id' => $this->buyer_id,
      'shop_id' => $this->shop_id,
      'subtotal' => $this->subtotal,
      'shipping_cost' => $this->shipping_cost,
      'total_amount' => $this->total_amount,
      'shipping_method' => $this->shipping_method,
      'courier' => $this->courier,
      'payment_method' => $this->payment_method,
      'status' => $this->status,
      'shipping_address' => $this->shipping_address,
      'tracking_number' => $this->tracking_number,
      'notes' => $this->notes,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at,
      'buyer' => new UserResource($this->whenLoaded('buyer')),
      'shop' => new ShopResource($this->whenLoaded('shop')),
      'items' => OrderItemResource::collection($this->whenLoaded('items')),
      'payment' => new PaymentResource($this->whenLoaded('payment')),
    ];
  }
}
