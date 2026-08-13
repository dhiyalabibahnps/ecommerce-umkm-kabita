<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\User;
use App\Enums\ShopStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Shop>
 */
class ShopFactory extends Factory
{
    /**
     * The name of the factory corresponding to the model.
     *
     * @var string
     */
    protected $model = Shop::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(2, true);

        return [
            'seller_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name) . '-' . time(),
            'description' => fake()->paragraph(),
            'logo' => null,
            'status' => ShopStatus::VERIFIED,
        ];
    }
}
