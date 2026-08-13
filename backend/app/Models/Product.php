<?php

namespace App\Models;

use Database\Factories\ProductFactory;
use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read Shop $shop
 * @property-read Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<ProductImage> $images
 */

class Product extends Model
{
  /** @use HasFactory<ProductFactory> */
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'shop_id',
    'category_id',
    'name',
    'slug',
    'description',
    'price',
    'cost_price',
    'stock',
    'status',
    'weight',
    'verified_at',
    'rejection_reason',
  ];

  protected function casts(): array
  {
    return [
      'price' => 'decimal:2',
      'cost_price' => 'decimal:2',
      'stock' => 'integer',
      'weight' => 'integer',
      'verified_at' => 'datetime',
      'status' => ProductStatus::class,
    ];
  }

  public function shop(): BelongsTo
  {
    return $this->belongsTo(Shop::class);
  }

  public function category(): BelongsTo
  {
    return $this->belongsTo(Category::class);
  }

  public function images(): HasMany
  {
    return $this->hasMany(ProductImage::class)->orderBy('order_column');
  }

  public function cartItems(): HasMany
  {
    return $this->hasMany(CartItem::class);
  }

  public function orderItems(): HasMany
  {
    return $this->hasMany(OrderItem::class);
  }

  public function dailySales(): HasMany
  {
    return $this->hasMany(DailyProductSales::class);
  }

  /**
   * Check if product has any active orders (non-cancelled).
   */
  public function hasActiveOrders(): bool
  {
    return $this->orderItems()
      ->whereHas('order', function ($query) {
        $query->whereNotIn('status', ['cancelled']);
      })
      ->exists();
  }

  /**
   * Check if product is available for purchase (approved and has stock).
   */
  public function isAvailableForPurchase(): bool
  {
    return $this->status === ProductStatus::APPROVED && $this->stock > 0;
  }

  /**
   * Delete associated images from storage when product is deleted.
   */
  protected static function boot(): void
  {
    parent::boot();

    static::deleting(function (Product $product) {
      foreach ($product->images as $image) {
        \Illuminate\Support\Facades\Storage::disk('public')->delete($image->image_path);
      }
    });
  }
}
