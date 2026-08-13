<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cart;
use App\Enums\ProductStatus;
use Illuminate\Support\Collection;

class CartCalculationService
{
  /**
   * Calculate subtotal of all cart items (price * quantity).
   */
  public function calculateSubtotal(Cart $cart): float
  {
    return $this->getItemsWithProducts($cart)
      ->sum(fn($item) => (float) $item->product->price * $item->quantity);
  }

  /**
   * Check stock availability for all cart items.
   *
   * @return array{available: bool, unavailable_items: array<int, array{id: int, product_id: int, product_name: string, requested: int, available_stock: int}>}
   */
  public function checkStockAvailability(Cart $cart): array
  {
    $unavailableItems = [];

    $items = $this->getItemsWithProducts($cart);

    foreach ($items as $item) {
      $product = $item->product;

      // Only check products that are approved and have stock capacity
      if ($product->status !== ProductStatus::APPROVED) {
        continue;
      }

      if ($item->quantity > $product->stock) {
        $unavailableItems[] = [
          'id' => $item->id,
          'product_id' => $item->product_id,
          'product_name' => $product->name,
          'requested' => $item->quantity,
          'available_stock' => $product->stock,
        ];
      }
    }

    return [
      'available' => empty($unavailableItems),
      'unavailable_items' => $unavailableItems,
    ];
  }

  /**
   * Group cart items by shop for multi-seller checkout.
   *
   * @return array<int, array{shop_id: int, shop_name: string, shop_slug: ?string, shop_logo: ?string, items: Collection<int, CartItem>, subtotal: float}>
   */
  public function groupItemsByShop(Cart $cart): array
  {
    $items = $this->getItemsWithProducts($cart)->load('product.shop');

    $grouped = $items->groupBy(fn($item) => $item->product->shop_id);

    $result = [];

    foreach ($grouped as $shopId => $shopItems) {
      $firstProduct = $shopItems->first();
      $shop = $firstProduct?->product?->shop;

      $result[] = [
        'shop_id' => $shopId,
        'shop_name' => $shop?->name ?? 'Unknown Shop',
        'shop_slug' => $shop?->slug,
        'shop_logo' => $shop?->logo ? asset('storage/' . $shop->logo) : null,
        'items' => $shopItems,
        'subtotal' => $shopItems->sum(fn($item) => (float) $item->product->price * $item->quantity),
      ];
    }

    return $result;
  }

  /**
   * Calculate shipping cost based on shipping method.
   *
   * @param string $shippingMethod
   * @return float
   */
  public function calculateShippingCost(string $shippingMethod): float
  {
    return $shippingMethod === 'kurir' ? 10000.0 : 0.0;
  }

  /**
   * Calculate shipping cost with weight-based pricing.
   *
   * @param string $shippingMethod
   * @param int|null $weight Weight in grams (nullable for backward compatibility)
   * @param string|null $courierType 'reguler' or 'express' (default: 'reguler')
   * @return float
   */
  public function calculateShippingCostWithWeight(string $shippingMethod, ?int $weight = null, ?string $courierType = null): float
  {
    if ($shippingMethod === 'cod') {
      return 0.0;
    }

    $courierType = $courierType ?? 'reguler';

    $baseRates = [
      'reguler' => 10000,
      'express' => 20000,
    ];

    $pricePerKg = 5000;

    // If weight is not provided, use default base rate
    if ($weight === null || $weight <= 0) {
      return (float) $baseRates[$courierType];
    }

    // Calculate: base_rate + (weight_grams / 1000 * price_per_kg)
    $weightInKg = $weight / 1000;
    return $baseRates[$courierType] + ($weightInKg * $pricePerKg);
  }

  /**
   * Calculate total cart weight in grams.
   *
   * @param Cart $cart
   * @return int Total weight in grams
   */
  public function calculateCartWeight(Cart $cart): int
  {
    $items = $this->getItemsWithProducts($cart);

    return (int) $items->sum(function ($item) {
      $productWeight = $item->product->weight ?? 0;
      return $productWeight * $item->quantity;
    });
  }

  /**
   * Calculate total amount including shipping cost.
   */
  public function calculateTotal(Cart $cart, string $shippingMethod): float
  {
    return $this->calculateSubtotal($cart) + $this->calculateShippingCost($shippingMethod);
  }

  /**
   * Get cart items with their associated products loaded.
   */
  protected function getItemsWithProducts(Cart $cart): Collection
  {
    return $cart->items()->with('product')->get();
  }
}
