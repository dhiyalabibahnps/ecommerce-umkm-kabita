<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = OrderItem::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'order_id' => Order::factory(),
      'product_id' => Product::factory(),
      'quantity' => $this->faker->numberBetween(1, 10),
      'price_snapshot' => $this->faker->randomFloat(2, 1, 1000),
      'cost_snapshot' => $this->faker->randomFloat(2, 1, 1000),
    ];
  }
}
