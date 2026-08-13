<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
  /**
   * The name of the factory's corresponding model.
   *
   * @var string
   */
  protected $model = Payment::class;

  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'order_id' => Order::factory(),
      'amount' => $this->faker->randomFloat(2, 10000, 1000000),
      'status' => $this->faker->randomElement(array_column(PaymentStatus::cases(), 'value')),
      'proof_image' => $this->faker->optional()->randomElement(['payments/receipt.jpg', null]),
    ];
  }
}
