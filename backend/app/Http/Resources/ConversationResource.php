<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversationResource extends JsonResource
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
            'order_id' => $this->order_id,
            'buyer_id' => $this->buyer_id,
            'seller_id' => $this->seller_id,
            'shop_id' => $this->shop_id,
            'buyer' => new UserResource($this->whenLoaded('buyer')),
            'seller' => new UserResource($this->whenLoaded('seller')),
            'shop' => new ShopResource($this->whenLoaded('shop')),
            'order' => new OrderResource($this->whenLoaded('order')),
            'messages' => MessageResource::collection($this->whenLoaded('messages')),
            'last_message_at' => $this->last_message_at?->toISOString() ?? $this->last_message_at,
            'created_at' => $this->created_at?->toISOString() ?? $this->created_at,
        ];
    }
}
