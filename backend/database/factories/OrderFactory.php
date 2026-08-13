<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Order::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'order_number' => 'ORD-' . Str::upper(Str::random(10)),
      'buyer_id' => User::factory(),
      'shop_id' => Shop::factory(),
      'subtotal' => $this->faker->randomFloat(2, 10, 1000),
      'shipping_cost' => $this->faker->randomFloat(2, 0, 50),
      'total_amount' => $this->faker->randomFloat(2, 10, 1000),
      'shipping_method' => $this->faker->randomElement(['cod', 'kurir']),
      'payment_method' => $this->faker->randomElement(['transfer', 'cod']),
      'status' => $this->faker->randomElement(array_column(OrderStatus::cases(), 'value')),
      'shipping_address' => $this->faker->address,
      'tracking_number' => $this->faker->optional()->uuid,
      'notes' => $this->faker->optional()->sentence,
    ];
  }
}
