<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Services\CartCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CartCalculationService();
    }

    public function test_calculate_subtotal_with_single_item(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $cart = Cart::factory()->create(['buyer_id' => $buyer->id]);
        $product = Product::factory()->create(['price' => 50000, 'stock' => 10]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $subtotal = $this->service->calculateSubtotal($cart);

        $this->assertEquals(100000.0, $subtotal);
    }

    public function test_calculate_subtotal_with_multiple_items(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $cart = Cart::factory()->create(['buyer_id' => $buyer->id]);

        $product1 = Product::factory()->create(['price' => 30000, 'stock' => 10]);
        $product2 = Product::factory()->create(['price' => 50000, 'stock' => 20]);
        $product3 = Product::factory()->create(['price' => 20000, 'stock' => 15]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product3->id,
            'quantity' => 3,
        ]);

        $subtotal = $this->service->calculateSubtotal($cart);

        // (30000 * 2) + (50000 * 1) + (20000 * 3) = 60000 + 50000 + 60000 = 170000
        $this->assertEquals(170000.0, $subtotal);
    }

    public function test_calculate_total_matches_subtotal(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $cart = Cart::factory()->create(['buyer_id' => $buyer->id]);
        $product = Product::factory()->create(['price' => 25000, 'stock' => 10]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 4,
        ]);

        $subtotal = $this->service->calculateSubtotal($cart);
        $total = $this->service->calculateTotal($cart, 'cod');

        $this->assertEquals($subtotal, $total);
        $this->assertEquals(100000.0, $total);
    }

    public function test_check_stock_availability_all_available(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $cart = Cart::factory()->create(['buyer_id' => $buyer->id]);
        $product = Product::factory()->create(['price' => 50000, 'stock' => 100]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $result = $this->service->checkStockAvailability($cart);

        $this->assertTrue($result['available']);
        $this->assertEmpty($result['unavailable_items']);
    }

    public function test_check_stock_availability_some_unavailable(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $cart = Cart::factory()->create(['buyer_id' => $buyer->id]);

        $product1 = Product::factory()->create(['price' => 50000, 'stock' => 10]);
        $product2 = Product::factory()->create(['price' => 30000, 'stock' => 2]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 5, // within stock
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 10, // exceeds stock of 2
        ]);

        $result = $this->service->checkStockAvailability($cart);

        $this->assertFalse($result['available']);
        $this->assertCount(1, $result['unavailable_items']);
        $this->assertEquals($product2->id, $result['unavailable_items'][0]['product_id']);
        $this->assertEquals(10, $result['unavailable_items'][0]['requested']);
        $this->assertEquals(2, $result['unavailable_items'][0]['available_stock']);
    }

    public function test_check_stock_availability_all_unavailable(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $cart = Cart::factory()->create(['buyer_id' => $buyer->id]);

        $product = Product::factory()->create(['price' => 50000, 'stock' => 1]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 50,
        ]);

        $result = $this->service->checkStockAvailability($cart);

        $this->assertFalse($result['available']);
        $this->assertCount(1, $result['unavailable_items']);
    }

    public function test_group_items_by_shop_single_shop(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $cart = Cart::factory()->create(['buyer_id' => $buyer->id]);
        $shop = Shop::factory()->create();
        $product1 = Product::factory()->create(['shop_id' => $shop->id, 'price' => 30000]);
        $product2 = Product::factory()->create(['shop_id' => $shop->id, 'price' => 50000]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);

        $groups = $this->service->groupItemsByShop($cart);

        $this->assertCount(1, $groups);
        $this->assertEquals($shop->id, $groups[0]['shop_id']);
        $this->assertEquals($shop->name, $groups[0]['shop_name']);
        $this->assertCount(2, $groups[0]['items']);
        // (30000 * 2) + (50000 * 1) = 110000
        $this->assertEquals(110000.0, $groups[0]['subtotal']);
    }

    public function test_group_items_by_shop_multiple_shops(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $cart = Cart::factory()->create(['buyer_id' => $buyer->id]);

        $shop1 = Shop::factory()->create(['name' => 'Shop A']);
        $shop2 = Shop::factory()->create(['name' => 'Shop B']);

        $product1 = Product::factory()->create(['shop_id' => $shop1->id, 'price' => 25000]);
        $product2 = Product::factory()->create(['shop_id' => $shop2->id, 'price' => 40000]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 3,
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 2,
        ]);

        $groups = $this->service->groupItemsByShop($cart);

        $this->assertCount(2, $groups);

        $shopA = collect($groups)->firstWhere('shop_id', $shop1->id);
        $shopB = collect($groups)->firstWhere('shop_id', $shop2->id);

        $this->assertNotNull($shopA);
        $this->assertNotNull($shopB);

        $this->assertEquals('Shop A', $shopA['shop_name']);
        $this->assertEquals('Shop B', $shopB['shop_name']);

        $this->assertCount(1, $shopA['items']);
        $this->assertCount(1, $shopB['items']);

        // Shop A: 25000 * 3 = 75000
        $this->assertEquals(75000.0, $shopA['subtotal']);
        // Shop B: 40000 * 2 = 80000
        $this->assertEquals(80000.0, $shopB['subtotal']);
    }

    public function test_group_items_by_shop_item_structure(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $cart = Cart::factory()->create(['buyer_id' => $buyer->id]);
        $shop = Shop::factory()->create(['name' => 'Test Shop']);
        $product = Product::factory()->create([
            'shop_id' => $shop->id,
            'price' => 15000,
            'name' => 'Test Product',
        ]);
        $cartItem = CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 4,
        ]);

        $groups = $this->service->groupItemsByShop($cart);

        $items = $groups[0]['items'];
        $this->assertCount(1, $items);

        $item = $items->first();
        $this->assertEquals($cartItem->id, $item->id);
        $this->assertEquals($product->id, $item->product_id);
        $this->assertEquals('Test Product', $item->product->name);
        $this->assertEquals(4, $item->quantity);
        $this->assertEquals(15000.0, $item->product->price);
        $this->assertEquals(60000.0, $groups[0]['subtotal']);
    }

    public function test_empty_cart_returns_zero_subtotal(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $cart = Cart::factory()->create(['buyer_id' => $buyer->id]);

        $subtotal = $this->service->calculateSubtotal($cart);

        $this->assertEquals(0.0, $subtotal);
    }

    public function test_empty_cart_is_available(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $cart = Cart::factory()->create(['buyer_id' => $buyer->id]);

        $result = $this->service->checkStockAvailability($cart);

        $this->assertTrue($result['available']);
        $this->assertEmpty($result['unavailable_items']);
    }

    public function test_empty_cart_returns_empty_groups(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $cart = Cart::factory()->create(['buyer_id' => $buyer->id]);

        $groups = $this->service->groupItemsByShop($cart);

        $this->assertIsArray($groups);
        $this->assertEmpty($groups);
    }

    public function test_calculate_shipping_cost_cod_returns_zero(): void
    {
        $cost = $this->service->calculateShippingCostWithWeight('cod', 1000, 'reguler');
        $this->assertEquals(0.0, $cost);
    }

    public function test_calculate_shipping_cost_courier_reguler_with_weight(): void
    {
        // Weight: 2000g = 2kg, base_rate: 10000, price_per_kg: 5000
        // Expected: 10000 + (2 * 5000) = 20000
        $cost = $this->service->calculateShippingCostWithWeight('kurir', 2000, 'reguler');
        $this->assertEquals(20000.0, $cost);
    }

    public function test_calculate_shipping_cost_courier_express_with_weight(): void
    {
        // Weight: 1000g = 1kg, base_rate: 20000, price_per_kg: 5000
        // Expected: 20000 + (1 * 5000) = 25000
        $cost = $this->service->calculateShippingCostWithWeight('kurir', 1000, 'express');
        $this->assertEquals(25000.0, $cost);
    }

    public function test_calculate_shipping_cost_defaults_to_reguler(): void
    {
        // Weight: 1000g = 1kg, default courier_type: reguler
        // Expected: 10000 + (1 * 5000) = 15000
        $cost = $this->service->calculateShippingCostWithWeight('kurir', 1000, null);
        $this->assertEquals(15000.0, $cost);
    }

    public function test_calculate_shipping_cost_without_weight_uses_base_rate(): void
    {
        // No weight provided, should use base rate only
        $cost = $this->service->calculateShippingCostWithWeight('kurir', null, 'reguler');
        $this->assertEquals(10000.0, $cost);
    }

    public function test_calculate_cart_weight_with_single_item(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $cart = Cart::factory()->create(['buyer_id' => $buyer->id]);
        $product = Product::factory()->create(['weight' => 500, 'stock' => 10]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $weight = $this->service->calculateCartWeight($cart);
        $this->assertEquals(1500, $weight); // 500g * 3 = 1500g
    }

    public function test_calculate_cart_weight_with_multiple_items(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $cart = Cart::factory()->create(['buyer_id' => $buyer->id]);

        $product1 = Product::factory()->create(['weight' => 200, 'stock' => 10]);
        $product2 = Product::factory()->create(['weight' => 500, 'stock' => 10]);

        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->id,
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);

        $weight = $this->service->calculateCartWeight($cart);
        $this->assertEquals(900, $weight); // (200 * 2) + (500 * 1) = 900g
    }

    public function test_calculate_cart_weight_returns_zero_for_empty_cart(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $cart = Cart::factory()->create(['buyer_id' => $buyer->id]);

        $weight = $this->service->calculateCartWeight($cart);
        $this->assertEquals(0, $weight);
    }
}
