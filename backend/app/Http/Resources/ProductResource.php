<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
  public function toArray(Request $request): array
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'slug' => $this->slug,
      'description' => $this->description,
      'price' => $this->price,
      'cost_price' => $this->cost_price,
      'stock' => $this->stock,
      'weight' => $this->weight,
      'status' => $this->status,
      'verified_at' => $this->verified_at?->toDateTimeString(),
      'rejection_reason' => $this->rejection_reason,
      'shop' => $this->whenLoaded('shop', fn() => [
        'id' => $this->shop?->id,
        'name' => $this->shop?->name,
        'slug' => $this->shop?->slug,
        'seller' => $this->when($request->routeIs('admin.*'), [
          'id' => $this->shop?->seller?->id,
          'name' => $this->shop?->seller?->name,
          'email' => $this->shop?->seller?->email,
        ]),
      ]),
      'category' => $this->whenLoaded('category', fn() => [
        'id' => $this->category?->id,
        'name' => $this->category?->name,
      ]),
      'images' => $this->whenLoaded('images', fn() => $this->images->map(fn($image) => [
        'id' => $image->id,
        'url' => $image->image_path ? (str_starts_with($image->image_path, 'http://') || str_starts_with($image->image_path, 'https://') ? $image->image_path : asset('storage/' . $image->image_path)) : null,
      ])),
      'created_at' => $this->created_at?->toDateTimeString(),
    ];
  }
}
