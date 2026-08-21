<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
  use HasFactory;

  protected $fillable = [
    'order_number',
    'buyer_id',
    'shop_id',
    'subtotal',
    'shipping_cost',
    'total_amount',
    'shipping_method',
    'courier',
    'payment_method',
    'status',
    'shipping_address',
    'tracking_number',
    'notes',
  ];

  protected function casts(): array
  {
    return [
      'subtotal' => 'decimal:2',
      'shipping_cost' => 'decimal:2',
      'total_amount' => 'decimal:2',
      'status' => OrderStatus::class,
    ];
  }

  public function buyer(): BelongsTo
  {
    return $this->belongsTo(User::class, 'buyer_id');
  }

  public function shop(): BelongsTo
  {
    return $this->belongsTo(Shop::class);
  }

  public function items(): HasMany
  {
    return $this->hasMany(OrderItem::class);
  }

  public function payment(): HasOne
  {
    return $this->hasOne(Payment::class);
  }

  public function conversation(): HasOne
  {
    return $this->hasOne(Conversation::class);
  }
}
