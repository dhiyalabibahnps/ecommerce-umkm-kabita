<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// TAMBAHKAN 3 BARIS INI SECARA EKSPLISIT
use App\Models\Category;
use App\Models\Product;
use App\Models\Shop;

class DailyProductSales extends Model
{
  /** @use HasFactory<DailyProductSalesFactory> */
  use HasFactory;

  protected $fillable = [
    'date',
    'product_id',
    'shop_id',
    'category_id',
    'total_qty_sold',
    'total_revenue',
    'total_profit',
  ];

  protected function casts(): array
  {
    return [
      'date' => 'date',
      'total_qty_sold' => 'integer',
      'total_revenue' => 'decimal:2',
      'total_profit' => 'decimal:2',
    ];
  }

  public function product(): BelongsTo
  {
    return $this->belongsTo(Product::class);
  }

  public function shop(): BelongsTo
  {
    return $this->belongsTo(Shop::class);
  }

  public function category(): BelongsTo
  {
    return $this->belongsTo(Category::class);
  }
}
